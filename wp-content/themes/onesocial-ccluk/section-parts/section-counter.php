<?php
$id       = get_theme_mod( 'ccluk_counter_id', esc_html__('counter', 'onesocial') );
$disable  = get_theme_mod( 'ccluk_counter_disable' ) == 1 ? true : false;
$title    = get_theme_mod( 'ccluk_counter_title', esc_html__('Our Numbers', 'onesocial' ));
$subtitle = get_theme_mod( 'ccluk_counter_subtitle', esc_html__('Section subtitle', 'onesocial' ));
if ( ccluk_is_selective_refresh() ) {
    $disable = false;
}

// Get counter data
if ( ! function_exists( 'ccluk_get_section_counter_data' ) ) {
    /**
     * Get counter data
     *
     * @return array
     */
    function ccluk_get_section_counter_data()
    {
        $boxes = get_theme_mod('ccluk_counter_boxes');
        if (is_string($boxes)) {
            $boxes = json_decode($boxes, true);
        }
        if (empty($boxes) || !is_array($boxes)) {
            $boxes = array();
        }
        return $boxes;
    }
}
$boxes = ccluk_get_section_counter_data();

if ( ! empty ( $boxes ) ) {
    $desc = wp_kses_post( get_theme_mod( 'ccluk_counter_desc' ) );
    ?>
    <?php if ($disable != '1') : ?>
        <?php if ( ! ccluk_is_selective_refresh() ){ ?>
        <section id="<?php if ($id != '') {  echo esc_attr( $id ); } ?>" <?php do_action('ccluk_section_atts', 'counter'); ?>
                 class="<?php echo esc_attr(apply_filters('ccluk_section_class', 'section-counter section-padding onepage-section', 'counter')); ?>">
        <?php } ?>
            <?php do_action('ccluk_section_before_inner', 'counter'); ?>
            <div class="<?php echo esc_attr( apply_filters( 'ccluk_section_container_class', 'container', 'counter' ) ); ?>">
                <?php if ( $title || $subtitle || $desc ){ ?>
                <div class="section-title-area">
                    <?php if ($subtitle != '') echo '<h5 class="section-subtitle">' . esc_html($subtitle) . '</h5>'; ?>
                    <?php if ($title != '') echo '<h2 class="section-title">' . esc_html($title) . '</h2>'; ?>
                    <?php if ( $desc ) {
                        echo '<div class="section-desc">' . apply_filters( 'ccluk_the_content', $desc ) . '</div>';
                    } ?>
                </div>
                <?php } ?>
                <div class="row">
                    <?php
                    $col = 3;
                    $num_col = 4;
                    $n = count( $boxes );
                    if ( $n < 4 ) {
                        switch ($n) {
                            case 3:
                                $col = 4;
                                $num_col = 3;
                                break;
                            case 2:
                                $col = 6;
                                $num_col = 2;
                                break;
                            default:
                                $col = 12;
                                $num_col = 1;
                        }
                    }
                    $j = 0;
                    foreach ($boxes as $i => $box) {
                        $box = wp_parse_args($box,
                            array(
                                'title' => '',
                                'number' => '',
                                'unit_before' => '',
                                'unit_after' => '',
                            )
                        );

                        $class = 'col-sm-6 col-md-' . $col;
                        if ($j >= $num_col) {
                            $j = 1;
                            $class .= ' clearleft';
                        } else {
                            $j++;
                        }
                        ?>
                        <div class="<?php echo esc_attr($class); ?>">
                            <div class="counter_item">
                                <div class="counter__number">
                                    <?php if ($box['unit_before']) { ?>
                                        <span class="n-b"><?php echo esc_html($box['unit_before']); ?></span>
                                    <?php } ?>
                                    <span class="n counter"><?php echo esc_html($box['number']); ?></span>
                                    <?php if ($box['unit_after']) { ?>
                                        <span class="n-a"><?php echo esc_html($box['unit_after']); ?></span>
                                    <?php } ?>
                                </div>
                                <div class="counter_title"><?php echo esc_html($box['title']); ?></div>
                            </div>
                        </div>
                        <?php
                    } // end foreach

                    ?>
                </div>
            </div>
            <?php do_action('ccluk_section_after_inner', 'counter'); ?>
        <?php if ( ! ccluk_is_selective_refresh() ){ ?>
        </section>
        <?php } ?>
    <?php endif;
}
