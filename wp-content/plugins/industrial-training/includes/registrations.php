<?php
/**
 * Private "itp_registration" post type, admin list table and CSV export.
 */

defined( 'ABSPATH' ) || exit;

const ITP_CPT    = 'itp_registration';
// Year of study and training track are no longer collected (older entries keep theirs in the database).
const ITP_FIELDS = [ 'email', 'phone', 'college', 'branch' ];

add_action( 'init', 'itp_register_post_type' );

function itp_register_post_type(): void {
	$caps = array_fill_keys( [ 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'delete_posts', 'delete_others_posts', 'publish_posts', 'read_private_posts', 'edit_private_posts', 'delete_private_posts', 'edit_published_posts', 'delete_published_posts' ], 'manage_options' );

	register_post_type( ITP_CPT, [
		'labels'          => [
			'name'          => __( 'Training Registrations', 'industrial-training' ),
			'singular_name' => __( 'Registration', 'industrial-training' ),
			'menu_name'     => __( 'Registrations', 'industrial-training' ),
			'all_items'     => __( 'All registrations', 'industrial-training' ),
			'edit_item'     => __( 'Registration', 'industrial-training' ),
			'search_items'  => __( 'Search registrations', 'industrial-training' ),
			'not_found'     => __( 'No registrations yet.', 'industrial-training' ),
		],
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'menu_icon'       => 'dashicons-welcome-learn-more',
		'menu_position'   => 26,
		'supports'        => [ 'title' ],
		'capability_type' => 'post',
		'capabilities'    => $caps + [ 'create_posts' => 'do_not_allow' ],
		'map_meta_cap'    => false,
		'rewrite'         => false,
		'query_var'       => false,
	] );
}

/** @return int Post ID, or 0 on failure. */
function itp_save_registration( array $d ): int {
	$meta = [ '_itp_created' => current_time( 'mysql', true ) ];
	foreach ( ITP_FIELDS as $f ) {
		$meta[ '_itp_' . $f ] = $d[ $f ];
	}
	$id = wp_insert_post( [
		'post_type'    => ITP_CPT,
		'post_status'  => 'private',
		'post_title'   => $d['fullName'],
		// Stored so the built-in admin search also matches email, phone and college.
		'post_content' => implode( ' | ', [ $d['email'], $d['phone'], $d['college'] ] ),
		'meta_input'   => $meta,
	], true );

	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * When the student registered, as a Unix timestamp. Read from the UTC column: post_date holds whatever the
 * site timezone was at the time (UTC on older entries), so it would show 5½ hours early in India.
 */
function itp_registered_at( WP_Post $post ): int {
	$gmt = '0000-00-00 00:00:00' !== $post->post_date_gmt ? $post->post_date_gmt : (string) get_post_meta( $post->ID, '_itp_created', true );
	return (int) strtotime( $gmt . ' UTC' );
}

if ( ! is_admin() ) {
	return;
}

/* ---------- List table ---------- */

add_filter( 'manage_' . ITP_CPT . '_posts_columns', function () {
	return [
		'cb'          => '<input type="checkbox">',
		'title'       => __( 'Name', 'industrial-training' ),
		'itp_email'   => __( 'Email', 'industrial-training' ),
		'itp_phone'   => __( 'Phone', 'industrial-training' ),
		'itp_college' => __( 'College', 'industrial-training' ),
		'itp_branch'  => __( 'Branch', 'industrial-training' ),
		'itp_mail'    => __( 'Email alert', 'industrial-training' ),
		// Own column instead of WordPress's "date", which shows "Last Modified" for private posts.
		'itp_date'    => __( 'Registered', 'industrial-training' ),
	];
} );

add_filter( 'manage_edit-' . ITP_CPT . '_sortable_columns', function ( $columns ) {
	return [ 'title' => 'title', 'itp_date' => [ 'date', true ] ];
} );

add_action( 'manage_' . ITP_CPT . '_posts_custom_column', function ( $column, $post_id ) {
	$value = (string) get_post_meta( $post_id, '_' . $column, true );
	if ( 'itp_date' === $column ) {
		$time = itp_registered_at( get_post( $post_id ) );
		/* translators: 1: date, 2: time */
		printf( esc_html__( '%1$s at %2$s', 'industrial-training' ), esc_html( wp_date( get_option( 'date_format' ), $time ) ), esc_html( wp_date( get_option( 'time_format' ), $time ) ) );
	} elseif ( 'itp_email' === $column ) {
		printf( '<a href="mailto:%1$s">%2$s</a>', esc_attr( $value ), esc_html( $value ) );
	} elseif ( 'itp_mail' === $column ) {
		// "sent" = accepted by the mail server; delivery to the inbox still depends on SMTP / spam filters.
		if ( 'sent' === $value ) {
			echo '<span style="color:#15803d">✓ ' . esc_html__( 'Sent', 'industrial-training' ) . '</span>';
		} elseif ( str_starts_with( $value, 'failed' ) ) {
			printf( '<span style="color:#b91c1c" title="%1$s">✗ %2$s</span><br><small>%1$s</small>', esc_attr( trim( substr( $value, 7 ) ) ), esc_html__( 'Failed', 'industrial-training' ) );
		} else {
			echo '<span style="color:#64748b">—</span>';
		}
	} elseif ( 'itp_phone' === $column ) {
		printf( '<a href="tel:%1$s">%2$s</a>', esc_attr( preg_replace( '/[^0-9+]/', '', $value ) ), esc_html( $value ) );
	} else {
		echo esc_html( $value );
	}
}, 10, 2 );

add_filter( 'post_row_actions', function ( $actions, $post ) {
	if ( ITP_CPT === $post->post_type ) {
		unset( $actions['inline hide-if-no-js'] );
		$actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( get_edit_post_link( $post ) ), esc_html__( 'View', 'industrial-training' ) );
	}
	return $actions;
}, 10, 2 );

add_filter( 'bulk_actions-edit-' . ITP_CPT, function ( $actions ) {
	unset( $actions['edit'] );
	return $actions;
} );

add_action( 'manage_posts_extra_tablenav', function ( $which ) {
	global $typenow;
	if ( ITP_CPT !== $typenow || 'top' !== $which || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$url = wp_nonce_url( add_query_arg( [
		'action'    => 'itp_export',
		's'         => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
	], admin_url( 'admin-post.php' ) ), 'itp_export' );
	printf( '<div class="alignleft actions"><a class="button" href="%s">%s</a></div>', esc_url( $url ), esc_html__( 'Export CSV', 'industrial-training' ) );
} );

/* ---------- Read-only detail screen ---------- */

add_action( 'add_meta_boxes_' . ITP_CPT, function () {
	add_meta_box( 'itp-details', __( 'Registration details', 'industrial-training' ), function ( $post ) {
		$labels = [
			'email'   => __( 'Email', 'industrial-training' ),
			'phone'   => __( 'Phone', 'industrial-training' ),
			'college' => __( 'College / Polytechnic', 'industrial-training' ),
			'branch'  => __( 'Branch', 'industrial-training' ),
			'created' => __( 'Submitted (UTC)', 'industrial-training' ),
		];
		echo '<table class="widefat striped"><tbody>';
		foreach ( $labels as $key => $label ) {
			printf( '<tr><th scope="row" style="width:200px">%1$s</th><td>%2$s</td></tr>', esc_html( $label ), esc_html( (string) get_post_meta( $post->ID, '_itp_' . $key, true ) ) );
		}
		echo '</tbody></table>';
	}, ITP_CPT, 'normal', 'high' );
} );

/* ---------- CSV export ---------- */

add_action( 'admin_post_itp_export', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to export registrations.', 'industrial-training' ), 403 );
	}
	check_admin_referer( 'itp_export' );

	$args = [
		'post_type'      => ITP_CPT,
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	];
	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	if ( '' !== $search ) {
		$args['s'] = $search;
	}

	// Prevents spreadsheet formula injection (=, +, -, @ at the start of a cell).
	$cell = static fn( $v ) => preg_match( '/^[=+\-@\t\r]/', (string) $v ) ? "'" . $v : (string) $v;

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=training-registrations-' . gmdate( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM so Excel shows ₹/accents correctly.
	fputcsv( $out, [ 'ID', 'Name', 'Email', 'Phone', 'College', 'Branch', 'Registered (' . wp_timezone_string() . ')' ] );
	foreach ( get_posts( $args ) as $post ) {
		$row = [ $post->ID, $post->post_title ];
		foreach ( ITP_FIELDS as $f ) {
			$row[] = get_post_meta( $post->ID, '_itp_' . $f, true );
		}
		$row[] = wp_date( 'Y-m-d H:i', itp_registered_at( $post ) );
		fputcsv( $out, array_map( $cell, $row ) );
	}
	fclose( $out );
	exit;
} );
