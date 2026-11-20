<?php
/**
 * /admin screen: Email notifications. Reads the status the Industrial Training plugin stores on each
 * registration (_itp_mail). Nothing here sends or re-sends email.
 */

defined( 'ABSPATH' ) || exit;

$rg         = $ctx['range'];
$range_view = 'emails';
$mail       = $ctx['mail'];
$cfg        = $ctx['config'];
$recorded   = $mail['sent'] + $mail['failed'];
?>
<header class="srna-top">
	<div>
		<p class="srna-eyebrow"><?php esc_html_e( 'Registration alerts', 'skillrise-new' ); ?></p>
		<h1><?php esc_html_e( 'Email notifications', 'skillrise-new' ); ?></h1>
	</div>
	<a class="srna-btn srna-btn-ghost" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'settings' ] ) . '#tab-emails' ); ?>"><?php echo srn_icon( 'gear' ); // phpcs:ignore ?><?php esc_html_e( 'Email settings', 'skillrise-new' ); ?></a>
</header>

<?php include SRN_DIR . 'templates/admin/part-range.php'; ?>

<dl class="srna-kpis">
	<div class="srna-kpi"><dt><?php esc_html_e( 'Alerts sent', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $mail['sent'] ) ); ?></dd></div>
	<div class="srna-kpi<?php echo $mail['failed'] ? ' srna-kpi-bad' : ''; ?>"><dt><?php esc_html_e( 'Alerts failed', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $mail['failed'] ) ); ?></dd></div>
	<div class="srna-kpi"><dt><?php esc_html_e( 'Success rate', 'skillrise-new' ); ?></dt><dd><?php echo $recorded ? esc_html( round( $mail['sent'] / $recorded * 100 ) . '%' ) : '—'; ?></dd></div>
	<div class="srna-kpi srna-kpi-quiet"><dt><?php esc_html_e( 'Status not recorded', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $mail['none'] ) ); ?></dd></div>
</dl>

<div class="srna-grid">
	<section class="srna-card srna-card-wide" aria-labelledby="srna-fail">
		<header class="srna-card-head">
			<?php /* translators: %s: number */ ?>
			<h2 id="srna-fail"><?php echo esc_html( sprintf( _n( '%s failed alert', '%s failed alerts', $ctx['total'], 'skillrise-new' ), number_format_i18n( $ctx['total'] ) ) ); ?></h2>
			<p class="srna-muted"><?php echo esc_html( $rg['label'] ); ?></p>
		</header>
		<?php if ( ! $ctx['failures'] ) : ?>
			<div class="srna-empty srna-empty-ok"><?php echo srn_icon( 'check' ); // phpcs:ignore ?><p><strong><?php esc_html_e( 'No failed alerts', 'skillrise-new' ); ?></strong><?php esc_html_e( 'Every alert in this range was accepted by the mail server.', 'skillrise-new' ); ?></p></div>
		<?php else : ?>
			<ul class="srna-faillist" role="list">
				<?php foreach ( $ctx['failures'] as $r ) : ?>
					<li>
						<a class="srna-row-link" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registration', 'id' => $r['id'] ] ) ); ?>"><?php echo esc_html( $r['name'] ); ?></a>
						<time datetime="<?php echo esc_attr( gmdate( 'c', $r['time'] ) ); ?>"><?php echo esc_html( $when( $r['time'] ) ); ?></time>
						<code><?php echo esc_html( $r['mailErr'] ?: __( 'unknown error', 'industrial-training' ) ); ?></code>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $ctx['pages'] > 1 ) : ?>
				<nav class="srna-pages" aria-label="<?php esc_attr_e( 'Pages', 'skillrise-new' ); ?>">
					<?php echo wp_kses_post( paginate_links( [ 'base' => add_query_arg( 'paged', '%#%', srn_admin_url( [ 'view' => 'emails' ] + srn_admin_range_args( $rg ) ) ), 'format' => '', 'current' => $ctx['filters']['paged'], 'total' => $ctx['pages'] ] ) ); ?>
				</nav>
			<?php endif; ?>
			<p class="srna-muted srna-small"><?php esc_html_e( 'The registration itself is always saved; only the alert email failed. Contact these students from their page, and check the SMTP setup.', 'skillrise-new' ); ?></p>
		<?php endif; ?>
	</section>

	<section class="srna-card" aria-labelledby="srna-split">
		<header class="srna-card-head"><h2 id="srna-split"><?php esc_html_e( 'Status split', 'skillrise-new' ); ?></h2></header>
		<?php include SRN_DIR . 'templates/admin/part-mail.php'; ?>
	</section>

	<section class="srna-card srna-card-full" aria-labelledby="srna-config">
		<header class="srna-card-head"><h2 id="srna-config"><?php esc_html_e( 'How alerts are sent', 'skillrise-new' ); ?></h2></header>
		<dl class="srna-details">
			<div><dt><?php esc_html_e( 'Alert recipients', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( implode( ', ', $cfg['to'] ) ); ?></dd></div>
			<div><dt><?php esc_html_e( 'From address', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( $cfg['from'] ?: __( 'Server default', 'skillrise-new' ) ); ?></dd></div>
			<div><dt><?php esc_html_e( 'Confirmation to the student', 'skillrise-new' ); ?></dt><dd><?php echo $cfg['confirm'] ? '<span class="srna-tag srna-tag-ok">' . srn_icon( 'check' ) . esc_html__( 'On', 'skillrise-new' ) . '</span>' : '<span class="srna-tag">' . esc_html__( 'Off', 'skillrise-new' ) . '</span>'; // phpcs:ignore ?></dd></div>
			<div><dt><?php esc_html_e( 'SMTP plugin', 'skillrise-new' ); ?></dt><dd><?php echo $cfg['smtp'] ? '<span class="srna-tag srna-tag-ok">' . srn_icon( 'check' ) . esc_html__( 'Detected', 'skillrise-new' ) . '</span>' : '<span class="srna-tag srna-tag-warn">' . srn_icon( 'bolt' ) . esc_html__( 'Not detected — Hostinger may drop alerts', 'skillrise-new' ) . '</span>'; // phpcs:ignore ?></dd></div>
		</dl>
	</section>
</div>
