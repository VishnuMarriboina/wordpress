<?php
/**
 * /admin screen: a section the signed-in user's role can't open.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="srna-card srna-forbidden">
	<span class="srna-icon-round" aria-hidden="true"><?php echo srn_icon( 'shield' ); // phpcs:ignore ?></span>
	<h1><?php esc_html_e( 'This section is for administrators', 'skillrise-new' ); ?></h1>
	<p class="srna-muted"><?php esc_html_e( 'Your account can view registrations and contact students.', 'skillrise-new' ); ?></p>
	<a class="srna-btn srna-btn-primary" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations' ] ) ); ?>"><?php esc_html_e( 'Go to registrations', 'skillrise-new' ); ?></a>
</div>
