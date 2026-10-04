<?php
/**
 * Shared helpers: settings access, allowed values, validation, images.
 */

defined( 'ABSPATH' ) || exit;

/** Merges saved settings over defaults (lists such as tracks are replaced, not merged). */
function itp_merge( array $defaults, array $saved ): array {
	foreach ( $saved as $key => $value ) {
		if ( is_array( $value ) && isset( $defaults[ $key ] ) && is_array( $defaults[ $key ] ) && ! array_is_list( $defaults[ $key ] ) ) {
			$defaults[ $key ] = itp_merge( $defaults[ $key ], $value );
		} else {
			$defaults[ $key ] = $value;
		}
	}
	return $defaults;
}

/** Valid addresses from a comma/semicolon/space separated list. */
function itp_email_list( $value ): array {
	return array_values( array_unique( array_filter( array_map( 'sanitize_email', preg_split( '/[\s,;]+/', (string) $value ) ), 'is_email' ) ) );
}

/**
 * Cache-busting version for a plugin file: changes whenever the file does, so CDNs (Hostinger's caches
 * for 7 days) and browsers fetch the new copy after every upload.
 */
function itp_asset_ver( string $path ): string {
	$time = @filemtime( ITP_DIR . $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	return ITP_VERSION . ( $time ? '.' . $time : '' );
}

function itp_settings(): array {
	static $cache = null;
	if ( null === $cache ) {
		$saved = get_option( ITP_OPTION, [] );
		$cache = itp_merge( itp_defaults(), is_array( $saved ) ? $saved : [] );
	}
	return $cache;
}

/** Stored value => translated label. Stored values stay English so the data matches the existing API. */
function itp_branches(): array {
	return [
		'Mechanical'       => __( 'Mechanical', 'industrial-training' ),
		'Electrical'       => __( 'Electrical', 'industrial-training' ),
		'Electronics'      => __( 'Electronics', 'industrial-training' ),
		'Civil'            => __( 'Civil', 'industrial-training' ),
		'Computer Science' => __( 'Computer Science', 'industrial-training' ),
		'Automobile'       => __( 'Automobile', 'industrial-training' ),
		'Other'            => __( 'Other', 'industrial-training' ),
	];
}

function itp_years(): array {
	return [
		'1st Year'   => __( '1st Year', 'industrial-training' ),
		'2nd Year'   => __( '2nd Year', 'industrial-training' ),
		'3rd Year'   => __( '3rd Year', 'industrial-training' ),
		'Passed Out' => __( 'Passed Out', 'industrial-training' ),
	];
}

function itp_track_names(): array {
	return array_values( array_filter( wp_list_pluck( itp_settings()['tracks']['items'], 'name' ) ) );
}

function itp_skills( string $skills ): array {
	return array_values( array_filter( array_map( 'trim', preg_split( '/[,\n]/', $skills ) ) ) );
}

/** Field rules — the same regexes run in assets/js/itp.js. */
// name@domain.tld — letters/digits/._%+- before @, a real domain after it, and a 2+ letter ending (.com, .in …).
const ITP_EMAIL_RE = '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?(?:\.[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?)*\.[A-Za-z]{2,}$/';

/** Indian mobile number as 10 digits: drops spaces/dashes and a leading +91, 91 or 0. '' if it can't be read. */
function itp_phone_digits( string $raw ): string {
	$d = preg_replace( '/[\s().-]/', '', $raw );
	$d = preg_replace( '/^(?:\+91|0091|91(?=\d{10}$)|0(?=\d{10}$))/', '', $d );
	return preg_match( '/^\d{10}$/', $d ) ? $d : '';
}

/**
 * How a valid number is saved: typed with +91 → "+91" + 10 digits (13 characters), e.g. +919876543210;
 * typed any other way (9876543210, 09876543210, 98765 43210) → the 10 digits only.
 */
function itp_phone_store( string $raw ): string {
	$digits = itp_phone_digits( $raw );
	return str_starts_with( preg_replace( '/[\s().-]/', '', $raw ), '+91' ) ? '+91' . $digits : $digits;
}

/** Error message for a phone number, or '' when it is a valid mobile number. */
function itp_phone_error( string $raw ): string {
	$d = itp_phone_digits( $raw );
	if ( '' === $d ) {
		return __( 'Please enter a 10-digit mobile number.', 'industrial-training' );
	}
	if ( ! preg_match( '/^[6-9]/', $d ) ) {
		return __( 'Mobile number must start with 6, 7, 8 or 9.', 'industrial-training' );
	}
	if ( preg_match( '/(\d)\1{7}/', $d ) ) {
		return __( 'Please enter a real mobile number (the same digit can’t repeat 8 times).', 'industrial-training' );
	}
	return '';
}

/** Error message for an email address, or '' when it looks valid. */
function itp_email_error( string $email ): string {
	if ( '' === $email ) {
		return __( 'Please enter your email address.', 'industrial-training' );
	}
	if ( ! preg_match( ITP_EMAIL_RE, $email ) || str_contains( $email, '..' ) || ! is_email( $email ) ) {
		return __( 'Please enter a valid email address, e.g. name@gmail.com.', 'industrial-training' );
	}
	return '';
}

/**
 * Sanitizes and validates a submission.
 *
 * @return array{0: array<string,string>, 1: array<string,string>} [ clean data, field => error message ]
 */
function itp_validate( array $input ): array {
	$get   = static fn( $key ) => isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ? trim( sanitize_text_field( wp_unslash( (string) $input[ $key ] ) ) ) : '';
	$clean = [
		'fullName' => mb_substr( $get( 'fullName' ), 0, 100 ),
		'email'    => mb_substr( $get( 'email' ), 0, 254 ),
		'phone'    => mb_substr( $get( 'phone' ), 0, 20 ),
		'college'  => mb_substr( $get( 'college' ), 0, 150 ),
		'branch'   => $get( 'branch' ),
	];
	$errors = [];

	if ( '' === $clean['fullName'] ) {
		$errors['fullName'] = __( 'Please enter your full name.', 'industrial-training' );
	}
	if ( '' !== ( $msg = itp_email_error( $clean['email'] ) ) ) {
		$errors['email'] = $msg;
	}
	if ( '' !== ( $msg = itp_phone_error( $clean['phone'] ) ) ) {
		$errors['phone'] = $msg;
	} else {
		$clean['phone'] = itp_phone_store( $clean['phone'] );
	}
	if ( '' === $clean['college'] ) {
		$errors['college'] = __( 'Please enter your college or polytechnic.', 'industrial-training' );
	}
	if ( ! array_key_exists( $clean['branch'], itp_branches() ) ) {
		$errors['branch'] = __( 'Please choose your branch.', 'industrial-training' );
	}

	return [ $clean, $errors ];
}

/** Replaces {placeholders} in a template string. Values are not escaped here. */
function itp_fill( string $template, array $vars ): string {
	return strtr( $template, array_combine( array_map( static fn( $k ) => '{' . $k . '}', array_keys( $vars ) ), $vars ) );
}

/**
 * Responsive image: Media Library attachment if set, else the Unsplash placeholder.
 *
 * @param int    $id    Attachment ID (0 = use placeholder).
 * @param string $photo Placeholder key from itp_default_photos().
 * @param int[]  $ratio [ width, height ] aspect ratio for the placeholder crop.
 * @param string $sizes sizes attribute.
 * @param array  $attr  Extra attributes (class, loading, fetchpriority…).
 */
function itp_image( int $id, string $photo, array $ratio, string $sizes, array $attr = [] ): string {
	$attr += [ 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => $sizes ];

	if ( $id && wp_attachment_is_image( $id ) ) {
		return wp_get_attachment_image( $id, 'full', false, $attr );
	}

	$photos = itp_default_photos();
	if ( ! isset( $photos[ $photo ] ) ) {
		return '';
	}
	// Default photos ship with the plugin (assets/images/photos/<key>-<width>.webp), already cropped to the slot's ratio.
	$alt    = $photos[ $photo ][1];
	$url    = static fn( $w ) => ITP_URL . 'assets/images/photos/' . $photo . '-' . $w . '.webp?v=' . itp_asset_ver( 'assets/images/photos/' . $photo . '-' . $w . '.webp' );
	$srcset = implode( ', ', array_map( static fn( $w ) => $url( $w ) . ' ' . $w . 'w', [ 480, 800, 1200 ] ) );

	$attr = [
		'src'    => $url( 800 ),
		'srcset' => $srcset,
		'width'  => 800,
		'height' => (int) round( 800 * $ratio[1] / $ratio[0] ),
		'alt'    => $alt,
	] + $attr;

	$html = '<img';
	foreach ( $attr as $name => $value ) {
		if ( false !== $value && null !== $value ) {
			$html .= ' ' . $name . '="' . ( 'src' === $name ? esc_url( $value ) : esc_attr( $value ) ) . '"';
		}
	}
	return $html . '>';
}

/** Full URL of the hero photo, for Open Graph. */
function itp_hero_image_url(): string {
	$id = (int) itp_settings()['hero']['image'];
	if ( $id && ( $src = wp_get_attachment_image_url( $id, 'large' ) ) ) {
		return $src;
	}
	return ITP_URL . 'assets/images/photos/hero-og.jpg';
}

/** Whether the current request renders the landing page (shortcode, block or plugin template). */
function itp_is_landing(): bool {
	static $result = null;
	if ( null !== $result ) {
		return $result;
	}
	$post   = is_singular() ? get_queried_object() : null;
	$result = $post instanceof WP_Post && (
		has_shortcode( $post->post_content, 'industrial_training' )
		|| has_block( 'industrial-training/landing', $post )
		|| ITP_TEMPLATE === get_page_template_slug( $post )
		|| str_contains( (string) get_post_meta( $post->ID, '_elementor_data', true ), 'industrial_training' )
	);
	/** Filter: force-load assets/SEO on pages the detection misses (e.g. other page builders). */
	$result = (bool) apply_filters( 'itp_is_landing', $result, $post );
	return $result;
}
