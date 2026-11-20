<?php
/**
 * /admin screen: Registrations list. Included by templates/admin.php (variables: $ctx, $user, $when, $mail_tag).
 */

defined( 'ABSPATH' ) || exit;
?>
	<?php $f = $ctx['filters']; ?>
	<header class="srna-top">
		<div>
			<p class="srna-eyebrow"><?php esc_html_e( 'Students', 'skillrise-new' ); ?></p>
			<h1><?php esc_html_e( 'Registrations', 'skillrise-new' ); ?></h1>
		</div>
	<?php if ( srn_admin_is_manager() ) : ?>
		<a class="srna-btn srna-btn-primary" href="<?php echo esc_url( srn_admin_export_url( $f['s'] ) ); ?>"><?php echo srn_icon( 'clipboard' ); // phpcs:ignore ?><?php echo esc_html( '' !== $f['s'] ? __( 'Export search as CSV', 'skillrise-new' ) : __( 'Export all as CSV', 'skillrise-new' ) ); ?></a>
	<?php endif; ?>
	</header>

	<form class="srna-filters" method="get" action="<?php echo esc_url( srn_admin_url() ); ?>" role="search">
		<?php if ( ! get_option( 'permalink_structure' ) ) : ?>
			<input type="hidden" name="<?php echo esc_attr( SRN_ADMIN_QUERY_VAR ); ?>" value="1">
		<?php endif; ?>
		<input type="hidden" name="view" value="registrations">
		<div class="srna-field srna-field-grow">
			<label for="srna-s"><?php esc_html_e( 'Search', 'skillrise-new' ); ?></label>
			<input id="srna-s" type="search" name="s" value="<?php echo esc_attr( $f['s'] ); ?>" placeholder="<?php esc_attr_e( 'Name, email, phone or college', 'skillrise-new' ); ?>">
		</div>
		<div class="srna-field">
			<label for="srna-branch-f"><?php esc_html_e( 'Branch', 'industrial-training' ); ?></label>
			<select id="srna-branch-f" name="branch">
				<option value=""><?php esc_html_e( 'All branches', 'skillrise-new' ); ?></option>
				<?php foreach ( itp_branches() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $f['branch'], $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php $fr = $f['range']; $has_range = 'any' !== $fr['key']; ?>
		<input type="hidden" name="range" value="custom">
		<div class="srna-field srna-field-date">
			<label for="srna-from"><?php esc_html_e( 'From', 'skillrise-new' ); ?></label>
			<input id="srna-from" type="date" name="from" value="<?php echo esc_attr( $has_range ? $fr['fromYmd'] : '' ); ?>" max="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
		</div>
		<div class="srna-field srna-field-date">
			<label for="srna-to"><?php esc_html_e( 'To', 'skillrise-new' ); ?></label>
			<input id="srna-to" type="date" name="to" value="<?php echo esc_attr( $has_range ? $fr['toYmd'] : '' ); ?>" max="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
		</div>
		<div class="srna-field">
			<label for="srna-fu-f"><?php esc_html_e( 'Follow-up', 'skillrise-new' ); ?></label>
			<select id="srna-fu-f" name="fu">
				<?php foreach ( [ '' => __( 'All', 'skillrise-new' ), 'new' => __( 'Not contacted', 'skillrise-new' ), 'pending' => __( 'Pending (incl. not contacted)', 'skillrise-new' ), 'due' => __( 'Follow-up due', 'skillrise-new' ), 'progress' => __( 'Interested', 'skillrise-new' ), 'done' => __( 'Confirmed', 'skillrise-new' ), 'closed' => __( 'Closed', 'skillrise-new' ) ] as $k => $label ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $f['fu'], $k ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="srna-field">
			<label for="srna-order"><?php esc_html_e( 'Sort', 'skillrise-new' ); ?></label>
			<select id="srna-order" name="order">
				<option value="desc"<?php selected( $f['order'], 'desc' ); ?>><?php esc_html_e( 'Newest first', 'skillrise-new' ); ?></option>
				<option value="asc"<?php selected( $f['order'], 'asc' ); ?>><?php esc_html_e( 'Oldest first', 'skillrise-new' ); ?></option>
			</select>
		</div>
		<div class="srna-filter-actions">
			<button class="srna-btn srna-btn-dark" type="submit"><?php esc_html_e( 'Apply', 'skillrise-new' ); ?></button>
			<?php if ( '' !== $f['s'] || '' !== $f['branch'] || 'asc' === $f['order'] || $has_range || '' !== $f['fu'] ) : ?>
				<a class="srna-btn srna-btn-ghost" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations' ] ) ); ?>"><?php esc_html_e( 'Reset', 'skillrise-new' ); ?></a>
			<?php endif; ?>
		</div>
	</form>

	<section class="srna-card srna-card-full" aria-labelledby="srna-results">
		<header class="srna-card-head">
			<?php /* translators: %s: number of registrations */ ?>
			<h2 id="srna-results" role="status"><?php echo esc_html( sprintf( _n( '%s registration', '%s registrations', $ctx['total'], 'skillrise-new' ), number_format_i18n( $ctx['total'] ) ) ); ?></h2>
			<?php if ( $has_range ) : ?>
				<?php /* translators: %s: date range */ ?>
				<p class="srna-range-label"><?php echo srn_icon( 'calendar' ); // phpcs:ignore ?><?php echo esc_html( sprintf( __( 'Registered %s', 'skillrise-new' ), $fr['label'] ) ); ?></p>
			<?php endif; ?>
			<?php if ( srn_admin_is_manager() && ( '' !== $f['branch'] || $has_range ) ) : ?>
				<p class="srna-muted"><?php esc_html_e( 'CSV export uses the search term only, not the branch or date filters.', 'skillrise-new' ); ?></p>
			<?php endif; ?>
		</header>
		<?php $rows = $ctx['rows']; include SRN_DIR . 'templates/admin/part-table.php'; ?>
		<?php if ( $ctx['pages'] > 1 ) : ?>
			<nav class="srna-pages" aria-label="<?php esc_attr_e( 'Pages', 'skillrise-new' ); ?>">
				<?php
				echo wp_kses_post( paginate_links( [
					'base'      => add_query_arg( 'paged', '%#%', srn_admin_url( [ 'view' => 'registrations', 's' => $f['s'], 'branch' => $f['branch'], 'fu' => $f['fu'], 'order' => 'asc' === $f['order'] ? 'asc' : '' ] + srn_admin_range_args( $f['range'] ) ) ),
					'format'    => '',
					'current'   => $f['paged'],
					'total'     => $ctx['pages'],
					'prev_text' => __( '← Previous', 'skillrise-new' ),
					'next_text' => __( 'Next →', 'skillrise-new' ),
				] ) );
				?>
			</nav>
		<?php endif; ?>
	</section>

