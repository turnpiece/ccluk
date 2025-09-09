<!-- get latest campaign post -->
<?php
$q = new WP_Query(
    array(
        'post_type' => array('post', 'ccluk_news'),
        'posts_per_page' => 1,
        'category_name' => 'campaign'
    )
);

if ($q->have_posts()) : ?>
    <section id="campaign" class="section site-content campaign feature">
        <header class="section-title">
            <a href="/category/campaign" title="<?php bloginfo('name') ?> <?php _e('campaigns', 'ccluk') ?>">
                <h4><?php _e('Campaign', 'ccluk') ?></h4>
            </a>
        </header>
        <div class="section-content">
            <?php while ($q->have_posts()) : $q->the_post(); ?>
                <?php get_template_part('template-parts/content', 'feature'); ?>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>
    </section>
<?php endif; ?>