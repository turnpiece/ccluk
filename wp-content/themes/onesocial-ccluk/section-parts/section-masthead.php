<?php

$slug       = 'homepage_masthead';
$audience   = get_theme_mod( $slug.'_audience', 'all' );

if ($audience == 'none' || 
    $audience == 'logged_in' && !is_user_logged_in() || 
    $audience == 'logged_out' && is_user_logged_in())
    return;

?>
<!-- get masthead -->
<?php if ($page = get_theme_mod( $slug.'_page')) :

    $id = get_theme_mod( $slug.'_id', esc_html__('masthead', 'onesocial') );
    $text = get_theme_mod( $slug.'_text' );
    $image = get_the_post_thumbnail( $page, 'full' );

    if (empty($image) && ($home = get_option( 'page_on_front' ))) {
        // if no featured image get home page image
        $image = get_the_post_thumbnail( $home, 'full' );
    }

    if (!empty($image)) :
?>
<section id="<?php echo $id ?>" class="section site-content masthead">
    
    <?php if (!empty($text)) : ?>
    <header class="section-title">
        <a href="<?php echo get_permalink( $page ) ?>">
            <h2><?php echo $text ?></h2>
        </a>
    </header>
    <?php endif; ?>

    <a href="<?php echo get_permalink( $page ) ?>" title="<?php the_title_attribute( array( 'post' => $page ) ) ?>" class="section-content"><?php echo $image ?></a>

</section>
<?php endif; endif; ?>