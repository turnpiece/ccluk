<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package politics
 */

get_header();
?>

<div class="row">

  <div class="large-8 columns">

  	<div id="primary" class="content-area">
  		<main id="main" class="site-main" role="main">

  			<?php while ( have_posts() ) : the_post(); ?>

  				<?php get_template_part( 'template-parts/content', 'page' ); ?>

  				<?php
  					// If comments are open or we have at least one comment, load up the comment template.
  					if ( comments_open() || get_comments_number() ) :
  						comments_template();
  					endif;
  				?>

  			<?php endwhile; // End of the loop. ?>

  		</main><!-- #main -->
  	</div><!-- #primary -->

  </div><!-- .large-8 -->

	<div class="large-3 offset-1 columns inner-sidebar">

		<?php get_sidebar(); ?>

	</div><!-- .large-3 .offset-1 -->

</div><!-- .row -->

<?php get_footer(); ?>
