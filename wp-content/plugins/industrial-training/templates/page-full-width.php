<?php
/**
 * Full-screen page template: only the landing page (it has its own header and footer).
 * No theme header, footer or content container, and no theme stylesheets
 * (see itp_dequeue_theme_styles() in includes/frontend.php). Works with classic and block themes.
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>html,body{margin:0;padding:0}</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'itp-canvas' ); ?>>
<?php wp_body_open(); ?>
<?php echo itp_render( [ 'main' => true ] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the template. ?>
<?php wp_footer(); ?>
</body>
</html>
