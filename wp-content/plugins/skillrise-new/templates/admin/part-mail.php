<?php
/**
 * Email-alert status split as one 100% bar + legend. Status colours always come with an icon and a label.
 * Variables: $mail = [ 'sent' => int, 'failed' => int, 'none' => int ].
 */

defined( 'ABSPATH' ) || exit;

$mtotal = array_sum( $mail );
$parts  = [
	'sent'   => [ __( 'Sent', 'industrial-training' ), 'check', __( 'Accepted by the mail server', 'skillrise-new' ) ],
	'failed' => [ __( 'Failed', 'industrial-training' ), 'close', __( 'The server refused the email', 'skillrise-new' ) ],
	'none'   => [ __( 'Not recorded', 'skillrise-new' ), 'clock', __( 'Older registrations, before status was saved', 'skillrise-new' ) ],
];
?>
<?php if ( ! $mtotal ) : ?>
	<p class="srna-empty"><?php esc_html_e( 'No registrations in this date range.', 'skillrise-new' ); ?></p>
<?php else : ?>
	<div class="srna-split" role="img" aria-label="<?php echo esc_attr( implode( ', ', array_map( static fn( $k ) => $parts[ $k ][0] . ': ' . $mail[ $k ], array_keys( $parts ) ) ) ); ?>">
		<?php foreach ( $parts as $k => $p ) : ?>
			<?php if ( $mail[ $k ] ) : ?>
				<span class="srna-split-<?php echo esc_attr( $k ); ?>" style="flex-grow:<?php echo (int) $mail[ $k ]; ?>" title="<?php echo esc_attr( $p[0] . ': ' . number_format_i18n( $mail[ $k ] ) ); ?>"></span>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<ul class="srna-legend" role="list">
		<?php foreach ( $parts as $k => $p ) : ?>
			<li class="srna-legend-<?php echo esc_attr( $k ); ?>">
				<span class="srna-legend-dot"><?php echo srn_icon( $p[1] ); // phpcs:ignore ?></span>
				<span><strong><?php echo esc_html( $p[0] ); ?></strong><small><?php echo esc_html( $p[2] ); ?></small></span>
				<span class="srna-legend-val"><?php echo esc_html( number_format_i18n( $mail[ $k ] ) ); ?> <small><?php echo esc_html( (string) round( $mail[ $k ] / $mtotal * 100 ) ); ?>%</small></span>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
