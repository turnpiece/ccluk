<?php
/**
 * @package OneSocial Child Theme
 * The parent theme functions are located at /onesocial/buddyboss-inc/theme-functions.php
 * Add your own functions in this file.
 */

define( 'CCLUK_DEBUGGING', false );
define( 'CCLUK_BB_COVER_PHOTO', false ); // use group cover photos?
define( 'CCLUK_JOIN_URL', 'https://community.citizensclimate.org/join' );

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

    // include functions for logged in users
    if (is_user_logged_in()) {
        require_once 'inc/members-functions.php';
    }

    // disable public messaging
    add_filter('bp_get_send_public_message_button', '__return_false');

    // check for WordPress Social Login
	if (isset($GLOBALS['WORDPRESS_SOCIAL_LOGIN_VERSION'])) {
		add_action( 'wsl_process_login_authenticate_wp_user_start', 'ccluk_wsl_secure_avatar_fix', 10, 6 );
		add_action( 'wsl_hook_process_login_before_wp_safe_redirect', 'ccluk_wsl_secure_avatar_check', 10, 4 );
		add_filter( 'wsl_hook_alter_get_bp_user_custom_avatar', 'ccluk_wsl_get_bp_avatar_filter', 10, 5 );
		add_filter( 'wsl_hook_alter_wp_user_custom_avatar', 'ccluk_wsl_get_wp_avatar_filter', 10, 8 );
	}
}
add_action( 'after_setup_theme', 'ccluk_theme_setup' );

/****************************** CUSTOM FUNCTIONS ******************************/

// Add your own custom functions here

/*
 *
 * WordPress Social Login hooks
 * 
 * secure avatar fix
 * updates an http:// image to //
 * 
 */
function ccluk_wsl_secure_avatar_fix( $user_id, $provider, $redirect_to, $adapter, $hybridauth_user_profile, $wp_user ) {
	// check for insecure avatar
	if ($hybridauth_user_profile->photoURL && strpos( $hybridauth_user_profile->photoURL, 'http://' ) !== false) {
		$hybridauth_user_profile->photoURL = str_replace( 'http:', '', $hybridauth_user_profile->photoURL );
	}
}

function ccluk_wsl_secure_avatar_check( $user_id, $provider, $hybridauth_user_profile, $redirect_to ) {
	// check for insecure avatar
	if ($hybridauth_user_profile->photoURL && strpos( $hybridauth_user_profile->photoURL, 'http://' ) !== false) {
		update_user_meta( $user_id, 'wsl_current_user_image', str_replace( 'http:', '', $hybridauth_user_profile->photoURL ) );
	}
}

function ccluk_wsl_get_bp_avatar_filter( $wsl_html, $user_id, $wsl_avatar, $html, $args ) {
	if (strpos( $wsl_avatar, 'http://' ) !== false) {
		$wsl_avatar = str_replace( 'http:', '', $wsl_avatar );
		$img_class  = ('class="' .(!empty($args ['class']) ?($args ['class'] . ' ') : '') . 'avatar-wordpress-social-login" ');
		$img_width  = (!empty($args ['width']) ? 'width="' . $args ['width'] . '" ' : 'width="' . bp_core_avatar_full_width() . '" ' );
		$img_height = (!empty($args ['height']) ? 'height="' . $args ['height'] . '" ' : 'height="' . bp_core_avatar_full_height() . '" ' );
		$img_alt    = (!empty( $args['alt'] ) ? 'alt="' . esc_attr( $args['alt'] ) . '" ' : '' );
		$wsl_html = preg_replace('#<img[^>]+>#i', '<img src="' . $wsl_avatar . '" ' . $img_alt . $img_class . $img_height . $img_width . '/>', $html );
	}
	return $wsl_html;
}

function ccluk_wsl_get_wp_avatar_filter( $wsl_html, $user_id, $wsl_avatar, $html, $mixed, $size, $default, $alt ) {
	if (strpos( $wsl_avatar, 'http://' ) !== false) {
		$wsl_avatar = str_replace( 'http:', '', $wsl_avatar );
		$wsl_html = '<img alt="'. $alt .'" src="' . $wsl_avatar . '" class="avatar avatar-wordpress-social-login avatar-' . $size . ' photo" height="' . $size . '" width="' . $size . '" />';
	}
	return $wsl_html;
}

/**
* Redirect buddypress and bbpress pages to registration page
*/
function ccluk_page_template_redirect()
{
    //if not logged in and on a bp page except registration or activation
    if( ! is_user_logged_in() &&
        ( ( ! bp_is_blog_page() && ! bp_is_activation_page() && ! bp_is_register_page() ) || 
        function_exists( 'is_bbpress' ) && is_bbpress() )
    )
    {
        wp_redirect( home_url( '/join/' ) );
        exit();
    }
}
add_action( 'template_redirect', 'ccluk_page_template_redirect' );

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

// display user link
function ccluk_the_user_link( $author_id ) {
    echo ccluk_get_user_link( $author_id );
}

/**
 *
 * get user link
 *
 * @param int $author_id
 * @return $string
 *
 */
function ccluk_get_user_link( $author_id ) {

    if ( is_user_logged_in() ) {
        if ( function_exists( 'bp_core_get_userlink' ) ) {
            $user_link = bp_core_get_userlink( $author_id, false, true );
            if (function_exists( 'buddyboss_sap' ) )
                return $user_link . 'blog';
            else
                return $user_link;
        }
    }

    return get_author_posts_url( $author_id );
}

function ccluk_debug( $message ) {
  if (CCLUK_DEBUGGING)
    error_log( $message );
}

/**
 *
 * vardump
 *
 * @param array $array
 *
 */
function ccluk_vardump( $array ) {
  ccluk_debug( print_r( $array, true ) );
}
