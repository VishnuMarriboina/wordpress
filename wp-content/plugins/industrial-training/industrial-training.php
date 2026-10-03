<?php
/**
 * Plugin Name:       Industrial Training
 * Description:       "Industrial Training Program for Diploma Students" landing page — shortcode [industrial_training], block, registration form and admin.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Servcrust
 * License:           GPL-2.0-or-later
 * Text Domain:       industrial-training
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'ITP_VERSION', '1.0.0' );
define( 'ITP_FILE', __FILE__ );
define( 'ITP_DIR', plugin_dir_path( __FILE__ ) );
define( 'ITP_URL', plugin_dir_url( __FILE__ ) );
define( 'ITP_OPTION', 'itp_settings' );

require_once ITP_DIR . 'includes/defaults.php';
require_once ITP_DIR . 'includes/helpers.php';
require_once ITP_DIR . 'includes/content.php';
require_once ITP_DIR . 'includes/frontend.php';
require_once ITP_DIR . 'includes/seo.php';
require_once ITP_DIR . 'includes/rest.php';
require_once ITP_DIR . 'includes/registrations.php';

if ( is_admin() ) {
	require_once ITP_DIR . 'includes/settings.php';
}

add_action( 'init', function () {
	load_plugin_textdomain( 'industrial-training', false, dirname( plugin_basename( ITP_FILE ) ) . '/languages' );
} );

register_activation_hook( __FILE__, function () {
	if ( false === get_option( ITP_OPTION ) ) {
		add_option( ITP_OPTION, itp_defaults() );
	}

	// Create the landing page (full screen, no theme header/footer) so nothing has to be set up by hand.
	$page = get_page_by_path( 'industrial-training' );
	if ( ! $page ) {
		$id = wp_insert_post( [
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => __( 'Industrial Training', 'industrial-training' ),
			'post_name'   => 'industrial-training',
		] );
		$page = $id && ! is_wp_error( $id ) ? get_post( $id ) : null;
	}
	if ( $page ) {
		if ( ! get_page_template_slug( $page ) ) {
			update_post_meta( $page->ID, '_wp_page_template', ITP_TEMPLATE );
		}
		// Make it the homepage, unless the site already has a static homepage.
		if ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_on_front' ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page->ID );
		}
	}

	itp_register_post_type();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
