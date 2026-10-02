<?php
/**
 * Plugin Name: Vercel Platform
 * Description: Adjustments for running on Vercel (installed by vercel/build.php).
 */

defined( 'ABSPATH' ) || exit;

// api/index.php routes every URL to WordPress, so pretty permalinks work without /index.php/.
add_filter( 'got_url_rewrite', '__return_true' );

/*
 * Demo mode (SQLite in /tmp): each function instance has its own copy of the database,
 * so a login session saved in one instance is unknown to the others. Keep sessions
 * stateless; the auth cookie itself is still HMAC-signed with the salts.
 */
if ( defined( 'TRAINING_DEMO' ) && TRAINING_DEMO ) {
	class Training_Demo_Session_Tokens extends WP_Session_Tokens {
		protected function get_sessions() {
			return [];
		}

		protected function get_session( $verifier ) {
			return [ 'expiration' => PHP_INT_MAX ];
		}

		protected function update_session( $verifier, $session = null ) {}

		protected function destroy_other_sessions( $verifier ) {}

		protected function destroy_all_sessions() {}

		public static function drop_sessions() {}
	}

	add_filter( 'session_token_manager', function () {
		return 'Training_Demo_Session_Tokens';
	} );
}
