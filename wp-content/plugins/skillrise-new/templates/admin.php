<?php
/**
 * /admin — standalone dashboard document. No wp_head(): no theme or third-party front-end scripts run here.
 *
 * @var array $ctx From srn_admin_context().
 */

defined( 'ABSPATH' ) || exit;

require_once SRN_DIR . 'templates/icons.php';

$screen   = $ctx['screen'];
$brand    = srn_dependency_ok() ? (string) itp_settings()['brand_name'] : get_bloginfo( 'name' );
$logo     = srn_dependency_ok() ? srn_logo_url() : '';
$user     = wp_get_current_user();
$is_app   = in_array( $screen, [ 'dashboard', 'home', 'registrations', 'registration', 'analytics', 'emails', 'settings', 'profile', 'team', 'forbidden', 'notfound' ], true );
$manager  = $is_app && srn_admin_is_manager();
$when     = static fn( int $ts ) => wp_date( get_option( 'date_format' ), $ts ) . ', ' . wp_date( get_option( 'time_format' ), $ts );
$mail_tag = static function ( array $r ): string {
	if ( 'sent' === $r['mail'] ) {
		return '<span class="srna-tag srna-tag-ok">' . srn_icon( 'check' ) . esc_html__( 'Sent', 'industrial-training' ) . '</span>';
	}
	if ( 'failed' === $r['mail'] ) {
		return '<span class="srna-tag srna-tag-bad" title="' . esc_attr( $r['mailErr'] ) . '">' . srn_icon( 'close' ) . esc_html__( 'Failed', 'industrial-training' ) . '</span>';
	}
	return '<span class="srna-muted">—</span>';
};
$failed = $manager && ! in_array( $screen, [ 'notfound', 'forbidden' ], true ) ? srn_admin_recent_failed() : 0;
$nav    = [ 'dashboard' => [ __( 'Overview', 'skillrise-new' ), 'chart', srn_admin_url(), 0 ], 'registrations' => [ __( 'Registrations', 'skillrise-new' ), 'users', srn_admin_url( [ 'view' => 'registrations' ] ), 0 ] ];
if ( $manager ) {
	$nav += [
		'analytics' => [ __( 'Analytics', 'skillrise-new' ), 'target', srn_admin_url( [ 'view' => 'analytics' ] ), 0 ],
		'emails'    => [ __( 'Email notifications', 'skillrise-new' ), 'mail', srn_admin_url( [ 'view' => 'emails' ] ), $failed ],
		'settings'  => [ __( 'Settings', 'skillrise-new' ), 'gear', srn_admin_url( [ 'view' => 'settings' ] ), 0 ],
		'team'      => [ __( 'Team', 'skillrise-new' ), 'handshake', srn_admin_url( [ 'view' => 'team' ] ), 0 ],
	];
}
$nav['profile'] = [ __( 'My profile', 'skillrise-new' ), 'mentor', srn_admin_url( [ 'view' => 'profile' ] ), 0 ];
$current = [ 'registration' => 'registrations', 'home' => 'dashboard' ][ $screen ] ?? $screen;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( $ctx['title'] . ' · ' . $brand . ' ' . __( 'Admin', 'skillrise-new' ) ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( srn_asset_url( 'assets/css/srn-admin.css' ) ); ?>">
	<script>try{if(localStorage.getItem('srnaCollapsed')==='1')document.documentElement.classList.add('srna-collapsed')}catch(e){}</script>
	<script src="<?php echo esc_url( srn_asset_url( 'assets/js/srn-admin.js' ) ); ?>" defer></script>
	<?php if ( defined( 'ITP_URL' ) ) : ?>
		<link rel="icon" type="image/png" sizes="64x64" href="<?php echo esc_url( ITP_URL . 'assets/images/skillrise-icon-64.png' ); ?>">
	<?php endif; ?>
</head>
<body class="srna-body">
<?php echo srn_icon_sprite(); // phpcs:ignore WordPress.Security.EscapeOutput -- static markup. ?>
<div class="srna<?php echo $is_app ? ' srna-app' : ' srna-gate'; ?>">

<?php if ( ! $is_app ) : ?>
	<main class="srna-gate-main">
		<div class="srna-gate-card">
			<div class="srna-gate-logo">
				<?php if ( $logo ) : ?>
					<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $brand ); ?>" width="175" height="78">
				<?php else : ?>
					<strong><?php echo esc_html( $brand ); ?></strong>
				<?php endif; ?>
			</div>

			<?php if ( 'login' === $screen ) : ?>
				<h1><?php esc_html_e( 'Admin sign in', 'skillrise-new' ); ?></h1>
				<p class="srna-muted"><?php esc_html_e( 'Sign in with your Skillrise team account.', 'skillrise-new' ); ?></p>
				<?php if ( ! empty( $ctx['signed_out'] ) ) : ?>
					<p class="srna-note srna-note-ok" role="status"><?php echo srn_icon( 'check' ); // phpcs:ignore ?><?php esc_html_e( 'You have been signed out.', 'skillrise-new' ); ?></p>
				<?php endif; ?>
				<?php // Posts to the standard WordPress login, so its security checks and any login/2FA plugins apply. ?>
				<form class="srna-login" method="post" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>">
					<label for="srna-user"><?php esc_html_e( 'Username or email', 'skillrise-new' ); ?></label>
					<input id="srna-user" name="log" type="text" autocomplete="username" required autocapitalize="off" spellcheck="false">
					<label for="srna-pass"><?php esc_html_e( 'Password', 'skillrise-new' ); ?></label>
					<input id="srna-pass" name="pwd" type="password" autocomplete="current-password" required>
					<label class="srna-check"><input name="rememberme" type="checkbox" value="forever"> <?php esc_html_e( 'Keep me signed in', 'skillrise-new' ); ?></label>
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( srn_admin_url() ); ?>">
					<input type="hidden" name="testcookie" value="1">
					<button class="srna-btn srna-btn-primary srna-btn-block" type="submit"><?php esc_html_e( 'Sign in', 'skillrise-new' ); ?></button>
				</form>
				<p class="srna-gate-links">
					<a href="<?php echo esc_url( wp_lostpassword_url( srn_admin_url() ) ); ?>"><?php esc_html_e( 'Forgot password?', 'skillrise-new' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '← Back to website', 'skillrise-new' ); ?></a>
				</p>
			<?php elseif ( 'denied' === $screen ) : ?>
				<h1><?php esc_html_e( 'No access', 'skillrise-new' ); ?></h1>
				<?php /* translators: %s: user display name */ ?>
				<p class="srna-muted"><?php echo esc_html( sprintf( __( 'You are signed in as %s, but this account does not have access to the team portal. Ask an administrator to add you to the team.', 'skillrise-new' ), $user->display_name ) ); ?></p>
				<p class="srna-gate-links">
					<a class="srna-btn srna-btn-primary" href="<?php echo esc_url( wp_logout_url( srn_admin_url( [ 'signed_out' => 1 ] ) ) ); ?>"><?php esc_html_e( 'Sign in with another account', 'skillrise-new' ); ?></a>
				</p>
			<?php else : ?>
				<h1><?php echo esc_html( $ctx['title'] ); ?></h1>
				<p class="srna-muted"><?php esc_html_e( 'The registrations module is switched off. Please contact the website administrator.', 'skillrise-new' ); ?></p>
			<?php endif; ?>
		</div>
	</main>

<?php else : ?>
	<a class="srna-skip" href="#srna-main"><?php esc_html_e( 'Skip to content', 'industrial-training' ); ?></a>
	<div class="srna-progress" aria-hidden="true"></div>
	<div class="srna-scrim" data-srna-close hidden></div>
	<aside class="srna-side" id="srna-side" aria-label="<?php esc_attr_e( 'Sidebar', 'skillrise-new' ); ?>">
		<div class="srna-side-top">
			<a class="srna-side-logo" href="<?php echo esc_url( srn_admin_url() ); ?>">
				<?php if ( $logo ) : ?>
					<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $brand ); ?>" width="117" height="52">
				<?php else : ?>
					<strong><?php echo esc_html( $brand ); ?></strong>
				<?php endif; ?>
			</a>
			<button type="button" class="srna-collapse" data-srna-collapse aria-pressed="false" title="<?php esc_attr_e( 'Collapse sidebar', 'skillrise-new' ); ?>"><?php echo srn_icon( 'arrow', 'srn-ico srna-flip' ); // phpcs:ignore ?><span class="srna-sr"><?php esc_html_e( 'Collapse sidebar', 'skillrise-new' ); ?></span></button>
			<button type="button" class="srna-drawer-close" data-srna-close><?php echo srn_icon( 'close' ); // phpcs:ignore ?><span class="srna-sr"><?php esc_html_e( 'Close menu', 'skillrise-new' ); ?></span></button>
		</div>
		<nav class="srna-nav" aria-label="<?php esc_attr_e( 'Admin', 'skillrise-new' ); ?>">
			<ul role="list">
				<?php foreach ( $nav as $key => [ $label, $icon, $url, $badge ] ) : ?>
					<li><a href="<?php echo esc_url( $url ); ?>" data-tip="<?php echo esc_attr( $label ); ?>"<?php echo $key === $current ? ' aria-current="page"' : ''; ?>><?php echo srn_icon( $icon ); // phpcs:ignore ?><span class="srna-nav-text"><?php echo esc_html( $label ); ?></span><?php if ( $badge ) : ?><?php /* translators: %d: failed email alerts */ ?><span class="srna-badge" title="<?php echo esc_attr( sprintf( _n( '%d failed email alert in the last 30 days', '%d failed email alerts in the last 30 days', $badge, 'skillrise-new' ), $badge ) ); ?>"><?php echo (int) $badge; ?></span><?php endif; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</aside>

	<div class="srna-shell">
		<header class="srna-topbar">
			<button type="button" class="srna-burger" data-srna-open aria-controls="srna-side" aria-expanded="false"><?php echo srn_icon( 'menu' ); // phpcs:ignore ?><span class="srna-sr"><?php esc_html_e( 'Open menu', 'skillrise-new' ); ?></span></button>
			<nav class="srna-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'skillrise-new' ); ?>">
				<ol role="list">
					<li><a href="<?php echo esc_url( srn_admin_url() ); ?>"><?php esc_html_e( 'Portal', 'skillrise-new' ); ?></a></li>
					<?php if ( 'registration' === $screen ) : ?>
						<li><a href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations' ] ) ); ?>"><?php esc_html_e( 'Registrations', 'skillrise-new' ); ?></a></li>
					<?php endif; ?>
					<?php if ( ! in_array( $screen, [ 'dashboard', 'home' ], true ) ) : ?>
						<li aria-current="page"><?php echo esc_html( $ctx['title'] ); ?></li>
					<?php else : ?>
						<li aria-current="page"><?php esc_html_e( 'Overview', 'skillrise-new' ); ?></li>
					<?php endif; ?>
				</ol>
			</nav>
			<div class="srna-topbar-actions">
				<?php if ( $manager ) : ?>
				<a class="srna-topbar-btn" href="<?php echo esc_url( srn_admin_export_url() ); ?>" title="<?php esc_attr_e( 'Export CSV', 'industrial-training' ); ?>"><?php echo srn_icon( 'clipboard' ); // phpcs:ignore ?><span class="srna-topbar-text"><?php esc_html_e( 'Export CSV', 'industrial-training' ); ?></span></a>
				<?php endif; ?>
				<a class="srna-topbar-btn" href="<?php echo esc_url( home_url( '/' . srn_slug() . '/' ) ); ?>" target="_blank" rel="noopener" title="<?php esc_attr_e( 'Visit website', 'skillrise-new' ); ?>"><?php echo srn_icon( 'external' ); // phpcs:ignore ?><span class="srna-topbar-text"><?php esc_html_e( 'Visit website', 'skillrise-new' ); ?></span></a>
				<details class="srna-usermenu">
					<summary aria-label="<?php esc_attr_e( 'Account menu', 'skillrise-new' ); ?>"><span class="srna-avatar" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( $user->display_name, 0, 1 ) ) ); ?></span><span class="srna-topbar-text srna-user-name"><?php echo esc_html( $user->display_name ); ?></span></summary>
					<div class="srna-menu" role="menu">
						<p class="srna-menu-head"><strong><?php echo esc_html( $user->display_name ); ?></strong><span><?php echo esc_html( $user->user_email ); ?></span></p>
						<a role="menuitem" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'profile' ] ) ); ?>"><?php echo srn_icon( 'mentor' ); // phpcs:ignore ?><?php esc_html_e( 'My profile', 'skillrise-new' ); ?></a>
						<?php if ( $manager ) : ?>
						<a role="menuitem" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'settings' ] ) ); ?>"><?php echo srn_icon( 'gear' ); // phpcs:ignore ?><?php esc_html_e( 'Settings', 'skillrise-new' ); ?></a>
						<?php endif; ?>
						<a role="menuitem" class="srna-menu-out" href="<?php echo esc_url( wp_logout_url( srn_admin_url( [ 'signed_out' => 1 ] ) ) ); ?>"><?php echo srn_icon( 'arrow' ); // phpcs:ignore ?><?php esc_html_e( 'Sign out', 'skillrise-new' ); ?></a>
					</div>
				</details>
			</div>
		</header>

	<main class="srna-main" id="srna-main" tabindex="-1">
	<?php
	$file = SRN_DIR . 'templates/admin/' . ( in_array( $screen, [ 'dashboard', 'home', 'registrations', 'registration', 'analytics', 'emails', 'settings', 'profile', 'team', 'forbidden' ], true ) ? $screen : 'notfound' ) . '.php';
	include $file;
	?>

	</main>
	</div>
	<?php if ( ! empty( $ctx['toast'] ) ) : ?>
		<div class="srna-toast srna-toast-<?php echo esc_attr( $ctx['toast']['type'] ); ?>" role="status" data-srna-toast><?php echo srn_icon( 'ok' === $ctx['toast']['type'] ? 'check' : 'close' ); // phpcs:ignore ?><span><?php echo esc_html( $ctx['toast']['text'] ); ?></span><button type="button" data-srna-toast-close><?php echo srn_icon( 'close' ); // phpcs:ignore ?><span class="srna-sr"><?php esc_html_e( 'Dismiss', 'skillrise-new' ); ?></span></button></div>
	<?php endif; ?>
<?php endif; ?>
</div>
</body>
</html>
