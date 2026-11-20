<?php
/**
 * Plugin Name:       Skillrise New Website (/new)
 * Description:       Redesigned Skillrise Technologies website served at /new, with 3D visuals and animations. Reads content from the Industrial Training plugin and submits to its existing registration API. Changes nothing in that plugin.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Skillrise Technologies
 * License:           GPL-2.0-or-later
 * Text Domain:       skillrise-new
 */

defined( 'ABSPATH' ) || exit;

define( 'SRN_VERSION', '1.0.0' );
define( 'SRN_FILE', __FILE__ );
define( 'SRN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SRN_URL', plugin_dir_url( __FILE__ ) );
const SRN_QUERY_VAR = 'skillrise_new';

require_once SRN_DIR . 'includes/data.php';
require_once SRN_DIR . 'includes/admin-dashboard.php';
require_once SRN_DIR . 'includes/followups.php';

/** URL slug of the new website (default "new" → https://example.com/new). */
function srn_slug(): string {
	return trim( (string) apply_filters( 'srn_slug', 'new' ), '/' );
}

/** The Industrial Training plugin provides content, images and the registration API. */
function srn_dependency_ok(): bool {
	return function_exists( 'itp_settings' ) && function_exists( 'itp_content' ) && function_exists( 'itp_branches' ) && defined( 'ITP_URL' );
}

/* ---------- Route: /new ---------- */

function srn_add_rewrite(): void {
	add_rewrite_rule( '^' . preg_quote( srn_slug(), '/' ) . '/?$', 'index.php?' . SRN_QUERY_VAR . '=1', 'top' );
	add_rewrite_rule( srn_admin_rule(), 'index.php?' . SRN_ADMIN_QUERY_VAR . '=1', 'top' );
}

/** Changes whenever a route changes, so the rules are flushed once. */
function srn_rewrite_sig(): string {
	return SRN_VERSION . '|' . srn_slug() . '|' . srn_admin_slug();
}

add_action( 'init', function () {
	srn_add_rewrite();
	// Plugin replaced via "Upload Plugin" doesn't re-run activation: flush once per version/slug so /new resolves.
	$sig = srn_rewrite_sig();
	if ( get_option( 'srn_rewrite_sig' ) !== $sig ) {
		flush_rewrite_rules( false );
		update_option( 'srn_rewrite_sig', $sig, false );
	}
} );

add_filter( 'query_vars', static function ( $vars ) {
	$vars[] = SRN_QUERY_VAR;
	return $vars;
} );

/** Whether this request is the new website (pretty permalink /new, or ?skillrise_new=1 with plain permalinks). */
function srn_is_page(): bool {
	return ! is_admin() && '1' === (string) get_query_var( SRN_QUERY_VAR );
}

// The main query for /new needs no posts.
add_action( 'pre_get_posts', static function ( WP_Query $q ) {
	if ( $q->is_main_query() && '1' === (string) $q->get( SRN_QUERY_VAR ) ) {
		$q->set( 'posts_per_page', 1 );
		$q->set( 'no_found_rows', true );
		$q->set( 'ignore_sticky_posts', true );
	}
} );

// Render before redirect_canonical (priority 10) and the theme's template loader.
add_action( 'template_redirect', static function () {
	if ( ! srn_is_page() ) {
		return;
	}
	if ( ! srn_dependency_ok() ) {
		wp_die( esc_html__( 'The new website needs the Industrial Training plugin to be active.', 'skillrise-new' ), '', [ 'response' => 503 ] );
	}
	global $wp_query;
	$wp_query->is_404  = false;
	$wp_query->is_home = false;
	status_header( 200 );
	include SRN_DIR . 'templates/page.php';
	exit;
}, 1 );

/* ---------- Assets (only on /new) ---------- */

function srn_asset_ver( string $path ): string {
	$time = @filemtime( SRN_DIR . $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	return SRN_VERSION . ( $time ? '.' . $time : '' );
}

function srn_asset_url( string $path ): string {
	return SRN_URL . $path . '?v=' . srn_asset_ver( $path );
}

add_action( 'wp_enqueue_scripts', static function () {
	if ( ! srn_is_page() ) {
		return;
	}
	wp_enqueue_style( 'skillrise-new', SRN_URL . 'assets/css/skillrise-new.css', [], srn_asset_ver( 'assets/css/skillrise-new.css' ) );
	wp_enqueue_script( 'skillrise-new', SRN_URL . 'assets/js/srn-core.js', [], srn_asset_ver( 'assets/js/srn-core.js' ), [ 'strategy' => 'defer', 'in_footer' => true ] );
	wp_localize_script( 'skillrise-new', 'srnData', srn_script_data() );
} );

/**
 * The page is a standalone document: theme and block-library stylesheets would only leak into it
 * (they style body, h1, a, button…), so drop them on /new. Nothing changes on any other URL.
 */
add_action( 'wp_enqueue_scripts', static function () {
	if ( ! srn_is_page() ) {
		return;
	}
	$theme_urls = array_unique( [ get_stylesheet_directory_uri(), get_template_directory_uri() ] );
	$styles     = wp_styles();
	foreach ( $styles->queue as $handle ) {
		$src = (string) ( $styles->registered[ $handle ]->src ?? '' );
		foreach ( $theme_urls as $url ) {
			if ( $src && str_starts_with( $src, $url ) ) {
				wp_dequeue_style( $handle );
			}
		}
	}
	foreach ( [ 'global-styles', 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'core-block-supports', 'itp' ] as $handle ) {
		wp_dequeue_style( $handle );
	}
	wp_dequeue_script( 'itp' );
}, 999 );

// Block themes print these directly; keep them off /new.
add_action( 'wp', static function () {
	if ( srn_is_page() ) {
		remove_action( 'wp_head', 'wp_global_styles_render_svg_filters' );
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
		remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	}
} );

/** Data for assets/js/srn-core.js: existing endpoints, module URLs and the existing plugin's messages. */
function srn_script_data(): array {
	return [
		// The existing Industrial Training API (includes/rest.php in that plugin). Nothing new is registered.
		'endpoint' => esc_url_raw( rest_url( 'industrial-training/v1/register' ) ),
		'nonceUrl' => esc_url_raw( rest_url( 'industrial-training/v1/nonce' ) ),
		'motion'   => srn_asset_url( 'assets/js/srn-motion.js' ),
		'scene'    => srn_asset_url( 'assets/js/srn-scene.js' ),
		// Same wording as the Industrial Training plugin (and its translations), so both forms read identically.
		'i18n'     => [
			'submitting'  => __( 'Submitting…', 'industrial-training' ),
			'submit'      => __( 'Submit registration', 'industrial-training' ),
			'retry'       => __( 'Try again', 'industrial-training' ),
			'timeout'     => __( 'The request timed out. Please check your connection and try again.', 'industrial-training' ),
			'offline'     => __( 'You appear to be offline. Please reconnect and try again.', 'industrial-training' ),
			'generic'     => __( 'Something went wrong on our side. Please try again in a moment.', 'industrial-training' ),
			'fullName'    => __( 'Please enter your full name.', 'industrial-training' ),
			'emailEmpty'  => __( 'Please enter your email address.', 'industrial-training' ),
			'email'       => __( 'Please enter a valid email address, e.g. name@gmail.com.', 'industrial-training' ),
			'phone'       => __( 'Please enter a 10-digit mobile number.', 'industrial-training' ),
			'phoneStart'  => __( 'Mobile number must start with 6, 7, 8 or 9.', 'industrial-training' ),
			'phoneRepeat' => __( 'Please enter a real mobile number (the same digit can’t repeat 8 times).', 'industrial-training' ),
			'anyTrack'    => __( 'the internship program', 'industrial-training' ),
			'college'     => __( 'Please enter your college or polytechnic.', 'industrial-training' ),
			'branch'      => __( 'Please choose your branch.', 'industrial-training' ),
			'menuOpen'    => __( 'Open menu', 'skillrise-new' ),
			'menuClose'   => __( 'Close menu', 'skillrise-new' ),
		],
	];
}

/* ---------- <head>: title, meta, fonts ---------- */

add_filter( 'pre_get_document_title', static function ( $title ) {
	if ( srn_is_page() && srn_dependency_ok() ) {
		$seo = itp_settings()['seo'] ?? [];
		return (string) ( $seo['title'] ?? '' ) ?: $title;
	}
	return $title;
}, 30 );

add_action( 'wp_head', static function () {
	if ( ! srn_is_page() || ! srn_dependency_ok() ) {
		return;
	}
	$s   = itp_settings();
	$seo = $s['seo'] ?? [];
	/**
	 * Filter: keep /new out of search results while the classic site is the homepage (default true),
	 * so the two versions don't compete as duplicate content. Return false once /new becomes the main site.
	 */
	if ( apply_filters( 'srn_noindex', true ) ) {
		echo "<meta name=\"robots\" content=\"noindex, follow\">\n";
	}
	printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( (string) ( $seo['description'] ?? '' ) ) );
	printf( "<meta name=\"theme-color\" content=\"#070b1a\">\n" );
	printf( "<meta property=\"og:type\" content=\"website\">\n<meta property=\"og:title\" content=\"%s\">\n", esc_attr( (string) ( $seo['title'] ?? '' ) ) );
	printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( (string) ( $seo['description'] ?? '' ) ) );
	printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( home_url( '/' . srn_slug() . '/' ) ) );
	if ( function_exists( 'itp_hero_image_url' ) ) {
		printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( itp_hero_image_url() ) );
	}
	if ( ! has_site_icon() ) {
		printf( "<link rel=\"icon\" type=\"image/png\" sizes=\"64x64\" href=\"%s\">\n", esc_url( ITP_URL . 'assets/images/skillrise-icon-64.png' ) );
		printf( "<link rel=\"apple-touch-icon\" href=\"%s\">\n", esc_url( ITP_URL . 'assets/images/skillrise-icon-180.png' ) );
	}
	// Self-hosted fonts: preload the two the first screen needs.
	foreach ( [ 'space-grotesk-latin-wght-normal.woff2', 'inter-latin-wght-normal.woff2' ] as $font ) {
		printf( "<link rel=\"preload\" href=\"%s\" as=\"font\" type=\"font/woff2\" crossorigin>\n", esc_url( SRN_URL . 'assets/fonts/' . $font ) );
	}
}, 2 );

/* ---------- Activation / admin ---------- */

register_activation_hook( __FILE__, static function () {
	srn_add_rewrite();
	flush_rewrite_rules( false );
	update_option( 'srn_rewrite_sig', srn_rewrite_sig(), false );
} );

register_deactivation_hook( __FILE__, static function () {
	// The rule was added on init in this same request; drop it before flushing or it stays in the saved rules.
	global $wp_rewrite;
	unset( $wp_rewrite->extra_rules_top[ '^' . preg_quote( srn_slug(), '/' ) . '/?$' ], $wp_rewrite->extra_rules_top[ srn_admin_rule() ] );
	delete_option( 'srn_rewrite_sig' );
	flush_rewrite_rules( false );
} );

add_action( 'admin_notices', static function () {
	if ( ! srn_dependency_ok() && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Skillrise New Website needs the Industrial Training plugin to be active. /new stays unavailable until it is.', 'skillrise-new' ) . '</p></div>';
	}
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), static function ( $links ) {
	array_unshift( $links, sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( home_url( '/' . srn_slug() . '/' ) ), esc_html__( 'View /new', 'skillrise-new' ) ), sprintf( '<a href="%s">%s</a>', esc_url( srn_admin_url() ), esc_html__( 'Dashboard', 'skillrise-new' ) ) );
	return $links;
} );
