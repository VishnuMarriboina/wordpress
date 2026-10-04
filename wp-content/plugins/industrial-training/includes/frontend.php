<?php
/**
 * Front end: assets, shortcode, block and the optional full-width page template.
 */

defined( 'ABSPATH' ) || exit;

const ITP_TEMPLATE = 'industrial-training-full-width.php';

add_action( 'init', function () {
	wp_register_style( 'itp', ITP_URL . 'assets/css/itp.css', [], itp_asset_ver( 'assets/css/itp.css' ) );
	wp_register_script( 'itp', ITP_URL . 'assets/js/itp.js', [], itp_asset_ver( 'assets/js/itp.js' ), [ 'strategy' => 'defer', 'in_footer' => true ] );
	wp_register_script( 'itp-block-editor', ITP_URL . 'assets/js/block.js', [ 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor' ], ITP_VERSION, true );
	wp_set_script_translations( 'itp-block-editor', 'industrial-training', ITP_DIR . 'languages' );

	add_shortcode( 'industrial_training', 'itp_render' );

	register_block_type( ITP_DIR . 'blocks/landing', [
		'render_callback' => static fn( $attributes ) => itp_render( [ 'align' => $attributes['align'] ?? 'full' ] ),
	] );
} );

/** Enqueues assets in <head> on pages known to use the landing page. */
add_action( 'wp_enqueue_scripts', function () {
	if ( itp_is_landing() ) {
		itp_enqueue();
	}
} );

function itp_enqueue(): void {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	wp_enqueue_style( 'itp' );
	wp_enqueue_script( 'itp' );
	wp_localize_script( 'itp', 'itpData', [
		'endpoint' => esc_url_raw( rest_url( 'industrial-training/v1/register' ) ),
		'nonce'    => wp_create_nonce( 'wp_rest' ),
		'i18n'     => [
			/* translators: %d: carousel page number */
			'slide'      => __( 'Go to page %d', 'industrial-training' ),
			'submitting' => __( 'Submitting…', 'industrial-training' ),
			'submit'     => __( 'Submit registration', 'industrial-training' ),
			'retry'      => __( 'Try again', 'industrial-training' ),
			'timeout'    => __( 'The request timed out. Please check your connection and try again.', 'industrial-training' ),
			'offline'    => __( 'You appear to be offline. Please reconnect and try again.', 'industrial-training' ),
			'generic'    => __( 'Something went wrong on our side. Please try again in a moment.', 'industrial-training' ),
			'fullName'   => __( 'Please enter your full name.', 'industrial-training' ),
			'emailEmpty' => __( 'Please enter your email address.', 'industrial-training' ),
			'email'      => __( 'Please enter a valid email address, e.g. name@gmail.com.', 'industrial-training' ),
			'phone'      => __( 'Please enter a 10-digit mobile number.', 'industrial-training' ),
			'phoneStart' => __( 'Mobile number must start with 6, 7, 8 or 9.', 'industrial-training' ),
			'phoneRepeat' => __( 'Please enter a real mobile number (the same digit can’t repeat 8 times).', 'industrial-training' ),
			'anyTrack'   => __( 'the internship program', 'industrial-training' ),
			'college'    => __( 'Please enter your college or polytechnic.', 'industrial-training' ),
			'branch'     => __( 'Please choose your branch.', 'industrial-training' ),
		],
	] );
}

/** Shortcode / block output. */
function itp_render( $atts = [] ): string {
	static $rendered = false;
	if ( $rendered ) {
		// The page uses fixed ids (#tracks, #itp-register…), so only one instance per page.
		return '';
	}
	$rendered = true;

	// Fallback for builders whose content isn't detected early: styles then load in the footer.
	itp_enqueue();

	$atts = shortcode_atts( [ 'align' => 'full', 'main' => false ], (array) $atts, 'industrial_training' );
	$s    = itp_settings();

	ob_start();
	include ITP_DIR . 'templates/landing.php';
	return (string) ob_get_clean();
}

/* ---------- Plugin page template (works with classic and block themes) ---------- */

add_filter( 'theme_page_templates', function ( $templates ) {
	$templates[ ITP_TEMPLATE ] = __( 'Industrial Training (full screen)', 'industrial-training' );
	return $templates;
} );

add_filter( 'template_include', function ( $template ) {
	if ( itp_is_canvas_page() ) {
		return ITP_DIR . 'templates/page-full-width.php';
	}
	return $template;
} );

/** Whether the current request is a page using the full-screen template. */
function itp_is_canvas_page(): bool {
	return is_page() && ITP_TEMPLATE === get_page_template_slug();
}

/**
 * The full-screen template has no theme markup, so theme stylesheets (link colours,
 * containers, ...) would only leak into the landing page. Drop every style served
 * from the active theme's folders.
 */
function itp_dequeue_theme_styles(): void {
	if ( ! itp_is_canvas_page() ) {
		return;
	}
	$theme_urls = array_unique( [ get_stylesheet_directory_uri(), get_template_directory_uri() ] );
	$styles     = wp_styles();
	foreach ( $styles->queue as $handle ) {
		$src = $styles->registered[ $handle ]->src ?? '';
		foreach ( $theme_urls as $url ) {
			if ( $src && str_starts_with( $src, $url ) ) {
				wp_dequeue_style( $handle );
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'itp_dequeue_theme_styles', 999 );
