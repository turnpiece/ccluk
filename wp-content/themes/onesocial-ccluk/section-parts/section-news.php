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