<?php

//if (defined('MC4WP_VERSION') && !is_user_logged_in()) :

$slug       = 'homepage_embed';
$audience   = get_theme_mod($slug . '_audience', 'all');

if (
    $audience == 'none' ||
    $audience == 'logged_in' && !is_user_logged_in() ||
    $audience == 'logged_out' && is_user_logged_in()
)
    return;

$id         = get_theme_mod($slug . '_id', esc_html__('embed', 'onesocial'));
$embed      = get_theme_mod($slug . '_embed');
$disable    = get_theme_mod($slug . '_disable') == 1 ? true : false;
$title      = get_theme_mod($slug . '_title');
$text       = get_theme_mod($slug . '_text');
$link_text  = get_theme_mod($slug . '_link_text');
$link_page  = get_theme_mod($slug . '_link_page');

if (ccluk_is_selective_refresh()) {
    $disable = false;
}

// Get data
if (!$disable && $embed) :
    if (! ccluk_is_selective_refresh()) : ?>
        <section id="<?php echo esc_attr($id) ?>" <?php do_action('ccluk_section_atts', 'embed'); ?> class="section embed site-content">
        <?php endif; ?>

        <?php if ($title !== '') : ?>
            <div class="section-title">
                <h4><?php echo $title ?></h4>
            </div>
        <?php endif; ?>

        <?php do_action('ccluk_section_before_inner', 'embed'); ?>

        <div class="section-content">
            <?php if ($text !== '') : ?>
                <div class="intro list-item">
                    <p><?php echo $text ?></p>

                    <?php if ($link_text && $link_page) : ?>
                        <p class="link-text">
                            <a href="<?php echo get_permalink($link_page) ?>"><?php echo $link_text ?></a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; // end of if text 
            ?>

            <div class="form list-item">
                <?php echo $embed ?>
            </div>

        </div>

        <?php do_action('ccluk_section_after_inner', 'embed'); ?>

        <?php if (! ccluk_is_selective_refresh()) : ?>
        </section>
    <?php endif; ?>
<?php endif;
