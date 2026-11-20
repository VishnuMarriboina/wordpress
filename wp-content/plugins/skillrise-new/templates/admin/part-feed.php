<?php
/**
 * Activity feed: follow-up log entries. Variables: $feed (srn_fu_recent()), $feed_show_by (bool).
 */

defined( 'ABSPATH' ) || exit;

if ( ! $feed ) : ?>
	<p class="srna-empty"><?php esc_html_e( 'No follow-ups logged yet.', 'skillrise-new' ); ?></p>
	<?php
	return;
endif;
?>
<ol class="srna-feed" role="list">
	<?php foreach ( $feed as $e ) : ?>
		<li>
			<span class="srna-log-dot srna-fu-<?php echo esc_attr( $e['group'] ); ?>" aria-hidden="true"><?php echo srn_icon( srn_fu_channels()[ $e['channel'] ][1] ?? 'phone' ); // phpcs:ignore ?></span>
			<div>
				<p>
					<?php if ( ! empty( $feed_show_by ) ) : ?><strong><?php echo esc_html( $e['by'] ); ?></strong> · <?php endif; ?>
					<a class="srna-row-link" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registration', 'id' => $e['reg'] ] ) . '#followup' ); ?>"><?php echo esc_html( $e['name'] ); ?></a>
					<?php echo srn_fu_badge( $e ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>
				<?php if ( '' !== $e['note'] ) : ?><p class="srna-log-note"><?php echo esc_html( mb_strimwidth( $e['note'], 0, 160, '…' ) ); ?></p><?php endif; ?>
				<p class="srna-log-meta"><?php echo esc_html( ( srn_fu_channels()[ $e['channel'] ][0] ?? '' ) . ' · ' . srn_fu_ago( $e['time'] ) ); ?></p>
			</div>
		</li>
	<?php endforeach; ?>
</ol>
