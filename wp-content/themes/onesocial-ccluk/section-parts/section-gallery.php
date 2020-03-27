<?php
$id       = get_theme_mod( 'ccluk_gallery_id', esc_html__('gallery', 'onesocial') );
$disable  = get_theme_mod( 'ccluk_gallery_disable', 1 ) ==  1 ? true : false;
$title    = get_theme_mod( 'ccluk_gallery_title', esc_html__('Gallery', 'onesocial' ));
$subtitle = get_theme_mod( 'ccluk_gallery_subtitle', esc_html__('Section subtitle', 'onesocial' ));
$desc     = get_theme_mod( 'ccluk_gallery_desc' );

if ( ccluk_is_selective_refresh() ) {
    $disable = false;
}
$layout = get_theme_mod( 'ccluk_gallery_layout', 'default' );

?>
<?php if ( ! $disable ) { ?>
    <?php if ( ! ccluk_is_selective_refresh() ){ ?>
        <section id="<?php echo esc_attr( $id ); ?>" <?php do_action('ccluk_section_atts', 'gallery'); ?>
        class="<?php echo esc_attr(apply_filters('ccluk_section_class', 'section-gallery '.( ( $title || $subtitle || $desc ) ? 'section-padding' : '' ).' section-meta onepage-section', 'gallery' )); ?>">
    <?php } ?>
    <?php do_action('ccluk_section_before_inner', 'gallery'); ?>

    <div class="g-layout-<?php echo esc_attr( $layout ); ?> <?php echo esc_attr( apply_filters( 'ccluk_section_container_class', 'container', 'gallery' ) ); ?>">
        <?php if ( $title || $subtitle || $desc ){ ?>
            <div class="section-title-area">
                <?php if ($subtitle != '') echo '<h5 class="section-subtitle">' . esc_html($subtitle) . '</h5>'; ?>
                <?php if ($title != '') echo '<h2 class="section-title">' . esc_html($title) . '</h2>'; ?>
                <?php if ( $desc ) {
                    echo '<div class="section-desc">' . apply_filters( 'ccluk_the_content', wp_kses_post( $desc ) ) . '</div>';
                } ?>
            </div>
        <?php } ?>
        <div class="gallery-content">
            <?php
            ccluk_gallery_generate();
            ?>
        </div>
        <?php
        $readmore_link = get_theme_mod( 'ccluk_g_readmore_link' );
        $readmore_text = get_theme_mod( 'ccluk_g_readmore_text', esc_html__('View More', 'onesocial') );
        if ( $readmore_link ) {
        ?>
        <div class="all-gallery">
            <a class="btn btn-theme-primary-outline" href="<?php echo esc_attr( $readmore_link ); ?>"><?php echo esc_html( $readmore_text ); ?></a>
        </div>
        <?php } ?>

    </div>
    <?php do_action('ccluk_section_after_inner', 'gallery'); ?>
    <?php if ( ! ccluk_is_selective_refresh() ){ ?>
        </section>
    <?php } ?>
<?php }

