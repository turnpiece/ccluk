<?php
$id       = get_theme_mod('ccluk_features_id', esc_html__('features', 'ccluk'));
$disable  = get_theme_mod('ccluk_features_disable') == 1 ? true : false;
$title    = get_theme_mod('ccluk_features_title', esc_html__('Features', 'ccluk'));
$subtitle = get_theme_mod('ccluk_features_subtitle', esc_html__('Why choose Us', 'ccluk'));
if (ccluk_is_selective_refresh()) {
    $disable = false;
}

if (! function_exists('ccluk_get_features_data')) {
    /**
     * Get features data
     *
     * @since 1.1.4
     * @return array
     */
    function ccluk_get_features_data()
    {
        $array = get_theme_mod('ccluk_features_boxes');
        if (is_string($array)) {
            $array = json_decode($array, true);
        }
        if (!empty($array) && is_array($array)) {
            foreach ($array as $k => $v) {
                $array[$k] = wp_parse_args($v, array(
                    'icon' => 'gg',
                    'title' => '',
                    'desc' => '',
                    'link' => '',
                ));

                //Get/Set social icons
                $array[$k]['icon'] = trim($array[$k]['icon']);
                if ($array[$k]['icon'] != '' && strpos($array[$k]['icon'], 'fa-') !== 0) {
                    $array[$k]['icon'] = 'fa-' . $array[$k]['icon'];
                }
            }
        }
        return $array;
    }
}
$data  = ccluk_get_features_data();

if (!$disable && !empty($data)) {
    $desc = get_theme_mod('ccluk_features_desc');
?>
    <?php if (! ccluk_is_selective_refresh()) { ?>
        <section id="<?php if ($id != '') {
                            echo esc_attr($id);
                        } ?>" <?php do_action('ccluk_section_atts', 'features'); ?>
            class="<?php echo esc_attr(apply_filters('ccluk_section_class', 'section-features section-padding section-meta onepage-section', 'features')); ?>">
        <?php } ?>
        <?php do_action('ccluk_section_before_inner', 'features'); ?>
        <div class="<?php echo esc_attr(apply_filters('ccluk_section_container_class', 'container', 'features')); ?>">
            <?php if ($title ||  $subtitle || $desc) { ?>
                <div class="section-title-area">
                    <?php if ($subtitle != '') echo '<h5 class="section-subtitle">' . esc_html($subtitle) . '</h5>'; ?>
                    <?php if ($title != '') echo '<h2 class="section-title">' . esc_html($title) . '</h2>'; ?>
                    <?php if ($desc) {
                        echo '<div class="section-desc">' . apply_filters('ccluk_the_content', wp_kses_post($desc)) . '</div>';
                    } ?>
                </div>
            <?php } ?>
            <div class="section-content">
                <div class="row">
                    <?php
                    $layout = intval(get_theme_mod('ccluk_features_layout', 3));
                    foreach ($data as $k => $f) {
                        $media = '';
                        $f =  wp_parse_args($f, array(
                            'icon_type' => 'icon',
                            'icon' => 'gg',
                            'image' => '',
                            'link' => '',
                            'title' => '',
                            'desc' => '',
                        ));
                        if ($f['icon_type'] == 'image' && $f['image']) {
                            $url = ccluk_get_media_url($f['image']);
                            if ($url) {
                                $media = '<span class="icon-image"><img src="' . esc_url($url) . '" alt=""></span>';
                            }
                        } else if ($f['icon']) {
                            $f['icon'] = trim($f['icon']);
                            $media = '<span class="fa-stack fa-5x"><i class="fa fa-circle fa-stack-2x icon-background-default"></i> <i class="feature-icon fa ' . esc_attr($f['icon']) . ' fa-stack-1x"></i></span>';
                        }

                    ?>
                        <div class="feature-item col-lg-<?php echo esc_attr($layout); ?> col-sm-6 wow slideInUp">
                            <div class="feature-media">
                                <?php if ($f['link']) { ?><a href="<?php echo esc_url($f['link']); ?>"><?php } ?>
                                    <?php echo $media; ?>
                                    <?php if ($f['link']) { ?></a><?php } ?>
                            </div>
                            <h4><?php if ($f['link']) { ?><a href="<?php echo esc_url($f['link']); ?>"><?php } ?><?php echo esc_html($f['title']); ?><?php if ($f['link']) { ?></a><?php } ?></h4>
                            <div class="feature-item-content"><?php echo apply_filters('the_content', $f['desc']); ?></div>
                        </div>
                    <?php
                    } // end loop featues

                    ?>
                </div>
            </div>
        </div>
        <?php do_action('ccluk_section_after_inner', 'features'); ?>

        <?php if (! ccluk_is_selective_refresh()) { ?>
        </section>
    <?php } ?>
<?php } ?>