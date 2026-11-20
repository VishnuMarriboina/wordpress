<?php
/**
 * /admin screen: Overview for counsellors — their follow-up work. No email status, totals, charts or exports.
 */

defined( 'ABSPATH' ) || exit;

$h  = $ctx['home'];
$fu = $h['fu'];
?>
<header class="srna-top">
	<div>
		<p class="srna-eyebrow"><?php echo esc_html( wp_date( 'l, j F Y' ) ); ?></p>
		<?php /* translators: %s: first name */ ?>
		<h1><?php echo esc_html( sprintf( __( 'Hello, %s', 'skillrise-new' ), $user->first_name ?: $user->display_name ) ); ?></h1>
		<p class="srna-muted"><?php esc_html_e( 'Contact students, then log the response on their page.', 'skillrise-new' ); ?></p>
	</div>
</header>

<dl class="srna-kpis">
	<div class="srna-kpi srna-kpi-main srna-kpi-link"><dt><a href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations', 'fu' => 'new' ] ) ); ?>"><?php esc_html_e( 'Not contacted yet', 'skillrise-new' ); ?></a></dt><dd><?php echo esc_html( number_format_i18n( $fu['by']['new'] ) ); ?></dd></div>
	<div class="srna-kpi srna-kpi-link<?php echo $fu['due'] ? ' srna-kpi-warn' : ''; ?>"><dt><a href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations', 'fu' => 'due' ] ) ); ?>"><?php esc_html_e( 'Follow-ups due', 'skillrise-new' ); ?></a></dt><dd><?php echo esc_html( number_format_i18n( $fu['due'] ) ); ?></dd></div>
	<div class="srna-kpi"><dt><?php esc_html_e( 'My contacts today', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $h['myToday'] ) ); ?></dd></div>
	<div class="srna-kpi"><dt><?php esc_html_e( 'My contacts (7 days)', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $h['myWeek'] ) ); ?></dd></div>
	<div class="srna-kpi"><dt><?php esc_html_e( 'New registrations today', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $h['today'] ) ); ?></dd></div>
</dl>

<?php include SRN_DIR . 'templates/admin/part-funnel.php'; ?>

<?php if ( $h['due'] ) : ?>
<section class="srna-card srna-card-full" aria-labelledby="srna-due">
	<header class="srna-card-head">
		<h2 id="srna-due"><?php esc_html_e( 'Follow-ups due', 'skillrise-new' ); ?></h2>
		<a class="srna-link" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations', 'fu' => 'due' ] ) ); ?>"><?php esc_html_e( 'See all', 'skillrise-new' ); ?> <?php echo srn_icon( 'arrow' ); // phpcs:ignore ?></a>
	</header>
	<?php $rows = $h['due']; include SRN_DIR . 'templates/admin/part-table.php'; ?>
</section>
<?php endif; ?>

<section class="srna-card srna-card-full" aria-labelledby="srna-new">
	<header class="srna-card-head">
		<h2 id="srna-new"><?php esc_html_e( 'Not contacted yet', 'skillrise-new' ); ?></h2>
		<a class="srna-link" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations', 'fu' => 'new' ] ) ); ?>"><?php esc_html_e( 'See all', 'skillrise-new' ); ?> <?php echo srn_icon( 'arrow' ); // phpcs:ignore ?></a>
	</header>
	<?php if ( $h['notContact'] ) : ?>
		<?php $rows = $h['notContact']; include SRN_DIR . 'templates/admin/part-table.php'; ?>
	<?php else : ?>
		<div class="srna-empty srna-empty-ok"><?php echo srn_icon( 'check' ); // phpcs:ignore ?><p><strong><?php esc_html_e( 'All caught up', 'skillrise-new' ); ?></strong><?php esc_html_e( 'Every student has been contacted.', 'skillrise-new' ); ?></p></div>
	<?php endif; ?>
</section>

<section class="srna-card srna-card-full" aria-labelledby="srna-mine">
	<header class="srna-card-head"><h2 id="srna-mine"><?php esc_html_e( 'My recent activity', 'skillrise-new' ); ?></h2></header>
	<?php $feed = $h['mine']; $feed_show_by = false; include SRN_DIR . 'templates/admin/part-feed.php'; ?>
</section>
