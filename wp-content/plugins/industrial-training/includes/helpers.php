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
const ITP_EMAIL_RE = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
const ITP_PHONE_RE = '/^\+?[0-9 ()-]{7,20}$/';

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
		'year'     => $get( 'year' ),
		'track'    => $get( 'track' ),
	];
	$errors = [];

	if ( '' === $clean['fullName'] ) {
		$errors['fullName'] = __( 'Please enter your full name.', 'industrial-training' );
	}
	if ( ! preg_match( ITP_EMAIL_RE, $clean['email'] ) ) {
		$errors['email'] = __( 'Please enter a valid email address.', 'industrial-training' );
	}
	if ( ! preg_match( ITP_PHONE_RE, $clean['phone'] ) ) {
		$errors['phone'] = __( 'Please enter a valid phone number.', 'industrial-training' );
	}
	if ( '' === $clean['college'] ) {
		$errors['college'] = __( 'Please enter your college or polytechnic.', 'industrial-training' );
	}
	if ( ! array_key_exists( $clean['branch'], itp_branches() ) ) {
		$errors['branch'] = __( 'Please choose your branch.', 'industrial-training' );
	}
	if ( ! array_key_exists( $clean['year'], itp_years() ) ) {
		$errors['year'] = __( 'Please choose your year of study.', 'industrial-training' );
	}
	if ( ! in_array( $clean['track'], itp_track_names(), true ) ) {
		$errors['track'] = __( 'Please choose a training track.', 'industrial-training' );
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
	$url    = static fn( $w ) => ITP_URL . 'assets/images/photos/' . $photo . '-' . $w . '.webp?v=' . ITP_VERSION;
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
