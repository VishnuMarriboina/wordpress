<?php
/**
 * Removes plugin settings. Registrations are kept unless ITP_DELETE_DATA is true in wp-config.php.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'itp_settings' );

if ( defined( 'ITP_DELETE_DATA' ) && ITP_DELETE_DATA ) {
	$ids = get_posts( [
		'post_type'      => 'itp_registration',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	] );
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
}
