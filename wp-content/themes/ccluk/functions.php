<?php

/**
 * @package CCLUK Child Theme
 * Add your own functions in this file.
 */

define('CCLUK_DEBUGGING', true);
define('CCLUK_JOIN_URL', 'https://community.citizensclimate.org/join');

/**
 * To view theme functions, navigate to /inc/theme.php
 *
 * @package CCL UK Theme
 */
$init_file = get_stylesheet_directory() . '/inc/init.php';

if (!file_exists($init_file)) {

    $err_msg = __('Cannot find the starter file, should be located at: *wp root*/wp-content/themes/ccluk/inc/init.php', 'ccluk');

    wp_die($err_msg);
}

require_once($init_file);

/**
 * Add image size for posts
 *
 */
add_image_size('ccluk-medium', 750, 1000, false);
add_image_size('ccluk-hero', 1200, 800, true);
add_image_size('ccluk-feature', 580, 387, true);

// don't show the admin bar
add_filter('show_admin_bar', '__return_false');

/**
 * Customizer additions.
 */
require get_stylesheet_directory() . '/inc/customizer.php';

// load any widgets
require get_stylesheet_directory() . '/inc/widgets/newsletter-signup.php';

// Category archives to include news posts
function ccluk_show_cpt_archives($query)
{
    if ((is_category() || is_tag()) && empty($query->query_vars['suppress_filters'])) {
        $query->set(
            'post_type',
            array(
                'post',
                'ccluk_news'
            )
        );
        return $query;
    }
}
add_filter('pre_get_posts', 'ccluk_show_cpt_archives');

/**
 * Custom Pagination
 * Credits: http://www.kriesi.at/archives/how-to-build-a-wordpress-post-pagination-without-plugin
 *
 * @since CCL UK Theme 1.0.0
 */
function ccluk_pagination()
{
    global $paged, $wp_query;

    $max_page = 0;

    if (!$max_page) {
        $max_page = $wp_query->max_num_pages;
    }

    if (!$paged) {
        $paged = 1;
    }

    $nextpage = intval($paged) + 1;

    if (is_front_page() || is_home()) {
        $template = 'home';
    } elseif (is_category()) {
        $template = 'category';
    } elseif (is_search()) {
        $template = 'search';
    } else {
        $template = 'archive';
    }

    $class     = ' post-infinite-scroll';
    $label     = __('Load More', 'ccluk');

    if (!is_single() && ($nextpage <= $max_page)) {
        /**
         * Filter the anchor tag attributes for the next posts page link.
         *
         * @since 2.7.0
         *
         * @param string $attributes Attributes for the anchor tag.
         */
        $attr = 'data-page=' . $nextpage . ' data-template=' . $template;

        echo '<a class="button-load-more-posts' . $class . '" href="' . next_posts($max_page, false) . "\" $attr>" . preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label) . '</a>';
    }
}

/*
 * Override default home page title
 *
 */
function ccluk_override_post_title($title)
{

    if (is_front_page()) {

        $sep = apply_filters('document_title_separator', '-');

        $title = implode(" $sep ", array(get_bloginfo('name', 'display'), get_bloginfo('description', 'display')));
        $title = wptexturize($title);
        $title = convert_chars($title);
        $title = esc_html($title);
        $title = capital_P_dangit($title);
    }

    return $title;
}
add_filter('pre_get_document_title', 'ccluk_override_post_title', 99);

/**
 * Sets up theme defaults
 *
 * @since CCLUK Child Theme 1.0.0
 */
function ccluk_theme_setup()
{
    /**
     * Makes child theme available for translation.
     * Translations can be added into the /languages/ directory.
     */

    // Translate text from the PARENT theme.
    #load_theme_textdomain('ccluk', get_stylesheet_directory() . '/languages');

    // Translate text from the CHILD theme only.
    // Change 'ccluk' instances in all child theme files to 'ccluk_theme'.
    // load_theme_textdomain( 'ccluk_theme', get_stylesheet_directory() . '/languages' );

    // Add block theme support
    add_theme_support('block-templates');
    add_theme_support('wp-block-styles');
    add_theme_support('editor-styles');
    
    // Add custom logo support
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // Register navigation menus
    register_nav_menus(array(
        'main-menu' => __('Main Menu', 'ccluk'),
        'social' => __('Social Links', 'ccluk')
    ));

    // add class to front page
    if (is_front_page()) {
        add_filter('body_class', function ($classes) {
            $classes[] = 'front-page';
            return $classes;
        });
    }
}
add_action('after_setup_theme', 'ccluk_theme_setup');

/****************************** CUSTOM FUNCTIONS ******************************/


// create news post type
function ccluk_create_news_post_type()
{
    register_post_type(
        'ccluk_news',
        array(
            'labels' => array(
                'name' => __('News'),
                'singular_name' => __('News')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'news'),
            'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'author'),
            'taxonomies' => array('category', 'post_tag'),
            'menu_position' => 4
        )
    );
}
add_action('init', 'ccluk_create_news_post_type');

// from OnePress
// load section into home page
if (! function_exists('ccluk_load_section')) {
    /**
     * Load section
     * @since 2.0.0
     * @param $section_id
     */
    function ccluk_load_section($section_id)
    {
        /**
         * Hook before section
         */
        do_action('ccluk_before_section_' . $section_id);
        do_action('ccluk_before_section_part', $section_id);

        get_template_part('section-parts/section', $section_id);

        /**
         * Hook after section
         */
        do_action('ccluk_after_section_part', $section_id);
        do_action('ccluk_after_section_' . $section_id);
    }
}

if (! function_exists('ccluk_is_selective_refresh')) {
    function ccluk_is_selective_refresh()
    {
        return isset($GLOBALS['ccluk_is_selective_refresh']) && $GLOBALS['ccluk_is_selective_refresh'] ? true : false;
    }
}

if (! function_exists('ccluk_posted_on')) {

    function ccluk_posted_on()
    {
        printf('<a href="%1$s" title="%2$s" rel="bookmark" class="entry-date"><time datetime="%3$s">%4$s</time></a>', esc_url(get_permalink()), esc_attr(get_the_time()), esc_attr(get_the_date('c')), esc_html(get_the_date()));
    }
}

/**
 * Admin styles
 */
function ccluk_admin_assets()
{
    /**
     * Assign the CCLUK version to a var
     */
    $theme = wp_get_theme('ccluk');
    $ccluk_version = $theme['Version'];

    wp_enqueue_style('ccluk-main-admin-css', get_stylesheet_directory_uri() . '/assets/css/admin.css', array(), $ccluk_version, 'all');
}
add_action('admin_enqueue_scripts', 'ccluk_admin_assets');

// homepage sections
add_action('ccluk_section_before_inner', function ($arg) {

    switch ($arg) {

        case 'newsletter':
            echo '<span class="section-title icon">@</span>';
            break;
    }
}, 10, 1);

// display user link
function ccluk_the_user_link($author_id)
{
    echo ccluk_get_user_link($author_id);
}

/**
 *
 * get user link
 *
 * @param int $author_id
 * @return $string
 *
 */
function ccluk_get_user_link($author_id)
{
    return get_author_posts_url($author_id);
}

function ccluk_debug($message)
{
    if (CCLUK_DEBUGGING)
        error_log($message);
}

/**
 *
 * vardump
 *
 * @param array $array
 *
 */
function ccluk_vardump($array)
{
    ccluk_debug(print_r($array, true));
}

/**
 * Disable comments globally
 */
// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page from admin menu
add_action('admin_menu', function() {
    remove_menu_page('edit-comments.php');
    remove_submenu_page('options-general.php', 'options-discussion.php');
});

// Remove comments links from admin bar
add_action('wp_before_admin_bar_render', function() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
});

/**
 * Load a block template part with dynamic content
 *
 * @param string $slug The template part slug
 * @return string|false The template part content or false if not found
 */
function get_block_template_part($slug) {
    $template_path = get_stylesheet_directory() . '/parts/' . $slug . '.html';
    
    if (file_exists($template_path)) {
        $content = file_get_contents($template_path);
        if ($content !== false) {
            // Replace dynamic content placeholders
            $content = str_replace(
                array(
                    '{{join_url}}'
                ),
                array(
                    esc_url(CCLUK_JOIN_URL)
                ),
                $content
            );
            
            // Add debug output
            if (CCLUK_DEBUGGING) {
                error_log('Loading block template: ' . $template_path);
                error_log('Menu locations: ' . print_r(get_nav_menu_locations(), true));
            }
            
            return do_blocks($content);
        }
    }
    
    return false;
}

// Add debug output for menu locations
add_action('wp_footer', function() {
    if (CCLUK_DEBUGGING) {
        echo '<!-- Debug: Menu Locations -->';
        echo '<!-- ' . print_r(get_nav_menu_locations(), true) . ' -->';
        echo '<!-- Debug: Main Menu Items -->';
        $menu_items = wp_get_nav_menu_items('main-menu');
        echo '<!-- ' . print_r($menu_items, true) . ' -->';
    }
});

// Add debug for menu registration
add_action('after_setup_theme', function() {
    if (CCLUK_DEBUGGING) {
        error_log('Menu locations after registration: ' . print_r(get_nav_menu_locations(), true));
    }
}, 20);
