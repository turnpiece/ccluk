<?php
/**
 * Template Name: Home Page Template
 *
 * Description: Use this page template for a page with VC plugin.
 *
 * @package WordPress
 * @subpackage OneSocial Theme
 * @since OneSocial 1.0.0
 */
get_header();
?>

<div id="primary" class="site-content">
    
	<div id="content" role="main">

		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <header class="entry-header">
                     <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail(); ?>
                    <?php endif; ?>

                    <h2 class="entry-title"><?php the_title(); ?></h2>
                </header>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

            </article>
		<?php endwhile; // end of the loop.  ?>
        
	</div>

 	<?php get_sidebar(); ?>
 	
</div>

<?php

get_footer();