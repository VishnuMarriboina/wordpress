<?php
/**
 * /admin screen: Analytics. All figures come from srn_admin_analytics() (real registrations in the selected range).
 */

defined( 'ABSPATH' ) || exit;

$an         = $ctx['an'];
$rg         = $ctx['range'];
$range_view = 'analytics';
$rargs      = srn_admin_range_args( $rg );
$diff       = $an['count'] - $an['prev'];
$hbars      = static function ( array $rows, string $key, int $total, ?callable $link = null ) {
	if ( ! $rows ) {
		echo '<p class="srna-empty">' . esc_html__( 'No registrations in this date range.', 'skillrise-new' ) . '</p>';
		return;
	}
	$max = max( 1, max( array_column( $rows, 'n' ) ) );
	echo '<ul class="srna-hbars" role="list">';
	foreach ( $rows as $row ) {
		$inner = '<span class="srna-hbar-label">' . esc_html( $row[ $key ] ) . '</span>'
			. '<span class="srna-hbar-val">' . esc_html( number_format_i18n( $row['n'] ) ) . ' <small>(' . esc_html( (string) round( $row['n'] / max( 1, $total ) * 100 ) ) . '%)</small></span>'
			. '<span class="srna-hbar" aria-hidden="true"><span style="width:' . esc_attr( (string) round( $row['n'] / $max * 100, 1 ) ) . '%"></span></span>';
		echo '<li>' . ( $link ? '<a href="' . esc_url( $link( $row ) ) . '">' . $inner . '</a>' : '<div>' . $inner . '</div>' ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
	}
	echo '</ul>';
};
?>
<header class="srna-top">
	<div>
		<p class="srna-eyebrow"><?php esc_html_e( 'Registration analytics', 'skillrise-new' ); ?></p>
		<h1><?php esc_html_e( 'Analytics', 'skillrise-new' ); ?></h1>
	</div>
</header>

<?php include SRN_DIR . 'templates/admin/part-range.php'; ?>

<dl class="srna-kpis">
	<div class="srna-kpi srna-kpi-main">
		<dt><?php esc_html_e( 'Registrations', 'skillrise-new' ); ?></dt>
		<dd><?php echo esc_html( number_format_i18n( $an['count'] ) ); ?></dd>
		<?php if ( 'all' !== $rg['key'] ) : ?>
			<p class="srna-kpi-note srna-trend<?php echo $diff > 0 ? ' is-up' : ( $diff < 0 ? ' is-down' : '' ); ?>">
				<?php
				if ( 0 === $an['prev'] ) {
					esc_html_e( 'No registrations in the previous period', 'skillrise-new' );
				} else {
					$pct = round( $diff / $an['prev'] * 100 );
					echo srn_icon( $diff >= 0 ? 'arrow-up' : 'arrow' ); // phpcs:ignore
					/* translators: 1: signed change, 2: signed percent, 3: previous period */
					echo esc_html( sprintf( __( '%1$s (%2$s%%) vs %3$s', 'skillrise-new' ), ( $diff > 0 ? '+' : '' ) . number_format_i18n( $diff ), ( $pct > 0 ? '+' : '' ) . $pct, wp_date( 'j M', $an['prevFrom']->getTimestamp() ) . ' – ' . wp_date( 'j M', $an['prevTo']->getTimestamp() ) ) );
				}
				?>
			</p>
		<?php endif; ?>
	</div>
	<div class="srna-kpi"><dt><?php esc_html_e( 'Daily average', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $an['avg'], $an['avg'] < 10 ? 1 : 0 ) ); ?></dd></div>
	<div class="srna-kpi"><dt><?php esc_html_e( 'Busiest day', 'skillrise-new' ); ?></dt><dd><?php echo $an['busiest'] ? esc_html( number_format_i18n( $an['busiest']['n'] ) ) . ' <small>' . esc_html( $an['busiest']['date'] ) . '</small>' : '—'; ?></dd></div>
	<div class="srna-kpi"><dt><?php esc_html_e( 'Branches represented', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( number_format_i18n( count( $an['branches'] ) ) ); ?></dd></div>
</dl>

<div class="srna-grid">
	<section class="srna-card srna-card-wide" aria-labelledby="srna-trend">
		<header class="srna-card-head">
			<h2 id="srna-trend"><?php echo esc_html( [ 'day' => __( 'Registrations per day', 'skillrise-new' ), 'week' => __( 'Registrations per week', 'skillrise-new' ), 'month' => __( 'Registrations per month', 'skillrise-new' ) ][ $an['unit'] ] ); ?></h2>
			<p class="srna-muted"><?php echo esc_html( $rg['label'] ); ?></p>
		</header>
		<?php $series = $an['series']; $unit = $an['unit']; $count = $an['count']; include SRN_DIR . 'templates/admin/part-columns.php'; ?>
	</section>

	<section class="srna-card" aria-labelledby="srna-mail">
		<header class="srna-card-head">
			<h2 id="srna-mail"><?php esc_html_e( 'Email alert status', 'skillrise-new' ); ?></h2>
			<a class="srna-link" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'emails' ] + $rargs ) ); ?>"><?php esc_html_e( 'Details', 'skillrise-new' ); ?> <?php echo srn_icon( 'arrow' ); // phpcs:ignore ?></a>
		</header>
		<?php $mail = $an['mail']; include SRN_DIR . 'templates/admin/part-mail.php'; ?>
	</section>

	<section class="srna-card" aria-labelledby="srna-branch">
		<header class="srna-card-head"><h2 id="srna-branch"><?php esc_html_e( 'By branch', 'skillrise-new' ); ?></h2></header>
		<?php $hbars( $an['branches'], 'branch', $an['count'], static fn( $b ) => srn_admin_url( [ 'view' => 'registrations', 'branch' => $b['branch'] ] + $rargs ) ); ?>
	</section>

	<section class="srna-card" aria-labelledby="srna-colleges">
		<header class="srna-card-head">
			<h2 id="srna-colleges"><?php esc_html_e( 'Top colleges', 'skillrise-new' ); ?></h2>
			<p class="srna-muted"><?php esc_html_e( 'As typed by students', 'skillrise-new' ); ?></p>
		</header>
		<?php $hbars( $an['colleges'], 'college', $an['count'], static fn( $c ) => srn_admin_url( [ 'view' => 'registrations', 's' => $c['college'] ] + $rargs ) ); ?>
	</section>

	<section class="srna-card" aria-labelledby="srna-weekday">
		<header class="srna-card-head">
			<h2 id="srna-weekday"><?php esc_html_e( 'By day of the week', 'skillrise-new' ); ?></h2>
			<p class="srna-muted"><?php esc_html_e( 'When students register', 'skillrise-new' ); ?></p>
		</header>
		<?php $hbars( $an['count'] ? $an['weekday'] : [], 'label', $an['count'] ); ?>
	</section>
</div>
