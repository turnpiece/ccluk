<?php
/**
 * The template for displaying WordPress pages, including HTML from BuddyPress templates.
 *
 * @package WordPress
 * @subpackage OneSocial Theme
 * @since OneSocial Theme 1.0.0
 */
get_header();
?>

<section id="primary" class="section site-content default-page">

	<main id="content" role="main" class="section-content">

		<?php while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'template-parts/content', 'page' ); ?>
		<?php endwhile; // end of the loop.  ?>

	</main>

</section>

<!-- get latest three posts -->
<?php
	$q = new WP_Query(
		array(
			'posts_per_page' => 3
		)
	);

	if ( $q->have_posts() ) : ?>
<section id="posts" class="section site-content">
	<div class="section-title">
		<h4><?php _e( "Views", '' ) ?></h4>
	</div>
	<div class="section-content">
		<?php while( $q->have_posts() ) : $q->the_post(); ?>
		<?php get_template_part( 'template-parts/content', 'list' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
<?php endif;

get_footer();