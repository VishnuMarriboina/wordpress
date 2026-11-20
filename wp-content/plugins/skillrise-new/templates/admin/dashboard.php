<?php
/**
 * /admin screen: Overview. Included by templates/admin.php (variables: $ctx, $user, $when, $mail_tag).
 */

defined( 'ABSPATH' ) || exit;
?>
	<?php
	$st      = $ctx['stats'];
	$rg      = $ctx['range'];
	$rargs   = srn_admin_range_args( $rg );
	$unit_title = [
		'day'   => __( 'Registrations per day', 'skillrise-new' ),
		'week'  => __( 'Registrations per week', 'skillrise-new' ),
		'month' => __( 'Registrations per month', 'skillrise-new' ),
	][ $st['unit'] ];
	?>
	<header class="srna-top">
		<div>
			<p class="srna-eyebrow"><?php echo esc_html( wp_date( 'l, j F Y' ) ); ?></p>
			<?php /* translators: %s: first name */ ?>
			<h1><?php echo esc_html( sprintf( __( 'Welcome back, %s', 'skillrise-new' ), $user->first_name ?: $user->display_name ) ); ?></h1>
		</div>
		<a class="srna-btn srna-btn-primary" href="<?php echo esc_url( srn_admin_export_url() ); ?>"><?php echo srn_icon( 'clipboard' ); // phpcs:ignore ?><?php esc_html_e( 'Export all as CSV', 'skillrise-new' ); ?></a>
	</header>

	<?php include SRN_DIR . 'templates/admin/part-range.php'; ?>

	<dl class="srna-kpis">
		<div class="srna-kpi srna-kpi-main"><dt><?php esc_html_e( 'Registrations', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $st['count'] ) ); ?></dd></div>
		<div class="srna-kpi"><dt><?php esc_html_e( 'Daily average', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $st['avg'], $st['avg'] < 10 ? 1 : 0 ) ); ?></dd></div>
		<div class="srna-kpi">
			<dt><?php esc_html_e( 'Busiest day', 'skillrise-new' ); ?></dt>
			<dd><?php echo $st['busiest'] ? esc_html( number_format_i18n( $st['busiest']['n'] ) ) . ' <small>' . esc_html( $st['busiest']['date'] ) . '</small>' : '—'; ?></dd>
		</div>
		<div class="srna-kpi<?php echo $st['failed'] ? ' srna-kpi-warn' : ''; ?>">
			<dt><?php esc_html_e( 'Email alerts failed', 'skillrise-new' ); ?></dt>
			<dd><?php echo esc_html( number_format_i18n( $st['failed'] ) ); ?></dd>
			<p class="srna-kpi-note"><?php echo $st['failed'] ? srn_icon( 'bolt' ) . esc_html__( 'Check SMTP settings', 'skillrise-new' ) : srn_icon( 'check' ) . esc_html__( 'All delivered to the mail server', 'skillrise-new' ); // phpcs:ignore ?></p>
		</div>
		<div class="srna-kpi srna-kpi-quiet"><dt><?php esc_html_e( 'All-time total', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $st['total'] ) ); ?></dd></div>
	</dl>

	<?php $fu = $ctx['fu']; include SRN_DIR . 'templates/admin/part-funnel.php'; ?>

<div class="srna-grid">
		<section class="srna-card srna-card-wide" aria-labelledby="srna-trend">
			<header class="srna-card-head">
				<h2 id="srna-trend"><?php echo esc_html( $unit_title ); ?></h2>
				<p class="srna-muted"><?php echo esc_html( $rg['label'] ); ?></p>
			</header>
			<?php $series = $st['series']; $unit = $st['unit']; $count = $st['count']; include SRN_DIR . 'templates/admin/part-columns.php'; ?>
		</section>

		<section class="srna-card" aria-labelledby="srna-branch">
			<header class="srna-card-head">
				<h2 id="srna-branch"><?php esc_html_e( 'By branch', 'skillrise-new' ); ?></h2>
				<p class="srna-muted"><?php echo esc_html( $rg['label'] ); ?></p>
			</header>
			<?php if ( $st['branches'] ) : ?>
				<?php $bmax = max( 1, $st['branches'][0]['n'] ); ?>
				<ul class="srna-hbars" role="list">
					<?php foreach ( $st['branches'] as $b ) : ?>
						<li>
							<a href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations', 'branch' => $b['branch'] ] + $rargs ) ); ?>">
								<span class="srna-hbar-label"><?php echo esc_html( $b['branch'] ); ?></span>
								<span class="srna-hbar-val"><?php echo esc_html( number_format_i18n( $b['n'] ) ); ?> <small>(<?php echo esc_html( (string) round( $b['n'] / max( 1, $st['count'] ) * 100 ) ); ?>%)</small></span>
								<span class="srna-hbar" aria-hidden="true"><span style="width:<?php echo esc_attr( (string) round( $b['n'] / $bmax * 100, 1 ) ); ?>%"></span></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="srna-empty"><?php esc_html_e( 'No registrations in this date range.', 'skillrise-new' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="srna-card srna-card-full" aria-labelledby="srna-activity">
			<header class="srna-card-head">
				<h2 id="srna-activity"><?php esc_html_e( 'Latest follow-ups by the team', 'skillrise-new' ); ?></h2>
				<a class="srna-link" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'team' ] ) ); ?>"><?php esc_html_e( 'Team activity', 'skillrise-new' ); ?> <?php echo srn_icon( 'arrow' ); // phpcs:ignore ?></a>
			</header>
			<?php $feed = $ctx['feed']; $feed_show_by = true; include SRN_DIR . 'templates/admin/part-feed.php'; ?>
		</section>

		<section class="srna-card srna-card-full" aria-labelledby="srna-latest">
			<header class="srna-card-head">
				<h2 id="srna-latest"><?php esc_html_e( 'Latest registrations', 'skillrise-new' ); ?></h2>
				<a class="srna-link" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations' ] + $rargs ) ); ?>"><?php esc_html_e( 'View all in this range', 'skillrise-new' ); ?> <?php echo srn_icon( 'arrow' ); // phpcs:ignore ?></a>
			</header>
			<?php $rows = $ctx['latest']; include SRN_DIR . 'templates/admin/part-table.php'; ?>
		</section>
	</div>

