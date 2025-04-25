<?php

$slug       = 'homepage_banner';
$audience   = get_theme_mod($slug . '_audience', 'all');

if (
    $audience == 'none' ||
    $audience == 'logged_in' && !is_user_logged_in() ||
    $audience == 'logged_out' && is_user_logged_in()
)
    return;

?>
<!-- get banner -->
<?php if ($page = get_theme_mod($slug . '_page')) :

    $id = get_theme_mod($slug . '_id', esc_html__('banner', 'onesocial'));
    $heading = get_theme_mod($slug . '_heading');
    $text = get_theme_mod($slug . '_text');
    $image_id = get_theme_mod($slug . '_image');

    $layout = get_theme_mod($slug . '_layout', 'background-box');

    $image = !empty($image_id) ? '<img src="' . $image_id . '" alt="' . $heading . '" />' : get_the_post_thumbnail($page, 'full');

    $button_1_page = get_theme_mod($slug . '_button_1_page');
    $button_1_text = get_theme_mod($slug . '_button_1_text');

    $button_2_page = get_theme_mod($slug . '_button_2_page');
    $button_2_text = get_theme_mod($slug . '_button_2_text');

    if (empty($image) && ($home = get_option('page_on_front'))) {
        // if no featured image get home page image
        $image = get_the_post_thumbnail($home, 'full');
    }

    if (!empty($image)) :
?>
        <section id="<?php echo $id ?>" class="section site-content banner <?php echo $layout ?>">

            <?php if (!empty($heading)) : ?>
                <header class="section-title-container">
                    <div class="section-title">
                        <a href="<?php echo get_permalink($page) ?>" class="page-link">
                            <h2><?php echo $heading ?></h2>
                            <?php if (!empty($text)) : ?>
                                <p><?php echo $text ?></p>
                            <?php endif; ?>
                        </a>

                        <div class="buttons">
                            <?php for ($i = 1; $i <= 2; $i++) :
                                $button_page = get_theme_mod($slug . '_button_' . $i . '_page');
                                $button_text = get_theme_mod($slug . '_button_' . $i . '_text');

                                if ($button_page && $button_text) : ?>
                                    <a class="button" href="<?php echo get_permalink($button_page) ?>">
                                        <?php echo $button_text ?>
                                    </a>
                            <?php endif;
                            endfor; ?>
                        </div>
                    </div>
                </header>
            <?php endif; ?>

            <div class="section-content"><?php echo $image ?></div>

            <script>
                jQuery(document).ready(function($) {
                    $content = $('#<?php echo $id ?>').find('.section-content');
                    $(window).on('load resize', function() {
                        if ($content.height() > $content.find('img').height()) {
                            $content.find('img').height($content.height());
                        }
                    })
                })
            </script>

        </section>
<?php endif;
endif; ?>