<?php
/**
 * Vercel entrypoint: routes every request into the WordPress install at ../wordpress.
 *
 * - Existing static files are streamed with a long cache header (Vercel's CDN caches them).
 * - Existing .php files (wp-login.php, wp-admin/*.php, ...) are executed directly.
 * - Everything else goes to WordPress' front controller (pretty permalinks).
 *
 * Files are required at the top level (not inside a function) so WordPress globals work.
 */

$wp_root = realpath( __DIR__ . '/../wordpress' );
if ( $wp_root === false ) {
	http_response_code( 500 );
	exit( 'WordPress core is missing; the Vercel build step (vercel/build.php) did not run.' );
}

$path = rawurldecode( parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ?: '/' );
$file = realpath( $wp_root . $path );

// Reject anything resolving outside the WordPress root, plus config/dotfiles.
if ( $file !== false && ! str_starts_with( $file, $wp_root ) ) {
	$file = false;
}
if ( $file !== false && ( basename( $file ) === 'wp-config.php' || str_starts_with( basename( $file ), '.' ) ) ) {
	http_response_code( 404 );
	exit;
}

if ( $file !== false && is_dir( $file ) ) {
	$file = is_file( $file . '/index.php' ) ? $file . '/index.php' : false;
	// wp-admin -> wp-admin/ so relative links resolve.
	if ( $file !== false && ! str_ends_with( $path, '/' ) ) {
		header( 'Location: ' . $path . '/' . ( isset( $_SERVER['QUERY_STRING'] ) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '' ), true, 301 );
		exit;
	}
}

if ( $file !== false && is_file( $file ) && ! str_ends_with( $file, '.php' ) ) {
	$types = [
		'css'   => 'text/css',
		'js'    => 'application/javascript',
		'mjs'   => 'application/javascript',
		'json'  => 'application/json',
		'map'   => 'application/json',
		'svg'   => 'image/svg+xml',
		'png'   => 'image/png',
		'jpg'   => 'image/jpeg',
		'jpeg'  => 'image/jpeg',
		'gif'   => 'image/gif',
		'webp'  => 'image/webp',
		'avif'  => 'image/avif',
		'ico'   => 'image/x-icon',
		'woff'  => 'font/woff',
		'woff2' => 'font/woff2',
		'ttf'   => 'font/ttf',
		'eot'   => 'application/vnd.ms-fontobject',
		'txt'   => 'text/plain',
		'xml'   => 'application/xml',
		'html'  => 'text/html',
	];
	$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	if ( ! isset( $types[ $ext ] ) ) {
		http_response_code( 404 );
		exit;
	}
	header( 'Content-Type: ' . $types[ $ext ] );
	header( 'Content-Length: ' . filesize( $file ) );
	header( 'Cache-Control: public, max-age=86400, s-maxage=31536000, immutable' );
	readfile( $file );
	exit;
}

if ( $file === false || ! is_file( $file ) ) {
	$file = $wp_root . '/index.php';
}

$script_name                = substr( $file, strlen( $wp_root ) );
$_SERVER['SCRIPT_FILENAME'] = $file;
$_SERVER['SCRIPT_NAME']     = $script_name;
$_SERVER['PHP_SELF']        = $script_name;
$_SERVER['DOCUMENT_ROOT']   = $wp_root;

chdir( dirname( $file ) );
define( 'TRAINING_VERCEL_ENTRY', $file );
unset( $wp_root, $path, $file, $types, $ext, $script_name );
require TRAINING_VERCEL_ENTRY;
