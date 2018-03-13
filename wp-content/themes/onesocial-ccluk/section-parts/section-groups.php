<?php // groups

    if (is_user_logged_in() &&
        function_exists('bp_has_groups') &&
        bp_has_groups( array(
        'max' => 3
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