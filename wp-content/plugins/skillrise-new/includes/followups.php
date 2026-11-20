<?php
/**
 * Counsellor follow-ups: a contact log per registration.
 *
 * Each log entry is a private post of the type `srn_followup` (post_parent = registration ID,
 * post_author = who contacted the student, post_content = note) with meta _srn_status, _srn_channel and _srn_next.
 * The Industrial Training registrations themselves are never modified; no tables or columns are added.
 * The current follow-up status of a registration is its newest log entry.
 */

defined( 'ABSPATH' ) || exit;

const SRN_FU_TYPE = 'srn_followup';

add_action( 'init', static function () {
	register_post_type( SRN_FU_TYPE, [
		'label'           => __( 'Follow-ups', 'skillrise-new' ),
		'public'          => false,
		'show_ui'         => false,
		'show_in_rest'    => false,
		'rewrite'         => false,
		'query_var'       => false,
		'supports'        => [ 'author' ],
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	] );
} );

/**
 * Outcomes: key => [ label, group, icon ]. Groups: pending, progress, done, closed.
 * "new" is not stored; it means "no log yet".
 */
function srn_fu_statuses(): array {
	return [
		'new'            => [ __( 'Not contacted', 'skillrise-new' ), 'pending', 'clock' ],
		'no_answer'      => [ __( 'No answer', 'skillrise-new' ), 'pending', 'phone' ],
		'callback'       => [ __( 'Call back later', 'skillrise-new' ), 'pending', 'calendar' ],
		'interested'     => [ __( 'Interested', 'skillrise-new' ), 'progress', 'spark' ],
		'confirmed'      => [ __( 'Confirmed for batch', 'skillrise-new' ), 'done', 'check' ],
		'not_interested' => [ __( 'Not interested', 'skillrise-new' ), 'closed', 'close' ],
		'wrong_number'   => [ __( 'Wrong number', 'skillrise-new' ), 'closed', 'close' ],
	];
}

function srn_fu_groups(): array {
	return [
		'pending'  => __( 'Pending', 'skillrise-new' ),
		'progress' => __( 'Interested', 'skillrise-new' ),
		'done'     => __( 'Confirmed', 'skillrise-new' ),
		'closed'   => __( 'Closed', 'skillrise-new' ),
	];
}

function srn_fu_channels(): array {
	return [
		'call'     => [ __( 'Call', 'skillrise-new' ), 'phone' ],
		'whatsapp' => [ __( 'WhatsApp', 'industrial-training' ), 'chat' ],
		'email'    => [ __( 'Email', 'industrial-training' ), 'mail' ],
	];
}

/** Save one log entry. Returns the new ID or 0. */
function srn_fu_add( int $reg_id, string $status, string $channel, string $note, string $next ): int {
	$id = wp_insert_post( [
		'post_type'    => SRN_FU_TYPE,
		'post_status'  => 'private',
		'post_parent'  => $reg_id,
		'post_author'  => get_current_user_id(),
		'post_title'   => 'Follow-up #' . $reg_id,
		'post_content' => $note,
		'meta_input'   => [ '_srn_status' => $status, '_srn_channel' => $channel, '_srn_next' => $next ],
	], true );
	return is_wp_error( $id ) ? 0 : (int) $id;
}

/** Turns a DB row into the shape templates use. */
function srn_fu_row( object $r ): array {
	$st = srn_fu_statuses()[ $r->status ] ?? srn_fu_statuses()['new'];
	return [
		'id'      => (int) $r->ID,
		'reg'     => (int) $r->post_parent,
		'author'  => (int) $r->post_author,
		'by'      => (string) ( $r->display_name ?? '' ),
		'status'  => (string) $r->status,
		'label'   => $st[0],
		'group'   => $st[1],
		'channel' => (string) ( $r->channel ?? '' ),
		'note'    => (string) ( $r->post_content ?? '' ),
		'next'    => (string) ( $r->next ?? '' ),
		'time'    => (int) strtotime( $r->post_date_gmt . ' UTC' ),
		'name'    => (string) ( $r->reg_name ?? '' ),
	];
}

/** SELECT shared by the follow-up queries (log + status/channel/next + author name + student name). */
function srn_fu_select(): string {
	global $wpdb;
	return "SELECT f.ID, f.post_parent, f.post_author, f.post_content, f.post_date_gmt, u.display_name, r.post_title AS reg_name,
		ms.meta_value AS status, mc.meta_value AS channel, mn.meta_value AS next
		FROM {$wpdb->posts} f
		INNER JOIN {$wpdb->posts} r ON r.ID = f.post_parent AND r.post_type = '" . esc_sql( ITP_CPT ) . "' AND r.post_status NOT IN ('trash','auto-draft')
		LEFT JOIN {$wpdb->users} u ON u.ID = f.post_author
		LEFT JOIN {$wpdb->postmeta} ms ON ms.post_id = f.ID AND ms.meta_key = '_srn_status'
		LEFT JOIN {$wpdb->postmeta} mc ON mc.post_id = f.ID AND mc.meta_key = '_srn_channel'
		LEFT JOIN {$wpdb->postmeta} mn ON mn.post_id = f.ID AND mn.meta_key = '_srn_next'
		WHERE f.post_type = '" . esc_sql( SRN_FU_TYPE ) . "' AND f.post_status = 'private'";
}

/**
 * Newest log entry per registration. With $reg_ids, only those registrations (one query for a whole table page).
 *
 * @return array<int, array> reg_id => row
 */
function srn_fu_latest( ?array $reg_ids = null ): array {
	global $wpdb;
	$where = '';
	if ( null !== $reg_ids ) {
		$reg_ids = array_values( array_filter( array_map( 'intval', $reg_ids ) ) );
		if ( ! $reg_ids ) {
			return [];
		}
		$where = ' AND f.post_parent IN (' . implode( ',', $reg_ids ) . ')';
	}
	// Newest = highest ID per registration (IDs grow with time).
	$sql  = srn_fu_select() . $where . " AND f.ID = (SELECT MAX(f2.ID) FROM {$wpdb->posts} f2 WHERE f2.post_type = '" . esc_sql( SRN_FU_TYPE ) . "' AND f2.post_status = 'private' AND f2.post_parent = f.post_parent)";
	$rows = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB -- built from constants and integers only.
	$out  = [];
	foreach ( (array) $rows as $r ) {
		$out[ (int) $r->post_parent ] = srn_fu_row( $r );
	}
	return $out;
}

/** Full history for one registration, newest first. */
function srn_fu_history( int $reg_id ): array {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare( srn_fu_select() . ' AND f.post_parent = %d ORDER BY f.ID DESC', $reg_id ) ); // phpcs:ignore WordPress.DB
	return array_map( 'srn_fu_row', (array) $rows );
}

/** Recent log entries (activity feed). Optional: only one author, only since a UTC datetime. */
function srn_fu_recent( int $limit = 15, int $author = 0, string $since_gmt = '' ): array {
	global $wpdb;
	$sql = srn_fu_select();
	if ( $author ) {
		$sql .= $wpdb->prepare( ' AND f.post_author = %d', $author );
	}
	if ( $since_gmt ) {
		$sql .= $wpdb->prepare( ' AND f.post_date_gmt >= %s', $since_gmt );
	}
	$sql .= $wpdb->prepare( ' ORDER BY f.ID DESC LIMIT %d', $limit );
	return array_map( 'srn_fu_row', (array) $wpdb->get_results( $sql ) ); // phpcs:ignore WordPress.DB
}

/** Total live registrations. */
function srn_fu_total_regs(): int {
	global $wpdb;
	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash','auto-draft','inherit')", ITP_CPT ) ); // phpcs:ignore WordPress.DB
}

/**
 * Status of every registration: counts per status and per group, how many are due today or earlier,
 * and the ID sets the Registrations filter needs.
 */
function srn_fu_summary(): array {
	$latest = srn_fu_latest();
	$today  = wp_date( 'Y-m-d' );
	$by     = array_fill_keys( array_keys( srn_fu_statuses() ), 0 );
	$groups = array_fill_keys( array_keys( srn_fu_groups() ), 0 );
	$ids    = [ 'contacted' => [], 'due' => [] ] + array_fill_keys( array_keys( srn_fu_groups() ), [] );
	foreach ( $latest as $reg => $row ) {
		++$by[ $row['status'] ];
		++$groups[ $row['group'] ];
		$ids['contacted'][]        = $reg;
		$ids[ $row['group'] ][]    = $reg;
		if ( in_array( $row['group'], [ 'pending', 'progress' ], true ) && '' !== $row['next'] && $row['next'] <= $today ) {
			$ids['due'][] = $reg;
		}
	}
	$total        = srn_fu_total_regs();
	$by['new']    = max( 0, $total - count( $latest ) );
	$groups['pending'] += $by['new'];
	return [ 'total' => $total, 'by' => $by, 'groups' => $groups, 'due' => count( $ids['due'] ), 'ids' => $ids ];
}

/** Work per person (everyone who logged follow-ups, plus all counsellors) since a UTC datetime. */
function srn_fu_team_stats( string $since_gmt ): array {
	global $wpdb;
	$rows  = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB
		"SELECT f.post_author, ms.meta_value AS status, COUNT(*) AS n, MAX(f.post_date_gmt) AS last
		 FROM {$wpdb->posts} f LEFT JOIN {$wpdb->postmeta} ms ON ms.post_id = f.ID AND ms.meta_key = '_srn_status'
		 WHERE f.post_type = %s AND f.post_status = 'private' AND f.post_date_gmt >= %s GROUP BY f.post_author, ms.meta_value",
		SRN_FU_TYPE,
		$since_gmt
	) );
	$lastEver = $wpdb->get_results( $wpdb->prepare( "SELECT post_author, MAX(post_date_gmt) AS last FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'private' GROUP BY post_author", SRN_FU_TYPE ), OBJECT_K ); // phpcs:ignore WordPress.DB
	$out = [];
	foreach ( (array) $rows as $r ) {
		$a = (int) $r->post_author;
		$out[ $a ] = $out[ $a ] ?? [ 'total' => 0, 'by' => [] ];
		$out[ $a ]['total']                     += (int) $r->n;
		$out[ $a ]['by'][ (string) $r->status ]  = (int) $r->n;
	}
	foreach ( (array) $lastEver as $a => $r ) {
		$out[ (int) $a ]          = $out[ (int) $a ] ?? [ 'total' => 0, 'by' => [] ];
		$out[ (int) $a ]['last']  = (int) strtotime( $r->last . ' UTC' );
	}
	return $out;
}

/** Registrations without any log yet, newest first. */
function srn_fu_not_contacted( int $limit ): array {
	$contacted = srn_fu_summary()['ids']['contacted'];
	$q         = new WP_Query( [ 'post_type' => ITP_CPT, 'post_status' => 'any', 'posts_per_page' => $limit, 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true, 'post__not_in' => $contacted ] );
	return array_map( 'srn_admin_row', $q->posts );
}

/** Registrations whose next follow-up date is today or earlier (pending / interested), soonest first. */
function srn_fu_due_list( int $limit ): array {
	$latest = srn_fu_latest();
	$today  = wp_date( 'Y-m-d' );
	$due    = array_filter( $latest, static fn( $r ) => in_array( $r['group'], [ 'pending', 'progress' ], true ) && '' !== $r['next'] && $r['next'] <= $today );
	uasort( $due, static fn( $a, $b ) => strcmp( $a['next'], $b['next'] ) );
	$ids = array_slice( array_keys( $due ), 0, $limit );
	if ( ! $ids ) {
		return [];
	}
	$q = new WP_Query( [ 'post_type' => ITP_CPT, 'post_status' => 'any', 'post__in' => $ids, 'orderby' => 'post__in', 'posts_per_page' => $limit, 'no_found_rows' => true ] );
	return array_map( 'srn_admin_row', $q->posts );
}

/* ---------- Saving (POST from a registration page) ---------- */

/** Handles the "Log contact" form. Members only; nonce per registration. */
function srn_fu_handle_post(): void {
	$reg_id = absint( $_POST['reg_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification -- verified below.
	check_admin_referer( 'srn_followup_' . $reg_id );
	$reg     = get_post( $reg_id );
	$back    = static function ( string $notice, string $type = 'ok' ) use ( $reg_id ): void {
		wp_safe_redirect( srn_admin_url( [ 'view' => 'registration', 'id' => $reg_id, 'notice' => $notice, 'type' => $type ] ) . '#followup' );
		exit;
	};
	$status  = sanitize_key( wp_unslash( $_POST['status'] ?? '' ) );
	$channel = sanitize_key( wp_unslash( $_POST['channel'] ?? '' ) );
	$note    = mb_substr( sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) ), 0, 2000 );
	$next    = sanitize_text_field( wp_unslash( $_POST['next'] ?? '' ) );
	if ( ! $reg || ITP_CPT !== $reg->post_type ) {
		wp_die( esc_html__( 'Registration not found.', 'skillrise-new' ), 404 );
	}
	if ( 'new' === $status || ! isset( srn_fu_statuses()[ $status ] ) ) {
		$back( 'fu_status', 'bad' );
	}
	if ( ! isset( srn_fu_channels()[ $channel ] ) ) {
		$back( 'fu_channel', 'bad' );
	}
	$d    = DateTimeImmutable::createFromFormat( '!Y-m-d', $next, wp_timezone() );
	$next = $d && $d->format( 'Y-m-d' ) === $next ? $next : '';
	$ok = srn_fu_add( $reg_id, $status, $channel, $note, $next );
	$back( $ok ? 'fu_saved' : 'error', $ok ? 'ok' : 'bad' );
}

/* ---------- Small view helpers ---------- */

function srn_fu_badge( ?array $latest ): string {
	$key = $latest['status'] ?? 'new';
	$st  = srn_fu_statuses()[ $key ] ?? srn_fu_statuses()['new'];
	return '<span class="srna-fu srna-fu-' . esc_attr( $st[1] ) . '">' . srn_icon( $st[2] ) . esc_html( $st[0] ) . '</span>';
}

function srn_fu_ago( int $ts ): string {
	/* translators: %s: time difference, e.g. "5 mins" */
	return sprintf( __( '%s ago', 'skillrise-new' ), human_time_diff( $ts, time() ) );
}

function srn_fu_next_label( string $ymd ): string {
	if ( '' === $ymd ) {
		return '';
	}
	$today = wp_date( 'Y-m-d' );
	$ts    = DateTimeImmutable::createFromFormat( '!Y-m-d', $ymd, wp_timezone() )->getTimestamp();
	if ( $ymd < $today ) {
		/* translators: %s: date */
		return sprintf( __( 'Overdue since %s', 'skillrise-new' ), wp_date( 'j M', $ts ) );
	}
	return $ymd === $today ? __( 'Due today', 'skillrise-new' ) : sprintf( /* translators: %s: date */ __( 'Next: %s', 'skillrise-new' ), wp_date( 'D j M', $ts ) );
}
