<?php
/**
 * Column chart: one series, one hue, CSS bars with hover/focus tooltips and a table view.
 * Variables: $series (srn_admin_stats()['series']), $unit (day|week|month), $count (total in range).
 */

defined( 'ABSPATH' ) || exit;
?>
			<?php
			$n    = count( $series );
			$max  = max( 1, max( array_column( $series, 'n' ) ?: [ 0 ] ) );
			$step = max( 1, (int) ceil( $max / 4 ) );
			$top  = $step * (int) ceil( $max / $step );
			$xstep = max( 1, (int) ceil( $n / 6 ) );
			?>
			<?php if ( ! $count ) : ?>
				<p class="srna-empty"><?php esc_html_e( 'No registrations in this date range.', 'skillrise-new' ); ?></p>
			<?php else : ?>
			<div class="srna-chart" style="--top:<?php echo (int) $top; ?>">
				<div class="srna-chart-grid" aria-hidden="true">
					<?php for ( $v = $top; $v >= 0; $v -= $step ) : ?>
						<span style="--v:<?php echo (int) $v; ?>"><i><?php echo esc_html( number_format_i18n( $v ) ); ?></i></span>
					<?php endfor; ?>
				</div>
				<ol class="srna-bars<?php echo $n > 40 ? ' srna-bars-dense' : ''; ?>" role="list">
					<?php foreach ( $series as $i => $b ) : ?>
						<?php /* translators: 1: day, week or month, 2: number of registrations */ ?>
						<?php $label = sprintf( _n( '%1$s: %2$s registration', '%1$s: %2$s registrations', $b['n'], 'skillrise-new' ), $b['tip'], number_format_i18n( $b['n'] ) ); ?>
						<?php $tip = $i < $n / 3 ? ' srna-tip-l' : ( $i >= 2 * $n / 3 ? ' srna-tip-r' : '' ); ?>
						<li class="<?php echo esc_attr( trim( $tip ) ); ?>" tabindex="0" aria-label="<?php echo esc_attr( $label ); ?>" data-tip="<?php echo esc_attr( $label ); ?>">
							<span class="srna-bar" style="--n:<?php echo (int) $b['n']; ?>"></span>
							<?php if ( ( 0 === $i % $xstep && $n - 1 - $i >= $xstep * 0.6 ) || $n - 1 === $i ) : ?>
								<span class="srna-bar-x" aria-hidden="true"><?php echo esc_html( $b['axis'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
			<?php endif; ?>
			<details class="srna-table-toggle">
				<summary><?php esc_html_e( 'Show as table', 'skillrise-new' ); ?></summary>
				<table class="srna-table srna-table-compact">
					<thead><tr><th scope="col"><?php echo esc_html( [ 'day' => __( 'Day', 'skillrise-new' ), 'week' => __( 'Week', 'skillrise-new' ), 'month' => __( 'Month', 'skillrise-new' ) ][ $unit ] ); ?></th><th scope="col"><?php esc_html_e( 'Registrations', 'skillrise-new' ); ?></th></tr></thead>
					<tbody>
						<?php foreach ( array_reverse( $series ) as $b ) : ?>
							<tr><td><?php echo esc_html( $b['tip'] . ( 'day' === $unit ? ' ' . wp_date( 'Y', $b['ts'] ) : '' ) ); ?></td><td><?php echo esc_html( number_format_i18n( $b['n'] ) ); ?></td></tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</details>
