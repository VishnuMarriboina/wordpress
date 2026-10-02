<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<h1><?php the_title(); ?></h1>
		<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large' ); ?>
		<?php the_content(); ?>
	</article>
	<?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
<?php endwhile; ?>

<?php get_footer(); ?>
