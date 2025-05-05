<!-- get latest three posts -->
<?php
$q = new WP_Query(
    array(
        'posts_per_page' => 3
    )
);

if ($q->have_posts()) : ?>
    <section id="posts" class="section site-content posts">
        <header class="section-title">
            <a href="/blog" title="<?php bloginfo('name') ?> <?php _e('Blog', 'ccluk') ?>">
                <h4><?php _e("Views", 'ccluk') ?></h4>
        </header>
        <div class="section-content">
            <?php while ($q->have_posts()) : $q->the_post(); ?>
                <?php get_template_part('template-parts/content', 'list'); ?>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>
        <footer class="section-footer">
            <a href="/blog"><?php _e('More views', 'ccluk') ?> &raquo;</a>
        </footer>
    </section>
<?php endif; ?>