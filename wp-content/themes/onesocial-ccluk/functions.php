<?php
/**
 * @package OneSocial Child Theme
 * The parent theme functions are located at /onesocial/buddyboss-inc/theme-functions.php
 * Add your own functions in this file.
 */

define( 'CCLUK_DEBUGGING', false );

/**
 * To view theme functions, navigate to /buddyboss-inc/theme.php
 *
 * @package OneSocial Theme
 */
$init_file = get_stylesheet_directory() . '/buddyboss-inc/init.php';

if ( !file_exists( $init_file ) ) {

    $err_msg = __( 'OneSocial cannot find the starter file, should be located at: *wp root*/wp-content/themes/onesocial-ccluk/buddyboss-inc/init.php', 'onesocial' );

    wp_die( $err_msg );
}

require_once( $init_file );

/**
 * Add image size for posts
 *
 */
add_image_size( 'ccluk-medium', 750, 1000, false );
add_image_size( 'ccluk-hero', 1200, 800, true );
add_image_size( 'ccluk-feature', 580, 387, true );

/**
 * Customizer additions.
 */
require get_stylesheet_directory() . '/inc/customizer.php';

// load any widgets
require get_stylesheet_directory() . '/inc/widgets/newsletter-signup.php';

// BP custom text
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'buddypress', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
} );


// Category archives to include news posts
function ccluk_show_cpt_archives( $query ) {
    if( ( is_category() || is_tag() ) && empty( $query->query_vars['suppress_filters'] ) ) {
        $query->set(
            'post_type',
            array(
                'post', 'ccluk_news'
            )
        );
        return $query;
    }
}
add_filter( 'pre_get_posts', 'ccluk_show_cpt_archives' );

/*
 * Override default home page title
 *
 */
function ccluk_override_post_title($title){

    if (is_front_page()) {

        $sep = apply_filters( 'document_title_separator', '-' );

        $title = implode( " $sep ", array( get_bloginfo( 'name', 'display' ), get_bloginfo( 'description', 'display' ) ) );
        $title = wptexturize( $title );
        $title = convert_chars( $title );
        $title = esc_html( $title );
        $title = capital_P_dangit( $title );
    }

    return $title;
}
add_filter('pre_get_document_title', 'ccluk_override_post_title', 99);

/**
 * Sets up theme defaults
 *
 * @since OneSocial Child Theme 1.0.0
 */
function ccluk_theme_setup()
{
    /**
     * Makes child theme available for translation.
     * Translations can be added into the /languages/ directory.
     * Read more at: http://www.buddyboss.com/tutorials/language-translations/
     */

    // Translate text from the PARENT theme.
    load_theme_textdomain( 'onesocial', get_stylesheet_directory() . '/languages' );

    // Translate text from the CHILD theme only.
    // Change 'onesocial' instances in all child theme files to 'ccluk_theme'.
    // load_theme_textdomain( 'ccluk_theme', get_stylesheet_directory() . '/languages' );

    // add class to front page
    if (is_front_page()) {
        add_filter( 'body_class', function( $classes ) {
            $classes[] = 'front-page';
            return $classes;
        });
    }

    if (onesocial_get_option( 'boss_layout_switcher' )) {
        global $onesocial_options;

        $onesocial_options[ 'boss_layout_switcher' ] = 0;

        update_option( 'onesocial_options', $onesocial_options );
    }

    // disable public messaging
    add_filter('bp_get_send_public_message_button', '__return_false');
}
add_action( 'after_setup_theme', 'ccluk_theme_setup' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since OneSocial Child Theme  1.0.0
 */
function ccluk_theme_scripts_styles()
{
  /**
   * Scripts and Styles loaded by the parent theme can be unloaded if needed
   * using wp_deregister_script or wp_deregister_style.
   *
   * See the WordPress Codex for more information about those functions:
   * http://codex.wordpress.org/Function_Reference/wp_deregister_script
   * http://codex.wordpress.org/Function_Reference/wp_deregister_style
   **/

   $css = get_stylesheet_directory_uri() . '/assets/' . ( CCLUK_DEBUGGING ? 'css' : 'css-compressed' );

  /*
   * Styles
   *
   * need to ensure this stylesheet loads after the parent stylesheets
   *
   */
   wp_enqueue_style( 'ccluk-custom', $css.'/custom.css', array( 'onesocial-main-global' ) );

   if (is_user_logged_in()) {
       wp_enqueue_style( 'ccluk-members', $css.'/members.css', array( 'ccluk-custom' ) );
   }

   // load fonts
   wp_enqueue_style( 'ccluk-open-sans', 'https://fonts.googleapis.com/css?family=Open+Sans&display=swap' );
   wp_enqueue_style( 'ccluk-ubuntu', 'https://fonts.googleapis.com/css?family=Ubuntu&display=swap' );

  /*
   * Scripts
   *
   * need to ensure this script loads after the parent scripts
   *
   */
   wp_enqueue_script( 'ccluk-menu-js', get_stylesheet_directory_uri() . '/assets/js/menu.'.(CCLUK_DEBUGGING ? '' : 'min.').'js', array( 'jquery' ) );

   // Google Analytics tracking
   wp_enqueue_script( 'ccluk-ga-tracking-js', get_stylesheet_directory_uri() . '/assets/js/ga-tracking.'.(CCLUK_DEBUGGING ? '' : 'min.').'js', array( 'jquery' ) );

}
add_action( 'wp_enqueue_scripts', 'ccluk_theme_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

// Add your own custom functions here

function ccluk_login_styles() { ?>
    <style type="text/css">
        .login #loginform input[type=text],
        .login #loginform input[type=password] {
            font-size: 21px;
            border-bottom: 1px solid #54ae68;
        }
    </style>
<?php }

add_action( 'login_enqueue_scripts', 'ccluk_login_styles' );

// create news post type
function ccluk_create_news_post_type() {
    register_post_type( 'ccluk_news',
        array(
          'labels' => array(
            'name' => __( 'News' ),
            'singular_name' => __( 'News' )
          ),
          'public' => true,
          'has_archive' => true,
          'rewrite' => array('slug' => 'news'),
          'supports' => array( 'title', 'editor', 'thumbnail', 'revisions', 'author' ),
          'taxonomies' => array( 'category', 'post_tag' ),
          'menu_position' => 4
        )
    );

    // add to Buddypress activity stream
    add_post_type_support( 'ccluk_news', 'buddypress-activity' );
}
add_action( 'init', 'ccluk_create_news_post_type' );

// from OnePress
// load section into home page
if ( ! function_exists( 'ccluk_load_section' ) ) {
    /**
     * Load section
     * @since 2.0.0
     * @param $section_id
     */
    function ccluk_load_section( $section_id )
    {
        /**
         * Hook before section
         */
        do_action('ccluk_before_section_' . $section_id);
        do_action('ccluk_before_section_part', $section_id);

        get_template_part('section-parts/section', $section_id );

        /**
         * Hook after section
         */
        do_action('ccluk_after_section_part', $section_id);
        do_action('ccluk_after_section_' . $section_id);
    }
}

if ( ! function_exists( 'ccluk_is_selective_refresh' ) ) {
    function ccluk_is_selective_refresh()
    {
        return isset($GLOBALS['ccluk_is_selective_refresh']) && $GLOBALS['ccluk_is_selective_refresh'] ? true : false;
    }
}

if ( ! function_exists( 'ccluk_get_section_about_data' ) ) {
    /**
     * Get About data
     *
     * @return array
     */
    function ccluk_get_section_about_data()
    {
        $boxes = get_theme_mod('ccluk_homepage_about_boxes');
        if (is_string($boxes)) {
            $boxes = json_decode($boxes, true);
        }
        $page_ids = array();
        if (!empty($boxes) && is_array($boxes)) {
            foreach ($boxes as $k => $v) {
                if (isset ($v['content_page'])) {
                    $v['content_page'] = absint($v['content_page']);
                    if ($v['content_page'] > 0) {
                        $page_ids[] = wp_parse_args($v, array('enable_link' => 0, 'hide_title' => 0));
                    }
                }
            }
        }

        return $page_ids;
    }
}

if ( ! function_exists( 'ccluk_posted_on' ) ) {

    function ccluk_posted_on() {
        printf( '<a href="%1$s" title="%2$s" rel="bookmark" class="entry-date"><time datetime="%3$s">%4$s</time></a>', esc_url( get_permalink() ), esc_attr( get_the_time() ), esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ));
    }

}

/**
 * Admin styles
 */
function ccluk_admin_assets() {

    /**
     * Assign the OneSocial version to a var
     */
    $theme               = wp_get_theme( 'onesocial' );
    $onesocial_version   = $theme[ 'Version' ];

    wp_enqueue_style( 'ccluk-main-admin-css', get_stylesheet_directory_uri() . '/assets/css/admin.css', array(), $onesocial_version, 'all' );
}
add_action( 'admin_enqueue_scripts', 'ccluk_admin_assets' );

// homepage sections
add_action( 'ccluk_section_before_inner', function( $arg ) {

    switch( $arg ) {

        case 'newsletter' :
            echo '<span class="section-title icon">@</span>';
            break;

    }
}, 10, 1 );


// Fix for deprecation BP function called by OneSocial theme

if (!function_exists('bp_is_user_forums')) :

    function bp_is_user_forums() {
        return false;
    }

endif;


// Customize user menu
// remove forums, groups and friends tabs
function ccluk_remove_forums_from_profile()
{
    bp_core_remove_nav_item('forums');
}
add_action('bp_forums_setup_nav','ccluk_remove_forums_from_profile');

function ccluk_remove_groups_from_profile()
{
    bp_core_remove_nav_item('groups');
}
add_action('bp_groups_setup_nav','ccluk_remove_groups_from_profile');

function ccluk_remove_friends_from_profile()
{
    bp_core_remove_nav_item('friends');
}
add_action('bp_friends_setup_nav','ccluk_remove_friends_from_profile');

// add messages to nav
add_action( 'bp_setup_nav', function() {

    $bp = buddypress();

    bp_core_new_nav_item(
        array(
            'name' => __('Messages', 'buddypress'),
            'slug' => $bp->messages->slug,
            'position' => 50,
            'show_for_displayed_user' => false,
            'screen_function' => 'messages_screen_inbox',
            'default_subnav_slug' => 'inbox',
            'item_css_id' => $bp->messages->id
        )
    );
});

// remove submenu links from adminbar
function ccluk_remove_admin_bar_links() {
    if ( is_admin() ) { //nothing to do on admin
        return;
    }
    global $wp_admin_bar;

    $rm_items = array(
        'forums',
        'friends',
        'groups',
        'notifications-read',
        'notifications-unread',
        'settings-general',
        'settings-notifications',
        'settings-profile',
        'settings-delete-account',
        'messages-inbox',
        'messages-starred',
        'messages-sentbox',
        'messages-compose',
        'messages-notices',
        'xprofile-public',
        'xprofile-edit',
        'xprofile-change-avatar',
        'activity-personal',
        'activity-friends',
        'activity-groups',
        'activity-favorites',
        'activity-mentions'
    );

    foreach( $rm_items as $item )
        $wp_admin_bar->remove_menu( 'my-account-'.$item );

    //error_log( print_r( $wp_admin_bar, true ) );
}
add_action( 'wp_before_admin_bar_render', 'ccluk_remove_admin_bar_links' );

// WordPress social login
add_action( 'bp_after_registration_submit_buttons', function() {
    if ( 'registration-disabled' != bp_get_current_signup_step() )
        do_action( 'wordpress_social_login' );
} );

// output privacy policy link
add_action( 'bp_before_registration_submit_buttons', function() {
    // get MailChimp integration options
    $options = get_option('mc4wp_integrations');

    // bail if implicit option is not set as then we'll
    // be displaying a checkbox and text
    if (empty($options['buddypress']['implicit']))
        return;

    ?>
    <p class="privacy-text">
        <a href="/privacy-policy"><?php _e( 'We respect your privacy.', 'onesocial' ) ?></a>
    </p>
<?php } );

// remove wordpress authentication
remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
add_filter( 'authenticate', 'ccluk_authenticate', 10, 3 );

// custom authentication
function ccluk_authenticate($user, $email, $password){

    //Check for empty fields
    if(empty($email) || empty ($password)){
        //create new error object and add errors to it.
        $error = new WP_Error();

        if(empty($email)){ //No email
            $error->add('empty_username', __('<strong>ERROR</strong>: Email field is empty.'));
        }
        else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){ //Invalid Email
            $error->add('invalid_username', __('<strong>ERROR</strong>: Email is invalid.'));
        }

        if(empty($password)){ //No password
            $error->add('empty_password', __('<strong>ERROR</strong>: Password field is empty.'));
        }

        return $error;
    }

    //Check if user exists in WordPress database
    $user = get_user_by('email', $email);

    //bad email
    if(!$user){
        $error = new WP_Error();
        $error->add('invalid', __('<strong>ERROR</strong>: Either the email or password you entered is invalid.'));
        return $error;
    }
    else{ //check password
        if(!wp_check_password($password, $user->user_pass, $user->ID)){ //bad password
            $error = new WP_Error();
            $error->add('invalid', __('<strong>ERROR</strong>: Either the email or password you entered is invalid.'));
            return $error;
        }else{
            return $user; //passed
        }
    }
}
