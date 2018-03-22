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

<?php

do_action( 'ccluk_frontpage_before_section_parts' );

if ( ! has_action( 'ccluk_frontpage_section_parts' ) ) {

	$sections = apply_filters( 'ccluk_frontpage_sections_order', array(
        'about',
        'news',
        'twitter',
        'posts',
        'events',
        'groups'
    ) );

	foreach ( $sections as $section ){
        ccluk_load_section( $section );
	}

} else {
	do_action( 'ccluk_frontpage_section_parts' );
}

do_action( 'ccluk_frontpage_after_section_parts' );

get_footer();