<?php
/**
 * Full-width page template: theme header, landing page, theme footer.
 */

defined( 'ABSPATH' ) || exit;

if ( wp_is_block_theme() ) : ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
	<?php block_template_part( 'header' ); ?>
	<?php echo itp_render( [ 'main' => true ] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the template. ?>
	<?php block_template_part( 'footer' ); ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
	<?php
else :
	get_header();
	echo itp_render( [ 'main' => true ] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the template.
	get_footer();
endif;
