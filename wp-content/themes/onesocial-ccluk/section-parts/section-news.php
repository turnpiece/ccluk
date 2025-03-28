<!-- get latest three news articles -->
<?php
$q = new WP_Query(
    array(
        'posts_per_page' => 3,
        'post_type' => 'ccluk_news'
    )
);

if ($q->have_posts()) : ?>
    <section id="news" class="section site-content posts">
        <header class="section-title">
            <a href="/news" title="<?php bloginfo('name') ?> <?php _e('News', 'onesocial') ?>">
                <h4><?php _e("News", 'onesocial') ?></h4>
            </a>
        </header>
        <div class="section-content">
            <?php while ($q->have_posts()) : $q->the_post(); ?>
                <?php get_template_part('template-parts/content', 'list_news'); ?>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>
        <footer class="section-footer">
            <a href="/news"><?php _e('More news', 'onesocial') ?> &raquo;</a>
        </footer>
    </section>
<?php endif; ?>