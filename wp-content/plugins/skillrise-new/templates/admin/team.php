<?php
/**
 * /admin screen: Team (administrators only). Add counsellors, change their password, remove access.
 * Counsellors can sign in to /admin to view registrations and call / WhatsApp / email students — nothing else.
 */

defined( 'ABSPATH' ) || exit;

$team = $ctx['team'];
?>
<header class="srna-top">
	<div>
		<p class="srna-eyebrow"><?php esc_html_e( 'People', 'skillrise-new' ); ?></p>
		<h1><?php esc_html_e( 'Team', 'skillrise-new' ); ?></h1>
		<p class="srna-muted"><?php esc_html_e( 'Counsellors can see registrations and call, WhatsApp or email students. They can’t see analytics, email reports or settings, or export data.', 'skillrise-new' ); ?></p>
	</div>
</header>

<div class="srna-grid">
	<section class="srna-card srna-card-wide" aria-labelledby="srna-team-list">
		<header class="srna-card-head">
			<?php /* translators: %d: number of counsellors */ ?>
			<h2 id="srna-team-list"><?php echo esc_html( sprintf( _n( '%d counsellor', '%d counsellors', count( $team ), 'skillrise-new' ), count( $team ) ) ); ?></h2>
		</header>
		<?php if ( ! $team ) : ?>
			<p class="srna-empty"><?php esc_html_e( 'No counsellors yet. Add your first team member with the form.', 'skillrise-new' ); ?></p>
		<?php else : ?>
			<ul class="srna-team" role="list">
				<?php foreach ( $team as $m ) : ?>
					<li class="srna-member">
						<span class="srna-avatar" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( $m['name'], 0, 1 ) ) ); ?></span>
						<div class="srna-member-info">
							<strong><?php echo esc_html( $m['name'] ); ?></strong>
							<span class="srna-muted"><?php echo esc_html( $m['email'] ); ?></span>
							<?php /* translators: 1: date, 2: number of devices */ ?>
							<small class="srna-muted"><?php echo esc_html( sprintf( _n( 'Added %1$s · signed in on %2$d device', 'Added %1$s · signed in on %2$d devices', $m['sessions'], 'skillrise-new' ), wp_date( 'j M Y', $m['since'] ), $m['sessions'] ) ); ?></small>
							<?php $w7 = $ctx['work7'][ $m['id'] ] ?? [ 'total' => 0, 'by' => [] ]; $w30 = $ctx['work30'][ $m['id'] ] ?? [ 'total' => 0, 'by' => [] ]; ?>
							<dl class="srna-work">
								<div><dt><?php esc_html_e( 'Contacts, 7 days', 'skillrise-new' ); ?></dt><dd><?php echo (int) $w7['total']; ?></dd></div>
								<div><dt><?php esc_html_e( 'Contacts, 30 days', 'skillrise-new' ); ?></dt><dd><?php echo (int) $w30['total']; ?></dd></div>
								<div><dt><?php esc_html_e( 'Confirmed, 30 days', 'skillrise-new' ); ?></dt><dd><?php echo (int) ( $w30['by']['confirmed'] ?? 0 ); ?></dd></div>
								<div><dt><?php esc_html_e( 'Interested, 30 days', 'skillrise-new' ); ?></dt><dd><?php echo (int) ( $w30['by']['interested'] ?? 0 ); ?></dd></div>
								<div><dt><?php esc_html_e( 'Last active', 'skillrise-new' ); ?></dt><dd><?php echo empty( $w30['last'] ) && empty( $w7['last'] ) ? '—' : esc_html( srn_fu_ago( (int) ( $w30['last'] ?? $w7['last'] ) ) ); ?></dd></div>
							</dl>
						</div>
						<details class="srna-member-actions">
							<summary class="srna-btn srna-btn-ghost"><?php esc_html_e( 'Manage', 'skillrise-new' ); ?></summary>
							<div class="srna-member-panel">
								<form method="post" action="<?php echo esc_url( srn_admin_url( [ 'view' => 'team' ] ) ); ?>" class="srna-form">
									<input type="hidden" name="srn_action" value="team_password">
									<input type="hidden" name="user_id" value="<?php echo (int) $m['id']; ?>">
									<?php wp_nonce_field( 'srn_team_password' ); ?>
									<label class="srna-label" for="srna-tp-<?php echo (int) $m['id']; ?>"><?php esc_html_e( 'New password', 'skillrise-new' ); ?></label>
									<div class="srna-inline">
										<input id="srna-tp-<?php echo (int) $m['id']; ?>" name="password" type="text" minlength="8" required autocomplete="new-password" spellcheck="false">
										<button class="srna-btn srna-btn-dark" type="submit" data-srna-busy><?php esc_html_e( 'Set', 'skillrise-new' ); ?></button>
									</div>
								</form>
								<form method="post" action="<?php echo esc_url( srn_admin_url( [ 'view' => 'team' ] ) ); ?>" data-srna-confirm="<?php echo esc_attr( sprintf( /* translators: %s: name */ __( 'Remove portal access for %s?', 'skillrise-new' ), $m['name'] ) ); ?>">
									<input type="hidden" name="srn_action" value="team_remove">
									<input type="hidden" name="user_id" value="<?php echo (int) $m['id']; ?>">
									<?php wp_nonce_field( 'srn_team_remove' ); ?>
									<button class="srna-btn srna-btn-danger srna-btn-block" type="submit"><?php esc_html_e( 'Remove access', 'skillrise-new' ); ?></button>
								</form>
							</div>
						</details>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>

	<form class="srna-card srna-form srna-team-add" method="post" action="<?php echo esc_url( srn_admin_url( [ 'view' => 'team' ] ) ); ?>">
		<header class="srna-card-head"><h2><?php esc_html_e( 'Add a counsellor', 'skillrise-new' ); ?></h2></header>
		<input type="hidden" name="srn_action" value="team_add">
		<?php wp_nonce_field( 'srn_team_add' ); ?>
		<div class="srna-settings-grid">
			<div class="srna-setting"><label class="srna-label" for="srna-ta-first"><?php esc_html_e( 'First name', 'skillrise-new' ); ?></label><input id="srna-ta-first" name="first_name" type="text" required maxlength="60" autocomplete="off"></div>
			<div class="srna-setting"><label class="srna-label" for="srna-ta-last"><?php esc_html_e( 'Last name', 'skillrise-new' ); ?></label><input id="srna-ta-last" name="last_name" type="text" maxlength="60" autocomplete="off"></div>
			<div class="srna-setting srna-span-all"><label class="srna-label" for="srna-ta-email"><?php esc_html_e( 'Email (used to sign in)', 'skillrise-new' ); ?></label><input id="srna-ta-email" name="email" type="email" required autocomplete="off" spellcheck="false"></div>
			<div class="srna-setting srna-span-all"><label class="srna-label" for="srna-ta-pass"><?php esc_html_e( 'Starting password', 'skillrise-new' ); ?></label><input id="srna-ta-pass" name="password" type="text" minlength="8" required autocomplete="new-password" spellcheck="false"><p class="srna-help"><?php esc_html_e( 'At least 8 characters. Share it with them privately; they can change it in My profile.', 'skillrise-new' ); ?></p></div>
		</div>
		<div class="srna-form-foot"><button class="srna-btn srna-btn-primary" type="submit" data-srna-busy><?php echo srn_icon( 'plus' ); // phpcs:ignore ?><?php esc_html_e( 'Add counsellor', 'skillrise-new' ); ?></button></div>
	</form>

	<section class="srna-card srna-card-full" aria-labelledby="srna-team-feed">
		<header class="srna-card-head">
			<h2 id="srna-team-feed"><?php esc_html_e( 'Team activity', 'skillrise-new' ); ?></h2>
			<p class="srna-muted"><?php esc_html_e( 'Every contact logged by counsellors and administrators, newest first.', 'skillrise-new' ); ?></p>
		</header>
		<?php $feed = $ctx['feed']; $feed_show_by = true; include SRN_DIR . 'templates/admin/part-feed.php'; ?>
	</section>
</div>
