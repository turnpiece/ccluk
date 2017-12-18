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
<section id="posts" class="section site-content posts">
	<div class="section-title">
		<h4><?php _e( "Views", '' ) ?></h4>
	</div>
	<div class="section-content">
		<?php while( $q->have_posts() ) : $q->the_post(); ?>
		<?php get_template_part( 'template-parts/content', 'list' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
<?php endif; ?>

<!-- get latest three news articles -->
<?php
	$q = new WP_Query(
		array(
			'posts_per_page' => 3,
			'post_type' => 'ccluk_news'
		)
	);

	if ( $q->have_posts() ) : ?>
<section id="news" class="section site-content posts">
	<div class="section-title">
		<h4><?php _e( "News", '' ) ?></h4>
	</div>
	<div class="section-content">
		<?php while( $q->have_posts() ) : $q->the_post(); ?>
		<?php get_template_part( 'template-parts/content', 'list' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
<?php endif; ?>

<!-- get latest three events -->
<?php
	$q = new WP_Query(
		array(
			'posts_per_page' => 3,
			'post_type' => 'incsub_event'
		)
	);

	if ( $q->have_posts() ) : ?>
<section id="events" class="section site-content posts">
	<div class="section-title">
		<h4><?php _e( "Events", '' ) ?></h4>
	</div>
	<div class="section-content">
		<?php while( $q->have_posts() ) : $q->the_post(); ?>
		<?php get_template_part( 'template-parts/content', 'list' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
<?php endif;

	if ( bp_has_groups( array(
		'max' => 3,
		'group_type' => 'public'
	) ) ) : ?>
<section id="groups" class="section site-content groups">
	<div class="section-title">
		<h4><?php _e( "Groups", '' ) ?></h4>
	</div>
	<div class="section-content">
	<?php while ( bp_groups() ) : bp_the_group(); ?>
	<?php get_template_part( 'template-parts/content', 'group' ); ?>
	<?php endwhile; ?>
	</div>
</section>
<?php endif;

get_footer();