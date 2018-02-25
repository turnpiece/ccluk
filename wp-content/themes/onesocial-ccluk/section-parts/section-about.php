<?php
$id       = get_theme_mod( 'ccluk_about_id', esc_html__('about', 'onesocial') );
$disable  = get_theme_mod( 'ccluk_about_disable' ) == 1 ? true : false;
$title    = get_theme_mod( 'ccluk_about_title', esc_html__('About Us', 'onesocial' ));
$link     = get_theme_mod( 'ccluk_about_source_page' );
$box_1    = wp_kses_post( get_theme_mod( 'ccluk_about_box_1') );
$box_2    = wp_kses_post( get_theme_mod( 'ccluk_about_box_2') );

if ( ccluk_is_selective_refresh() ) {
    $disable = false;
}

// Get data

?>
<?php if (!$disable) { ?>
    <?php if ( ! ccluk_is_selective_refresh() ){ ?>
    <section id="<?php if ($id != '') {
        echo esc_attr( $id );
    }; ?>" <?php do_action('ccluk_section_atts', 'about'); ?> class="section about site-content">
    <?php } ?>

        <?php do_action('ccluk_section_before_inner', 'about'); ?>

        <?php if ( $title != '' ) : ?>
        <div class="section-title">
            <?php if ($link) {
                echo '<a href="' . get_permalink($link) . '"><h4>' . esc_html($title) . '</h4></a>';
            } else {
                echo '<h4>' . esc_html($title) . '</h4>';
            } ?>
        </div>
        <?php endif; ?>

        <div class="section-content">
            <div class="box-1 list-item">
                <?php echo $box_1 ?>
            </div>
            <div class="box-2 list-item">
                <?php echo $box_2 ?>
            </div>
        </div>

        <?php do_action('ccluk_section_after_inner', 'about'); ?>
    <?php if ( ! ccluk_is_selective_refresh() ){ ?>
    </section>
    <?php } ?>
<?php }