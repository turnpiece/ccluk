<?php

define( 'THEME_PATH', get_template_directory_uri() );
if ( ! isset( $content_width ) ) $content_width = 940;
       

/* ========================================================================================================================
	
	Load OptionTree
	
======================================================================================================================== */

/**
 * Optional: set 'ot_show_pages' filter to false.
 * This will hide the settings & documentation pages.
 */

add_filter( 'ot_show_pages', '__return_true' );

/**
 * Required: set 'ot_theme_mode' filter to true.
 */
add_filter( 'ot_theme_mode', '__return_true' );

global $options;
$options = get_option('option_tree');

/**
 * Required: include OptionTree.
 */
include_once( get_template_directory() . '/option-tree/ot-loader.php' );

/* ========================================================================================================================
	
	Required External Files
	
======================================================================================================================== */

require_once get_template_directory() . '/includes/comment-list.php';
include('includes/cpt-causes.php');
include('includes/cpt-events.php');
include('includes/cpt-staff.php');
include('includes/theme-options.php');
include 'includes/shortcodes/shortcodes.php';
include("includes/widget-causes.php");
include("includes/widget-events.php");
include("includes/widget-news.php");
include_once("includes/tgn-meta-boxes.php");

/* ========================================================================================================================
	
	Custom Paginattion
	
======================================================================================================================== */


function kriesi_pagination($pages = '', $range = 2)
{
     $showitems = ($range * 2)+1;

     global $paged;
     if(empty($paged)) $paged = 1;

     if($pages == '')
     {
         global $wp_query;
         $pages = $wp_query->max_num_pages;
         if(!$pages)
         {
             $pages = 1;
         }
     }

     if(1 != $pages)
     {
         echo "<ul class='pagination clearfix'>";
         if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<li><a class='button-small grey rounded3' href='".get_pagenum_link(1)."'>&laquo;</a></li>";
         if($paged > 1 && $showitems < $pages) echo "<a class='button-small-theme rounded3' href='".get_pagenum_link($paged - 1)."'>&lsaquo;</a>";

         for ($i=1; $i <= $pages; $i++)
         {
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
             {
                 echo ($paged == $i)? "<li><span class='button-small-theme rounded3 current'>".$i."</span></li>":"<li><a class='button-small grey rounded3 inactive' href='".get_pagenum_link($i)."' >".$i."</a></li>";
             }
         }

         if ($paged < $pages && $showitems < $pages) echo "<li><a class='button-small-theme rounded3' href='".get_pagenum_link($paged + 1)."'>&rsaquo;</a></li>";
         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<li><a class='button-small-theme rounded3' href='".get_pagenum_link($pages)."'>&raquo;</a></li>";
         echo "</ul>\n";
     }
}

/* ========================================================================================================================
	
	Custom Menu
	
======================================================================================================================== */


add_theme_support('nav-menus');
register_nav_menu('main_menu', 'Main Menu');

function display_home2() {
    echo '<ul class="nav clearfix sf-menu sf-js-enabled sf-shadow">
		<li class="homelink"><a href="' . home_url() . '">Home</a></li>';
    wp_list_pages('title_li=&depth=3');
    echo '</nav>';
}

add_theme_support('automatic-feed-links');
// Ready for theme localisation
load_theme_textdomain('localization');

/* ========================================================================================================================
	
	Registering our sidebar
	
======================================================================================================================== */

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Home Full',
'before_widget' => '<li class="homeFull widget clearfix"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Home One Half',
'before_widget' => '<li class="eight columns widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Home One Third',
'before_widget' => '<li class="one-third column widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));

}
if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Home Two Thirds',
'before_widget' => '<li class="two-thirds column widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Home One Fourth',
'before_widget' => '<li class="four columns widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Pages',
'before_widget' => '<li class="widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Causes',
'before_widget' => '<li class="widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Events',
'before_widget' => '<li class="widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'News',
'before_widget' => '<li class="widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Staff',
'before_widget' => '<li class="widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Single Causes',
'before_widget' => '<li class="widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}
if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Single Events',
'before_widget' => '<li class="widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Single News',
'before_widget' => '<li class="widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Single Staff',
'before_widget' => '<li class="widget"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

if (function_exists('register_sidebar')) {
register_sidebar(array(
'name' => 'Footer',
'before_widget' => '<li class="widget one-third column"><div id="%1$s" class="%2$s">',
'after_widget' => '</div></li>',
'before_title' => '<h3>',
'after_title' => '</h3>',
));
}

/* ========================================================================================================================
	
	Set Custom Query
	
======================================================================================================================== */

function dd_set_query($custom_query=null) { global $wp_query, $wp_query_old, $post, $orig_post;
	$wp_query_old = $wp_query;
	$wp_query = $custom_query;
	$orig_post = $post;
}

function dd_restore_query() {  global $wp_query, $wp_query_old, $post, $orig_post;
	$wp_query = $wp_query_old;
	$post = $orig_post;
	setup_postdata($post);
}

/* ========================================================================================================================
	
	Adding Excerpt to custom posts
	
======================================================================================================================== */

add_post_type_support( 'post_causes', 'excerpt' );
add_post_type_support( 'post_events', 'excerpt' );
add_post_type_support( 'post_staff', 'excerpt' );

/* ========================================================================================================================
	
	Enqueues scripts and styles for front-end.
	
======================================================================================================================== */

function mission_scripts_styles() {
	global $wp_styles;

	/*
	 * Adds JavaScript to pages with the comment form to support
	 * sites with threaded comments (when in use).
	 */
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	/*
	 * Adds JavaScript for handling the navigation menu hide-and-show behavior.
	 */
        
        wp_enqueue_script("jquery"); 
	wp_enqueue_script( 'mission-script', get_template_directory_uri() . '/js/script.js', array(), '1.0', true );
        wp_enqueue_script( 'mission-prettyPhoto', get_template_directory_uri() . '/js/jquery.prettyPhoto.js', array(), '1.0', true );
        wp_enqueue_script( 'mission-superfish', get_template_directory_uri() . '/js/superfish.js', array(), '1.0', true );
        wp_enqueue_script( 'mission-flexslider', get_template_directory_uri() . '/js/jquery.flexslider-min.js', array(), '1.0', true );
        wp_enqueue_script( 'mission-bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array(), '1.0', true );
        wp_enqueue_script("jquery"); 

	/*
	 * Loads our main stylesheet.
	 */
	wp_enqueue_style( 'mission-style', get_stylesheet_uri() );

        /*
	 * Loads other stylesheet.
	 */
  
        wp_enqueue_style( 'mission-prettyPhoto', get_template_directory_uri() . '/stylesheets/prettyPhoto.css', array( 'mission-style' ), '20121010' );
        wp_enqueue_style( 'mission-custom', get_template_directory_uri() . '/stylesheets/custom.php', array( 'mission-style' ), '20121010' );
        wp_enqueue_style( 'mission-superfish', get_template_directory_uri() . '/stylesheets/superfish.css', array( 'mission-style' ), '20121010' );
        wp_enqueue_style( 'mission-flexslider', get_template_directory_uri() . '/stylesheets/flexslider.css', array( 'mission-style' ), '20121010' );
        wp_enqueue_style( 'mission-btn', get_template_directory_uri() . '/stylesheets/btn.css', array( 'mission-style' ), '20121010' );
        wp_enqueue_style( 'mission-skeleton', get_template_directory_uri() . '/stylesheets/skeleton.css', array( 'mission-style' ), '20121010' );
        wp_enqueue_style( 'mission-bootstrap', get_template_directory_uri() . '/stylesheets/bootstrap.css', array( 'mission-style' ), '20121010' );
        wp_enqueue_style( 'mission-fontello', get_template_directory_uri() . '/font/css/fontello.css', array( 'mission-style' ), '20121010' );
        wp_enqueue_style( 'mission-fontelloie7', get_template_directory_uri() . '/font/css/fontello-ie7.css', array( 'mission-style' ), '20121010' );
        wp_enqueue_style( 'mission-base', get_template_directory_uri() . '/stylesheets/base.css', array( 'mission-style' ), '20121010' );
  
        
	/*
	 * Loads the Internet Explorer specific stylesheet.
	 */
        
	wp_enqueue_style( 'mission-ie6', get_template_directory_uri() . '/stylesheets/ie6.css', array( 'mission-style' ), '20121010' );
        wp_enqueue_style( 'mission-ie7', get_template_directory_uri() . '/stylesheets/ie7.css', array( 'mission-style' ), '20121010' );
        wp_enqueue_style( 'mission-ie8', get_template_directory_uri() . '/stylesheets/ie8.css', array( 'mission-style' ), '20121010' );
	$wp_styles->add_data( 'mission-ie6', 'conditional', 'lt IE 6' );
        $wp_styles->add_data( 'mission-ie7', 'conditional', 'lt IE 7' );
        $wp_styles->add_data( 'mission-ie8', 'conditional', 'lt IE 8' );
   
}
add_action( 'wp_enqueue_scripts', 'mission_scripts_styles' );

