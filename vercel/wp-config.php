<?php
/**
 * wp-config.php for Vercel. All values come from Vercel environment variables.
 * Copied into ./wordpress by vercel/build.php.
 */

function training_env( string $key, $default = null ) {
	$value = getenv( $key );
	return ( $value === false || $value === '' ) ? $default : $value;
}

// Vercel terminates TLS at the edge; tell WordPress the request is HTTPS.
if ( ( $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '' ) === 'https' ) {
	$_SERVER['HTTPS'] = 'on';
}
if ( ! empty( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
	$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
}

// ** Database (external MySQL/MariaDB — Vercel does not host one) ** //
define( 'DB_NAME', training_env( 'DB_NAME', 'wordpress' ) );
define( 'DB_USER', training_env( 'DB_USER', 'wordpress' ) );
define( 'DB_PASSWORD', training_env( 'DB_PASSWORD', '' ) );
define( 'DB_HOST', training_env( 'DB_HOST', 'localhost' ) );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
if ( filter_var( training_env( 'DB_SSL', false ), FILTER_VALIDATE_BOOLEAN ) ) {
	define( 'MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL );
}

// ** Demo mode: no DB_HOST, so use the SQLite database seeded at build time (vercel/seed-demo.php). ** //
// The bundle is read-only, so each function instance works on its own copy in /tmp.
// Changes made in wp-admin are temporary and reset whenever Vercel starts a new instance.
define( 'TRAINING_DEMO', ! training_env( 'DB_HOST' ) );
if ( TRAINING_DEMO ) {
	if ( training_env( 'TRAINING_SEED_BUILD' ) ) {
		define( 'DB_DIR', __DIR__ . '/wp-content/database/' );
	} else {
		define( 'DB_DIR', '/tmp/wp-demo/' );
		if ( ! is_file( DB_DIR . '.ht.sqlite' ) ) {
			@mkdir( DB_DIR, 0700, true );
			$training_tmp = DB_DIR . uniqid( 'seed', true );
			copy( __DIR__ . '/wp-content/database/.ht.sqlite', $training_tmp );
			rename( $training_tmp, DB_DIR . '.ht.sqlite' );
		}
	}
}

// ** Keys and salts: from env vars, else generated per build (wp-salts.php, written by vercel/build.php) ** //
require __DIR__ . '/wp-salts.php';

$table_prefix = training_env( 'WP_TABLE_PREFIX', 'wp_' );

// ** URLs ** //
$training_home = training_env( 'WP_HOME' );
if ( ! $training_home ) {
	$scheme        = ( $_SERVER['HTTPS'] ?? '' ) === 'on' ? 'https' : 'http';
	$training_host = $_SERVER['HTTP_HOST'] ?? training_env( 'VERCEL_PROJECT_PRODUCTION_URL', 'localhost' );
	$training_home = $scheme . '://' . $training_host;
}
define( 'WP_HOME', rtrim( $training_home, '/' ) );
define( 'WP_SITEURL', WP_HOME );
define( 'FORCE_SSL_ADMIN', str_starts_with( WP_HOME, 'https://' ) );

// ** Serverless constraints: read-only filesystem except /tmp ** //
define( 'DISALLOW_FILE_MODS', true );  // no plugin/theme installs or updates from wp-admin
define( 'DISALLOW_FILE_EDIT', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'WP_TEMP_DIR', '/tmp' );
define( 'WP_DEFAULT_THEME', 'training-theme' );
define( 'WP_ENVIRONMENT_TYPE', training_env( 'WP_ENVIRONMENT_TYPE', 'production' ) );

// ** Debugging — logs go to Vercel function logs ** //
define( 'WP_DEBUG', filter_var( training_env( 'WP_DEBUG', false ), FILTER_VALIDATE_BOOLEAN ) );
define( 'WP_DEBUG_LOG', 'php://stderr' );
define( 'WP_DEBUG_DISPLAY', false );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
