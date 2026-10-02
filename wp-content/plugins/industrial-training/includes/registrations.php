<?php
/**
 * Private "itp_registration" post type, admin list table and CSV export.
 */

defined( 'ABSPATH' ) || exit;

const ITP_CPT    = 'itp_registration';
const ITP_FIELDS = [ 'email', 'phone', 'college', 'branch', 'year', 'track' ];

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
		'itp_year'    => __( 'Year', 'industrial-training' ),
		'itp_track'   => __( 'Track', 'industrial-training' ),
		'date'        => __( 'Date', 'industrial-training' ),
	];
} );

add_action( 'manage_' . ITP_CPT . '_posts_custom_column', function ( $column, $post_id ) {
	$value = (string) get_post_meta( $post_id, '_' . $column, true );
	if ( 'itp_email' === $column ) {
		printf( '<a href="mailto:%1$s">%2$s</a>', esc_attr( $value ), esc_html( $value ) );
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

/** Track filter + Export button above the table. */
add_action( 'restrict_manage_posts', function ( $post_type, $which ) {
	if ( ITP_CPT !== $post_type || 'top' !== $which ) {
		return;
	}
	$current = isset( $_GET['itp_track'] ) ? sanitize_text_field( wp_unslash( $_GET['itp_track'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	echo '<label class="screen-reader-text" for="itp-filter-track">' . esc_html__( 'Filter by track', 'industrial-training' ) . '</label>';
	echo '<select name="itp_track" id="itp-filter-track"><option value="">' . esc_html__( 'All tracks', 'industrial-training' ) . '</option>';
	foreach ( itp_track_names() as $name ) {
		printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $name ), selected( $current, $name, false ), esc_html( $name ) );
	}
	echo '</select>';
}, 10, 2 );

add_action( 'manage_posts_extra_tablenav', function ( $which ) {
	global $typenow;
	if ( ITP_CPT !== $typenow || 'top' !== $which || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$url = wp_nonce_url( add_query_arg( [
		'action'    => 'itp_export',
		'itp_track' => isset( $_GET['itp_track'] ) ? sanitize_text_field( wp_unslash( $_GET['itp_track'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
		's'         => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
	], admin_url( 'admin-post.php' ) ), 'itp_export' );
	printf( '<div class="alignleft actions"><a class="button" href="%s">%s</a></div>', esc_url( $url ), esc_html__( 'Export CSV', 'industrial-training' ) );
} );

add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( ! $q->is_main_query() || ITP_CPT !== $q->get( 'post_type' ) ) {
		return;
	}
	$track = isset( $_GET['itp_track'] ) ? sanitize_text_field( wp_unslash( $_GET['itp_track'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( '' !== $track ) {
		$q->set( 'meta_query', [ [ 'key' => '_itp_track', 'value' => $track ] ] );
	}
} );

/* ---------- Read-only detail screen ---------- */

add_action( 'add_meta_boxes_' . ITP_CPT, function () {
	add_meta_box( 'itp-details', __( 'Registration details', 'industrial-training' ), function ( $post ) {
		$labels = [
			'email'   => __( 'Email', 'industrial-training' ),
			'phone'   => __( 'Phone', 'industrial-training' ),
			'college' => __( 'College / Polytechnic', 'industrial-training' ),
			'branch'  => __( 'Branch', 'industrial-training' ),
			'year'    => __( 'Year of study', 'industrial-training' ),
			'track'   => __( 'Training track', 'industrial-training' ),
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
	$track = isset( $_GET['itp_track'] ) ? sanitize_text_field( wp_unslash( $_GET['itp_track'] ) ) : '';
	if ( '' !== $track ) {
		$args['meta_query'] = [ [ 'key' => '_itp_track', 'value' => $track ] ]; // phpcs:ignore WordPress.DB.SlowDBQuery
	}
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
	fputcsv( $out, [ 'ID', 'Name', 'Email', 'Phone', 'College', 'Branch', 'Year', 'Track', 'Date (UTC)' ] );
	foreach ( get_posts( $args ) as $post ) {
		$row = [ $post->ID, $post->post_title ];
		foreach ( ITP_FIELDS as $f ) {
			$row[] = get_post_meta( $post->ID, '_itp_' . $f, true );
		}
		$row[] = get_post_meta( $post->ID, '_itp_created', true );
		fputcsv( $out, array_map( $cell, $row ) );
	}
	fclose( $out );
	exit;
} );
