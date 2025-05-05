<?php
$ccluk_contact_id            = get_theme_mod('ccluk_contact_id', esc_html__('contact', 'ccluk'));
$ccluk_contact_disable       = get_theme_mod('ccluk_contact_disable') == 1 ?  true : false;
$ccluk_contact_title         = get_theme_mod('ccluk_contact_title', esc_html__('Get in touch', 'ccluk'));
$ccluk_contact_subtitle      = get_theme_mod('ccluk_contact_subtitle', esc_html__('Section subtitle', 'ccluk'));
$ccluk_contact_cf7           = get_theme_mod('ccluk_contact_cf7');
$ccluk_contact_cf7_disable   = get_theme_mod('ccluk_contact_cf7_disable');
$ccluk_contact_text          = get_theme_mod('ccluk_contact_text');
$ccluk_contact_address_title = get_theme_mod('ccluk_contact_address_title');
$ccluk_contact_address       = get_theme_mod('ccluk_contact_address');
$ccluk_contact_phone         = get_theme_mod('ccluk_contact_phone');
$ccluk_contact_email         = get_theme_mod('ccluk_contact_email');
$ccluk_contact_fax           = get_theme_mod('ccluk_contact_fax');

if (ccluk_is_selective_refresh()) {
    $ccluk_contact_disable = false;
}

if ($ccluk_contact_cf7 || $ccluk_contact_text || $ccluk_contact_address_title || $ccluk_contact_phone || $ccluk_contact_email || $ccluk_contact_fax) {
    $desc = wp_kses_post(get_theme_mod('ccluk_contact_desc'));
?>
    <?php if (!$ccluk_contact_disable) : ?>
        <?php if (! ccluk_is_selective_refresh()) { ?>
            <section id="<?php if ($ccluk_contact_id != '') {
                                echo esc_attr($ccluk_contact_id);
                            }; ?>" <?php do_action('ccluk_section_atts', 'counter'); ?>
                class="<?php echo esc_attr(apply_filters('ccluk_section_class', 'section-contact section-padding  section-meta onepage-section', 'contact')); ?>">
            <?php } ?>
            <?php do_action('ccluk_section_before_inner', 'contact'); ?>
            <div class="<?php echo esc_attr(apply_filters('ccluk_section_container_class', 'container', 'contact')); ?>">
                <?php if ($ccluk_contact_title || $ccluk_contact_subtitle || $desc) { ?>
                    <div class="section-title-area">
                        <?php if ($ccluk_contact_subtitle != '') echo '<h5 class="section-subtitle">' . esc_html($ccluk_contact_subtitle) . '</h5>'; ?>
                        <?php if ($ccluk_contact_title != '') echo '<h2 class="section-title">' . esc_html($ccluk_contact_title) . '</h2>'; ?>
                        <?php if ($desc) {
                            echo '<div class="section-desc">' . apply_filters('ccluk_the_content', $desc) . '</div>';
                        } ?>
                    </div>
                <?php } ?>
                <div class="row">
                    <?php if ($ccluk_contact_cf7_disable != '1') : ?>
                        <?php if (isset($ccluk_contact_cf7) && $ccluk_contact_cf7 != '') { ?>
                            <div class="contact-form col-sm-6 wow slideInUp">
                                <?php echo do_shortcode(wp_kses_post($ccluk_contact_cf7)); ?>
                            </div>
                        <?php } else { ?>
                            <div class="contact-form col-sm-6 wow slideInUp">
                                <br>
                                <small>
                                    <i><?php printf(esc_html__('You can install %1$s plugin and go to Customizer &rarr; Section: Contact &rarr; Section Content to show a working contact form here.', 'ccluk'), '<a href="' . esc_url('https://wordpress.org/plugins/contact-form-7/', 'ccluk') . '" target="_blank">Contact Form 7</a>'); ?></i>
                                </small>
                            </div>
                        <?php } ?>
                    <?php endif; ?>

                    <div class="col-sm-6 wow slideInUp">
                        <br>
                        <?php
                        if ($ccluk_contact_text != '') {
                            echo apply_filters('ccluk_the_content', wp_kses_post($ccluk_contact_text));
                        }
                        ?>
                        <br><br>
                        <div class="address-box">

                            <h3><?php if ($ccluk_contact_address_title != '') echo wp_kses_post($ccluk_contact_address_title); ?></h3>

                            <?php if ($ccluk_contact_address != ''): ?>
                                <div class="address-contact">
                                    <span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-map-marker fa-stack-1x fa-inverse"></i></span>

                                    <div class="address-content"><?php echo wp_kses_post($ccluk_contact_address); ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if ($ccluk_contact_phone != ''): ?>
                                <div class="address-contact">
                                    <span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-phone fa-stack-1x fa-inverse"></i></span>
                                    <div class="address-content"><?php echo wp_kses_post($ccluk_contact_phone); ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if ($ccluk_contact_email != ''): ?>
                                <div class="address-contact">
                                    <span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-envelope-o fa-stack-1x fa-inverse"></i></span>
                                    <div class="address-content"><a href="mailto:<?php echo antispambot($ccluk_contact_email); ?>"><?php echo antispambot($ccluk_contact_email); ?></a></div>
                                </div>
                            <?php endif; ?>

                            <?php if ($ccluk_contact_fax != ''): ?>
                                <div class="address-contact">
                                    <span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-fax fa-stack-1x fa-inverse"></i></span>

                                    <div class="address-content"><?php echo wp_kses_post($ccluk_contact_fax); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php do_action('ccluk_section_after_inner', 'contact'); ?>
            <?php if (! ccluk_is_selective_refresh()) { ?>
            </section>
        <?php } ?>
<?php endif;
}
