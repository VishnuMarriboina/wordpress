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
	itp_register_post_type();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
