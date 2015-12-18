<?php
/**
 *
 * Template Name: Full Width
 *
 * @package politics
 */

get_header(); ?>

<div class="row">

  <div class="large-12 columns">

  <div id="primary" class="content-area">

    <main id="main" class="site-main" role="main">

      <?php while ( have_posts() ) : the_post(); ?>

        <?php get_template_part( 'template-parts/content', 'full' ); ?>

        <?php
          // If comments are open or we have at least one comment, load up the comment template.
          if ( comments_open() || get_comments_number() ) :
            comments_template();
          endif;
        ?>

      <?php endwhile; // End of the loop. ?>

    </main><!-- #main -->

  </div><!-- #primary -->

</div><!-- .large-12 -->

</div><!-- .row -->

<?php get_footer(); ?>
