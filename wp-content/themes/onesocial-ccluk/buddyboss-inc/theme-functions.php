<?php

/**
 * @package OneSocial Theme
 */
/**
 * Sets up the content width value based on the theme's design and stylesheet.
 */
global $content_width;
$content_width = (isset($content_width)) ? $content_width : 1400;

/**
 * Sets up theme defaults and registers the various WordPress features that OneSocial supports.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_editor_style() To add a Visual Editor stylesheet.
 * @uses add_theme_support() To add support for post thumbnails and automatic feed links.
 * @uses register_nav_menu() To add support for navigation menus.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since OneSocial 1.0.0
 */
function onesocial_setup()
{
	// Makes OneSocial available for translation.
	load_theme_textdomain('onesocial', get_template_directory() . '/languages');

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support('automatic-feed-links');

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support('title-tag');

	// Adds wp_nav_menu() in two locations with BuddyPress deactivated.
	register_nav_menus(array(
		'primary-menu'		 => __('Titlebar', 'onesocial'),
		'secondary-menu'	 => __('Footer Menu', 'onesocial'),
		'header-my-account'	 => __('My Profile', 'onesocial'),
	));

	/*
	 * Enable support for Post Formats.
	 * See http://codex.wordpress.org/Post_Formats
	 */
	//	add_theme_support( 'post-formats', array(
	//		'aside', 'image', 'video', 'quote', 'link',
	//	) );
	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support('post-thumbnails');
	set_post_thumbnail_size(845, 9999); // Unlimited height, soft crop
}

add_action('after_setup_theme', 'onesocial_setup');

/**
 * Disable gallery style
 *
 * @since OneSocial 1.0.0
 */
add_filter('use_default_gallery_style', '__return_false');

/**
 * Detecting phones
 *
 * @since OneSocial 1.0.0
 * from detectmobilebrowsers.com
 */
function is_phone()
{
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4)))
		return true;
}

/**
 * Enqueues scripts and styles for front-end.
 *
 * @since OneSocial 1.0.0
 */
function buddyboss_onesocial_scripts_styles()
{

	global $bp, $onesocial, $buddyboss_js_params;

	$ext = 'css';

	// Used in js file to detect if we are using only mobile layout
	$only_mobile = false;

	/**
	 * Assign the OneSocial version to a var
	 */
	$theme		= wp_get_theme();
	$version	= $theme['Version'];


	/*	 * **************************** STYLES ***************************** */

	$css_dest = '/css';
	$css_compressed_dest = '/css-compressed';
	$assets_dir = get_stylesheet_directory_uri() . '/assets';

	$CSS_URL = $assets_dir . (!CCLUK_DEBUGGING ? $css_compressed_dest : $css_dest);
	$JS_URL = $assets_dir . '/js';

	// OneSocial icon fonts.
	wp_register_style('icons', $CSS_URL . '/onesocial-icons.css', array(), $version, 'all');
	wp_enqueue_style('icons');

	// Activate our main stylesheets.
	wp_enqueue_style('onesocial-main-global', $CSS_URL . '/main-global.css', array('icons'), $version, 'all');

	/*
	 * Custom styles
	 *
	 * need to ensure this stylesheet loads after the parent stylesheets
	 *
	 */
	wp_enqueue_style('ccluk-custom', $CSS_URL . '/custom.css', array('onesocial-main-global'), $version);

	if (is_user_logged_in()) {
		// styles for logged in members
		wp_enqueue_style('ccluk-members', $CSS_URL . '/members.css', array('ccluk-custom'), $version);
	}

	// load fonts
	wp_enqueue_style('ccluk-fonts', 'https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,600i|Ubuntu:700&display=swap');

	/*
	 * Scripts
	 *
	 * need to ensure this script loads after the parent scripts
	 *
	 */
	wp_enqueue_script('ccluk-menu-js', $JS_URL . '/menu.' . (CCLUK_DEBUGGING ? '' : 'min.') . 'js', array('jquery'));

	// Google Analytics tracking
	//wp_enqueue_script('ccluk-ga-tracking-js', $JS_URL . '/ga-tracking.' . (CCLUK_DEBUGGING ? '' : 'min.') . 'js', array('jquery'));

	// is adminbar fixed or floated
	$adminbar_layout = 'fixed';

	if (is_phone()) {
		wp_enqueue_style('onesocial-main-mobile', $CSS_URL . '/main-mobile.' . $ext, array('icons'), $version, 'all');
		$only_mobile = true;
	} elseif (wp_is_mobile()) {
		wp_enqueue_style('onesocial-main-mobile', $CSS_URL . '/main-mobile.' . $ext, array('icons'), $version, 'all');
		$only_mobile = true;
	} else {
		wp_enqueue_style('onesocial-main-desktop', $CSS_URL . '/main-desktop.css', array('icons'), $version, 'screen and (min-width: 1025px)');
		// Activate our own Fixed or Floating (defaults to Fixed) adminbar stylesheet. Load DashIcons and GoogleFonts first.
		wp_enqueue_style('buddyboss-wp-adminbar-desktop-' . $adminbar_layout, $CSS_URL . '/adminbar-desktop-' . $adminbar_layout . '.css', array('dashicons'), $version, 'screen and (min-width: 1025px)');
	}

	// Media query fallback
	if (!wp_script_is('onesocial-main-mobile', 'enqueued')) {
		wp_enqueue_style('onesocial-main-mobile', $CSS_URL . '/main-mobile.' . $ext, array('icons'), $version, 'screen and (max-width: 1024px)');
	}

	/*
	 * Load our BuddyPress styles manually if plugin is active.
	 * We need to deregister the BuddyPress styles first then load our own.
	 * We need to do this for proper CSS load order.
	 */
	if ($onesocial->buddypress_active) {
		// Deregister the built-in BuddyPress stylesheet
		wp_deregister_style('bp-child-css');
		wp_deregister_style('bp-parent-css');
		wp_deregister_style('bp-legacy-css');
		wp_deregister_style('bp-legacy-css-rtl');
	}

	/*
	 * Load our bbPress styles manually if plugin is active.
	 * We need to deregister the bbPress style first then load our own.
	 * We need to do this for proper CSS load order.
	 */
	if ($onesocial->bbpress_active) {
		// Deregister the built-in bbPress stylesheet
		wp_deregister_style('bbp-child-bbpress');
		wp_deregister_style('bbp-parent-bbpress');
		wp_deregister_style('bbp-default');
	}

	// Load our own adminbar (Toolbar) styles.
	if (!is_admin()) {
		// Deregister the built-in adminbar stylesheet
		wp_deregister_style('admin-bar');
	}

	/*	 * **************************** SCRIPTS ***************************** */

	$user_profile = null;

	if (is_object($bp) && is_object($bp->displayed_user) && !empty($bp->displayed_user->domain)) {
		$user_profile = $bp->displayed_user->domain;
	}

	/* UI scripts */
	//wp_enqueue_script('jquery-ui-tooltip');
	//wp_enqueue_script('jquery-form');

	/*
	 * Adds JavaScript to pages with the comment form to support
	 * sites with threaded comments (when in use).
	 */
	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}

	// Heartbeat
	wp_enqueue_script('heartbeat');

	/*
	 * Adds cover scripts
	 */
	/*wp_deregister_script( 'moxie' );
	wp_deregister_script( 'plupload' );
	wp_enqueue_script( 'moxie', get_stylesheet_directory_uri() . '/js/plupload/moxie.min.js', array( 'jquery' ), '1.2.1' );
	wp_enqueue_script( 'plupload', get_stylesheet_directory_uri() . '/js/plupload/plupload.dev.js', array( 'jquery', 'moxie' ), '2.1.2' );*/
	/*
	// Add BuddyBoss words that we need to use in JS to the end of the page
	// so they can be translataed and still used.
	$buddyboss_js_vars = array(
		'select_label'	 => __('Show:', 'onesocial'),
		'post_in_label'	 => __('Post in:', 'onesocial'),
		'tpl_url'		 => get_stylesheet_directory_uri(),
		'child_url'		 => get_stylesheet_directory_uri(),
		'user_profile'	 => $user_profile,
		'ajaxurl'		 => admin_url('admin-ajax.php')
	);

	$buddyboss_js_vars = apply_filters('buddyboss_js_vars', $buddyboss_js_vars);
	*/
	$translation_array = array(
		'only_mobile'	 => $only_mobile,
		'view_desktop'	 => __('View as Desktop', 'onesocial'),
		'view_mobile'	 => __('View as Mobile', 'onesocial'),
		'yes'			 => __('Yes', 'onesocial'),
		'no'			 => __('No', 'onesocial'),
		'other'			 => __('Other', 'onesocial')
	);

	$transport_array = array(
		'eh_url_path' => home_url()
	);

	$js = (defined('CCLUK_DEBUGGING') && CCLUK_DEBUGGING) ? '.js' : '.min.js';

	wp_register_script('ccluk-main', get_stylesheet_directory_uri() . '/js/compressed/ccluk-combined' . $js, array('jquery', 'jquery-form'), $version, true);
	wp_localize_script('ccluk-main', 'translation', $translation_array);
	wp_localize_script('ccluk-main', 'transport', $transport_array);
	wp_localize_script('ccluk-main', 'ajaxposts', array(
		'ajaxurl'	 => admin_url('admin-ajax.php'),
		'postsNonce' => wp_create_nonce('ajax-posts-nonce')
	));

	//wp_localize_script('ccluk-main', 'BuddyBossOptions', $buddyboss_js_vars);
	wp_enqueue_script('ccluk-main');

	/* Custom CCL javascript */
	wp_register_script('onesocial-custom', get_stylesheet_directory_uri() . '/assets/js/custom' . $js, array('jquery'), $version, true);
	wp_enqueue_script('onesocial-custom');
}

add_action('wp_enqueue_scripts', 'buddyboss_onesocial_scripts_styles');

// remove block editor scripts and styles
function ccluk_remove_block_editor_assets()
{
	// Remove block library CSS
	wp_dequeue_style('wp-block-library');
	wp_dequeue_style('wp-block-library-theme');
	wp_dequeue_style('wc-block-style'); // If you use WooCommerce

	// Optionally remove Gutenberg scripts if they're being enqueued
	wp_dequeue_script('wp-block-library');
}
add_action('wp_enqueue_scripts', 'ccluk_remove_block_editor_assets', 100);

/**
 * Admin styles
 */
function onesocial_admin_assets()
{
	/**
	 * Assign theme version to a var
	 */
	$theme		= wp_get_theme();
	$version	= $theme['Version'];

	wp_enqueue_style('buddyboss-bm-main-admin-css', get_stylesheet_directory_uri() . '/css/admin.css', array(), $version, 'all');
}

add_action('admin_enqueue_scripts', 'onesocial_admin_assets');

function escapeJavaScriptText($string)
{
	$string	 = str_replace(array("\n", '"'), array('', '\"'), $string);
	$string	 = preg_replace('/\s+/', ' ', trim($string));
	return $string;
}

/**
 * We need to enqueue jQuery migrate before anything else for legacy
 * plugin support.
 * WordPress version 3.9 onwards already includes jquery 1.11.n version, which we required,
 * and jquery migrate is also properly enqueued.
 * So we dont need to do anything for WP versions greater than 3.9.
 *
 * @package  BuddyPress
 * @since    BuddyPress 3.0
 */
function buddyboss_scripts_jquery_migrate()
{
	global $wp_version;

	if ($wp_version >= 3.9) {
		return;
	}

	// Deregister the built-in version of jQuery
	wp_deregister_script('jquery');

	// Register jQuery. If browsing on a secure connection, use HTTPS.
	wp_register_script('jquery', "//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js", false, null);
	// Activate the jQuery script
	wp_enqueue_script('jquery');

	// Activate the jQuery Migrate script from WordPress
	wp_enqueue_script('jquery-migrate', false, array('jquery'));
}

add_action('wp_enqueue_scripts', 'buddyboss_scripts_jquery_migrate', 0);

/**
 * Removes CSS in the header so we can control the admin bar and be responsive
 *
 * @package  BuddyPress
 * @since    BuddyPress 3.1
 */
function buddyboss_remove_adminbar_inline_styles()
{
	if (!is_admin()) {

		remove_action('wp_head', 'wp_admin_bar_header');
		remove_action('wp_head', '_admin_bar_bump_cb');
	}
}

add_action('wp_head', 'buddyboss_remove_adminbar_inline_styles', 9);

/**
 * Dynamically removes the no-js class from the <body> element.
 *
 * By default, the no-js class is added to the body. The
 * JavaScript in this function is loaded into the <body> element immediately after the <body> tag, 
 * and uses JavaScript to switch the 'no-js' body class to 'js'. 
 * If your theme has styles that should only apply for JavaScript-enabled users, apply them
 * to body.js.
 *
 * This technique is borrowed from WordPress, wp-admin/admin-header.php.
 *
 */
function buddyboss_remove_nojs_body_class()
{
?><script type="text/JavaScript">//<![CDATA[
		(function(){var c=document.body.className;c=c.replace(/no-js/,'js');document.body.className=c;})();
		$=jQuery.noConflict();
		//]]></script>
<?php
}

add_action('buddyboss_before_header', 'buddyboss_remove_nojs_body_class');

/**
 * Remove an anonymous object filter.
 *
 * @param string $tag Hook name.
 * @param string $class Class name
 * @param string $method Method name
 * @return void
 */
function buddyboss_remove_anonymous_object_filter($tag, $class, $method)
{
	$filters = $GLOBALS['wp_filter'][$tag];

	if (empty($filters)) {
		return;
	}

	foreach ($filters as $priority => $filter) {
		foreach ($filter as $identifier => $function) {
			if (
				is_array($function)
				&& is_array($function['function'])
				&& is_a($function['function'][0], $class)
				&& $method === $function['function'][1]
			) {
				remove_filter(
					$tag,
					array($function['function'][0], $method),
					$priority
				);
			}
		}
	}
}

/**
 * Load admin bar in header (fixes JetPack chart issue)
 */
function buddyboss_admin_bar_in_header()
{
	if (!is_admin()) {
		remove_action('wp_footer', 'wp_admin_bar_render', 1000);
		add_action('buddyboss_before_header', 'wp_admin_bar_render');
	}
}

add_action('wp', 'buddyboss_admin_bar_in_header');

/**
 * Creates a nicely formatted and more specific title element text
 * for output in head of document, based on current view.
 *
 * @since OneSocial 1.0.0
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string Filtered title.
 */
function buddyboss_wp_title($title, $sep)
{
	global $paged, $page;

	if (is_feed())
		return $title;

	// Add the site name.
	$title .= get_bloginfo('name');

	// Add the site description for the home/front page.
	$site_description	 = get_bloginfo('description', 'display');
	if ($site_description && (is_home() || is_front_page()))
		$title				 = "$title $sep $site_description";

	// Add a page number if necessary.
	if ($paged >= 2 || $page >= 2)
		$title = "$title $sep " . sprintf(__('Page %s', 'onesocial'), max($paged, $page));

	return $title;
}

//add_filter( 'wp_title', 'buddyboss_wp_title', 10, 2 );

/**
 * Makes our wp_nav_menu() fallback -- wp_page_menu() -- show a home link.
 *
 * @since OneSocial 1.0.0
 */
function buddyboss_page_menu_args($args)
{
	$args['show_home'] = true;
	return $args;
}

add_filter('wp_page_menu_args', 'buddyboss_page_menu_args');

/**
 * Registers all of our widget areas.
 *
 * @since OneSocial Theme 1.0.0
 */
function buddyboss_widgets_init()
{
	// Area 1, located in the pages and posts right column.
	register_sidebar(array(
		'name'			 => 'Page Sidebar',
		'id'			 => 'sidebar',
		'description'	 => 'The default Page/Post widget area.',
		'before_widget'	 => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</aside>',
		'before_title'	 => '<h3 class="widgettitle">',
		'after_title'	 => '</h3>'
	));

	// Area 2, located in the homepage right column.
	register_sidebar(array(
		'name'			 => 'Homepage',
		'id'			 => 'home-sidebar',
		'description'	 => 'The Homepage widget area. Only appears if widgets are added.',
		'before_widget'	 => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</aside>',
		'before_title'	 => '<h3 class="widgettitle">',
		'after_title'	 => '</h3>'
	));

	// Area 16, Only appears on serach results page.
	register_sidebar(array(
		'name'			 => 'Search Results',
		'id'			 => 'search',
		'description'	 => 'The search widget area. Only appears if widgets are added.',
		'before_widget'	 => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</aside>',
		'before_title'	 => '<h4 class="widgettitle">',
		'after_title'	 => '</h4>'
	));


	// Area 12, located in the Footer column 1. Only appears if widgets are added.
	register_sidebar(array(
		'name'			 => 'Footer #1',
		'id'			 => 'footer-1',
		'description'	 => 'The first footer widget area. Only appears if widgets are added.',
		'before_widget'	 => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</aside>',
		'before_title'	 => '<h4 class="widgettitle">',
		'after_title'	 => '</h4>'
	));
	// Area 13, located in the Footer column 2. Only appears if widgets are added.
	register_sidebar(array(
		'name'			 => 'Footer #2',
		'id'			 => 'footer-2',
		'description'	 => 'The second footer widget area. Only appears if widgets are added.',
		'before_widget'	 => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</aside>',
		'before_title'	 => '<h4 class="widgettitle">',
		'after_title'	 => '</h4>'
	));
	// Area 14, located in the Footer column 3. Only appears if widgets are added.
	register_sidebar(array(
		'name'			 => 'Footer #3',
		'id'			 => 'footer-3',
		'description'	 => 'The third footer widget area. Only appears if widgets are added.',
		'before_widget'	 => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</aside>',
		'before_title'	 => '<h4 class="widgettitle">',
		'after_title'	 => '</h4>'
	));
	// Area 15, located in the Footer column 4. Only appears if widgets are added.
	register_sidebar(array(
		'name'			 => 'Footer #4',
		'id'			 => 'footer-4',
		'description'	 => 'The fourth footer widget area. Only appears if widgets are added.',
		'before_widget'	 => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'	 => '</aside>',
		'before_title'	 => '<h4 class="widgettitle">',
		'after_title'	 => '</h4>'
	));
}

add_action('widgets_init', 'buddyboss_widgets_init');

if (!function_exists('buddyboss_entry_meta')) {

	/**
	 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
	 *
	 * Create your own buddyboss_entry_meta() to override in a child theme.
	 *
	 * @since OneSocial 1.0.0
	 */
	function buddyboss_entry_meta()
	{
		// Translators: used between list items, there is a space after the comma.
		$categories_list = get_the_category_list(__(', ', 'onesocial'));

		// Translators: used between list items, there is a space after the comma.
		$tag_list = get_the_tag_list('', __(', ', 'onesocial'));

		$date = sprintf('<a href="%1$s" title="%2$s" rel="bookmark" class="meta-item"><time class="entry-date" datetime="%3$s">%4$s</time></a>', esc_url(get_permalink()), esc_attr(get_the_time()), esc_attr(get_the_date('c')), esc_html(get_the_date()));
		$author = sprintf('<span class="author vcard meta-item"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s%4$s</a></span>', esc_url(get_author_posts_url(get_the_author_meta('ID'))), esc_attr(sprintf(__('View all posts by %s', 'onesocial'), get_the_author())), get_avatar(get_the_author_meta('ID'), 30, '', get_the_author()), get_the_author());

		echo $author;
		echo $date;
		echo '<span class="meta-item">' . $categories_list . '</span>';

		$post_format		 = get_post_format(get_the_ID());
		$post_format_link	 = get_post_format_link($post_format);

		if ($post_format_link) {
			printf('<span class="post-format">Post Format: <a href="%s">%s</a></span>', $post_format_link, $post_format);
		}
	}
}

/**
 * Extends the default WordPress body classes.
 *
 * @since OneSocial 1.0.0
 *
 * @param array Existing class values.
 * @return array Filtered class values.
 */
function buddyboss_body_class($classes)
{
	global $bp, $wp_customize;

	if (!empty($wp_customize)) {
		$classes[] = 'wp-customizer';
	}

	if (!is_multi_author()) {
		$classes[] = 'single-author';
	}

	if (current_user_can('manage_options')) {
		$classes[] = 'role-admin';
	}

	// Default layout class
	if (is_phone()) {
		$classes[] = 'is-mobile';
	} elseif (wp_is_mobile()) {
		$classes[] = 'is-mobile';
		$classes[] = 'tablet';
	} else {
		$classes[] = 'is-desktop';
	}

	// Search sidebar
	if (is_active_sidebar('search') && is_search()) {
		$search_sidebar_alignment	 = 'right';
		$classes[]					 = 'search-sidebar-active bb-has-sidebar sidebar-' . $search_sidebar_alignment;
	}

	$page_sidebar		 = 'right';
	$sidebar_alignment	 = ($page_sidebar) ? $page_sidebar : 'right';

	// Home sidebar
	if (is_active_sidebar('sidebar') && is_home() && !is_front_page()) {
		$classes[] = 'page-sidebar-active home-page bb-has-sidebar sidebar-' . $sidebar_alignment;
	}

	$page_for_posts	 = get_option('page_for_posts');
	$front_page		 = get_option('page_on_front');

	// Home sidebar
	if (is_active_sidebar('home-sidebar') && is_front_page()) {
		$home_sidebar_alignment	 = 'right';
		$classes[]				 = 'homepage-sidebar-active frontpage-page bb-has-sidebar sidebar-' . $home_sidebar_alignment;
	}

	// Blog sidebar
	if (is_active_sidebar('sidebar') && !is_front_page() && is_home() && isset($page_for_posts) && $page_for_posts != 0) {
		$classes[] = 'page-sidebar-active home-page bb-has-sidebar sidebar-' . $sidebar_alignment;
	}

	// Page sidebar
	if (is_active_sidebar('sidebar') && is_page() && !is_front_page()) {
		$classes[] = 'page-sidebar-active bb-has-sidebar sidebar-' . $sidebar_alignment;
	}

	// Archive sidebar
	if (
		is_active_sidebar('sidebar') && is_archive()
	) {
		$classes[] = 'archive-sidebar-active bb-has-sidebar sidebar-' . $sidebar_alignment;
	}

	//Adminbar
	$classes[] = 'no-adminbar';

	// header class
	$header_style	 = 'header-style-1';
	$classes[]		 = $header_style;

	return array_unique($classes);
}

add_filter('body_class', 'buddyboss_body_class');



/* * **************************** ADMIN BAR FUNCTIONS ***************************** */

/**
 * Remove certain admin bar links
 *
 * @since OneSocial 1.0.0
 */
function remove_admin_bar_links()
{
	if (is_admin()) { //nothing to do on admin
		return;
	}
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wp-logo');

	if (!current_user_can('administrator')):
		$wp_admin_bar->remove_menu('site-name');
	endif;
}

add_action('wp_before_admin_bar_render', 'remove_admin_bar_links');

/**
 * Replace admin bar "Howdy" text
 *
 * @since OneSocial 1.0.0
 */
function replace_howdy($wp_admin_bar)
{
	if (is_user_logged_in()) {

		$my_account	 = $wp_admin_bar->get_node('my-account');
		$newtitle	 = str_replace('Howdy,', '', $my_account->title);
		$wp_admin_bar->add_node(array(
			'id'	 => 'my-account',
			'title'	 => $newtitle,
		));
	}
}

add_filter('admin_bar_menu', 'replace_howdy', 25);


/* * **************************** AVATAR FUNCTIONS ***************************** */

/**
 * Replace default member avatar
 *
 * @since OneSocial 1.0.0
 */
if (!function_exists('buddyboss_add_gravatar')) {

	function buddyboss_add_gravatar($avatar_defaults)
	{
		$myavatar = get_stylesheet_directory_uri() . '/images/avatar-member.png';
		//$myavatar = '//upload.wikimedia.org/wikipedia/en/b/b0/Avatar-Teaser-Poster.jpg';

		$avatar_defaults[$myavatar] = 'BuddyBoss Man';

		return $avatar_defaults;
	}

	add_filter('avatar_defaults', 'buddyboss_add_gravatar');
}

/* * **************************** WORDPRESS FUNCTIONS ***************************** */

/**
 * BuddyBoss Previous Logo Support
 *
 * @since OneSocial 1.0.0
 */
function buddyboss_set_previous_logo()
{

	// If there was a logo uploaded prior to upgrading to BuddyBoss 3.1,
	// set it as the new logo to be used in the Theme Customizer

	$previous_logo	 = '';
	$previous_logo	 = get_option("buddyboss_custom_logo");

	if ($previous_logo != '') {
		set_theme_mod('buddyboss_logo', $previous_logo);
	}

	// Remove the previous logo option afterwards

	delete_option("buddyboss_custom_logo");
}

add_action('after_setup_theme', 'buddyboss_set_previous_logo');

/**
 * Checks if a plugin is active.
 *
 * @since OneSocial 1.0.0
 */
function buddyboss_is_plugin_active($plugin)
{
	return in_array($plugin, (array) get_option('active_plugins', array()));
}

/**
 * Add image size for posts
 *
 * @since OneSocial Theme 1.0.0
 */
add_image_size('post-thumb', 845, 312, true);
add_image_size('medium-thumb', 360, 216, true);
add_image_size('large-thumb', 9999, 800, true);

/**
 * Show more posts on profile
 *
 * @since OneSocial Theme 1.0.0
 */
function buddyboss_more_posts_profile($posts, $sort, $count, $data_target)
{
?>
	<div class="wrap">
		<h3 class="title black"><?php _e('Articles', 'onesocial'); ?><span><?php echo $count; ?></span></h3>
		<div class="inner">
			<?php
			while ($posts->have_posts()) {
				$posts->the_post();
				get_template_part('template-parts/content', get_post_format());
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Get posts by ajax
 *
 * @since OneSocial Theme 1.0.0
 */
add_action('wp_ajax_nopriv_buddyboss_ajax_posts', 'buddyboss_ajax_posts');
add_action('wp_ajax_buddyboss_ajax_posts', 'buddyboss_ajax_posts');

function buddyboss_ajax_posts()
{

	$nonce = $_POST['postsNonce'];

	if (!wp_verify_nonce($nonce, 'ajax-posts-nonce')) {
		die('Busted!');
	}

	$data_target = $_POST['data_target'];
	$page		 = $_POST['page'];
	$sort		 = (isset($_POST['sort'])) ? $_POST['sort'] : 'latests';
	$per_page	 = -1;

	if ($page == 'blog') {
		$per_page = 3;
	}

	if ($sort === 'recommended') {
		$args = array(
			'author'		 => $_POST['author'],
			'posts_per_page' => $per_page,
			'orderby'		 => 'meta_value',
			'meta_key'		 => '_post_like_count',
			'orderby'		 => 'meta_value_num',
			'order'			 => 'DESC',
		);
	} else {
		$args = array(
			'author'		 => $_POST['author'],
			'posts_per_page' => $per_page
		);
	}

	$posts = new WP_Query($args);

	ob_start();

	if ($posts->have_posts()) {
		$function = 'buddyboss_more_posts_' . $page;
		$function($posts, $sort, $posts->post_count, $data_target);
	} else {
		_e('No stories found', 'onesocial');
	}

	$html = ob_get_contents();
	ob_end_clean();

	wp_reset_postdata();

	echo $html;

	die();
}


/* * **************************** ADMIN BAR FUNCTIONS ***************************** */

/**
 * Strip all waste and unuseful nodes and keep components only and memory for notification
 * @since OneSocial 1.0.0
 * */
function buddyboss_strip_unnecessary_admin_bar_nodes(&$wp_admin_bar)
{
	ccluk_debug(__FUNCTION__);

	global $admin_bar_myaccount, $bb_adminbar_notifications, $bb_adminbar_messages, $bp;

	$dontalter_adminbar = apply_filters('onesocial_prevent_adminbar_processing', is_admin());
	if ($dontalter_adminbar) { //nothing to do on admin
		return;
	}
	$nodes = $wp_admin_bar->get_nodes();

	$bb_adminbar_notifications[] = @$nodes["bp-notifications"];

	$current_href = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

	foreach ($nodes as $name => $node) {

		if ($node->parent == "bp-notifications") {
			$bb_adminbar_notifications[] = $node;
		}

		if ($node->parent == "" || $node->parent == "top-secondary" and $node->id != "top-secondary") {
			if ($node->id == "my-account") {
				continue;
			}
		}

		//adding active for parent link
		if (
			$node->id == "my-account-xprofile-edit" ||
			$node->id == "my-account-groups-create"
		) {

			if (
				strpos("http://" . $current_href, $node->href) !== false ||
				strpos("https://" . $current_href, $node->href) !== false
			) {
				buddyboss_adminbar_item_add_active($wp_admin_bar, $name);
			}
		}

		//adding active for child link
		if ($node->id == "my-account-settings-general") {
			if (
				$bp->current_component == "settings" ||
				$bp->current_action == "general"
			) {
				buddyboss_adminbar_item_add_active($wp_admin_bar, $name);
			}
		}


		//add active class if it has viewing page href
		if (!empty($node->href)) {
			if (
				("http://" . $current_href == $node->href || "https://" . $current_href == $node->href) ||
				($node->id = 'my-account-xprofile-edit' && strpos("http://" . $current_href, $node->href) === 0)
			) {
				buddyboss_adminbar_item_add_active($wp_admin_bar, $name);
				//add active class to its parent
				if ($node->parent != '' && $node->parent != 'my-account-buddypress') {
					foreach ($nodes as $name_inner => $node_inner) {
						if ($node_inner->id == $node->parent) {
							buddyboss_adminbar_item_add_active($wp_admin_bar, $name_inner);
							break;
						}
					}
				}
			}
		}
	}
}
add_action('admin_bar_menu', 'buddyboss_strip_unnecessary_admin_bar_nodes', 999);

function buddyboss_adminbar_item_add_active(&$wp_admin_bar, $name)
{
	ccluk_debug(__FUNCTION__ . ' ' . $name);
	$gnode = $wp_admin_bar->get_node($name);
	if ($gnode) {
		$gnode->meta["class"] = isset($gnode->meta["class"]) ? $gnode->meta["class"] . " active" : " active";
		$wp_admin_bar->add_node($gnode); //update
	}
}

/**
 * Store adminbar specific nodes to use later for buddyboss
 * @since OneSocial 1.0.0
 * */
function buddyboss_memory_admin_bar_nodes()
{

	ccluk_debug(__FUNCTION__);

	static $bb_memory_admin_bar_step;
	global $bb_adminbar_myaccount;

	$dontalter_adminbar = apply_filters('onesocial_prevent_adminbar_processing', is_admin());
	if ($dontalter_adminbar) { //nothing to do on admin
		ccluk_debug('do not alter admin bar');
		return;
	}

	if (!empty($bb_adminbar_myaccount)) { //avoid multiple run
		ccluk_debug('admin bar empty');
		return false;
	}

	if (empty($bb_memory_admin_bar_step)) {
		ccluk_debug('setting up admin bar');
		$bb_memory_admin_bar_step = 1;
		ob_start();
	} else {
		ccluk_debug('outputting admin bar');
		$admin_bar_output = ob_get_contents();
		ob_end_clean();

		echo $admin_bar_output;

		//strip some waste
		$admin_bar_output = str_replace(array(
			'id="wpadminbar"',
			'role="navigation"',
			'class ',
			'class="nojq nojs"',
			'class="quicklinks" id="wp-toolbar"',
			'id="wp-admin-bar-top-secondary" class="ab-top-secondary ab-top-menu"',
		), '', $admin_bar_output);

		//remove screen shortcut link
		$admin_bar_output	 = @explode('<a class="screen-reader-shortcut"', $admin_bar_output, 2);
		$admin_bar_output2	 = "";
		if (count($admin_bar_output) > 1) {
			$admin_bar_output2 = @explode("</a>", $admin_bar_output[1], 2);
			if (count($admin_bar_output2) > 1) {
				$admin_bar_output2 = $admin_bar_output2[1];
			}
		}
		$admin_bar_output = $admin_bar_output[0] . $admin_bar_output2;

		//remove screen logout link
		$admin_bar_output	 = @explode('<a class="screen-reader-shortcut"', $admin_bar_output, 2);
		$admin_bar_output2	 = "";
		if (count($admin_bar_output) > 1) {
			$admin_bar_output2 = @explode("</a>", $admin_bar_output[1], 2);
			if (count($admin_bar_output2) > 1) {
				$admin_bar_output2 = $admin_bar_output2[1];
			}
		}
		$admin_bar_output = $admin_bar_output[0] . $admin_bar_output2;

		//remove script tag
		$admin_bar_output	 = @explode('<script', $admin_bar_output, 2);
		$admin_bar_output2	 = "";
		if (count($admin_bar_output) > 1) {
			$admin_bar_output2 = @explode("</script>", $admin_bar_output[1], 2);
			if (count($admin_bar_output2) > 1) {
				$admin_bar_output2 = $admin_bar_output2[1];
			}
		}
		$admin_bar_output = $admin_bar_output[0] . $admin_bar_output2;

		//remove user details
		$admin_bar_output	 = @explode('<a class="ab-item"', $admin_bar_output, 2);
		$admin_bar_output2	 = "";
		if (count($admin_bar_output) > 1) {
			$admin_bar_output2 = @explode("</a>", $admin_bar_output[1], 2);
			if (count($admin_bar_output2) > 1) {
				$admin_bar_output2 = $admin_bar_output2[1];
			}
		}
		$admin_bar_output = $admin_bar_output[0] . $admin_bar_output2;

		//add active class into vieving link item
		$current_link = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

		ccluk_vardump($admin_bar_output);

		$bb_adminbar_myaccount = $admin_bar_output;
	}
}

add_action("wp_before_admin_bar_render", "buddyboss_memory_admin_bar_nodes");
add_action("wp_after_admin_bar_render", "buddyboss_memory_admin_bar_nodes");

/**
 * Get adminbar myaccount section output
 * Note :- this function can be overwrite with child-theme.
 * @since OneSocial 1.0.0
 *
 * */
function buddyboss_adminbar_myaccount()
{
	ccluk_debug(__FUNCTION__);
	global $bb_adminbar_myaccount;
	echo $bb_adminbar_myaccount;
}

/**
 * Removing 3rd party hooks
 */
if (!function_exists('onesocial_remove_hooks')) {

	function onesocial_remove_hooks()
	{
		// Bookmark Button Single Post
		remove_filter('the_content', 'sap_add_bookmark_button_after_post');

		// Recommend Button Single Post
		remove_filter('the_content', 'sap_add_recommend_button_after_post');
	}

	add_action('init', 'onesocial_remove_hooks');
}

/**
 * Get Notification from admin bar
 * @since OneSocial 1.0.0
 * */
function buddyboss_adminbar_notification()
{
	global $bb_adminbar_notifications;
	return @$bb_adminbar_notifications;
}

function buddyboss_adminbar_messages()
{
	global $bb_adminbar_messages;
	return @$bb_adminbar_messages;
}

/**
 * Heartbeat settings
 *
 * @since OneSocial 1.0.0
 *
 */
function buddyboss_heartbeat_settings($settings)
{
	$settings['interval'] = 5; //pulse on each 20 sec.
	return $settings;
}

add_filter('heartbeat_settings', 'buddyboss_heartbeat_settings');


/**
 * Add avatar to comment form
 *
 * @since Education 1.0.0
 *
 */
add_action('comment_form_logged_in_after', 'post_comment_form_avatar');

function post_comment_form_avatar()
{

	$user_link = ccluk_get_user_link(get_current_user_id());

	printf('<span class="comment-avatar authors-avatar vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>', $user_link, esc_attr(sprintf(__('View all posts by %s', 'onesocial'), get_the_author())), get_avatar(get_current_user_id(), 85, '', get_the_author()));
}



/**
 * Messages date function
 *
 * @since Education 1.0.0
 *
 */
function buddyboss_format_time($time, $just_date = true, $localize_time = true)
{

	if (!isset($time) || !is_numeric($time)) {
		return false;
	}

	// Get GMT offset from root blog
	$root_blog_offset = false;
	if (!empty($localize_time)) {
		$root_blog_offset = get_option('gmt_offset');
	}

	// Calculate offset time
	$time_offset = $time + ($root_blog_offset * 3600);

	// Current date (January 1, 2010)
	$date = date_i18n('M j', $time_offset);

	// Should we show the time also?
	if (empty($just_date)) {
		// Current time (9:50pm)
		$time = date_i18n(get_option('time_format'), $time_offset);

		// Return string formatted with date and time
		$date = sprintf(__('%1$s at %2$s', 'buddypress'), $date, $time);
	}

	return $date;
}

/**
 * Estimate time required to read the article
 *
 * @return string
 */
function boss_estimated_reading_time($post_content)
{

	$words	 = str_word_count(strip_tags($post_content));
	$minutes = floor($words / 120);
	$seconds = floor($words % 120 / (120 / 60));

	if (1 <= $minutes) {
		$estimated_time = $minutes . __(' min read', 'onesocial');
	} else {
		$estimated_time = $seconds . __(' sec read', 'onesocial');
	}

	return $estimated_time;
}

// Custom Excerpt
if (!function_exists('onesocial_custom_excerpt')) {

	function onesocial_custom_excerpt($content, $limit)
	{
		$excerpt = explode(' ', $content, $limit);
		if (count($excerpt) >= $limit) {
			array_pop($excerpt);
			$excerpt = implode(" ", $excerpt) . '&hellip;';
		} else {
			$excerpt = implode(" ", $excerpt);
		}
		$excerpt = preg_replace('`\[[^\]]*\]`', '', $excerpt);
		return $excerpt;
	}
}

// Change more
function onesocial_excerpt_more()
{
	return '&hellip;';
}

add_filter('excerpt_more', 'onesocial_excerpt_more');

function onesocial_custom_excerpt_length()
{
	return 15;
}

/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own buddyboss_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Twelve 1.0
 */
if (!function_exists('buddyboss_comment')) {

	function buddyboss_comment($comment, $args, $depth)
	{

		$GLOBALS['comment'] = $comment;

		switch ($comment->comment_type) {
			case 'pingback':
			case 'trackback':
	?>

				<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
					<p><?php _e('Pingback:', 'onesocial'); ?> <?php comment_author_link(); ?> <?php edit_comment_link(__('(Edit)', 'onesocial'), '<span class="edit-link">', '</span>'); ?></p>
				<?php
				break;
			default:
				global $post;
				?>
				<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
					<article id="comment-<?php comment_ID(); ?>" class="comment">
						<header class="comment-meta comment-author vcard">
							<?php
							$author_id	 = $comment->user_id;
							$user_link	 = ccluk_get_user_link($author_id);

							printf('<span class="authors-avatar vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>', $user_link, esc_attr(sprintf(__('View all posts by %s', 'onesocial'), get_the_author())), get_avatar($author_id, 85, '', get_the_author()));

							//echo get_avatar( $comment, 44 );
							printf(
								'<cite class="fn">%1$s %2$s</cite>',
								get_comment_author_link(),
								// If current post author is also comment author, make it known visually.
								($comment->user_id === $post->post_author) ? '<span> ' . __('Post author', 'onesocial') . '</span>' : ''
							);
							printf(
								'<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
								esc_url(get_comment_link($comment->comment_ID)),
								get_comment_time('c'),
								/* translators: 1: date, 2: time */
								sprintf(__('%1$s at %2$s', 'onesocial'), get_comment_date(), get_comment_time())
							);
							?>
						</header><!-- .comment-meta -->

						<?php if ('0' == $comment->comment_approved) : ?>
							<p class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.', 'onesocial'); ?></p>
						<?php endif; ?>

						<section class="comment-content comment">
							<?php comment_text(); ?>
						</section><!-- .comment-content -->

						<div class="reply">
							<?php edit_comment_link(__('Edit', 'onesocial'), '<span class="edit-link">', '</span>'); ?>
							<?php comment_reply_link(array_merge($args, array('reply_text' => __('Reply <span>&darr;</span>', 'onesocial'), 'depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
						</div><!-- .reply -->
					</article><!-- #comment-## -->
	<?php
				break;
		} // end comment_type check
	}
}


function onesocial_theme_wrapper_start()
{
	// Fixed - Sidebar moved to the bottom of shop page (Not needed)
	//		if ( is_active_sidebar( 'woo_sidebar' ) ) {
	//			echo '<div class="page-right-sidebar">';
	//		} else {
	//			echo '<div class="page-full-width">';
	//		}

	echo '<div id="primary" class="site-content">';
	echo '<div id="content" role="main" class="woo-content">';
}

function onesocial_theme_wrapper_end()
{
	echo '</div><!-- .woo-content -->';
	echo '</div><!-- #primary -->';
	$show_sidebar = apply_filters('onesocial_show_woo_sidebar', true);
	if (is_active_sidebar('woo_sidebar') && $show_sidebar) {
		echo '<div id="secondary" class="widget-area" role="complementary">';
		dynamic_sidebar('woo_sidebar');
		echo '</div><!-- #secondary -->';
	}
	// Fixed - Sidebar moved to the bottom of shop page (Not needed)
	// echo '</div>';
}

/**
 * Add image size for cover photo.
 *
 * @since OneSocial 1.0.0
 */
if (!function_exists('boss_cover_thumbnail')) :

	add_action('after_setup_theme', 'boss_cover_thumbnail');

	function boss_cover_thumbnail()
	{
		add_image_size('boss-cover-image', 1500, 500, true);
	}

endif;

/**
 * Remove change cover image option from adminbar.
 */
if (!function_exists('buddyboss_admin_bar_remove_links')) {

	function buddyboss_admin_bar_remove_links()
	{
		global $wp_admin_bar;
		$wp_admin_bar->remove_node('my-account-xprofile-change-cover-image');
	}

	add_action('wp_before_admin_bar_render', 'buddyboss_admin_bar_remove_links');
}

/**
 * Return the tags onesocial_trim_excerpt allow
 * @return string
 */
function onesocial_excerpt_allowedtags()
{
	// Add custom tags to this string
	return '<em>,<i>,<br>,<p>,<a>';
}

/**
 * Return OneSocial custom excerpt that will allow few tags
 * @param $wpse_excerpt
 * @return mixed|string|void
 */
function onesocial_trim_excerpt($wpse_excerpt)
{

	$raw_excerpt = $wpse_excerpt;

	if ('' == $wpse_excerpt) {

		$wpse_excerpt = get_the_content('');
		$wpse_excerpt = strip_shortcodes($wpse_excerpt);
		$wpse_excerpt = apply_filters('the_content', $wpse_excerpt);
		$wpse_excerpt = str_replace(']]>', ']]>', $wpse_excerpt);
		$wpse_excerpt = strip_tags($wpse_excerpt, onesocial_excerpt_allowedtags()); /*IF you need to allow just certain tags. Delete if all tags are allowed */

		//Set the excerpt word count and only break after sentence is complete.
		$excerpt_length 	= apply_filters('excerpt_length', 55);
		$tokens 			= array();
		$excerpt_output 	= '';
		$count 				= 0;

		// Divide the string into tokens; HTML tags, or words, followed by any whitespace
		preg_match_all('/(<[^>]+>|[^<>\s]+)\s*/u', $wpse_excerpt, $tokens);

		foreach ($tokens[0] as $token) {

			if ($count >= $excerpt_length) {
				// Limit reached, continue until , ; ? . or ! occur at the end
				$excerpt_output .= trim($token);
				break;
			}

			// Add words to complete sentence
			$count++;

			// Append what's left of the token
			$excerpt_output .= $token;
		}

		$wpse_excerpt = trim(force_balance_tags($excerpt_output));

		if ($count >= $excerpt_length) {
			$excerpt_end 	= '...';
			$excerpt_more 	= apply_filters('excerpt_more', ' ' . $excerpt_end);
			$wpse_excerpt 	.= $excerpt_more; /*Add read more in new paragraph */
		}

		return $wpse_excerpt;
	}

	return apply_filters('onesocial_trim_excerpt', $wpse_excerpt, $raw_excerpt);
}
