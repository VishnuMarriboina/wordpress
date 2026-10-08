<?php
/**
 * POST /wp-json/industrial-training/v1/register
 */

defined( 'ABSPATH' ) || exit;

const ITP_RATE_WINDOW = 10 * MINUTE_IN_SECONDS;

add_action( 'rest_api_init', function () {
	register_rest_route( 'industrial-training/v1', '/register', [
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'itp_rest_register',
		// Public form: authorisation is the wp_rest nonce, checked in the callback so the
		// response keeps the { error } shape the front end expects.
		'permission_callback' => '__return_true',
	] );
	// The page HTML is cached by Hostinger, so the nonce printed into it goes stale after 12–24 h and
	// WordPress then rejects the form with "Cookie check failed". The form fetches a fresh one here first.
	register_rest_route( 'industrial-training/v1', '/nonce', [
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => static function () {
			$res = new WP_REST_Response( [ 'nonce' => wp_create_nonce( 'wp_rest' ) ] );
			$res->set_headers( wp_get_nocache_headers() );
			return $res;
		},
		'permission_callback' => '__return_true',
	] );
} );

function itp_rest_error( string $message, int $status, array $extra = [] ): WP_REST_Response {
	return new WP_REST_Response( [ 'error' => $message ] + $extra, $status );
}

function itp_client_ip(): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	/** Filter: return the real client IP when WordPress sits behind a trusted proxy/CDN. */
	return (string) apply_filters( 'itp_client_ip', $ip );
}

function itp_rest_register( WP_REST_Request $request ): WP_REST_Response {
	$nonce = (string) $request->get_header( 'X-WP-Nonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return itp_rest_error( __( 'Your session has expired. Please refresh the page and try again.', 'industrial-training' ), 403 );
	}

	$data = $request->get_json_params();
	if ( ! is_array( $data ) ) {
		$data = $request->get_body_params();
	}

	// Rate limit: every attempt counts, including invalid ones.
	$key    = 'itp_rl_' . md5( itp_client_ip() );
	$bucket = get_transient( $key );
	$bucket = is_array( $bucket ) ? $bucket : [ 'count' => 0, 'start' => time() ];
	$limit  = min( 500, max( 1, (int) ( itp_settings()['form']['rate_limit'] ?? 30 ) ) );
	if ( $bucket['count'] >= $limit ) {
		$retry = max( 1, $bucket['start'] + ITP_RATE_WINDOW - time() );
		$res   = itp_rest_error( __( 'Too many registrations from your network. Please wait a few minutes and try again.', 'industrial-training' ), 429 );
		$res->header( 'Retry-After', (string) $retry );
		return $res;
	}
	++$bucket['count'];
	set_transient( $key, $bucket, max( 1, $bucket['start'] + ITP_RATE_WINDOW - time() ) );

	// Honeypot: pretend success so bots don't retry.
	if ( ! empty( $data['website'] ) ) {
		return new WP_REST_Response( [ 'message' => __( 'Registration received.', 'industrial-training' ), 'id' => 0 ], 201 );
	}

	[ $clean, $errors ] = itp_validate( $data );
	if ( $errors ) {
		return itp_rest_error( reset( $errors ), 400, [ 'fields' => $errors ] );
	}

	$id = itp_save_registration( $clean );
	if ( ! $id ) {
		return itp_rest_error( __( 'We could not save your registration. Please try again.', 'industrial-training' ), 500 );
	}

	itp_send_emails( $id, $clean );

	return new WP_REST_Response( [ 'message' => __( 'Registration received.', 'industrial-training' ), 'id' => $id ], 201 );
}

function itp_send_emails( int $id, array $d ): void {
	$s    = itp_settings();
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	// Fall back to the company contact address, not the WordPress admin email (often someone's personal inbox).
	$to   = itp_email_list( $s['form']['notify_email'] ) ?: itp_email_list( $s['contact']['email'] ?? '' ) ?: get_option( 'admin_email' );

	$lines = [
		__( 'Name', 'industrial-training' )          => $d['fullName'],
		__( 'Email', 'industrial-training' )         => $d['email'],
		__( 'Phone', 'industrial-training' )         => $d['phone'],
		__( 'College', 'industrial-training' )       => $d['college'],
		__( 'Branch', 'industrial-training' )        => $d['branch'],
	];
	$lines = array_filter( $lines, static fn( $v ) => '' !== (string) $v );
	$body = '';
	foreach ( $lines as $label => $value ) {
		$body .= $label . ': ' . $value . "\n";
	}
	$body .= "\n" . admin_url( 'post.php?post=' . $id . '&action=edit' );

	// Without an SMTP plugin WordPress sends from wordpress@<domain>, a mailbox that doesn't exist, and Hostinger
	// often drops those. Send from the real contact mailbox instead when it is on the site's own domain.
	$from = itp_from_header( $s );

	// Record whether the alert was handed to the mail server, shown in the Registrations list ("Email alert").
	$error   = '';
	$on_fail = static function ( WP_Error $e ) use ( &$error ) {
		$error = $e->get_error_message();
	};
	add_action( 'wp_mail_failed', $on_fail );
	// Hostinger rejects mail whose envelope sender (Return-Path) isn't the logged-in mailbox
	// ("Sender address rejected: not owned by user"), so match it to the From address for our emails.
	add_action( 'phpmailer_init', 'itp_match_envelope_sender', 999 );
	/* translators: 1: site name, 2: student name */
	$sent = wp_mail( $to, sprintf( __( '[%1$s] New training registration: %2$s', 'industrial-training' ), $site, $d['fullName'] ), $body, array_merge( $from, [ 'Reply-To: ' . $d['fullName'] . ' <' . $d['email'] . '>' ] ) );
	remove_action( 'wp_mail_failed', $on_fail );
	update_post_meta( $id, '_itp_mail', $sent ? 'sent' : 'failed: ' . ( $error ?: __( 'unknown error', 'industrial-training' ) ) );

	if ( ! empty( $s['form']['confirm_student'] ) && is_email( $d['email'] ) ) {
		/* translators: 1: first name, 2: "the internship program", 3: phone, 4: site name */
		$msg = sprintf( __( "Hi %1\$s,\n\nThank you for registering for %2\$s. Our counsellor will call you on %3\$s within 2 working days to confirm your batch.\n\n— %4\$s", 'industrial-training' ), strtok( $d['fullName'], ' ' ), __( 'the internship program', 'industrial-training' ), $d['phone'], $site );
		/* translators: %s: site name */
		// Replies from students go to the public contact address.
		$reply = is_email( $s['contact']['email'] ) ? [ 'Reply-To: ' . $s['brand_name'] . ' <' . $s['contact']['email'] . '>' ] : [];
		wp_mail( $d['email'], sprintf( __( 'Your Industrial Training registration — %s', 'industrial-training' ), $site ), $msg, array_merge( $from, $reply ) );
	}
	remove_action( 'phpmailer_init', 'itp_match_envelope_sender', 999 );
}

/** Envelope sender = From address (runs last, after SMTP plugins have set From). */
function itp_match_envelope_sender( $phpmailer ): void {
	if ( ! empty( $phpmailer->From ) && is_email( $phpmailer->From ) ) {
		$phpmailer->Sender = $phpmailer->From;
	}
}

/** "From: Brand <info@domain>" when the contact email is on the site's own domain (so the server may send as it). */
function itp_from_header( array $s ): array {
	$email = (string) ( $s['contact']['email'] ?? '' );
	$host  = preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	if ( ! is_email( $email ) || '' === $host || strtolower( substr( strrchr( $email, '@' ), 1 ) ) !== strtolower( $host ) ) {
		return [];
	}
	return [ 'From: ' . wp_specialchars_decode( $s['brand_name'], ENT_QUOTES ) . ' <' . $email . '>' ];
}
