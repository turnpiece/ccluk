<?php

/**
 * The template for displaying the front page of the site
 *
 * @package WordPress
 * @subpackage CCLUK Theme
 * @since CCLUK Theme 1.0.0
 */
get_header();

ccluk_load_section('banner');

if (have_posts() && get_the_content() != '') : // insert whatever content is on the home page 
?>

        <section id="primary" class="section site-content default-page">

                <main id="content" role="main" class="section-content">
                        <?php while (have_posts()) : the_post(); ?>
                                <?php get_template_part('template-parts/content', 'primary'); ?>
                        <?php endwhile; // end of the loop.  
                        ?>
                </main>

        </section>

<?php endif;

do_action('ccluk_frontpage_before_section_parts');

if (! has_action('ccluk_frontpage_section_parts')) {

        $sections = apply_filters('ccluk_frontpage_sections_order', array(
                'about',
                'news',
                'newsletter',
                'posts',
                'events'
        ));

        foreach ($sections as $section) {
                ccluk_load_section($section);
        }
} else {
        do_action('ccluk_frontpage_section_parts');
}

do_action('ccluk_frontpage_after_section_parts');

get_footer();
