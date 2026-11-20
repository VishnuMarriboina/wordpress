<?php
/**
 * /admin screen: Settings. A redesigned form for the EXISTING option `itp_settings`.
 *
 * It posts to wp-admin/options.php with the same option group and nonce as Settings → Industrial Training, so the
 * Industrial Training plugin's own itp_sanitize_settings() validates and saves it. Because that function resets any
 * missing field to its default, every saved value that has no visible input here is sent as a hidden field.
 */

defined( 'ABSPATH' ) || exit;

$s        = itp_settings();
$tabs     = srn_admin_settings_tabs();
$name     = static fn( array $path ) => ITP_OPTION . '[' . implode( '][', $path ) . ']';
$id       = static fn( array $path ) => 'srna-set-' . implode( '-', $path );
$value    = static function ( array $path ) use ( $s ) {
	$v = $s;
	foreach ( $path as $k ) {
		$v = is_array( $v ) && array_key_exists( $k, $v ) ? $v[ $k ] : '';
	}
	return $v;
};
$rendered = [];
$photo    = static function ( $attachment, string $key ): string {
	if ( $attachment && wp_attachment_is_image( (int) $attachment ) ) {
		return (string) wp_get_attachment_image( (int) $attachment, 'thumbnail', false, [ 'class' => 'srna-thumb' ] );
	}
	$photos = itp_default_photos();
	return isset( $photos[ $key ] ) ? '<img class="srna-thumb" src="' . esc_url( ITP_URL . 'assets/images/photos/' . $key . '-480.webp' ) . '" alt="" width="80" height="50" loading="lazy">' : '';
};
$field = static function ( array $f ) use ( $name, $id, $value, &$rendered, $photo, $s ) {
	[ 'path' => $path, 'label' => $label, 'type' => $type, 'help' => $help ] = $f;
	$rendered[ implode( '.', $path ) ] = true;
	$v      = $value( $path );
	$fid    = $id( $path );
	$helpid = $help ? $fid . '-help' : '';
	echo '<div class="srna-setting srna-setting-' . esc_attr( $type ) . '">';
	if ( 'checkbox' === $type ) {
		printf(
			'<label class="srna-switch" for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s%4$s><span class="srna-switch-ui" aria-hidden="true"></span><span>%5$s</span></label>',
			esc_attr( $fid ),
			esc_attr( $name( $path ) ),
			checked( (int) $v, 1, false ),
			$helpid ? ' aria-describedby="' . esc_attr( $helpid ) . '"' : '',
			esc_html( $label )
		);
	} elseif ( 'image' === $type ) {
		// Photos are picked with the Media Library in WP Admin; the current choice is kept unchanged here.
		$item_photo = '';
		if ( count( $path ) >= 4 ) {
			$parent     = $value( array_slice( $path, 0, -1 ) );
			$item_photo = is_array( $parent ) ? (string) ( $parent['photo'] ?? '' ) : '';
		}
		$key = $item_photo ?: ( 'inset_image' === end( $path ) ? 'inset' : ( 'contact' === $path[0] ? 'contact' : 'hero' ) );
		printf( '<span class="srna-label">%s</span>', esc_html( $label ) );
		echo '<div class="srna-photo">' . $photo( $v, $key ) . '<span class="srna-muted">' . esc_html( $v ? __( 'Custom photo', 'skillrise-new' ) : __( 'Default photo', 'skillrise-new' ) ) . '</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		printf( '<input type="hidden" name="%1$s" value="%2$s">', esc_attr( $name( $path ) ), esc_attr( (string) $v ) );
	} else {
		printf( '<label class="srna-label" for="%1$s">%2$s</label>', esc_attr( $fid ), esc_html( $label ) );
		if ( 'textarea' === $type ) {
			printf( '<textarea id="%1$s" name="%2$s" rows="3"%4$s>%3$s</textarea>', esc_attr( $fid ), esc_attr( $name( $path ) ), esc_textarea( (string) $v ), $helpid ? ' aria-describedby="' . esc_attr( $helpid ) . '"' : '' );
		} else {
			$extra = 'number' === $type ? ' min="1" max="500"' : '';
			printf( '<input type="%1$s" id="%2$s" name="%3$s" value="%4$s"%5$s%6$s>', esc_attr( $type ), esc_attr( $fid ), esc_attr( $name( $path ) ), esc_attr( (string) $v ), $extra, $helpid ? ' aria-describedby="' . esc_attr( $helpid ) . '"' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
	if ( $help ) {
		printf( '<p class="srna-help" id="%1$s">%2$s</p>', esc_attr( $helpid ), esc_html( $help ) );
	}
	echo '</div>';
};
$track_row = static function ( $i, array $t ) use ( $name ) {
	$p = static fn( $k ) => $name( [ 'tracks', 'items', $i, $k ] );
	?>
	<fieldset class="srna-track" data-srna-track>
		<legend class="srna-sr"><?php esc_html_e( 'Track', 'industrial-training' ); ?></legend>
		<div class="srna-track-bar">
			<strong data-srna-track-title><?php echo esc_html( $t['name'] ?: __( 'New track', 'industrial-training' ) ); ?></strong>
			<span>
				<button type="button" class="srna-icon-btn srna-icon-plain" data-srna-up title="<?php esc_attr_e( 'Move up', 'industrial-training' ); ?>"><?php echo srn_icon( 'arrow-up' ); // phpcs:ignore ?><span class="srna-sr"><?php esc_html_e( 'Move up', 'industrial-training' ); ?></span></button>
				<button type="button" class="srna-icon-btn srna-icon-plain srna-flipy" data-srna-down title="<?php esc_attr_e( 'Move down', 'industrial-training' ); ?>"><?php echo srn_icon( 'arrow-up' ); // phpcs:ignore ?><span class="srna-sr"><?php esc_html_e( 'Move down', 'industrial-training' ); ?></span></button>
				<button type="button" class="srna-icon-btn srna-icon-danger" data-srna-remove title="<?php esc_attr_e( 'Remove', 'industrial-training' ); ?>"><?php echo srn_icon( 'close' ); // phpcs:ignore ?><span class="srna-sr"><?php esc_html_e( 'Remove track', 'skillrise-new' ); ?></span></button>
			</span>
		</div>
		<div class="srna-track-grid">
			<label><span class="srna-label"><?php esc_html_e( 'Track name', 'industrial-training' ); ?></span><input type="text" name="<?php echo esc_attr( $p( 'name' ) ); ?>" value="<?php echo esc_attr( $t['name'] ); ?>" data-srna-track-name required></label>
			<label><span class="srna-label"><?php esc_html_e( 'Branches', 'industrial-training' ); ?></span><input type="text" name="<?php echo esc_attr( $p( 'branch' ) ); ?>" value="<?php echo esc_attr( $t['branch'] ); ?>"></label>
			<label class="srna-span-2"><span class="srna-label"><?php esc_html_e( 'Summary', 'industrial-training' ); ?></span><textarea name="<?php echo esc_attr( $p( 'summary' ) ); ?>" rows="2"><?php echo esc_textarea( $t['summary'] ); ?></textarea></label>
			<label class="srna-span-2"><span class="srna-label"><?php esc_html_e( 'Skills', 'industrial-training' ); ?></span><input type="text" name="<?php echo esc_attr( $p( 'skills' ) ); ?>" value="<?php echo esc_attr( $t['skills'] ); ?>"><span class="srna-help"><?php esc_html_e( 'Comma-separated, e.g. CNC programming, GD&T basics', 'skillrise-new' ); ?></span></label>
		</div>
		<input type="hidden" name="<?php echo esc_attr( $p( 'photo' ) ); ?>" value="<?php echo esc_attr( (string) ( $t['photo'] ?? '' ) ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $p( 'image' ) ); ?>" value="<?php echo esc_attr( (string) ( $t['image'] ?? 0 ) ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $p( 'weeks' ) ); ?>" value="<?php echo esc_attr( (string) ( $t['weeks'] ?? 6 ) ); ?>">
	</fieldset>
	<?php
};
?>
<header class="srna-top">
	<div>
		<p class="srna-eyebrow"><?php esc_html_e( 'Industrial Training', 'skillrise-new' ); ?></p>
		<h1><?php esc_html_e( 'Settings', 'skillrise-new' ); ?></h1>
		<p class="srna-muted"><?php esc_html_e( 'Content and emails for both websites. Changes apply as soon as you save.', 'skillrise-new' ); ?></p>
	</div>
</header>

<form class="srna-settings" method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" data-srna-settings>
	<?php // Same fields settings_fields( 'itp_settings_group' ) prints on the classic page. ?>
	<input type="hidden" name="option_page" value="itp_settings_group">
	<input type="hidden" name="action" value="update">
	<?php wp_nonce_field( 'itp_settings_group-options' ); ?>

	<div class="srna-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'skillrise-new' ); ?>">
		<?php $first = true; foreach ( $tabs as $key => [ $label, $icon ] ) : ?>
			<a class="srna-tab" role="tab" id="tabbtn-<?php echo esc_attr( $key ); ?>" href="#tab-<?php echo esc_attr( $key ); ?>" aria-controls="tab-<?php echo esc_attr( $key ); ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>"><?php echo srn_icon( $icon ); // phpcs:ignore ?><span><?php echo esc_html( $label ); ?></span></a>
		<?php $first = false; endforeach; ?>
	</div>

	<?php foreach ( $tabs as $key => [ $label, $icon, $intro, $fields ] ) : ?>
		<section class="srna-card srna-panel" role="tabpanel" id="tab-<?php echo esc_attr( $key ); ?>" aria-labelledby="tabbtn-<?php echo esc_attr( $key ); ?>">
			<header class="srna-card-head">
				<h2><?php echo esc_html( $label ); ?></h2>
				<?php if ( $intro ) : ?><p class="srna-muted"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
			</header>
			<div class="srna-settings-grid">
				<?php foreach ( $fields as $f ) : ?>
					<?php if ( 'tracks' === $f['type'] ) : ?>
						<div class="srna-span-all">
							<div class="srna-tracks" data-srna-tracks>
								<?php foreach ( array_values( (array) $s['tracks']['items'] ) as $i => $t ) { $track_row( $i, $t ); } ?>
							</div>
							<template data-srna-track-template><?php $track_row( '__i__', [ 'name' => '', 'branch' => '', 'summary' => '', 'skills' => '', 'photo' => '', 'image' => 0, 'weeks' => 6 ] ); ?></template>
							<button type="button" class="srna-btn srna-btn-ghost" data-srna-add-track><?php echo srn_icon( 'plus' ); // phpcs:ignore ?><?php esc_html_e( 'Add track', 'skillrise-new' ); ?></button>
							<p class="srna-help"><?php esc_html_e( 'Each track’s picture stays as it is. New tracks use the standard animation or photo.', 'skillrise-new' ); ?></p>
						</div>
						<?php $rendered['tracks.items'] = true; ?>
					<?php else : ?>
						<?php $field( $f ); ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endforeach; ?>

	<?php
	// Everything else that is saved keeps its current value (the save handler would otherwise reset it).
	foreach ( srn_admin_setting_leaves( $s ) as $path ) {
		if ( empty( $rendered[ implode( '.', $path ) ] ) ) {
			printf( '<input type="hidden" name="%1$s" value="%2$s">', esc_attr( $name( $path ) ), esc_attr( (string) $value( $path ) ) );
		}
	}
	?>

	<div class="srna-savebar">
		<p class="srna-muted" data-srna-dirty-note><?php esc_html_e( 'Changes apply to both websites as soon as you save.', 'skillrise-new' ); ?></p>
		<button type="submit" class="srna-btn srna-btn-primary" data-srna-busy><?php echo srn_icon( 'check' ); // phpcs:ignore ?><?php esc_html_e( 'Save settings', 'skillrise-new' ); ?></button>
	</div>
</form>
