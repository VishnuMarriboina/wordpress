<?php
/**
 * Branded admin dashboard at /admin.
 *
 * - Read-only view of the EXISTING registrations (private `itp_registration` posts from the Industrial Training
 *   plugin). Registrations are never written here. CSV export links to the existing
 *   `admin-post.php?action=itp_export` handler.
 * - Sign-in posts to the standard wp-login.php, so WordPress (and any login-limit / 2FA plugin) handles it.
 * - Administrators (`manage_options`) see everything. Counsellors (role `srn_counsellor`, capability
 *   `srn_contact_students`) see a simple overview, registrations and their profile, to call / message / email students.
 */

defined( 'ABSPATH' ) || exit;

const SRN_ADMIN_QUERY_VAR = 'skillrise_admin';
const SRN_ADMIN_PER_PAGE  = 20;

/** URL slug of the dashboard (default "admin" → https://example.com/admin). */
function srn_admin_slug(): string {
	return trim( (string) apply_filters( 'srn_admin_slug', 'admin' ), '/' );
}

function srn_admin_rule(): string {
	return '^' . preg_quote( srn_admin_slug(), '/' ) . '/?$';
}

/** Dashboard URL with query args. Works with plain permalinks too. */
function srn_admin_url( array $args = [] ): string {
	$base = get_option( 'permalink_structure' ) ? home_url( '/' . srn_admin_slug() . '/' ) : add_query_arg( SRN_ADMIN_QUERY_VAR, '1', home_url( '/' ) );
	$args = array_filter( $args, static fn( $v ) => null !== $v && '' !== $v && 0 !== $v );
	return $args ? add_query_arg( array_map( 'rawurlencode', $args ), $base ) : $base;
}

// The rewrite rule itself is added with the /new rule in srn_add_rewrite() (skillrise-new.php).

add_filter( 'query_vars', static function ( $vars ) {
	$vars[] = SRN_ADMIN_QUERY_VAR;
	return $vars;
} );

function srn_is_admin_page(): bool {
	return ! is_admin() && '1' === (string) get_query_var( SRN_ADMIN_QUERY_VAR );
}

add_action( 'pre_get_posts', static function ( WP_Query $q ) {
	if ( $q->is_main_query() && '1' === (string) $q->get( SRN_ADMIN_QUERY_VAR ) ) {
		$q->set( 'posts_per_page', 1 );
		$q->set( 'no_found_rows', true );
	}
} );

add_action( 'template_redirect', static function () {
	if ( ! srn_is_admin_page() ) {
		return;
	}
	// Never cache: the page is per-user, and the sign-in screen must set WordPress's test cookie.
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	nocache_headers();
	header( 'X-LiteSpeed-Cache-Control: no-cache' );
	header( 'X-Robots-Tag: noindex, nofollow' );
	header( 'Referrer-Policy: same-origin' );
	send_frame_options_header();

	global $wp_query;
	$wp_query->is_404  = false;
	$wp_query->is_home = false;

	srn_admin_handle_post(); // Profile forms: verifies nonce + capability, then redirects.
	$ctx = srn_admin_context();
	status_header( $ctx['status'] );
	include SRN_DIR . 'templates/admin.php';
	exit;
}, 1 );

/* ---------- Controller ---------- */

/** Works out which screen to show and loads its data. */
function srn_admin_context(): array {
	$ctx = [ 'status' => 200, 'screen' => 'login', 'title' => __( 'Sign in', 'skillrise-new' ) ];

	if ( ! is_user_logged_in() ) {
		// Same test cookie wp-login.php sets on GET; without it the login POST fails with "cookies are blocked".
		setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		if ( SITECOOKIEPATH !== COOKIEPATH ) {
			setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		}
		$ctx['signed_out'] = isset( $_GET['signed_out'] ); // phpcs:ignore WordPress.Security.NonceVerification
		return $ctx;
	}
	if ( ! srn_admin_is_member() ) {
		return [ 'status' => 403, 'screen' => 'denied', 'title' => __( 'No access', 'skillrise-new' ) ];
	}
	if ( ! srn_dependency_ok() || ! defined( 'ITP_CPT' ) ) {
		return [ 'status' => 503, 'screen' => 'missing', 'title' => __( 'Industrial Training plugin needed', 'skillrise-new' ) ];
	}

	// phpcs:disable WordPress.Security.NonceVerification -- read-only GET filters.
	$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'dashboard';
	$id   = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	// phpcs:enable

	// phpcs:disable WordPress.Security.NonceVerification -- read-only notice flags.
	$notice = isset( $_GET['notice'] ) ? sanitize_key( wp_unslash( $_GET['notice'] ) ) : ( isset( $_GET['settings-updated'] ) ? 'saved' : '' );
	$ntype  = isset( $_GET['type'] ) && 'bad' === $_GET['type'] ? 'bad' : 'ok';
	// phpcs:enable
	$toast  = srn_admin_notice_text( $notice ) ? [ 'text' => srn_admin_notice_text( $notice ), 'type' => $ntype ] : null;

	if ( 'registration' === $view && $id ) {
		$post = get_post( $id );
		if ( ! $post || ITP_CPT !== $post->post_type || 'trash' === $post->post_status ) {
			return [ 'status' => 404, 'screen' => 'notfound', 'title' => __( 'Registration not found', 'skillrise-new' ) ];
		}
		$history = srn_fu_history( $post->ID );
		return [ 'status' => 200, 'screen' => 'registration', 'title' => $post->post_title, 'reg' => srn_admin_row( $post ), 'history' => $history, 'toast' => $toast ];
	}
	// Counsellors: their overview, registrations, a registration, and their profile. Everything else is admin-only.
	if ( ! srn_admin_is_manager() && in_array( $view, [ 'analytics', 'emails', 'settings', 'team' ], true ) ) {
		return [ 'status' => 403, 'screen' => 'forbidden', 'title' => __( 'Not available', 'skillrise-new' ) ];
	}
	if ( 'team' === $view ) {
		return [ 'status' => 200, 'screen' => 'team', 'title' => __( 'Team', 'skillrise-new' ), 'toast' => $toast, 'team' => srn_admin_team(), 'work7' => srn_fu_team_stats( gmdate( 'Y-m-d H:i:s', time() - 7 * DAY_IN_SECONDS ) ), 'work30' => srn_fu_team_stats( gmdate( 'Y-m-d H:i:s', time() - 30 * DAY_IN_SECONDS ) ), 'feed' => srn_fu_recent( 20 ) ];
	}
	if ( 'analytics' === $view ) {
		$range = srn_admin_range();
		return [ 'status' => 200, 'screen' => 'analytics', 'title' => __( 'Analytics', 'skillrise-new' ), 'range' => $range, 'an' => srn_admin_analytics( $range ) ];
	}
	if ( 'emails' === $view ) {
		$range = srn_admin_range();
		return [ 'status' => 200, 'screen' => 'emails', 'title' => __( 'Email notifications', 'skillrise-new' ), 'range' => $range ] + srn_admin_emails( $range );
	}
	if ( 'settings' === $view ) {
		return [ 'status' => 200, 'screen' => 'settings', 'title' => __( 'Settings', 'skillrise-new' ), 'toast' => $toast ];
	}
	if ( 'profile' === $view ) {
		return [ 'status' => 200, 'screen' => 'profile', 'title' => __( 'My profile', 'skillrise-new' ), 'toast' => $toast ];
	}
	if ( 'registrations' === $view ) {
		return [ 'status' => 200, 'screen' => 'registrations', 'title' => __( 'Registrations', 'skillrise-new' ) ] + srn_admin_list();
	}
	if ( ! srn_admin_is_manager() ) {
		return [ 'status' => 200, 'screen' => 'home', 'title' => __( 'Overview', 'skillrise-new' ), 'home' => srn_admin_home() ];
	}
	$range = srn_admin_range();
	return [ 'status' => 200, 'screen' => 'dashboard', 'title' => __( 'Dashboard', 'skillrise-new' ), 'range' => $range, 'stats' => srn_admin_stats( $range ), 'latest' => srn_admin_list( 6, $range )['rows'], 'fu' => srn_fu_summary(), 'feed' => srn_fu_recent( 6 ) ];
}

/** One registration as a plain array (existing meta keys, read only). */
function srn_admin_row( WP_Post $post ): array {
	$meta = static fn( string $k ) => (string) get_post_meta( $post->ID, '_itp_' . $k, true );
	$mail = $meta( 'mail' );
	return [
		'id'      => $post->ID,
		'name'    => $post->post_title,
		'email'   => $meta( 'email' ),
		'phone'   => $meta( 'phone' ),
		'college' => $meta( 'college' ),
		'branch'  => $meta( 'branch' ),
		// Older entries may still hold year/track (no longer collected).
		'year'    => $meta( 'year' ),
		'track'   => $meta( 'track' ),
		'time'    => function_exists( 'itp_registered_at' ) ? itp_registered_at( $post ) : (int) strtotime( $post->post_date_gmt . ' UTC' ),
		'mail'    => 'sent' === $mail ? 'sent' : ( str_starts_with( $mail, 'failed' ) ? 'failed' : '' ),
		'mailErr' => str_starts_with( $mail, 'failed' ) ? trim( substr( $mail, 7 ) ) : '',
		'edit'    => admin_url( 'post.php?post=' . $post->ID . '&action=edit' ),
	];
}

/**
 * Date range from the query string (site timezone), validated.
 * ?range=today|7|30|90|month|all, or ?range=custom&from=YYYY-MM-DD&to=YYYY-MM-DD. Default: last 30 days.
 *
 * @return array{key: string, from: DateTimeImmutable, to: DateTimeImmutable, fromYmd: string, toYmd: string, label: string, days: int, gmtFrom: string, gmtTo: string}
 */
function srn_admin_range( string $default = '30' ): array {
	// phpcs:disable WordPress.Security.NonceVerification -- read-only GET filters.
	$key  = isset( $_GET['range'] ) ? sanitize_key( wp_unslash( $_GET['range'] ) ) : $default;
	$fRaw = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
	$tRaw = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
	// phpcs:enable
	$tz    = wp_timezone();
	$today = new DateTimeImmutable( 'today', $tz );
	$day   = static fn( string $ymd ) => ( $d = DateTimeImmutable::createFromFormat( '!Y-m-d', $ymd, $tz ) ) && $d->format( 'Y-m-d' ) === $ymd ? $d : null;

	if ( ! in_array( $key, [ 'today', '7', '30', '90', 'month', 'all', 'custom', 'any' ], true ) ) {
		$key = $default;
	}
	// A custom range without valid dates falls back to the default.
	$from = $to = null;
	if ( 'custom' === $key ) {
		$from = $day( $fRaw );
		$to   = $day( $tRaw );
		if ( ! $from && ! $to ) {
			$key = $default;
		} else {
			$from = $from ?: $to;
			$to   = $to ?: $today;
			if ( $from > $to ) {
				[ $from, $to ] = [ $to, $from ];
			}
			$to = min( $to, $today );
			$from = min( $from, $to );
		}
	}
	switch ( $key ) {
		case 'today':
			$from = $to = $today;
			break;
		case '7':
		case '30':
		case '90':
			$to   = $today;
			$from = $today->modify( '-' . ( (int) $key - 1 ) . ' days' );
			break;
		case 'month':
			$to   = $today;
			$from = $today->modify( 'first day of this month' );
			break;
		case 'all':
		case 'any':
			global $wpdb;
			$first = $wpdb->get_var( $wpdb->prepare( "SELECT MIN(post_date_gmt) FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash','auto-draft','inherit') AND post_date_gmt > '1971-01-01'", ITP_CPT ) ); // phpcs:ignore WordPress.DB
			$to    = $today;
			$from  = $first ? ( new DateTimeImmutable( $first, new DateTimeZone( 'UTC' ) ) )->setTimezone( $tz )->setTime( 0, 0 ) : $today;
			break;
	}
	$end   = $to->setTime( 23, 59, 59 );
	$utc   = new DateTimeZone( 'UTC' );
	$days  = (int) $from->diff( $to )->days + 1;
	$fmt   = static fn( DateTimeImmutable $d, bool $year ) => wp_date( $year ? 'j M Y' : 'j M', $d->getTimestamp(), $tz );
	$label = $from == $to ? $fmt( $from, true ) : $fmt( $from, $from->format( 'Y' ) !== $to->format( 'Y' ) ) . ' – ' . $fmt( $to, true );
	return [
		'key'     => $key,
		'from'    => $from,
		'to'      => $to,
		'fromYmd' => $from->format( 'Y-m-d' ),
		'toYmd'   => $to->format( 'Y-m-d' ),
		'label'   => $label,
		'days'    => $days,
		'gmtFrom' => $from->setTimezone( $utc )->format( 'Y-m-d H:i:s' ),
		'gmtTo'   => $end->setTimezone( $utc )->format( 'Y-m-d H:i:s' ),
	];
}

/** Query args that carry a range into another dashboard link. 'any' (the list default) adds nothing. */
function srn_admin_range_args( array $r ): array {
	if ( 'any' === $r['key'] ) {
		return [];
	}
	return 'custom' === $r['key'] ? [ 'range' => 'custom', 'from' => $r['fromYmd'], 'to' => $r['toYmd'] ] : [ 'range' => $r['key'] ];
}

/** Current list filters from the query string, validated. The list shows all dates unless a range is given. */
function srn_admin_filters(): array {
	// phpcs:disable WordPress.Security.NonceVerification -- read-only GET filters.
	$s      = isset( $_GET['s'] ) ? mb_substr( sanitize_text_field( wp_unslash( $_GET['s'] ) ), 0, 100 ) : '';
	$branch = isset( $_GET['branch'] ) ? sanitize_text_field( wp_unslash( $_GET['branch'] ) ) : '';
	$order  = isset( $_GET['order'] ) && 'asc' === $_GET['order'] ? 'asc' : 'desc';
	$paged  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	// phpcs:enable
	if ( ! array_key_exists( $branch, itp_branches() ) ) {
		$branch = '';
	}
	$range = srn_admin_range( 'any' );
	$fu    = isset( $_GET['fu'] ) ? sanitize_key( wp_unslash( $_GET['fu'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$fu    = in_array( $fu, [ 'new', 'pending', 'due', 'progress', 'done', 'closed' ], true ) ? $fu : '';
	return compact( 's', 'branch', 'order', 'paged', 'range', 'fu' );
}

/** date_query clause for a range (on the UTC column, like the stats). */
function srn_admin_date_query( array $r ): array {
	return [ [ 'column' => 'post_date_gmt', 'after' => $r['gmtFrom'], 'before' => $r['gmtTo'], 'inclusive' => true ] ];
}

/**
 * Registrations list. Search matches name, email, phone and college — the same fields the
 * Registrations screen searches (they are stored in the post title/content by the existing plugin).
 *
 * @param int        $limit Only the newest N (dashboard), ignoring search/branch/paging.
 * @param array|null $range Range to apply (dashboard); null = the list's own filters.
 */
function srn_admin_list( int $limit = 0, ?array $range = null ): array {
	$f    = srn_admin_filters();
	$args = [
		'post_type'      => ITP_CPT,
		'post_status'    => 'any',
		'posts_per_page' => $limit ?: SRN_ADMIN_PER_PAGE,
		'paged'          => $limit ? 1 : $f['paged'],
		'orderby'        => 'date',
		'order'          => $limit ? 'DESC' : strtoupper( $f['order'] ),
		'no_found_rows'  => (bool) $limit,
	];
	if ( ! $limit && '' !== $f['s'] ) {
		$args['s'] = $f['s'];
	}
	if ( ! $limit && '' !== $f['branch'] ) {
		$args['meta_query'] = [ [ 'key' => '_itp_branch', 'value' => $f['branch'] ] ]; // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	if ( ! $limit && '' !== $f['fu'] ) {
		$ids = srn_fu_summary()['ids'];
		if ( 'new' === $f['fu'] ) {
			$args['post__not_in'] = $ids['contacted'];
		} elseif ( 'pending' === $f['fu'] ) {
			// Not contacted + no answer + call back later.
			$args['post__not_in'] = array_values( array_diff( $ids['contacted'], $ids['pending'] ) );
		} else {
			$args['post__in'] = $ids[ $f['fu'] ] ?: [ 0 ];
		}
	}
	$range = $range ?? ( 'any' === $f['range']['key'] ? null : $f['range'] );
	if ( $range ) {
		$args['date_query'] = srn_admin_date_query( $range );
	}
	$q = new WP_Query( $args );
	return [
		'rows'    => array_map( 'srn_admin_row', $q->posts ),
		'total'   => (int) $q->found_posts,
		'pages'   => (int) $q->max_num_pages,
		'filters' => $f,
	];
}

/**
 * Numbers for the selected range: count, daily average, busiest day, failed alerts, the chart series
 * (per day up to 62 days, per week up to 366, then per month) and the branch breakdown. Read-only SQL.
 */
function srn_admin_stats( array $r ): array {
	global $wpdb;
	$live = "post_status NOT IN ('trash','auto-draft','inherit')";
	$in   = $wpdb->prepare( 'post_date_gmt BETWEEN %s AND %s', $r['gmtFrom'], $r['gmtTo'] );
	$tz   = wp_timezone();

	$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND {$live}", ITP_CPT ) ); // phpcs:ignore WordPress.DB
	$dates = $wpdb->get_col( $wpdb->prepare( "SELECT post_date_gmt FROM {$wpdb->posts} WHERE post_type = %s AND {$live} AND {$in}", ITP_CPT ) ); // phpcs:ignore WordPress.DB

	// Per-day counts in the site's timezone (what the Registrations screen shows).
	$perDay = [];
	foreach ( $dates as $d ) {
		$k            = ( new DateTimeImmutable( $d, new DateTimeZone( 'UTC' ) ) )->setTimezone( $tz )->format( 'Y-m-d' );
		$perDay[ $k ] = ( $perDay[ $k ] ?? 0 ) + 1;
	}

	$unit   = $r['days'] <= 62 ? 'day' : ( $r['days'] <= 366 ? 'week' : 'month' );
	$series = [];
	$cursor = 'week' === $unit ? $r['from']->modify( 'monday this week' ) : ( 'month' === $unit ? $r['from']->modify( 'first day of this month' ) : $r['from'] );
	while ( $cursor <= $r['to'] ) {
		$next  = $cursor->modify( '+1 ' . $unit );
		$start = max( $cursor, $r['from'] );
		$end   = min( $next->modify( '-1 day' ), $r['to'] );
		$n     = 0;
		for ( $d = $start; $d <= $end; $d = $d->modify( '+1 day' ) ) {
			$n += $perDay[ $d->format( 'Y-m-d' ) ] ?? 0;
		}
		$ts = $start->getTimestamp();
		if ( 'day' === $unit ) {
			$tip = wp_date( 'D j M', $ts, $tz );
		} elseif ( 'week' === $unit ) {
			/* translators: %s: first day of the week */
			$tip = sprintf( __( 'Week of %s', 'skillrise-new' ), wp_date( 'j M', $ts, $tz ) );
		} else {
			$tip = wp_date( 'M Y', $ts, $tz );
		}
		$series[] = [ 'ts' => $ts, 'n' => $n, 'tip' => $tip, 'axis' => wp_date( 'month' === $unit ? 'M y' : 'j M', $ts, $tz ) ];
		$cursor   = $next;
	}

	$busiest = '';
	if ( $perDay ) {
		arsort( $perDay );
		$bk      = (string) array_key_first( $perDay );
		$busiest = [ 'date' => wp_date( 'j M', DateTimeImmutable::createFromFormat( '!Y-m-d', $bk, $tz )->getTimestamp(), $tz ), 'n' => (int) $perDay[ $bk ] ];
	}

	$branches = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB
		"SELECT pm.meta_value AS branch, COUNT(*) AS n FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE p.post_type = %s AND p.{$live} AND p.{$in} AND pm.meta_key = '_itp_branch' GROUP BY pm.meta_value ORDER BY n DESC",
		ITP_CPT
	), ARRAY_A );

	$failed = (int) $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB
		"SELECT COUNT(*) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE p.post_type = %s AND p.{$live} AND p.{$in} AND pm.meta_key = '_itp_mail' AND pm.meta_value LIKE %s",
		ITP_CPT,
		$wpdb->esc_like( 'failed' ) . '%'
	) );

	$count = count( $dates );
	return [
		'total'    => $total,
		'count'    => $count,
		'avg'      => $count / max( 1, $r['days'] ),
		'busiest'  => $busiest,
		'failed'   => $failed,
		'unit'     => $unit,
		'series'   => $series,
		'branches' => array_map( static fn( $b ) => [ 'branch' => '' === (string) $b['branch'] ? __( '(not set)', 'skillrise-new' ) : (string) $b['branch'], 'n' => (int) $b['n'] ], (array) $branches ),
	];
}

/** Existing CSV export (industrial-training/includes/registrations.php). It supports the search term only. */
function srn_admin_export_url( string $search = '' ): string {
	return wp_nonce_url( add_query_arg( [ 'action' => 'itp_export', 's' => $search ], admin_url( 'admin-post.php' ) ), 'itp_export' );
}

/**
 * WhatsApp chat link for a student, with a greeting already typed in (the admin can edit it before sending).
 * Filter `srn_admin_wa_message` to change the text; {name} = first name, {brand} = company name.
 */
function srn_admin_wa_url( string $phone, string $name = '' ): string {
	// Indian mobile → 91 + 10 digits, using the Industrial Training plugin's own parser (handles +91, 0091, 0…).
	$ten = function_exists( 'itp_phone_digits' ) ? itp_phone_digits( $phone ) : '';
	$d   = '' !== $ten ? '91' . $ten : preg_replace( '/\D/', '', $phone );
	if ( '' === $d ) {
		return '';
	}
	/* translators: {name}: student's first name, {brand}: company name. Keep the braces. */
	$msg = (string) apply_filters( 'srn_admin_wa_message', __( 'Hi {name}, this is {brand} about your Industrial Training registration.', 'skillrise-new' ) );
	$msg = strtr( $msg, [
		'{name}'  => (string) strtok( trim( $name ) ?: ' ', ' ' ),
		'{brand}' => srn_dependency_ok() ? (string) itp_settings()['brand_name'] : get_bloginfo( 'name' ),
	] );
	return 'https://wa.me/' . $d . '?text=' . rawurlencode( trim( preg_replace( '/\s+/', ' ', $msg ) ) );
}

/** tel: link for a stored phone number (keeps + and digits). */
function srn_admin_tel_url( string $phone ): string {
	$t = preg_replace( '/[^0-9+]/', '', $phone );
	return '' === $t ? '' : 'tel:' . $t;
}

/* ---------- Analytics (read-only, selected range) ---------- */

/** Everything on the Analytics screen. Builds on srn_admin_stats() so the numbers agree with the Overview. */
function srn_admin_analytics( array $r ): array {
	global $wpdb;
	$live = "post_status NOT IN ('trash','auto-draft','inherit')";
	$st   = srn_admin_stats( $r );

	// Same-length window right before the selected one, for a real comparison.
	$utc      = new DateTimeZone( 'UTC' );
	$prevTo   = $r['from']->modify( '-1 day' )->setTime( 23, 59, 59 );
	$prevFrom = $r['from']->modify( '-' . $r['days'] . ' days' );
	$prev     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND {$live} AND post_date_gmt BETWEEN %s AND %s", ITP_CPT, $prevFrom->setTimezone( $utc )->format( 'Y-m-d H:i:s' ), $prevTo->setTimezone( $utc )->format( 'Y-m-d H:i:s' ) ) ); // phpcs:ignore WordPress.DB

	$in = $wpdb->prepare( 'p.post_date_gmt BETWEEN %s AND %s', $r['gmtFrom'], $r['gmtTo'] );

	// Weekday pattern (site timezone), Monday first.
	$dates   = $wpdb->get_col( $wpdb->prepare( "SELECT p.post_date_gmt FROM {$wpdb->posts} p WHERE p.post_type = %s AND p.{$live} AND {$in}", ITP_CPT ) ); // phpcs:ignore WordPress.DB
	$weekday = array_fill( 1, 7, 0 );
	foreach ( $dates as $d ) {
		++$weekday[ (int) ( new DateTimeImmutable( $d, $utc ) )->setTimezone( wp_timezone() )->format( 'N' ) ];
	}
	$wd_labels = [];
	$monday    = new DateTimeImmutable( 'monday this week', wp_timezone() );
	for ( $i = 0; $i < 7; $i++ ) {
		$wd_labels[ $i + 1 ] = wp_date( 'l', $monday->modify( "+{$i} days" )->getTimestamp() );
	}

	// Top colleges as typed by students (grouped case-insensitively).
	$colleges = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB
		"SELECT MIN(pm.meta_value) AS college, COUNT(*) AS n FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE p.post_type = %s AND p.{$live} AND {$in} AND pm.meta_key = '_itp_college' AND pm.meta_value <> ''
		 GROUP BY LOWER(TRIM(pm.meta_value)) ORDER BY n DESC LIMIT 8",
		ITP_CPT
	), ARRAY_A );

	return $st + [
		'prev'     => $prev,
		'prevFrom' => $prevFrom,
		'prevTo'   => $prevTo,
		'weekday'  => array_map( static fn( $k ) => [ 'label' => $wd_labels[ $k ], 'n' => $weekday[ $k ] ], array_keys( $weekday ) ),
		'colleges' => array_map( static fn( $c ) => [ 'college' => (string) $c['college'], 'n' => (int) $c['n'] ], (array) $colleges ),
		'mail'     => srn_admin_mail_split( $r ),
	];
}

/** Email-alert status for registrations in a range: sent / failed / not recorded (older entries have no status). */
function srn_admin_mail_split( array $r ): array {
	global $wpdb;
	$live = "p.post_status NOT IN ('trash','auto-draft','inherit')";
	$rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB
		"SELECT CASE WHEN pm.meta_value = 'sent' THEN 'sent' WHEN pm.meta_value LIKE %s THEN 'failed' ELSE 'none' END AS s, COUNT(*) AS n
		 FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_itp_mail'
		 WHERE p.post_type = %s AND {$live} AND p.post_date_gmt BETWEEN %s AND %s GROUP BY s",
		$wpdb->esc_like( 'failed' ) . '%',
		ITP_CPT,
		$r['gmtFrom'],
		$r['gmtTo']
	), ARRAY_A );
	$out = [ 'sent' => 0, 'failed' => 0, 'none' => 0 ];
	foreach ( (array) $rows as $row ) {
		$out[ $row['s'] ] = (int) $row['n'];
	}
	return $out;
}

/* ---------- Email notifications ---------- */

/** Email screen data: split, recent failures (paginated) and the current alert configuration. */
function srn_admin_emails( array $r ): array {
	$f    = srn_admin_filters();
	$q    = new WP_Query( [
		'post_type'      => ITP_CPT,
		'post_status'    => 'any',
		'posts_per_page' => SRN_ADMIN_PER_PAGE,
		'paged'          => $f['paged'],
		'orderby'        => 'date',
		'order'          => 'DESC',
		'date_query'     => srn_admin_date_query( $r ),
		'meta_query'     => [ [ 'key' => '_itp_mail', 'value' => 'failed', 'compare' => 'LIKE' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery
	] );
	$s        = itp_settings();
	// Same recipient fallback as itp_send_emails() in the Industrial Training plugin.
	$to       = itp_email_list( $s['form']['notify_email'] ?? '' ) ?: itp_email_list( $s['contact']['email'] ?? '' ) ?: [ (string) get_option( 'admin_email' ) ];
	$from     = function_exists( 'itp_from_header' ) ? itp_from_header( $s ) : [];
	$smtp     = defined( 'WPMS_PLUGIN_VER' ) || class_exists( 'WPMailSMTP\\Core' ) || function_exists( 'wp_mail_smtp' ) || defined( 'POST_SMTP_VER' ) || class_exists( 'FluentMail\\App\\App' ) || defined( 'EASY_WP_SMTP_PLUGIN_VERSION' );
	return [
		'mail'     => srn_admin_mail_split( $r ),
		'failures' => array_map( 'srn_admin_row', $q->posts ),
		'total'    => (int) $q->found_posts,
		'pages'    => (int) $q->max_num_pages,
		'filters'  => $f,
		'config'   => [
			'to'      => $to,
			'from'    => $from ? preg_replace( '/^From:\s*/', '', $from[0] ) : '',
			'confirm' => ! empty( $s['form']['confirm_student'] ),
			'smtp'    => $smtp,
		],
	];
}

/** Failed alerts in the last 30 days — the sidebar badge (real count, hidden when 0). */
function srn_admin_recent_failed(): int {
	static $n = null;
	if ( null === $n ) {
		$n = srn_admin_mail_split( srn_admin_range_for( '30' ) )['failed'];
	}
	return $n;
}

/** A preset range regardless of the query string. */
function srn_admin_range_for( string $key ): array {
	$saved = $_GET; // phpcs:ignore WordPress.Security.NonceVerification
	$_GET  = [ 'range' => $key ];
	$r     = srn_admin_range();
	$_GET  = $saved;
	return $r;
}

/* ---------- Settings (existing option, existing save handler) ---------- */

/**
 * Tabs and fields of Settings → Industrial Training, regrouped. Paths, labels and types mirror
 * industrial-training/includes/settings.php. The form posts to wp-admin/options.php with the same
 * option group, so itp_sanitize_settings() validates and saves it exactly as before.
 */
function srn_admin_settings_tabs(): array {
	$d     = itp_defaults();
	$f     = static fn( array $path, string $label, string $type = 'text', string $help = '' ) => compact( 'path', 'label', 'type', 'help' );
	$cards = static function ( string $group, array $keys, array $fields ) use ( $d, $f ) {
		$out = [];
		foreach ( array_keys( $d[ $group ]['items'] ?? $d[ $group ][ $keys[0] ] ?? [] ) as $i ) {
			foreach ( $fields as [ $k, $label, $type ] ) {
				/* translators: 1: item number, 2: field name */
				$out[] = $f( array_merge( [ $group ], $keys, [ $i, $k ] ), sprintf( __( '#%1$d %2$s', 'skillrise-new' ), $i + 1, $label ), $type );
			}
		}
		return $out;
	};
	return [
		'general'  => [ __( 'General', 'skillrise-new' ), 'gear', __( 'Brand and the classic site’s menu labels.', 'skillrise-new' ), [
			$f( [ 'brand_name' ], __( 'Brand name', 'industrial-training' ) ),
			$f( [ 'brand_logo' ], __( 'Logo', 'industrial-training' ), 'text', __( 'A file in the plugin (e.g. assets/images/skillrise-logo.png) or an image URL. Empty shows the brand mark and name as text.', 'skillrise-new' ) ),
			$f( [ 'brand_mark' ], __( 'Brand mark text', 'industrial-training' ) ),
			$f( [ 'nav', 'about' ], __( 'Link: about', 'industrial-training' ) ),
			$f( [ 'nav', 'highlights' ], __( 'Link: highlights', 'industrial-training' ) ),
			$f( [ 'nav', 'offers' ], __( 'Link: what we offer', 'industrial-training' ) ),
			$f( [ 'nav', 'tracks' ], __( 'Link: tracks', 'industrial-training' ) ),
			$f( [ 'nav', 'reviews' ], __( 'Link: reviews', 'industrial-training' ) ),
			$f( [ 'nav', 'founder' ], __( 'Link: founder', 'industrial-training' ) ),
			$f( [ 'nav', 'contact' ], __( 'Link: contact', 'industrial-training' ) ),
			$f( [ 'nav', 'register' ], __( 'Register button', 'industrial-training' ) ),
		] ],
		'hero'     => [ __( 'Hero & stats', 'skillrise-new' ), 'spark', __( 'Top of the classic page. Stats also appear on /new.', 'skillrise-new' ), array_merge( [
			$f( [ 'hero', 'badge' ], __( 'Badge', 'industrial-training' ) ),
			$f( [ 'hero', 'title' ], __( 'Heading', 'industrial-training' ) ),
			$f( [ 'hero', 'title_accent' ], __( 'Heading (amber line)', 'industrial-training' ) ),
			$f( [ 'hero', 'lead' ], __( 'Lead text', 'industrial-training' ), 'textarea' ),
			$f( [ 'hero', 'eligible_title' ], __( 'Under-18 highlight — title', 'industrial-training' ), 'text', __( 'Leave empty to hide.', 'skillrise-new' ) ),
			$f( [ 'hero', 'eligible_text' ], __( 'Under-18 highlight — text', 'industrial-training' ), 'textarea' ),
			$f( [ 'hero', 'eligible_note' ], __( 'Under-18 highlight — condition', 'industrial-training' ) ),
			$f( [ 'hero', 'cta_primary' ], __( 'Primary button', 'industrial-training' ) ),
			$f( [ 'hero', 'cta_secondary' ], __( 'Secondary button', 'industrial-training' ) ),
		], array_merge( ...array_map( static fn( $i ) => [
			/* translators: %d: stat number */
			$f( [ 'stats', $i, 'value' ], sprintf( __( 'Stat %d value', 'industrial-training' ), $i + 1 ) ),
			/* translators: %d: stat number */
			$f( [ 'stats', $i, 'label' ], sprintf( __( 'Stat %d label', 'industrial-training' ), $i + 1 ) ),
		], array_keys( $d['stats'] ) ) ), [
			$f( [ 'hero', 'image' ], __( 'Main photo', 'industrial-training' ), 'image' ),
			$f( [ 'hero', 'inset_image' ], __( 'Inset photo', 'industrial-training' ), 'image' ),
			$f( [ 'hero', 'float_icon' ], __( 'Floating label icon', 'industrial-training' ) ),
			$f( [ 'hero', 'float_title' ], __( 'Floating label title', 'industrial-training' ) ),
			$f( [ 'hero', 'float_text' ], __( 'Floating label text', 'industrial-training' ) ),
			$f( [ 'hero', 'scroll_label' ], __( 'Scroll cue label (screen readers)', 'industrial-training' ) ),
		] ) ],
		'about'    => [ __( 'About', 'skillrise-new' ), 'users', '', array_merge( [
			$f( [ 'about', 'eyebrow' ], __( 'Small label', 'industrial-training' ) ),
			$f( [ 'about', 'title' ], __( 'Section title', 'industrial-training' ) ),
			$f( [ 'about', 'text' ], __( 'Main text', 'industrial-training' ), 'textarea' ),
			$f( [ 'about', 'text2' ], __( 'Second paragraph', 'industrial-training' ), 'textarea' ),
		], $cards( 'about', [ 'points' ], [ [ 'icon', __( 'icon', 'industrial-training' ), 'text' ], [ 'title', __( 'title', 'industrial-training' ), 'text' ], [ 'text', __( 'text', 'industrial-training' ), 'text' ] ] ) ) ],
		'programs' => [ __( 'Programs & stipend', 'skillrise-new' ), 'cap', __( 'Duration, stipend, NAPS and the “What we offer” cards.', 'skillrise-new' ), array_merge( [
			$f( [ 'batches', 'duration' ], __( 'Duration', 'industrial-training' ), 'text', __( 'Shown on every track card, e.g. "6 months".', 'industrial-training' ) ),
			$f( [ 'offers', 'stipend' ], __( 'Stipend banner', 'industrial-training' ), 'text', __( 'Leave empty to hide.', 'industrial-training' ) ),
			$f( [ 'offers', 'age' ], __( 'Age banner', 'industrial-training' ), 'text', __( 'Leave empty to hide.', 'skillrise-new' ) ),
			$f( [ 'offers', 'title' ], __( 'Section title', 'industrial-training' ) ),
			$f( [ 'offers', 'lead' ], __( 'Lead text', 'industrial-training' ), 'textarea' ),
			$f( [ 'offers', 'naps', 'title' ], __( 'NAPS banner — title', 'industrial-training' ), 'text', __( 'Leave empty to hide the banner and show NAPS as a normal card instead.', 'industrial-training' ) ),
			$f( [ 'offers', 'naps', 'eligible' ], __( 'NAPS banner — under-18 line', 'industrial-training' ) ),
			$f( [ 'offers', 'naps', 'text' ], __( 'NAPS banner — text', 'industrial-training' ), 'textarea' ),
			$f( [ 'offers', 'naps', 'note' ], __( 'NAPS banner — conditions', 'industrial-training' ), 'textarea' ),
		], $cards( 'offers', [ 'items' ], [ [ 'icon', __( 'icon', 'industrial-training' ), 'text' ], [ 'title', __( 'title', 'industrial-training' ), 'text' ], [ 'text', __( 'text', 'industrial-training' ), 'textarea' ], [ 'note', __( 'fine print', 'industrial-training' ), 'text' ], [ 'image', __( 'photo', 'industrial-training' ), 'image' ] ] ), [
			$f( [ 'offers', 'cta_title' ], __( 'Banner title', 'industrial-training' ) ),
			$f( [ 'offers', 'cta_text' ], __( 'Banner text', 'industrial-training' ), 'textarea' ),
			$f( [ 'offers', 'cta_button' ], __( 'Banner button', 'industrial-training' ) ),
		] ) ],
		'tracks'   => [ __( 'Training tracks', 'skillrise-new' ), 'tool', __( 'Also the program cards on /new.', 'skillrise-new' ), [
			$f( [ 'tracks', 'title' ], __( 'Section title', 'industrial-training' ) ),
			$f( [ 'tracks', 'lead' ], __( 'Lead text', 'industrial-training' ), 'textarea' ),
			$f( [ 'tracks', 'button' ], __( 'Card button', 'industrial-training' ) ),
			$f( [ 'tracks', 'items' ], '', 'tracks' ),
		] ],
		'why'      => [ __( 'Why us & steps', 'skillrise-new' ), 'chart', '', array_merge( [
			$f( [ 'highlights', 'title' ], __( 'Why us — title', 'skillrise-new' ) ),
			$f( [ 'highlights', 'lead' ], __( 'Why us — lead', 'skillrise-new' ), 'textarea' ),
		], $cards( 'highlights', [ 'items' ], [ [ 'icon', __( 'icon', 'industrial-training' ), 'text' ], [ 'title', __( 'title', 'industrial-training' ), 'text' ], [ 'text', __( 'text', 'industrial-training' ), 'textarea' ] ] ), [
			$f( [ 'steps', 'title' ], __( 'How it works — title', 'skillrise-new' ) ),
			$f( [ 'steps', 'lead' ], __( 'How it works — lead', 'skillrise-new' ), 'textarea' ),
		], $cards( 'steps', [ 'items' ], [ [ 'icon', __( 'icon', 'industrial-training' ), 'text' ], [ 'title', __( 'title', 'industrial-training' ), 'text' ], [ 'text', __( 'text', 'industrial-training' ), 'textarea' ], [ 'image', __( 'photo', 'industrial-training' ), 'image' ] ] ) ) ],
		'contact'  => [ __( 'Contact', 'skillrise-new' ), 'phone', __( 'Shown on both sites and used for WhatsApp links.', 'skillrise-new' ), [
			$f( [ 'contact', 'phone' ], __( 'Phone', 'industrial-training' ), 'tel' ),
			$f( [ 'contact', 'whatsapp' ], __( 'WhatsApp number', 'industrial-training' ), 'tel', __( 'With country code, e.g. +91 86393 54430. Leave empty to hide.', 'skillrise-new' ) ),
			$f( [ 'contact', 'email' ], __( 'Email', 'industrial-training' ), 'email' ),
			$f( [ 'contact', 'address' ], __( 'Address', 'industrial-training' ), 'textarea' ),
			$f( [ 'contact', 'hours' ], __( 'Hours', 'industrial-training' ) ),
			$f( [ 'contact', 'map_url' ], __( 'Google Maps link', 'industrial-training' ), 'url' ),
			$f( [ 'contact', 'map_query' ], __( 'Map pin', 'industrial-training' ), 'text', __( 'Business name with plus code, or the address. Leave empty to hide the map.', 'skillrise-new' ) ),
			$f( [ 'contact', 'title' ], __( 'Section title', 'industrial-training' ) ),
			$f( [ 'contact', 'lead' ], __( 'Lead text', 'industrial-training' ), 'textarea' ),
			$f( [ 'contact', 'cta_title' ], __( 'Card title', 'industrial-training' ) ),
			$f( [ 'contact', 'cta_text' ], __( 'Card text', 'industrial-training' ), 'textarea' ),
			$f( [ 'contact', 'cta_button' ], __( 'Card button', 'industrial-training' ) ),
			$f( [ 'contact', 'image' ], __( 'Card photo', 'industrial-training' ), 'image' ),
		] ],
		'emails'   => [ __( 'Form & emails', 'skillrise-new' ), 'mail', __( 'Registration form texts, alert recipients and anti-spam limit.', 'skillrise-new' ), [
			$f( [ 'form', 'notify_email' ], __( 'Notification emails', 'industrial-training' ), 'text', __( 'One or more addresses, separated by commas. Leave empty to use the contact email.', 'industrial-training' ) ),
			$f( [ 'form', 'confirm_student' ], __( 'Send confirmation email to the student', 'industrial-training' ), 'checkbox' ),
			$f( [ 'form', 'rate_limit' ], __( 'Registrations per network (per 10 minutes)', 'industrial-training' ), 'number', __( 'Raise it (e.g. 200) before a college registration drive, when many students share one Wi-Fi. 1–500.', 'skillrise-new' ) ),
			$f( [ 'form', 'title' ], __( 'Form title', 'industrial-training' ) ),
			$f( [ 'form', 'note' ], __( 'Form note', 'industrial-training' ) ),
			$f( [ 'form', 'success_title' ], __( 'Success heading', 'industrial-training' ), 'text', __( '{name} = first name.', 'skillrise-new' ) ),
			$f( [ 'form', 'success_text' ], __( 'Success text', 'industrial-training' ), 'textarea', __( 'Placeholders: {name} {track} {phone}', 'skillrise-new' ) ),
		] ],
		'site'     => [ __( 'Footer, SEO & testing', 'skillrise-new' ), 'clipboard', '', [
			$f( [ 'footer', 'copyright' ], __( 'Copyright', 'industrial-training' ), 'text', __( '{year} becomes the current year.', 'skillrise-new' ) ),
			$f( [ 'footer', 'credits' ], __( 'Photo credits', 'industrial-training' ), 'textarea', __( 'Links allowed.', 'industrial-training' ) ),
			$f( [ 'seo', 'enabled' ], __( 'Output SEO tags', 'industrial-training' ), 'checkbox' ),
			$f( [ 'seo', 'title' ], __( 'Page title', 'industrial-training' ) ),
			$f( [ 'seo', 'description' ], __( 'Meta description', 'industrial-training' ), 'textarea' ),
			$f( [ 'samples', 'show' ], __( 'Show sample content to visitors', 'industrial-training' ), 'checkbox', __( 'For testing only — keep this off on the live site.', 'skillrise-new' ) ),
		] ],
	];
}

/** Every leaf path in the saved settings, except the tracks list (it has its own editor). */
function srn_admin_setting_leaves( array $tree, array $prefix = [] ): array {
	$out = [];
	foreach ( $tree as $k => $v ) {
		$path = array_merge( $prefix, [ $k ] );
		if ( [ 'tracks', 'items' ] === $path ) {
			continue;
		}
		if ( is_array( $v ) ) {
			$out = array_merge( $out, srn_admin_setting_leaves( $v, $path ) );
		} else {
			$out[] = $path;
		}
	}
	return $out;
}

/* ---------- Profile (current user only; WordPress user API) ---------- */

/** Handles the Profile forms (POST), then redirects back with a notice (PRG). */
function srn_admin_handle_post(): void {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! is_user_logged_in() || ! srn_admin_is_member() ) {
		return;
	}
	$action = isset( $_POST['srn_action'] ) ? sanitize_key( wp_unslash( $_POST['srn_action'] ) ) : '';
	if ( 'followup' === $action ) {
		srn_fu_handle_post(); // Verifies the nonce for this registration, then redirects.
		return;
	}
	if ( in_array( $action, [ 'team_add', 'team_password', 'team_remove' ], true ) ) {
		check_admin_referer( 'srn_' . $action );
		srn_admin_handle_team( $action );
		return;
	}
	if ( ! in_array( $action, [ 'profile', 'password', 'sessions' ], true ) ) {
		return;
	}
	check_admin_referer( 'srn_' . $action );
	$user = wp_get_current_user();
	if ( ! current_user_can( 'edit_user', $user->ID ) ) {
		wp_die( esc_html__( 'You are not allowed to edit this profile.', 'skillrise-new' ), 403 );
	}
	$back = static function ( string $notice, string $type = 'ok' ): void {
		wp_safe_redirect( srn_admin_url( [ 'view' => 'profile', 'notice' => $notice, 'type' => $type ] ) );
		exit;
	};

	if ( 'profile' === $action ) {
		$first   = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
		$last    = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
		$display = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
		$choices = srn_admin_display_names( $user, $first, $last );
		if ( ! in_array( $display, $choices, true ) ) {
			$display = $user->display_name;
		}
		$res = wp_update_user( [ 'ID' => $user->ID, 'first_name' => $first, 'last_name' => $last, 'display_name' => $display ] );
		$back( is_wp_error( $res ) ? 'error' : 'profile', is_wp_error( $res ) ? 'bad' : 'ok' );
	}

	if ( 'password' === $action ) {
		$current = (string) wp_unslash( $_POST['current_pass'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- passwords are not sanitized.
		$new     = (string) wp_unslash( $_POST['new_pass'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$confirm = (string) wp_unslash( $_POST['confirm_pass'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( ! wp_check_password( $current, $user->user_pass, $user->ID ) ) {
			$back( 'wrong_current', 'bad' );
		}
		if ( strlen( $new ) < 8 ) {
			$back( 'too_short', 'bad' );
		}
		if ( $new !== $confirm ) {
			$back( 'mismatch', 'bad' );
		}
		if ( $new === $current ) {
			$back( 'same', 'bad' );
		}
		// wp_update_user() hashes the password and, for the current user, re-issues the login cookie.
		$res = wp_update_user( [ 'ID' => $user->ID, 'user_pass' => $new ] );
		$back( is_wp_error( $res ) ? 'error' : 'password', is_wp_error( $res ) ? 'bad' : 'ok' );
	}

	if ( 'sessions' === $action ) {
		WP_Session_Tokens::get_instance( $user->ID )->destroy_others( wp_get_session_token() );
		$back( 'sessions' );
	}
}

/** The same "Display name publicly as" choices WordPress offers on its profile screen. */
function srn_admin_display_names( WP_User $user, ?string $first = null, ?string $last = null ): array {
	$first = $first ?? (string) $user->first_name;
	$last  = $last ?? (string) $user->last_name;
	$opts  = [ $user->display_name, $user->nickname, $user->user_login ];
	if ( '' !== $first ) {
		$opts[] = $first;
	}
	if ( '' !== $last ) {
		$opts[] = $last;
	}
	if ( '' !== $first && '' !== $last ) {
		$opts[] = $first . ' ' . $last;
		$opts[] = $last . ' ' . $first;
	}
	return array_values( array_unique( array_filter( array_map( 'trim', $opts ) ) ) );
}

/** Messages for ?notice= on the Profile and Settings screens. */
function srn_admin_notice_text( string $code ): string {
	return [
		'profile'       => __( 'Profile updated.', 'skillrise-new' ),
		'password'      => __( 'Password changed. You stay signed in on this device.', 'skillrise-new' ),
		'sessions'      => __( 'Signed out of all other devices.', 'skillrise-new' ),
		'wrong_current' => __( 'Your current password is not correct.', 'skillrise-new' ),
		'too_short'     => __( 'The new password must be at least 8 characters.', 'skillrise-new' ),
		'mismatch'      => __( 'The new passwords do not match.', 'skillrise-new' ),
		'same'          => __( 'Choose a password different from the current one.', 'skillrise-new' ),
		'error'         => __( 'Something went wrong. Nothing was changed.', 'skillrise-new' ),
		'saved'         => __( 'Settings saved. Both websites now use the new content.', 'skillrise-new' ),
		'fu_saved'      => __( 'Saved to the follow-up history.', 'skillrise-new' ),
		'fu_status'     => __( 'Choose what happened (the response).', 'skillrise-new' ),
		'fu_channel'    => __( 'Choose how you contacted the student.', 'skillrise-new' ),
		'team_added'    => __( 'Team member added. Share their email and password with them.', 'skillrise-new' ),
		'team_password' => __( 'Password changed. They are signed out on all devices.', 'skillrise-new' ),
		'team_removed'  => __( 'Access removed. They can no longer sign in to the portal.', 'skillrise-new' ),
		'bad_email'     => __( 'Enter a valid email address.', 'skillrise-new' ),
		'email_taken'   => __( 'An account with this email already exists.', 'skillrise-new' ),
		'need_name'     => __( 'Enter the team member’s name.', 'skillrise-new' ),
	][ $code ] ?? '';
}


/* ---------- Roles: administrators and counsellors ---------- */

const SRN_ROLE = 'srn_counsellor';
const SRN_CAP  = 'srn_contact_students';

/** The Counsellor role: can sign in (read) and contact students. Nothing else. */
add_action( 'init', static function () {
	if ( ! get_role( SRN_ROLE ) ) {
		add_role( SRN_ROLE, __( 'Counsellor', 'skillrise-new' ), [ 'read' => true, SRN_CAP => true ] );
	}
} );

function srn_admin_is_manager(): bool {
	return current_user_can( 'manage_options' );
}

/** Anyone who may use the portal: administrators and counsellors. */
function srn_admin_is_member(): bool {
	return srn_admin_is_manager() || current_user_can( SRN_CAP );
}

/** Counsellors never see WordPress itself: no toolbar, sign-in lands on /admin, wp-admin sends them back. */
function srn_admin_is_counsellor_only(): bool {
	return is_user_logged_in() && current_user_can( SRN_CAP ) && ! current_user_can( 'manage_options' );
}
add_filter( 'show_admin_bar', static fn( $show ) => srn_admin_is_counsellor_only() ? false : $show );
add_action( 'admin_init', static function () {
	if ( srn_admin_is_counsellor_only() && ! wp_doing_ajax() && ! ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		wp_safe_redirect( srn_admin_url() );
		exit;
	}
} );
add_filter( 'login_redirect', static function ( $to, $requested, $user ) {
	return ( $user instanceof WP_User && $user->has_cap( SRN_CAP ) && ! $user->has_cap( 'manage_options' ) ) ? srn_admin_url() : $to;
}, 10, 3 );

/* ---------- Counsellor overview ---------- */

/** Simple numbers for counsellors (no email status, no totals, no charts) and the newest students to contact. */
function srn_admin_home(): array {
	$count = static function ( string $key ): int {
		$r = srn_admin_range_for( $key );
		$q = new WP_Query( [ 'post_type' => ITP_CPT, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'date_query' => srn_admin_date_query( $r ) ] );
		return (int) $q->found_posts;
	};
	$me    = get_current_user_id();
	$today = srn_admin_range_for( 'today' );
	$week  = srn_admin_range_for( '7' );
	$sum   = srn_fu_summary();
	return [
		'today'      => $count( 'today' ),
		'fu'         => $sum,
		'myToday'    => count( srn_fu_recent( 500, $me, $today['gmtFrom'] ) ),
		'myWeek'     => count( srn_fu_recent( 2000, $me, $week['gmtFrom'] ) ),
		'due'        => srn_fu_due_list( 10 ),
		'notContact' => srn_fu_not_contacted( 10 ),
		'mine'       => srn_fu_recent( 8, $me ),
	];
}

/* ---------- Team (administrators manage counsellors) ---------- */

function srn_admin_team(): array {
	$users = get_users( [ 'role' => SRN_ROLE, 'orderby' => 'display_name' ] );
	return array_map( static fn( WP_User $u ) => [
		'id'       => $u->ID,
		'name'     => $u->display_name,
		'email'    => $u->user_email,
		'since'    => strtotime( $u->user_registered . ' UTC' ),
		'sessions' => count( WP_Session_Tokens::get_instance( $u->ID )->get_all() ),
	], $users );
}

/** Add a counsellor, change a counsellor's password, or remove access. Admins only; counsellors only. */
function srn_admin_handle_team( string $action ): void {
	if ( ! srn_admin_is_manager() || ! current_user_can( 'create_users' ) && 'team_add' === $action ) {
		wp_die( esc_html__( 'Only administrators can manage the team.', 'skillrise-new' ), 403 );
	}
	$back = static function ( string $notice, string $type = 'ok' ): void {
		wp_safe_redirect( srn_admin_url( [ 'view' => 'team', 'notice' => $notice, 'type' => $type ] ) );
		exit;
	};
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput -- passwords are not sanitized.
	if ( 'team_add' === $action ) {
		$first = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
		$last  = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
		$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$pass  = (string) wp_unslash( $_POST['password'] ?? '' );
		if ( '' === $first ) {
			$back( 'need_name', 'bad' );
		}
		if ( ! is_email( $email ) ) {
			$back( 'bad_email', 'bad' );
		}
		if ( email_exists( $email ) ) {
			$back( 'email_taken', 'bad' );
		}
		if ( strlen( $pass ) < 8 ) {
			$back( 'too_short', 'bad' );
		}
		$login = sanitize_user( strtok( $email, '@' ), true ) ?: 'counsellor';
		$base  = $login;
		for ( $i = 2; username_exists( $login ); $i++ ) {
			$login = $base . $i;
		}
		$id = wp_insert_user( [
			'user_login'   => $login,
			'user_email'   => $email,
			'user_pass'    => $pass,
			'first_name'   => $first,
			'last_name'    => $last,
			'display_name' => trim( $first . ' ' . $last ),
			'role'         => SRN_ROLE,
		] );
		$back( is_wp_error( $id ) ? 'error' : 'team_added', is_wp_error( $id ) ? 'bad' : 'ok' );
	}
	$user = get_userdata( absint( $_POST['user_id'] ?? 0 ) );
	// Only counsellor accounts can be changed here (never an administrator).
	if ( ! $user || ! in_array( SRN_ROLE, (array) $user->roles, true ) || $user->has_cap( 'manage_options' ) ) {
		$back( 'error', 'bad' );
	}
	if ( 'team_password' === $action ) {
		$pass = (string) wp_unslash( $_POST['password'] ?? '' );
		if ( strlen( $pass ) < 8 ) {
			$back( 'too_short', 'bad' );
		}
		wp_set_password( $pass, $user->ID ); // Also signs them out everywhere.
		$back( 'team_password' );
	}
	if ( 'team_remove' === $action ) {
		$user->remove_role( SRN_ROLE ); // Keeps the account (no data lost) but removes all access.
		WP_Session_Tokens::get_instance( $user->ID )->destroy_all();
		$back( 'team_removed' );
	}
	// phpcs:enable
}
