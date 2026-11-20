<?php
/**
 * Date-range filter row (presets + custom). Variables: $rg (srn_admin_range()), $range_view (screen slug or '').
 */

defined( 'ABSPATH' ) || exit;
?>
<?php // Date range: one row above everything it scopes (counters, chart, branches, latest). ?>
	<form class="srna-range" method="get" action="<?php echo esc_url( srn_admin_url() ); ?>" aria-label="<?php esc_attr_e( 'Date range', 'skillrise-new' ); ?>">
		<?php
	$presets = [
		'today' => __( 'Today', 'skillrise-new' ),
		'7'     => __( '7 days', 'skillrise-new' ),
		'30'    => __( '30 days', 'skillrise-new' ),
		'90'    => __( '90 days', 'skillrise-new' ),
		'month' => __( 'This month', 'skillrise-new' ),
		'all'   => __( 'All time', 'skillrise-new' ),
	];
	$range_view = $range_view ?? ''; $base_args = $range_view ? [ 'view' => $range_view ] : []; ?>
	<?php if ( $range_view ) : ?>
		<input type="hidden" name="view" value="<?php echo esc_attr( $range_view ); ?>">
	<?php endif; ?>
	<?php if ( ! get_option( 'permalink_structure' ) ) : ?>
			<input type="hidden" name="<?php echo esc_attr( SRN_ADMIN_QUERY_VAR ); ?>" value="1">
		<?php endif; ?>
		<div class="srna-presets" role="group" aria-label="<?php esc_attr_e( 'Quick ranges', 'skillrise-new' ); ?>">
			<?php foreach ( $presets as $key => $label ) : ?>
				<?php $on = $rg['key'] === (string) $key; ?>
				<a class="srna-preset" href="<?php echo esc_url( srn_admin_url( $base_args + ( '30' === (string) $key ? [] : [ 'range' => $key ] ) ) ); ?>"<?php echo $on ? ' aria-current="true"' : ''; ?>><?php echo $on ? srn_icon( 'check' ) : ''; // phpcs:ignore ?><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</div>
		<div class="srna-custom<?php echo 'custom' === $rg['key'] ? ' is-on' : ''; ?>">
			<input type="hidden" name="range" value="custom">
			<label><span><?php esc_html_e( 'From', 'skillrise-new' ); ?></span><input type="date" name="from" value="<?php echo esc_attr( $rg['fromYmd'] ); ?>" max="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" required></label>
			<label><span><?php esc_html_e( 'To', 'skillrise-new' ); ?></span><input type="date" name="to" value="<?php echo esc_attr( $rg['toYmd'] ); ?>" max="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" required></label>
			<button class="srna-btn srna-btn-dark" type="submit"><?php esc_html_e( 'Apply', 'skillrise-new' ); ?></button>
		</div>
		<?php /* translators: %s: date range, e.g. "1 Nov – 20 Nov 2026" */ ?>
		<p class="srna-range-label" role="status"><?php echo srn_icon( 'calendar' ); // phpcs:ignore ?><?php echo esc_html( sprintf( __( 'Showing %s', 'skillrise-new' ), $rg['label'] ) ); ?></p>
	</form>
