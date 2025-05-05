<!-- get latest three events -->
<?php
$q = new WP_Query(
    array(
        'posts_per_page' => 1,
        'post_type' => 'incsub_event',
        'meta_key' => 'incsub_event_start',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_query' => array(
            'key' => 'incsub_event_start',
            'value' => date('Y-m-d'),
            'compare' => '>='
        )
    )
);

if ($q->have_posts()) : ?>
    <section id="events" class="section site-content posts">
        <div class="section-title">
            <a href="/events" title="<?php bloginfo('name') ?> <?php _e('Events', 'ccluk') ?>">
                <h4><?php _e("Events", 'ccluk') ?></h4>
            </a>
        </div>
        <div class="section-content">
            <?php while ($q->have_posts()) : $q->the_post(); ?>
                <?php get_template_part('template-parts/content', 'event-list'); ?>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>
    </section>
<?php endif;
