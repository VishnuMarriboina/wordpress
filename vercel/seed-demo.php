<?php
/**
 * Demo mode (no DB_HOST set): installs WordPress into a bundled SQLite database
 * and creates the admin account. Run by vercel/build.php in a PHP process that
 * has the pdo_sqlite extension loaded.
 *
 * Env: WP_ADMIN_USER, WP_ADMIN_PASSWORD, WP_ADMIN_EMAIL, WP_SITE_TITLE.
 */

$wp_root = dirname( __DIR__ ) . '/wordpress';

define( 'WP_INSTALLING', true );
putenv( 'TRAINING_SEED_BUILD=1' );
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';

require $wp_root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

$user     = getenv( 'WP_ADMIN_USER' ) ?: 'admin';
$password = getenv( 'WP_ADMIN_PASSWORD' ) ?: wp_generate_password( 16, false );
$email    = getenv( 'WP_ADMIN_EMAIL' ) ?: 'admin@example.com';
$title    = getenv( 'WP_SITE_TITLE' ) ?: 'Training Site';

$result = wp_install( $title, $user, $email, false, '', wp_slash( $password ) );
if ( empty( $result['user_id'] ) ) {
	fwrite( STDERR, "[wp-demo] ERROR: WordPress install failed\n" );
	exit( 1 );
}

update_option( 'permalink_structure', '/%postname%/' );
switch_theme( 'training-theme' );
activate_plugin( 'sqlite-database-integration/load.php' );
activate_plugin( 'training-core/training-core.php' );

wp_insert_post( [
	'post_type'    => 'course',
	'post_status'  => 'publish',
	'post_title'   => 'Sample Course',
	'post_excerpt' => 'An example course from the Training Core plugin.',
	'post_content' => '<!-- wp:paragraph --><p>Demo content. [hello name="Trainee"]</p><!-- /wp:paragraph -->',
] );

// Rewrite rules are regenerated on the first request, once the course post type is registered.
delete_option( 'rewrite_rules' );

echo "[wp-demo] Demo site installed with SQLite.\n";
echo "[wp-demo] Admin user: {$user}\n";
if ( ! getenv( 'WP_ADMIN_PASSWORD' ) ) {
	echo "[wp-demo] Admin password (generated, set WP_ADMIN_PASSWORD to choose one): {$password}\n";
}
