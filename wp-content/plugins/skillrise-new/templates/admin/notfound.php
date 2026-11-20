<?php
/**
 * /admin screen: Not found. Included by templates/admin.php (variables: $ctx, $user, $when, $mail_tag).
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="srna-top"><div><h1><?php echo esc_html( $ctx['title'] ); ?></h1></div></header>
<p><a class="srna-btn srna-btn-primary" href="<?php echo esc_url( srn_admin_url( [ 'view' => 'registrations' ] ) ); ?>"><?php esc_html_e( 'All registrations', 'skillrise-new' ); ?></a></p>
