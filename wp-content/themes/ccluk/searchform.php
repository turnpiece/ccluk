<?php

/**
 * The template for displaying search forms in CCLUK
 *
 * @package CCLUK
 */
?>

<form method="get" id="searchform" class="searchform" action="<?php echo esc_url(home_url('/')); ?>">

    <div class="search-wrap">
        <label class="screen-reader-text" for="s"><?php _e('Search for:', 'ccluk'); ?></label>
        <input type="text" value="" name="s" id="s" placeholder="<?php _e('Type to Search', 'ccluk'); ?>" />
        <button type="submit" id="searchsubmit"><i class="ccluk-icon-search"></i></button>
        <button id="search-close"><i class="ccluk-icon-close"></i></button>
    </div>

</form>