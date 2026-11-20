<?php
/**
 * Registrations table (cards on small screens).
 *
 * @var array[]  $rows     From srn_admin_row().
 * @var callable $mail_tag Email-alert badge.
 * @var callable $when     Date formatter.
 */

defined( 'ABSPATH' ) || exit;

$fu_latest = $rows ? srn_fu_latest( array_column( $rows, 'id' ) ) : [];

if ( ! $rows ) : ?>
	<p class="srna-empty"><?php esc_html_e( 'No registrations match.', 'skillrise-new' ); ?></p>
	<?php
	return;
endif;
?>
<div class="srna-table-wrap">
	<table class="srna-table srna-table-regs">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Name', 'industrial-training' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Contact', 'skillrise-new' ); ?></th>
				<th scope="col"><?php esc_html_e( 'College', 'industrial-training' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Branch', 'industrial-training' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Follow-up', 'skillrise-new' ); ?></th>
				<?php if ( srn_admin_is_manager() ) : ?><th scope="col"><?php esc_html_e( 'Email alert', 'industrial-training' ); ?></th><?php endif; ?>
				<th scope="col"><?php esc_html_e( 'Registered', 'industrial-training' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Connect', 'skillrise-new' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $rows as $r ) : ?>
				<tr>
					<th scope="row" data-label="<?php esc_attr_e( 'Name', 'industrial-training' ); ?>">
						<a class="srna-row-link" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registration', 'id' => $r['id'] ] ) ); ?>"><?php echo esc_html( $r['name'] ); ?></a>
					</th>
					<td data-label="<?php esc_attr_e( 'Contact', 'skillrise-new' ); ?>">
						<a href="mailto:<?php echo esc_attr( $r['email'] ); ?>"><?php echo esc_html( $r['email'] ); ?></a>
						<a class="srna-muted" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $r['phone'] ) ); ?>"><?php echo esc_html( $r['phone'] ); ?></a>
					</td>
					<td data-label="<?php esc_attr_e( 'College', 'industrial-training' ); ?>"><?php echo esc_html( $r['college'] ); ?></td>
					<td data-label="<?php esc_attr_e( 'Branch', 'industrial-training' ); ?>"><?php echo $r['branch'] ? '<span class="srna-pill">' . esc_html( $r['branch'] ) . '</span>' : '—'; ?></td>
					<?php $fl = $fu_latest[ $r['id'] ] ?? null; ?>
					<td data-label="<?php esc_attr_e( 'Follow-up', 'skillrise-new' ); ?>">
						<span class="srna-fu-cell"><?php echo srn_fu_badge( $fl ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php if ( $fl ) : ?><small class="srna-muted"><?php echo esc_html( $fl['by'] . ' · ' . srn_fu_ago( $fl['time'] ) ); ?></small><?php endif; ?>
						<?php if ( $fl && $fl['next'] && in_array( $fl['group'], [ 'pending', 'progress' ], true ) ) : ?><small class="srna-fu-next"><?php echo esc_html( srn_fu_next_label( $fl['next'] ) ); ?></small><?php endif; ?></span>
					</td>
					<?php if ( srn_admin_is_manager() ) : ?>
					<td data-label="<?php esc_attr_e( 'Email alert', 'industrial-training' ); ?>"><?php echo $mail_tag( $r ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the closure. ?></td>
					<?php endif; ?>
					<td data-label="<?php esc_attr_e( 'Registered', 'industrial-training' ); ?>"><time datetime="<?php echo esc_attr( gmdate( 'c', $r['time'] ) ); ?>"><?php echo esc_html( $when( $r['time'] ) ); ?></time></td>
					<td class="srna-td-connect" data-label="<?php esc_attr_e( 'Connect', 'skillrise-new' ); ?>">
						<?php $tel = srn_admin_tel_url( $r['phone'] ); $wa = srn_admin_wa_url( $r['phone'], $r['name'] ); ?>
						<span class="srna-connect">
							<?php if ( $tel ) : ?>
								<?php /* translators: %s: student name */ ?>
								<a class="srna-icon-btn srna-icon-call" href="<?php echo esc_url( $tel, [ 'tel' ] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Call %s', 'skillrise-new' ), $r['name'] ) ); ?>" title="<?php esc_attr_e( 'Call', 'skillrise-new' ); ?>"><?php echo srn_icon( 'phone' ); // phpcs:ignore ?><span class="srna-btn-text" aria-hidden="true"><?php esc_html_e( 'Call', 'skillrise-new' ); ?></span></a>
							<?php endif; ?>
							<?php if ( $wa ) : ?>
								<?php /* translators: %s: student name */ ?>
								<a class="srna-icon-btn srna-icon-wa" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( sprintf( __( 'WhatsApp %s (opens in a new tab)', 'skillrise-new' ), $r['name'] ) ); ?>" title="<?php esc_attr_e( 'WhatsApp', 'industrial-training' ); ?>"><?php echo srn_icon( 'chat' ); // phpcs:ignore ?><span class="srna-btn-text" aria-hidden="true"><?php esc_html_e( 'WhatsApp', 'industrial-training' ); ?></span></a>
							<?php endif; ?>
							<?php /* translators: %s: student name */ ?>
							<a class="srna-icon-btn srna-icon-mail" href="mailto:<?php echo esc_attr( $r['email'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Email %s', 'skillrise-new' ), $r['name'] ) ); ?>" title="<?php esc_attr_e( 'Email', 'industrial-training' ); ?>"><?php echo srn_icon( 'mail' ); // phpcs:ignore ?><span class="srna-btn-text" aria-hidden="true"><?php esc_html_e( 'Email', 'industrial-training' ); ?></span></a>
							<?php /* translators: %s: student name */ ?>
							<a class="srna-icon-btn srna-icon-log" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registration', 'id' => $r['id'] ] ) . '#followup' ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Log follow-up for %s', 'skillrise-new' ), $r['name'] ) ); ?>" title="<?php esc_attr_e( 'Log follow-up', 'skillrise-new' ); ?>"><?php echo srn_icon( 'edit' ); // phpcs:ignore ?><span class="srna-btn-text" aria-hidden="true"><?php esc_html_e( 'Log', 'skillrise-new' ); ?></span></a>
						</span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
