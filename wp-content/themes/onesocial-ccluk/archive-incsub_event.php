<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package OneSocial Theme
 */
get_header();

?>

<div class="home-inner-wrap listing archive">

	<div id="primary" class="site-content">

		<div id="content" role="main">

		<?php if ( have_posts() ) : ?>

			<header class="archive-header dir-header">
				<?php 

				if (is_post_type_archive())
					echo '<h1 class="page-title">' . post_type_archive_title( '', false ) . '</h1>';
				else
					the_archive_title( '<h1 class="page-title">', '</h1>' );

				the_archive_description( '<div class="taxonomy-description">', '</div>' );
				?>
			</header><!-- .archive-header -->

			<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post(); ?>

				<div class="article-outher">
					
					<div class="content-wrap">
						<?php get_template_part( 'template-parts/content', get_post_format() ); ?>
					</div>

				</div>

			<?php endwhile; ?>

			<div class="pagination-below">
				<?php buddyboss_pagination(); ?>
			</div>

		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>

		</div><!-- #content -->

	</div><!-- #primary -->

</div>

<?php
get_footer();
