<?php
/**
 * Plugin Name: Training Core
 * Description: Site-specific functionality (custom post types, shortcodes, hooks).
 * Version:     1.0.0
 * Requires PHP: 8.0
 * Text Domain: training-core
 */

defined( 'ABSPATH' ) || exit;

define( 'TRAINING_CORE_PATH', plugin_dir_path( __FILE__ ) );

// Example custom post type.
add_action( 'init', function () {
	register_post_type( 'course', [
		'labels'       => [
			'name'          => __( 'Courses', 'training-core' ),
			'singular_name' => __( 'Course', 'training-core' ),
		],
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-welcome-learn-more',
		'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
		'rewrite'      => [ 'slug' => 'courses' ],
	] );
} );

// Example shortcode: [hello name="World"]
add_shortcode( 'hello', function ( $atts ) {
	$atts = shortcode_atts( [ 'name' => 'World' ], $atts );
	return '<p>' . esc_html( sprintf( __( 'Hello, %s!', 'training-core' ), $atts['name'] ) ) . '</p>';
} );

register_activation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );
