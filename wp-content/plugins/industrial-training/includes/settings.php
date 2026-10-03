<?php
/**
 * Settings → Industrial Training: every text, stat, track, contact detail and image.
 */

defined( 'ABSPATH' ) || exit;

const ITP_PAGE = 'industrial-training';

add_action( 'admin_menu', function () {
	add_options_page(
		__( 'Industrial Training', 'industrial-training' ),
		__( 'Industrial Training', 'industrial-training' ),
		'manage_options',
		ITP_PAGE,
		'itp_settings_page'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'itp_settings_group', ITP_OPTION, [
		'type'              => 'array',
		'sanitize_callback' => 'itp_sanitize_settings',
		'default'           => itp_defaults(),
	] );
} );

add_filter( 'plugin_action_links_' . plugin_basename( ITP_FILE ), function ( $links ) {
	array_unshift( $links, sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'options-general.php?page=' . ITP_PAGE ) ), esc_html__( 'Settings', 'industrial-training' ) ) );
	return $links;
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'settings_page_' . ITP_PAGE !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'itp-admin', ITP_URL . 'assets/css/admin.css', [], itp_asset_ver( 'assets/css/admin.css' ) );
	wp_enqueue_script( 'itp-admin', ITP_URL . 'assets/js/admin.js', [], itp_asset_ver( 'assets/js/admin.js' ), true );
	wp_localize_script( 'itp-admin', 'itpAdmin', [
		'choose' => __( 'Choose image', 'industrial-training' ),
		'use'    => __( 'Use this image', 'industrial-training' ),
		'remove' => __( 'Remove this track?', 'industrial-training' ),
	] );
} );

/* ---------- Sanitising ---------- */

function itp_sanitize_value( string $key, $value ) {
	if ( is_array( $value ) ) {
		return '';
	}
	$value = (string) $value;
	switch ( $key ) {
		case 'image':
		case 'inset_image':
		case 'weeks':
			return absint( $value );
		case 'email':
			return sanitize_email( $value );
		case 'notify_email':
			return implode( ', ', itp_email_list( $value ) );
		case 'credits':
			return wp_kses( $value, [ 'a' => [ 'href' => true, 'rel' => true, 'target' => true ] ] );
		case 'lead':
		case 'text':
		case 'summary':
		case 'description':
		case 'success_text':
		case 'skills':
		case 'address':
		case 'cta_text':
			return sanitize_textarea_field( $value );
		case 'photo':
			return array_key_exists( $value, itp_default_photos() ) ? $value : '';
		default:
			return sanitize_text_field( $value );
	}
}

/** Sanitises $input using the shape of $defaults (unknown keys are dropped). */
function itp_sanitize_tree( array $defaults, $input ): array {
	$input = is_array( $input ) ? $input : [];
	$out   = [];
	foreach ( $defaults as $key => $default ) {
		if ( is_array( $default ) ) {
			if ( array_is_list( $default ) ) {
				// Fixed-length lists (stats, highlights, steps): keep the default count.
				$items = array_values( is_array( $input[ $key ] ?? null ) ? $input[ $key ] : [] );
				foreach ( $default as $i => $row ) {
					$out[ $key ][ $i ] = itp_sanitize_tree( $row, $items[ $i ] ?? $row );
				}
			} else {
				$out[ $key ] = itp_sanitize_tree( $default, $input[ $key ] ?? $default );
			}
		} else {
			$out[ $key ] = array_key_exists( $key, $input ) ? itp_sanitize_value( (string) $key, $input[ $key ] ) : $default;
		}
	}
	return $out;
}

function itp_sanitize_settings( $input ): array {
	$input    = is_array( $input ) ? $input : [];
	$defaults = itp_defaults();
	$tracks   = $input['tracks']['items'] ?? [];
	unset( $defaults['tracks']['items'] );

	$out = itp_sanitize_tree( $defaults, $input );

	// Tracks: repeatable, order = submitted order, rows without a name are dropped.
	$blank                  = [ 'name' => '', 'branch' => '', 'weeks' => 6, 'summary' => '', 'skills' => '', 'photo' => '', 'image' => 0 ];
	$out['tracks']['items'] = [];
	foreach ( is_array( $tracks ) ? $tracks : [] as $row ) {
		$row = itp_sanitize_tree( $blank, $row );
		if ( '' !== $row['name'] ) {
			$row['weeks']             = max( 1, min( 52, $row['weeks'] ) );
			$out['tracks']['items'][] = $row;
		}
	}

	// Checkboxes are absent from the POST when unchecked.
	$out['seo']['enabled']          = empty( $input['seo']['enabled'] ) ? 0 : 1;
	$out['form']['confirm_student'] = empty( $input['form']['confirm_student'] ) ? 0 : 1;
	$out['samples']['show']         = empty( $input['samples']['show'] ) ? 0 : 1;

	return $out;
}

/* ---------- Import placeholder photos into the Media Library ---------- */

add_action( 'admin_post_itp_import_photos', function () {
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'upload_files' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'industrial-training' ), 403 );
	}
	check_admin_referer( 'itp_import_photos' );

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$saved    = get_option( ITP_OPTION, [] );
	$settings = itp_merge( itp_defaults(), is_array( $saved ) ? $saved : [] );
	$ids      = [];
	$failed   = 0;

	foreach ( itp_default_photos() as $key => [ , $alt ] ) {
		// Copies the bundled photo; sideloading moves the file, so it must be a temp copy.
		$tmp = wp_tempnam( 'itp-' . $key );
		if ( ! $tmp || ! copy( ITP_DIR . 'assets/images/photos/' . $key . '-1200.webp', $tmp ) ) {
			++$failed;
			continue;
		}
		$id = media_handle_sideload( [ 'name' => 'itp-' . $key . '.webp', 'tmp_name' => $tmp ], 0, $alt );
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			++$failed;
			continue;
		}
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		$ids[ $key ] = $id;
	}

	// Only fill slots that have no image yet.
	$fill = static function ( &$slot, $key ) use ( $ids ) {
		if ( empty( $slot ) && isset( $ids[ $key ] ) ) {
			$slot = $ids[ $key ];
		}
	};
	$fill( $settings['hero']['image'], 'hero' );
	$fill( $settings['hero']['inset_image'], 'inset' );
	$fill( $settings['contact']['image'], 'contact' );
	foreach ( $settings['tracks']['items'] as &$track ) {
		$fill( $track['image'], $track['photo'] ?? '' );
	}
	unset( $track );
	foreach ( $settings['offers']['items'] as &$offer ) {
		$fill( $offer['image'], $offer['photo'] ?? '' );
	}
	unset( $offer );
	update_option( ITP_OPTION, $settings );

	wp_safe_redirect( add_query_arg( [ 'page' => ITP_PAGE, 'itp_imported' => count( $ids ), 'itp_failed' => $failed ], admin_url( 'options-general.php' ) ) );
	exit;
} );

/* ---------- Field helpers ---------- */

function itp_name( array $path ): string {
	return ITP_OPTION . '[' . implode( '][', $path ) . ']';
}

function itp_id( array $path ): string {
	return 'itp-' . implode( '-', $path );
}

function itp_value( array $settings, array $path ) {
	foreach ( $path as $key ) {
		$settings = $settings[ $key ] ?? '';
	}
	return $settings;
}

function itp_field( array $s, array $path, string $label, string $type = 'text', string $help = '' ): void {
	$value = itp_value( $s, $path );
	$id    = itp_id( $path );
	echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th><td>';
	if ( 'textarea' === $type ) {
		printf( '<textarea class="large-text" rows="3" id="%1$s" name="%2$s">%3$s</textarea>', esc_attr( $id ), esc_attr( itp_name( $path ) ), esc_textarea( (string) $value ) );
	} elseif ( 'checkbox' === $type ) {
		printf( '<input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s>', esc_attr( $id ), esc_attr( itp_name( $path ) ), checked( (int) $value, 1, false ) );
	} elseif ( 'image' === $type ) {
		itp_image_control( $id, itp_name( $path ), (int) $value );
	} else {
		printf( '<input type="%1$s" class="regular-text" id="%2$s" name="%3$s" value="%4$s">', esc_attr( $type ), esc_attr( $id ), esc_attr( itp_name( $path ) ), esc_attr( (string) $value ) );
	}
	if ( $help ) {
		echo '<p class="description">' . wp_kses( $help, [ 'code' => [], 'strong' => [] ] ) . '</p>';
	}
	echo '</td></tr>';
}

function itp_image_control( string $id, string $name, int $value ): void {
	$preview = $value ? wp_get_attachment_image( $value, 'thumbnail' ) : '';
	?>
	<div class="itp-image" data-itp-image>
		<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) ( $value ?: '' ) ); ?>">
		<span class="itp-image-preview"><?php echo $preview ? wp_kses_post( $preview ) : '<em>' . esc_html__( 'Placeholder photo', 'industrial-training' ) . '</em>'; ?></span>
		<button type="button" class="button" data-itp-choose><?php esc_html_e( 'Choose image', 'industrial-training' ); ?></button>
		<button type="button" class="button-link button-link-delete" data-itp-clear><?php esc_html_e( 'Remove', 'industrial-training' ); ?></button>
	</div>
	<?php
}

function itp_track_row( $index, array $track ): void {
	$p = static fn( $k ) => [ 'tracks', 'items', $index, $k ];
	?>
	<div class="itp-track-row" data-itp-row>
		<div class="itp-track-bar">
			<strong class="itp-track-title"><?php echo esc_html( $track['name'] ?: __( 'New track', 'industrial-training' ) ); ?></strong>
			<span>
				<button type="button" class="button" data-itp-up aria-label="<?php esc_attr_e( 'Move up', 'industrial-training' ); ?>">↑</button>
				<button type="button" class="button" data-itp-down aria-label="<?php esc_attr_e( 'Move down', 'industrial-training' ); ?>">↓</button>
				<button type="button" class="button button-link-delete" data-itp-remove><?php esc_html_e( 'Remove', 'industrial-training' ); ?></button>
			</span>
		</div>
		<input type="hidden" name="<?php echo esc_attr( itp_name( $p( 'photo' ) ) ); ?>" value="<?php echo esc_attr( $track['photo'] ?? '' ); ?>">
		<table class="form-table" role="presentation"><tbody>
			<?php
			itp_field( [ 'tracks' => [ 'items' => [ $index => $track ] ] ], $p( 'name' ), __( 'Track name', 'industrial-training' ), 'text', __( 'Also used as the option in the form’s “Training track” list.', 'industrial-training' ) );
			itp_field( [ 'tracks' => [ 'items' => [ $index => $track ] ] ], $p( 'branch' ), __( 'Branches', 'industrial-training' ) );
			itp_field( [ 'tracks' => [ 'items' => [ $index => $track ] ] ], $p( 'weeks' ), __( 'Weeks', 'industrial-training' ), 'number' );
			itp_field( [ 'tracks' => [ 'items' => [ $index => $track ] ] ], $p( 'summary' ), __( 'Summary', 'industrial-training' ), 'textarea' );
			itp_field( [ 'tracks' => [ 'items' => [ $index => $track ] ] ], $p( 'skills' ), __( 'Skills', 'industrial-training' ), 'text', __( 'Comma-separated, e.g. <code>CNC programming, GD&amp;T basics</code>', 'industrial-training' ) );
			itp_field( [ 'tracks' => [ 'items' => [ $index => $track ] ] ], $p( 'image' ), __( 'Cover photo', 'industrial-training' ), 'image' );
			?>
		</tbody></table>
	</div>
	<?php
}

/* ---------- Page ---------- */

function itp_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s = itp_settings();

	$section = static function ( string $title, callable $fields, bool $open = false ) {
		echo '<details class="itp-section"' . ( $open ? ' open' : '' ) . '><summary>' . esc_html( $title ) . '</summary><table class="form-table" role="presentation"><tbody>';
		$fields();
		echo '</tbody></table></details>';
	};
	?>
	<div class="wrap itp-admin">
		<h1><?php esc_html_e( 'Industrial Training page', 'industrial-training' ); ?></h1>

		<?php if ( isset( $_GET['itp_imported'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php
				/* translators: 1: imported count, 2: failed count */
				echo esc_html( sprintf( __( 'Imported %1$d photos into the Media Library (%2$d failed). Empty image slots now use them.', 'industrial-training' ), absint( $_GET['itp_imported'] ), absint( $_GET['itp_failed'] ?? 0 ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				?>
			</p></div>
		<?php endif; ?>

		<div class="itp-intro">
			<p>
				<?php
				echo wp_kses(
					__( 'Add the page with the shortcode <code>[industrial_training]</code>, the “Industrial Training Page” block, or pick the page template <strong>Industrial Training (full screen)</strong>.', 'industrial-training' ),
					[ 'code' => [], 'strong' => [] ]
				);
				?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="itp_import_photos">
				<?php wp_nonce_field( 'itp_import_photos' ); ?>
				<button class="button"><?php esc_html_e( 'Import placeholder photos into Media Library', 'industrial-training' ); ?></button>
				<span class="description"><?php esc_html_e( 'Copies the default photos bundled with the plugin (with alt text) into the Media Library and assigns them to empty image slots. Optional: the page already shows them without this.', 'industrial-training' ); ?></span>
			</form>
		</div>

		<div class="itp-intro">
			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: 1: bundled content file, 2: override path */
						__( '<strong>Founder, student stories, college &amp; leadership testimonials and partners</strong> are edited in a content file: copy %1$s to %2$s and edit the copy (it survives plugin updates). Entries marked <code>placeholder</code> are only visible to logged-in editors.', 'industrial-training' ),
						'<code>wp-content/plugins/industrial-training/content/data.php</code>',
						'<code>wp-content/industrial-training/content.php</code>'
					),
					[ 'code' => [], 'strong' => [] ]
				);
				?>
			</p>
		</div>

		<form method="post" action="options.php">
			<?php settings_fields( 'itp_settings_group' ); ?>

			<?php
			$section( __( 'Header', 'industrial-training' ), function () use ( $s ) {
				itp_field( $s, [ 'brand_logo' ], __( 'Logo', 'industrial-training' ), 'text', __( 'A file in the plugin (e.g. <code>assets/images/skillrise-logo.png</code>) or an image URL. Leave empty to show the brand mark and name as text.', 'industrial-training' ) );
				itp_field( $s, [ 'brand_mark' ], __( 'Brand mark text', 'industrial-training' ) );
				itp_field( $s, [ 'brand_name' ], __( 'Brand name', 'industrial-training' ) );
				itp_field( $s, [ 'nav', 'highlights' ], __( 'Link: highlights', 'industrial-training' ) );
				itp_field( $s, [ 'nav', 'offers' ], __( 'Link: what we offer', 'industrial-training' ) );
				itp_field( $s, [ 'nav', 'reviews' ], __( 'Link: reviews', 'industrial-training' ) );
				itp_field( $s, [ 'nav', 'founder' ], __( 'Link: founder', 'industrial-training' ) );
				itp_field( $s, [ 'nav', 'tracks' ], __( 'Link: tracks', 'industrial-training' ) );
				itp_field( $s, [ 'nav', 'contact' ], __( 'Link: contact', 'industrial-training' ) );
				itp_field( $s, [ 'nav', 'register' ], __( 'Register button', 'industrial-training' ) );
			}, true );

			$section( __( 'Hero', 'industrial-training' ), function () use ( $s ) {
				itp_field( $s, [ 'hero', 'badge' ], __( 'Badge', 'industrial-training' ) );
				itp_field( $s, [ 'hero', 'title' ], __( 'Heading', 'industrial-training' ) );
				itp_field( $s, [ 'hero', 'title_accent' ], __( 'Heading (amber line)', 'industrial-training' ) );
				itp_field( $s, [ 'hero', 'lead' ], __( 'Lead text', 'industrial-training' ), 'textarea' );
				itp_field( $s, [ 'hero', 'cta_primary' ], __( 'Primary button', 'industrial-training' ) );
				itp_field( $s, [ 'hero', 'cta_secondary' ], __( 'Secondary button', 'industrial-training' ) );
				foreach ( [ 0, 1, 2 ] as $i ) {
					/* translators: %d: stat number */
					itp_field( $s, [ 'stats', $i, 'value' ], sprintf( __( 'Stat %d value', 'industrial-training' ), $i + 1 ), 'text', 0 === $i ? __( 'Numbers count up; keep any <code>+</code> or <code>%</code>.', 'industrial-training' ) : '' );
					/* translators: %d: stat number */
					itp_field( $s, [ 'stats', $i, 'label' ], sprintf( __( 'Stat %d label', 'industrial-training' ), $i + 1 ) );
				}
				itp_field( $s, [ 'hero', 'image' ], __( 'Main photo', 'industrial-training' ), 'image', __( 'Shown at 5:4. Alt text comes from the Media Library.', 'industrial-training' ) );
				itp_field( $s, [ 'hero', 'inset_image' ], __( 'Inset photo', 'industrial-training' ), 'image', __( 'Shown at 4:3.', 'industrial-training' ) );
				itp_field( $s, [ 'hero', 'float_icon' ], __( 'Floating label icon', 'industrial-training' ) );
				itp_field( $s, [ 'hero', 'float_title' ], __( 'Floating label title', 'industrial-training' ) );
				itp_field( $s, [ 'hero', 'float_text' ], __( 'Floating label text', 'industrial-training' ) );
				itp_field( $s, [ 'hero', 'scroll_label' ], __( 'Scroll cue label (screen readers)', 'industrial-training' ) );
			} );

			$section( __( 'What we offer students', 'industrial-training' ), function () use ( $s ) {
				itp_field( $s, [ 'offers', 'title' ], __( 'Section title', 'industrial-training' ) );
				itp_field( $s, [ 'offers', 'lead' ], __( 'Lead text', 'industrial-training' ), 'textarea' );
				foreach ( array_keys( itp_defaults()['offers']['items'] ) as $i ) {
					/* translators: %d: card number */
					$n = sprintf( __( 'Card %d', 'industrial-training' ), $i + 1 );
					itp_field( $s, [ 'offers', 'items', $i, 'icon' ], $n . ' — ' . __( 'icon', 'industrial-training' ) );
					itp_field( $s, [ 'offers', 'items', $i, 'title' ], $n . ' — ' . __( 'title', 'industrial-training' ) );
					itp_field( $s, [ 'offers', 'items', $i, 'text' ], $n . ' — ' . __( 'text', 'industrial-training' ), 'textarea' );
					itp_field( $s, [ 'offers', 'items', $i, 'note' ], $n . ' — ' . __( 'fine print', 'industrial-training' ), 'text', __( 'Conditions or disclaimer shown under the text. Leave empty to hide.', 'industrial-training' ) );
					itp_field( $s, [ 'offers', 'items', $i, 'image' ], $n . ' — ' . __( 'photo', 'industrial-training' ), 'image' );
				}
				itp_field( $s, [ 'offers', 'cta_title' ], __( 'Banner title', 'industrial-training' ) );
				itp_field( $s, [ 'offers', 'cta_text' ], __( 'Banner text', 'industrial-training' ), 'textarea' );
				itp_field( $s, [ 'offers', 'cta_button' ], __( 'Banner button', 'industrial-training' ) );
			} );

			$section( __( 'Why train with us', 'industrial-training' ), function () use ( $s ) {
				itp_field( $s, [ 'highlights', 'title' ], __( 'Section title', 'industrial-training' ) );
				itp_field( $s, [ 'highlights', 'lead' ], __( 'Lead text', 'industrial-training' ), 'textarea' );
				foreach ( [ 0, 1, 2, 3 ] as $i ) {
					/* translators: %d: card number */
					$n = sprintf( __( 'Card %d', 'industrial-training' ), $i + 1 );
					itp_field( $s, [ 'highlights', 'items', $i, 'icon' ], $n . ' — ' . __( 'icon', 'industrial-training' ) );
					itp_field( $s, [ 'highlights', 'items', $i, 'title' ], $n . ' — ' . __( 'title', 'industrial-training' ) );
					itp_field( $s, [ 'highlights', 'items', $i, 'text' ], $n . ' — ' . __( 'text', 'industrial-training' ), 'textarea' );
				}
			} );
			?>

			<details class="itp-section">
				<summary><?php esc_html_e( 'Training tracks', 'industrial-training' ); ?></summary>
				<table class="form-table" role="presentation"><tbody>
					<?php
					itp_field( $s, [ 'tracks', 'title' ], __( 'Section title', 'industrial-training' ) );
					itp_field( $s, [ 'tracks', 'lead' ], __( 'Lead text', 'industrial-training' ), 'textarea' );
					itp_field( $s, [ 'tracks', 'button' ], __( 'Card button', 'industrial-training' ) );
					?>
				</tbody></table>
				<div class="itp-tracks" data-itp-tracks>
					<?php
					foreach ( $s['tracks']['items'] as $i => $track ) {
						itp_track_row( $i, $track );
					}
					?>
				</div>
				<template id="itp-track-template"><?php itp_track_row( '__i__', [ 'name' => '', 'branch' => '', 'weeks' => 6, 'summary' => '', 'skills' => '', 'photo' => '', 'image' => 0 ] ); ?></template>
				<p><button type="button" class="button button-secondary" data-itp-add><?php esc_html_e( '+ Add track', 'industrial-training' ); ?></button></p>
			</details>

			<?php
			$section( __( 'How it works', 'industrial-training' ), function () use ( $s ) {
				itp_field( $s, [ 'steps', 'title' ], __( 'Section title', 'industrial-training' ) );
				foreach ( [ 0, 1, 2 ] as $i ) {
					/* translators: %d: step number */
					$n = sprintf( __( 'Step %d', 'industrial-training' ), $i + 1 );
					itp_field( $s, [ 'steps', 'items', $i, 'title' ], $n . ' — ' . __( 'title', 'industrial-training' ) );
					itp_field( $s, [ 'steps', 'items', $i, 'text' ], $n . ' — ' . __( 'text', 'industrial-training' ), 'textarea' );
				}
			} );

			$section( __( 'Contact', 'industrial-training' ), function () use ( $s ) {
				itp_field( $s, [ 'contact', 'title' ], __( 'Section title', 'industrial-training' ) );
				itp_field( $s, [ 'contact', 'lead' ], __( 'Lead text', 'industrial-training' ), 'textarea' );
				itp_field( $s, [ 'contact', 'phone' ], __( 'Phone', 'industrial-training' ), 'tel' );
				itp_field( $s, [ 'contact', 'whatsapp' ], __( 'WhatsApp number', 'industrial-training' ), 'tel', __( 'With country code, e.g. +91 93929 20858. Shown in Contact and as a floating chat button. Leave empty to hide both.', 'industrial-training' ) );
				itp_field( $s, [ 'contact', 'email' ], __( 'Email', 'industrial-training' ), 'email' );
				itp_field( $s, [ 'contact', 'address' ], __( 'Address', 'industrial-training' ), 'textarea' );
				itp_field( $s, [ 'contact', 'hours' ], __( 'Hours', 'industrial-training' ) );
				itp_field( $s, [ 'contact', 'cta_title' ], __( 'Card title', 'industrial-training' ) );
				itp_field( $s, [ 'contact', 'cta_text' ], __( 'Card text', 'industrial-training' ), 'textarea' );
				itp_field( $s, [ 'contact', 'cta_button' ], __( 'Card button', 'industrial-training' ) );
				itp_field( $s, [ 'contact', 'image' ], __( 'Card photo', 'industrial-training' ), 'image' );
			} );

			$section( __( 'Footer', 'industrial-training' ), function () use ( $s ) {
				itp_field( $s, [ 'footer', 'copyright' ], __( 'Copyright', 'industrial-training' ), 'text', __( '<code>{year}</code> becomes the current year.', 'industrial-training' ) );
				itp_field( $s, [ 'footer', 'credits' ], __( 'Photo credits', 'industrial-training' ), 'textarea', __( 'Links allowed.', 'industrial-training' ) );
			} );

			$section( __( 'Registration form & emails', 'industrial-training' ), function () use ( $s ) {
				itp_field( $s, [ 'form', 'title' ], __( 'Form title', 'industrial-training' ) );
				itp_field( $s, [ 'form', 'note' ], __( 'Form note', 'industrial-training' ) );
				itp_field( $s, [ 'form', 'success_title' ], __( 'Success heading', 'industrial-training' ), 'text', __( '<code>{name}</code> = first name.', 'industrial-training' ) );
				itp_field( $s, [ 'form', 'success_text' ], __( 'Success text', 'industrial-training' ), 'textarea', __( 'Placeholders: <code>{name}</code> <code>{track}</code> <code>{phone}</code>', 'industrial-training' ) );
				/* translators: %s: admin email */
				itp_field( $s, [ 'form', 'notify_email' ], __( 'Notification emails', 'industrial-training' ), 'text', esc_html( sprintf( __( 'One or more addresses, separated by commas. Leave empty to use %s.', 'industrial-training' ), get_option( 'admin_email' ) ) ) );
				itp_field( $s, [ 'form', 'confirm_student' ], __( 'Send confirmation email to the student', 'industrial-training' ), 'checkbox' );
			} );

			$section( __( 'Sample content (testing)', 'industrial-training' ), function () use ( $s ) {
				itp_field( $s, [ 'samples', 'show' ], __( 'Show sample content to visitors', 'industrial-training' ), 'checkbox', __( 'Shows the sample student reviews, success stories, test colleges and partners from the content file to everyone, each labelled “Sample”. Use this while testing; <strong>switch it off before launch</strong> (or replace every sample with real, approved entries).', 'industrial-training' ) );
			}, true );

			$section( __( 'SEO', 'industrial-training' ), function () use ( $s ) {
				itp_field( $s, [ 'seo', 'enabled' ], __( 'Output SEO tags', 'industrial-training' ), 'checkbox', __( 'Title, meta description and Open Graph are skipped automatically when Yoast SEO, Rank Math, AIOSEO or SEOPress is active. JSON-LD (EducationalOrganization + Courses) is always added while this is on.', 'industrial-training' ) );
				itp_field( $s, [ 'seo', 'title' ], __( 'Page title', 'industrial-training' ) );
				itp_field( $s, [ 'seo', 'description' ], __( 'Meta description', 'industrial-training' ), 'textarea' );
			} );
			?>

			<div class="itp-save"><?php submit_button( null, 'primary', 'submit', false ); ?></div>
		</form>
	</div>
	<?php
}
