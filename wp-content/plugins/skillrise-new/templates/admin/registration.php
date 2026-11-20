<?php
/**
 * /admin screen: One registration. Included by templates/admin.php (variables: $ctx, $user, $when, $mail_tag).
 */

defined( 'ABSPATH' ) || exit;
?>
	<?php $r = $ctx['reg']; $wa = srn_admin_wa_url( $r['phone'], $r['name'] ); $history = $ctx['history']; $cur = $history[0] ?? null; ?>
	<header class="srna-top">
		<div>
			<a class="srna-back" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations' ] ) ); ?>"><?php echo srn_icon( 'arrow', 'srn-ico srna-flip' ); // phpcs:ignore ?><?php esc_html_e( 'All registrations', 'skillrise-new' ); ?></a>
			<h1><?php echo esc_html( $r['name'] ); ?></h1>
			<?php /* translators: %s: date and time */ ?>
			<p class="srna-muted"><?php echo esc_html( sprintf( __( 'Registered %s', 'skillrise-new' ), $when( $r['time'] ) ) ); ?></p>
			<p class="srna-fu-head"><?php echo srn_fu_badge( $cur ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( $cur && $cur['next'] && in_array( $cur['group'], [ 'pending', 'progress' ], true ) ) : ?><span class="srna-fu-next"><?php echo esc_html( srn_fu_next_label( $cur['next'] ) ); ?></span><?php endif; ?></p>
		</div>
	</header>
	<div class="srna-grid">
		<section class="srna-card srna-card-wide" aria-labelledby="srna-details">
			<header class="srna-card-head"><h2 id="srna-details"><?php esc_html_e( 'Registration details', 'industrial-training' ); ?></h2></header>
			<dl class="srna-details">
				<div><dt><?php esc_html_e( 'Full name', 'industrial-training' ); ?></dt><dd><?php echo esc_html( $r['name'] ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Email', 'industrial-training' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( $r['email'] ); ?>"><?php echo esc_html( $r['email'] ); ?></a></dd></div>
				<div><dt><?php esc_html_e( 'Phone', 'industrial-training' ); ?></dt><dd><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $r['phone'] ) ); ?>"><?php echo esc_html( $r['phone'] ); ?></a></dd></div>
				<div><dt><?php esc_html_e( 'College / Polytechnic', 'industrial-training' ); ?></dt><dd><?php echo esc_html( $r['college'] ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Branch', 'industrial-training' ); ?></dt><dd><?php echo esc_html( $r['branch'] ); ?></dd></div>
				<?php if ( '' !== $r['year'] ) : ?><div><dt><?php esc_html_e( 'Year (older form)', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( $r['year'] ); ?></dd></div><?php endif; ?>
				<?php if ( '' !== $r['track'] ) : ?><div><dt><?php esc_html_e( 'Track (older form)', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( $r['track'] ); ?></dd></div><?php endif; ?>
				<div><dt><?php esc_html_e( 'Registered', 'industrial-training' ); ?></dt><dd><?php echo esc_html( $when( $r['time'] ) ); ?></dd></div>
			<?php if ( srn_admin_is_manager() ) : ?>
				<div><dt><?php esc_html_e( 'Email alert', 'industrial-training' ); ?></dt><dd><?php echo $mail_tag( $r ); // phpcs:ignore ?><?php if ( $r['mailErr'] ) : ?><small class="srna-muted srna-err"><?php echo esc_html( $r['mailErr'] ); ?></small><?php endif; ?></dd></div>
			<?php endif; ?>
				<div><dt><?php esc_html_e( 'Reference', 'skillrise-new' ); ?></dt><dd>#<?php echo (int) $r['id']; ?></dd></div>
			</dl>
		</section>
		<section class="srna-card srna-card-contact" aria-labelledby="srna-contact">
			<header class="srna-card-head"><h2 id="srna-contact"><?php esc_html_e( 'Contact the student', 'skillrise-new' ); ?></h2></header>
			<div class="srna-actions">
				<a class="srna-btn srna-btn-call srna-btn-block" data-srna-channel="call" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $r['phone'] ) ); ?>"><?php echo srn_icon( 'phone' ); // phpcs:ignore ?><?php esc_html_e( 'Call', 'skillrise-new' ); ?></a>
				<?php if ( $wa ) : ?>
					<a class="srna-btn srna-btn-wa srna-btn-block" data-srna-channel="whatsapp" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener noreferrer"><?php echo srn_icon( 'chat' ); // phpcs:ignore ?><?php esc_html_e( 'WhatsApp', 'industrial-training' ); ?></a>
				<?php endif; ?>
				<a class="srna-btn srna-btn-mail srna-btn-block" data-srna-channel="email" href="mailto:<?php echo esc_attr( $r['email'] ); ?>"><?php echo srn_icon( 'mail' ); // phpcs:ignore ?><?php esc_html_e( 'Email', 'industrial-training' ); ?></a>
			</div>
		</section>

		<section class="srna-card srna-card-full srna-followup" id="followup" aria-labelledby="srna-fu-title">
			<header class="srna-card-head">
				<h2 id="srna-fu-title"><?php esc_html_e( 'Follow-up', 'skillrise-new' ); ?></h2>
				<p class="srna-muted"><?php esc_html_e( 'After contacting the student, record how it went.', 'skillrise-new' ); ?></p>
			</header>
			<div class="srna-fu-grid">
				<form class="srna-fu-form" method="post" action="<?php echo esc_url( srn_admin_url( [ 'view' => 'registration', 'id' => $r['id'] ] ) ); ?>" data-srna-fu>
					<input type="hidden" name="srn_action" value="followup">
					<input type="hidden" name="reg_id" value="<?php echo (int) $r['id']; ?>">
					<?php wp_nonce_field( 'srn_followup_' . $r['id'] ); ?>
					<fieldset class="srna-choice">
						<legend class="srna-label"><?php esc_html_e( 'How did you contact them?', 'skillrise-new' ); ?></legend>
						<div class="srna-chips srna-chips-3">
							<?php foreach ( srn_fu_channels() as $k => [ $label, $icon ] ) : ?>
								<label class="srna-chip srna-chip-<?php echo esc_attr( $k ); ?>"><input type="radio" name="channel" value="<?php echo esc_attr( $k ); ?>" required<?php checked( 'call' === $k ); ?>><span><?php echo srn_icon( $icon ); // phpcs:ignore ?><?php echo esc_html( $label ); ?></span></label>
							<?php endforeach; ?>
						</div>
					</fieldset>
					<fieldset class="srna-choice">
						<legend class="srna-label"><?php esc_html_e( 'What happened?', 'skillrise-new' ); ?></legend>
						<div class="srna-chips">
							<?php foreach ( srn_fu_statuses() as $k => [ $label, $group, $icon ] ) : ?>
								<?php if ( 'new' === $k ) { continue; } ?>
								<label class="srna-chip srna-chip-<?php echo esc_attr( $group ); ?>"><input type="radio" name="status" value="<?php echo esc_attr( $k ); ?>" required data-group="<?php echo esc_attr( $group ); ?>"><span><?php echo srn_icon( $icon ); // phpcs:ignore ?><?php echo esc_html( $label ); ?></span></label>
							<?php endforeach; ?>
						</div>
					</fieldset>
					<div class="srna-setting">
						<label class="srna-label" for="srna-fu-note"><?php esc_html_e( 'Notes (response, questions, preferred batch…)', 'skillrise-new' ); ?></label>
						<textarea id="srna-fu-note" name="note" rows="3" maxlength="2000" placeholder="<?php esc_attr_e( 'e.g. Wants the May batch, will confirm after talking to parents.', 'skillrise-new' ); ?>"></textarea>
					</div>
					<div class="srna-setting" data-srna-next>
						<label class="srna-label" for="srna-fu-next"><?php esc_html_e( 'Next follow-up date (optional)', 'skillrise-new' ); ?></label>
						<input id="srna-fu-next" name="next" type="date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
					</div>
					<button class="srna-btn srna-btn-primary srna-btn-block" type="submit" data-srna-busy><?php echo srn_icon( 'check' ); // phpcs:ignore ?><?php esc_html_e( 'Save follow-up', 'skillrise-new' ); ?></button>
				</form>

				<div class="srna-fu-history">
					<h3 class="srna-label"><?php esc_html_e( 'History', 'skillrise-new' ); ?></h3>
					<?php if ( ! $history ) : ?>
						<p class="srna-empty"><?php esc_html_e( 'Not contacted yet.', 'skillrise-new' ); ?></p>
					<?php else : ?>
						<ol class="srna-timeline-log" role="list">
							<?php foreach ( $history as $h ) : ?>
								<li>
									<span class="srna-log-dot srna-fu-<?php echo esc_attr( $h['group'] ); ?>" aria-hidden="true"><?php echo srn_icon( srn_fu_channels()[ $h['channel'] ][1] ?? 'phone' ); // phpcs:ignore ?></span>
									<div>
										<p class="srna-log-head"><?php echo srn_fu_badge( $h ); // phpcs:ignore ?><span class="srna-muted"><?php echo esc_html( ( srn_fu_channels()[ $h['channel'] ][0] ?? '' ) . ' · ' . $h['by'] ); ?></span></p>
										<?php if ( '' !== $h['note'] ) : ?><p class="srna-log-note"><?php echo nl2br( esc_html( $h['note'] ) ); // phpcs:ignore ?></p><?php endif; ?>
										<p class="srna-log-meta"><time datetime="<?php echo esc_attr( gmdate( 'c', $h['time'] ) ); ?>" title="<?php echo esc_attr( $when( $h['time'] ) ); ?>"><?php echo esc_html( srn_fu_ago( $h['time'] ) ); ?></time><?php if ( $h['next'] ) : ?> · <?php /* translators: %s: date */ echo esc_html( sprintf( __( 'follow up %s', 'skillrise-new' ), wp_date( 'D j M', DateTimeImmutable::createFromFormat( '!Y-m-d', $h['next'], wp_timezone() )->getTimestamp() ) ) ); ?><?php endif; ?></p>
									</div>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</div>
