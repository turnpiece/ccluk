<?php
/**
 * Fallback header template part
 *
 * @package WordPress
 * @subpackage CCL UK Theme
 */
?>
<header id="masthead" class="site-header" data-infinite="on">
    <div class="header-wrapper">
        <?php get_template_part('template-parts/header-logo'); ?>
        <?php get_template_part('template-parts/header-nav'); ?>
        <?php get_template_part('template-parts/header-aside'); ?>
    </div>
</header>

<?php get_template_part('template-parts/header-mobile'); ?> 