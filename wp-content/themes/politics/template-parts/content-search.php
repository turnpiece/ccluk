<?php
/**
 * The template part for displaying results in search pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package politics
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
    <a href='<?php echo esc_url( get_permalink() ); ?>'>
		    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
    </a>
	</header><!-- .entry-header -->

	<hr>

	<div class="entry-content">
		<?php the_excerpt(); ?>
	</div><!-- .entry-content -->

</article><!-- #post-## -->
