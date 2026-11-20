<?php
/**
 * Follow-up status of all registrations: one 100% bar + clickable groups (filters the Registrations list).
 * Variables: $fu = srn_fu_summary().
 */

defined( 'ABSPATH' ) || exit;

$gl = srn_fu_groups();
$gi = [ 'pending' => 'clock', 'progress' => 'spark', 'done' => 'check', 'closed' => 'close' ];
?>
<section class="srna-card srna-card-full" aria-labelledby="srna-funnel">
	<header class="srna-card-head">
		<h2 id="srna-funnel"><?php esc_html_e( 'Follow-up status', 'skillrise-new' ); ?></h2>
		<?php /* translators: %s: number of registrations */ ?>
		<p class="srna-muted"><?php echo esc_html( sprintf( __( 'All %s registrations', 'skillrise-new' ), number_format_i18n( $fu['total'] ) ) ); ?></p>
	</header>
	<?php if ( $fu['total'] ) : ?>
		<div class="srna-split srna-split-fu" role="img" aria-label="<?php echo esc_attr( implode( ', ', array_map( static fn( $k ) => $gl[ $k ] . ': ' . $fu['groups'][ $k ], array_keys( $gl ) ) ) ); ?>">
			<?php foreach ( $gl as $k => $label ) : ?>
				<?php if ( $fu['groups'][ $k ] ) : ?><span class="srna-fu-bar-<?php echo esc_attr( $k ); ?>" style="flex-grow:<?php echo (int) $fu['groups'][ $k ]; ?>"></span><?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<ul class="srna-fu-groups" role="list">
		<?php foreach ( $gl as $k => $label ) : ?>
			<li>
				<a class="srna-fu-group srna-fu-<?php echo esc_attr( $k ); ?>" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations', 'fu' => $k ] ) ); ?>">
					<span class="srna-fu-group-icon"><?php echo srn_icon( $gi[ $k ] ); // phpcs:ignore ?></span>
					<span><strong><?php echo esc_html( number_format_i18n( $fu['groups'][ $k ] ) ); ?></strong><?php echo esc_html( $label ); ?></span>
				</a>
				<small class="srna-muted">
					<?php
					$parts = [];
					foreach ( srn_fu_statuses() as $sk => [ $sl, $sg ] ) {
						if ( $sg === $k && count( array_filter( srn_fu_statuses(), static fn( $x ) => $x[1] === $k ) ) > 1 ) {
							$parts[] = $sl . ' ' . number_format_i18n( $fu['by'][ $sk ] );
						}
					}
					echo esc_html( implode( ' · ', $parts ) );
					?>
				</small>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php if ( $fu['due'] ) : ?>
		<?php /* translators: %s: number */ ?>
		<a class="srna-due-note" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations', 'fu' => 'due' ] ) ); ?>"><?php echo srn_icon( 'calendar' ); // phpcs:ignore ?><?php echo esc_html( sprintf( _n( '%s follow-up is due today or overdue', '%s follow-ups are due today or overdue', $fu['due'], 'skillrise-new' ), number_format_i18n( $fu['due'] ) ) ); ?></a>
	<?php endif; ?>
</section>
