<?php
/**
 * /admin screen: My profile. The signed-in user's own account (forms handled by
 * srn_admin_handle_post() with nonces; changes go through wp_update_user()).
 */

defined( 'ABSPATH' ) || exit;

global $wp_roles;
$roles    = array_map( static fn( $r ) => translate_user_role( $wp_roles->roles[ $r ]['name'] ?? $r ), (array) $user->roles );
$sessions = count( WP_Session_Tokens::get_instance( $user->ID )->get_all() );
$initial  = mb_strtoupper( mb_substr( $user->display_name, 0, 1 ) );
?>
<header class="srna-top">
	<div>
		<p class="srna-eyebrow"><?php esc_html_e( 'Account', 'skillrise-new' ); ?></p>
		<h1><?php esc_html_e( 'My profile', 'skillrise-new' ); ?></h1>
	</div>
</header>

<div class="srna-grid srna-grid-profile">
	<section class="srna-card srna-profile-card" aria-label="<?php esc_attr_e( 'Account summary', 'skillrise-new' ); ?>">
		<span class="srna-avatar srna-avatar-xl" aria-hidden="true"><?php echo esc_html( $initial ); ?></span>
		<h2><?php echo esc_html( $user->display_name ); ?></h2>
		<p class="srna-muted"><?php echo esc_html( $user->user_email ); ?></p>
		<p class="srna-pills-row"><?php foreach ( $roles as $role ) : ?><span class="srna-pill"><?php echo esc_html( $role ); ?></span><?php endforeach; ?></p>
		<dl class="srna-details srna-details-tight">
			<div><dt><?php esc_html_e( 'Username', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( $user->user_login ); ?></dd></div>
			<div><dt><?php esc_html_e( 'Member since', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( $user->user_registered . ' UTC' ) ) ); ?></dd></div>
			<?php /* translators: %d: number of sessions */ ?>
			<div><dt><?php esc_html_e( 'Signed in on', 'skillrise-new' ); ?></dt><dd><?php echo esc_html( sprintf( _n( '%d device', '%d devices', $sessions, 'skillrise-new' ), $sessions ) ); ?></dd></div>
		</dl>
	</section>

	<div class="srna-stack">
		<form class="srna-card srna-form" method="post" action="<?php echo esc_url( srn_admin_url( [ 'view' => 'profile' ] ) ); ?>">
			<header class="srna-card-head"><h2><?php esc_html_e( 'Personal details', 'skillrise-new' ); ?></h2></header>
			<input type="hidden" name="srn_action" value="profile">
			<?php wp_nonce_field( 'srn_profile' ); ?>
			<div class="srna-settings-grid">
				<div class="srna-setting"><label class="srna-label" for="srna-first"><?php esc_html_e( 'First name', 'skillrise-new' ); ?></label><input id="srna-first" name="first_name" type="text" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" maxlength="60"></div>
				<div class="srna-setting"><label class="srna-label" for="srna-last"><?php esc_html_e( 'Last name', 'skillrise-new' ); ?></label><input id="srna-last" name="last_name" type="text" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" maxlength="60"></div>
				<div class="srna-setting srna-span-all">
					<label class="srna-label" for="srna-display"><?php esc_html_e( 'Display name', 'skillrise-new' ); ?></label>
					<select id="srna-display" name="display_name">
						<?php foreach ( srn_admin_display_names( $user ) as $opt ) : ?>
							<option value="<?php echo esc_attr( $opt ); ?>"<?php selected( $user->display_name, $opt ); ?>><?php echo esc_html( $opt ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="srna-help"><?php esc_html_e( 'Shown in the portal. Save your first and last name to get more choices.', 'skillrise-new' ); ?></p>
				</div>
			</div>
			<div class="srna-form-foot"><button class="srna-btn srna-btn-primary" type="submit" data-srna-busy><?php esc_html_e( 'Save details', 'skillrise-new' ); ?></button></div>
		</form>

		<form class="srna-card srna-form" method="post" action="<?php echo esc_url( srn_admin_url( [ 'view' => 'profile' ] ) ); ?>" data-srna-password>
			<header class="srna-card-head"><h2><?php esc_html_e( 'Change password', 'skillrise-new' ); ?></h2></header>
			<input type="hidden" name="srn_action" value="password">
			<?php wp_nonce_field( 'srn_password' ); ?>
			<input type="text" name="username" value="<?php echo esc_attr( $user->user_login ); ?>" autocomplete="username" hidden>
			<div class="srna-settings-grid">
				<div class="srna-setting srna-span-all"><label class="srna-label" for="srna-cur"><?php esc_html_e( 'Current password', 'skillrise-new' ); ?></label><input id="srna-cur" name="current_pass" type="password" autocomplete="current-password" required></div>
				<div class="srna-setting"><label class="srna-label" for="srna-new"><?php esc_html_e( 'New password', 'skillrise-new' ); ?></label><input id="srna-new" name="new_pass" type="password" autocomplete="new-password" minlength="8" required aria-describedby="srna-new-help"><p class="srna-help" id="srna-new-help"><?php esc_html_e( 'At least 8 characters. Longer is stronger.', 'skillrise-new' ); ?></p></div>
				<div class="srna-setting"><label class="srna-label" for="srna-conf"><?php esc_html_e( 'Confirm new password', 'skillrise-new' ); ?></label><input id="srna-conf" name="confirm_pass" type="password" autocomplete="new-password" minlength="8" required></div>
				<label class="srna-check srna-span-all"><input type="checkbox" data-srna-showpass> <?php esc_html_e( 'Show passwords', 'skillrise-new' ); ?></label>
			</div>
			<div class="srna-form-foot"><button class="srna-btn srna-btn-primary" type="submit" data-srna-busy><?php esc_html_e( 'Change password', 'skillrise-new' ); ?></button></div>
		</form>

		<form class="srna-card srna-form" method="post" action="<?php echo esc_url( srn_admin_url( [ 'view' => 'profile' ] ) ); ?>">
			<header class="srna-card-head"><h2><?php esc_html_e( 'Devices', 'skillrise-new' ); ?></h2></header>
			<input type="hidden" name="srn_action" value="sessions">
			<?php wp_nonce_field( 'srn_sessions' ); ?>
			<p class="srna-muted"><?php esc_html_e( 'Lost a phone, or signed in on a shared computer? Sign out everywhere except this device.', 'skillrise-new' ); ?></p>
			<div class="srna-form-foot"><button class="srna-btn srna-btn-ghost" type="submit" data-srna-busy<?php disabled( $sessions <= 1 ); ?>><?php esc_html_e( 'Sign out of other devices', 'skillrise-new' ); ?></button></div>
		</form>
	</div>
</div>
