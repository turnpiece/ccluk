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
    <div class="g-layout-<?php echo esc_attr( $layout ); ?> container">
        <?php if ( $title || $subtitle || $desc ){ ?>
            <div class="section-title-area">
                <?php if ($subtitle != '') echo '<h5 class="section-subtitle">' . esc_html($subtitle) . '</h5>'; ?>
                <?php if ($title != '') echo '<h2 class="section-title">' . esc_html($title) . '</h2>'; ?>
                <?php if ( $desc ) {
                    echo '<div class="section-desc">' . apply_filters( 'the_content', wp_kses_post( $desc ) ) . '</div>';
                } ?>
            </div>
        <?php } ?>
        <div class="gallery-content">
            <?php
            ccluk_gallery_generate();
            ?>
        </div>

    </div>
    <?php do_action('ccluk_section_after_inner', 'gallery'); ?>
    <?php if ( ! ccluk_is_selective_refresh() ){ ?>
        </section>
    <?php } ?>
<?php }

