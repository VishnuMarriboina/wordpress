<?php
/**
 * Training Theme functions.
 */

defined( 'ABSPATH' ) || exit;

define( 'TRAINING_THEME_VERSION', '1.0.0' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );

	register_nav_menus( [
		'primary' => __( 'Primary Menu', 'training-theme' ),
		'footer'  => __( 'Footer Menu', 'training-theme' ),
	] );
} );

add_action( 'wp_enqueue_scripts', function () {
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'training-theme', $uri . '/assets/css/main.css', [], TRAINING_THEME_VERSION );
	wp_enqueue_script( 'training-theme', $uri . '/assets/js/main.js', [], TRAINING_THEME_VERSION, true );
} );

add_action( 'widgets_init', function () {
	register_sidebar( [
		'name'          => __( 'Sidebar', 'training-theme' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	] );
} );
