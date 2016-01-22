<?php 

if ( ! isset( $content_width ) ) $content_width = 1170;

define('COSMICA_URI', get_template_directory_uri());
define('COSMICA_DIR', get_template_directory());

require_once dirname( __FILE__ ) . '/includes/comica-variables.php';

register_nav_menus( array(
    'primary_menu'     =>  __('Primary Menu','cosmica'),
));




function cosmica_register_sidebars()
{

    
    
    $SidebarArgs2 = array(
    'name'          => __( 'Right Sidebar', 'cosmica' ),
    'id'            => 'right-sidebar',
    'description'   => '',
    'class'         => '',
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h3 class="widgettitle">',
    'after_title'   => '</h3>'); 
    register_sidebar( $SidebarArgs2 );

    $SidebarArgs3 = array(
    'name'          => __( 'Footer Sidebar 1', 'cosmica' ),
    'id'            => 'footer-sidebar-1',
    'description'   => '',
    'class'         => '',
    'before_widget' => '<section id="%1$s" class="widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3 class="widgettitle">',
    'after_title'   => '</h3>'); 
    register_sidebar( $SidebarArgs3 );

    $SidebarArgs4 = array(
    'name'          => __( 'Footer Sidebar 2', 'cosmica' ),
    'id'            => 'footer-sidebar-2',
    'description'   => '',
    'class'         => '',
    'before_widget' => '<section id="%1$s" class="widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3 class="widgettitle">',
    'after_title'   => '</h3>'); 
    register_sidebar( $SidebarArgs4 );

    $SidebarArgs5 = array(
    'name'          => __( 'Footer Sidebar 3', 'cosmica' ),
    'id'            => 'footer-sidebar-3',
    'description'   => '',
    'class'         => '',
    'before_widget' => '<section id="%1$s" class="widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3 class="widgettitle">',
    'after_title'   => '</h3>'); 
    register_sidebar( $SidebarArgs5);

}

add_action( 'widgets_init', 'cosmica_register_sidebars' );



function cosmica_register_scripts()
{
    
    
    wp_enqueue_style('cosmica-style', get_stylesheet_uri());    
    wp_enqueue_style('cosmica-google-fonts-style', '//fonts.googleapis.com/css?family=Open+Sans:400,600,700,800,300,400italic|Lato:400,400italic,700,900,300'); 
    wp_enqueue_style('cosmica-fa-style',  COSMICA_URI."/css/font-awesome.min.css");
    wp_enqueue_style('cosmica-animation-style',  COSMICA_URI."/css/animate.min.css");
    wp_enqueue_style('cosmica-bootstrap-style',  COSMICA_URI."/css/bootstrap.css");
    wp_enqueue_style('cosmica-slicknav-style',  COSMICA_URI."/css/slicknav.min.css");
    wp_enqueue_style('cosmica-flexslider-style',  COSMICA_URI."/css/flexslider.css");
    wp_enqueue_style('cosmica-custom-style',  COSMICA_URI."/css/custom-style.css");

    wp_enqueue_script( 'jquery' );
    if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' );
    wp_enqueue_script('cosmica-slicknav-script', COSMICA_URI.'/js/jquery.slicknav.min.js', array( 'jquery' ));
    wp_enqueue_script('cosmica-easing-script', COSMICA_URI.'/js/jquery.easing.1.3.js', array( 'jquery' ));
    wp_enqueue_script('cosmica-flexslider-script', COSMICA_URI.'/js/jquery.flexslider-min.js', array( 'jquery' ));
    wp_enqueue_script('cosmica-lavalamp-script', COSMICA_URI.'/js/jquery.lavalamp-1.4.min.js', array( 'jquery' )); 
    wp_enqueue_script('cosmica-custom-script', COSMICA_URI.'/js/custom-script.js', array( 'jquery','cosmica-flexslider-script' ));
    wp_localize_script( 'cosmica-custom-script', 'cosmica_object', array( 'is_admin_bar_showing' => is_admin_bar_showing()));
        
}
add_action('wp_enqueue_scripts', 'cosmica_register_scripts');

function cosmica_lt_ie_9_html5_fix () {    
    if( ! is_admin() ){
        echo "\n".'<!--[if lt IE 9]>';
        echo "\n".'<script src="'. esc_url( COSMICA_URI . '/js/html5.js"' ).'" type="text/javascript"></script>';
        echo "\n".'<![endif]-->';
        echo "\n".'<!--[if lt IE 9]>';
        echo "\n".'<script src="'. esc_url( COSMICA_URI .'/js/css3-mediaqueries.js').'" type="text/javascript"></script>';
        echo "\n".'<![endif]-->'."\n";
    }
}
add_action('wp_head', 'cosmica_lt_ie_9_html5_fix');


function cosmica_custmizer_style()
{
        wp_enqueue_style('cosmica-customizer-css', COSMICA_URI.'/css/customizer-style.css');
}
add_action('customize_controls_print_styles','cosmica_custmizer_style');



function cosmica_is_woocommrce_active(){

include_once(ABSPATH.'wp-admin/includes/plugin.php');
if(is_plugin_active('woocommerce/woocommerce.php')) {
 return true;  
}
else{
    return false;
}

}






function  cosmica_add_supports(){

        load_theme_textdomain( 'cosmica', COSMICA_DIR . '/lang' );
        add_editor_style(esc_url(COSMICA_URI.'/css/custom-style.css'));
        add_theme_support( 'automatic-feed-links' );

        /*
         *  add post format options
         */

        add_theme_support( 'post-formats', array('aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ) );
        add_theme_support( "post-thumbnails" );
        add_theme_support( "title-tag" );
    
        // Add theme support for Custom Header
        $header_args = array(
            'default-image'          => '',
            'width'                  => 1600,
            'height'                 => 300,
            'flex-width'             => true,
            'flex-height'            => true,
            'uploads'                => true,
            'random-default'         => false,
            'header-text'            => true,
            'default-text-color'     => '',
            'wp-head-callback'       => '',
            'admin-head-callback'    => '',
            'admin-preview-callback' => ''
        );
        add_theme_support( 'custom-header', $header_args );

       // Add theme support for Custom Background
        $background_args = array(
        'default-color'          => '#fff',
        'default-image'          => '',
        'default-repeat'         => '',
        'default-position-x'     => '',
        'default-attachment'     => '',
        'admin-head-callback'    => '',
        'admin-preview-callback' => ''
        );
        add_theme_support( 'custom-background', $background_args ); 
        /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
        add_theme_support( 'html5', array(
            'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
        ) );
        
    add_theme_support( 'woocommerce' );

    
}
add_action( 'after_setup_theme', 'cosmica_add_supports' );





function cosmica_new_excerpt_more( $more ) {
    return '';
}
add_filter( 'excerpt_more', 'cosmica_new_excerpt_more' );



function cosmica_entry_date( $echo = true ) {
    if ( has_post_format( array( 'chat', 'status' ) ) )
        $format_prefix = _x( '%1$s on %2$s', '1: post format name. 2: date', 'cosmica' );
    else
        $format_prefix = '%2$s';

    $date = sprintf( '<span class="date"><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a></span>',
        esc_url( get_permalink() ),
        esc_attr( sprintf( __( 'Permalink to %s', 'cosmica' ), the_title_attribute( 'echo=0' ) ) ),
        esc_attr( get_the_date( 'c' ) ),
        esc_html( sprintf( $format_prefix, get_post_format_string( get_post_format() ), get_the_date() ) )
    );

    if ( $echo )
        echo $date;

    return $date;
}


function cosmica_social_links(){

                extract(cosmica_get_theme_var());
                $link_in_new_tab  = (absint($social_link_open_in_new_tab))?'target="_blank"':'';
?>
                <div class="social-nav">
                    <ul>
                        <li><a href="<?php echo esc_url($social_link_facebook); ?>" class="social-icon ico-facebook" <?php echo $link_in_new_tab; ?>></a></li>
                        <li><a href="<?php echo esc_url($social_link_google); ?>" class="social-icon ico-google" <?php echo $link_in_new_tab; ?>></a></li>
                        <li><a href="<?php echo esc_url($social_link_youtube); ?>" class="social-icon ico-youtube" <?php echo $link_in_new_tab; ?>></a></li>
                        <li><a href="<?php echo esc_url($social_link_twitter); ?>" class="social-icon ico-twitter" <?php echo $link_in_new_tab; ?>></a></li>
                        <li><a href="<?php echo esc_url($social_link_linkedin); ?>" class="social-icon ico-linkedin" <?php echo $link_in_new_tab; ?>></a></li>
                    </ul>
                </div>
                
<?php
}

function cosmica_demo_slider(){

extract(cosmica_get_theme_var());

if (!absint($cosmica_hide_demo_slider)):
?>
    <div class="slider-container">
        <div class="slider">
            <div class="flexslider">
                <div class="flexslider">
                  <ul class="slides">
                    <li>
                        <img src="<?php echo esc_url(COSMICA_URI.'/images/slides/slide1.jpg');?>" />
                        <div class="flex-caption">
                            <div class="caption-heading slide-text-title animated bounceInDown"><h2><?php echo esc_html( $cosmica_slide_1_heading); ?></h2></div>
                            <div class="caption-text slide-text-desc animated rotateIn"><span><?php echo esc_html( $cosmica_slide_1_description); ?></span></div>
                            <div class="buttons-con">
                                <a href="<?php echo esc_url($cosmica_slide_1_bt_1_link); ?>" class="button button-main button-success animated fadeInLeftBig" id="banner-action-two"> <?php _e('Read More', 'cosmica'); ?> </a>
                                <a href="<?php echo esc_url($cosmica_slide_1_bt_2_link); ?>" class="button button-main button-warning animated fadeInRightBig"  id="banner-action-one"> <?php _e('Buy Now', 'cosmica'); ?> </a>
                            </div>
                        </div>
                        </li>
                        <li>
                        <img src="<?php echo esc_url(COSMICA_URI.'/images/slides/slide2.jpg');?>" />
                        <div class="flex-caption">
                            <div class="caption-heading slide-text-title"><h2><?php echo esc_html( $cosmica_slide_2_heading); ?></h2></div>
                            <div class="caption-text slide-text-desc"><span><?php echo esc_html( $cosmica_slide_2_description); ?> </span></div>
                            <div class="buttons-con">
                                <a href="<?php echo esc_url($cosmica_slide_2_bt_1_link); ?>" class="button button-main button-success" id="banner-action-two"> <?php _e('Read More', 'cosmica'); ?> </a>
                                <a href="<?php echo esc_url($cosmica_slide_2_bt_2_link); ?>" class="button button-main button-warning"  id="banner-action-one"> <?php _e('Buy Now', 'cosmica'); ?> </a>
                            </div>

                        </div>
                        </li>
                        <li>
                        <img src="<?php echo esc_url(COSMICA_URI.'/images/slides/slide3.jpg');?>" />
                        <div class="flex-caption">
                            <div class="caption-heading slide-text-title"><h2><?php echo esc_html( $cosmica_slide_3_heading); ?></h2></div>
                            <div class="caption-text slide-text-desc"><span><?php echo esc_html( $cosmica_slide_3_description); ?> </span></div>
                            <div class="buttons-con">
                                <a href="<?php echo esc_url($cosmica_slide_3_bt_1_link); ?>" class="button button-main button-success" id="banner-action-two"> <?php _e('Read More', 'cosmica'); ?> </a>
                                <a href="<?php echo esc_url($cosmica_slide_3_bt_2_link); ?>" class="button button-main button-warning"  id="banner-action-one"> <?php _e('Buy Now', 'cosmica'); ?> </a>
                            </div>

                        </div>
                        </li>
                        <li>
                        <img src="<?php echo esc_url(COSMICA_URI.'/images/slides/slide4.jpg');?>" />
                        <div class="flex-caption">
                            <div class="caption-heading slide-text-title"><h2><?php echo esc_html( $cosmica_slide_4_heading); ?></h2></div>
                            <div class="caption-text slide-text-desc"><span><?php echo esc_html( $cosmica_slide_4_description); ?> </span></div>
                            <div class="buttons-con">
                                <a href="<?php echo esc_url($cosmica_slide_4_bt_1_link); ?>" class="button button-main button-success" id="banner-action-two"> <?php _e('Read More', 'cosmica'); ?> </a>
                                <a href="<?php echo esc_url($cosmica_slide_4_bt_2_link); ?>" class="button button-main button-warning"  id="banner-action-one"> <?php _e('Buy Now', 'cosmica'); ?> </a>
                            </div>
                        </div>
                        </li>
                  </ul>
            </div>
        </div>
    </div>
<?php
endif;
}



function cosmica_get_demo_services()
{
 extract(cosmica_get_theme_var());   
?>

            <div class="col-md-4 col-sm-6 cdns-service-container">
                <div class="media">
                    <div class="cdns-theme-feature-icon">
                        <i class="fa fa-tablet"></i>
                    </div>
                    <div id="service-1" class="media-body">
                        <h3><?php echo esc_html($cosmca_services_1_title); ?></h3>
                        <p><?php echo esc_html($cosmca_services_1_desc); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 cdns-service-container">
                <div class="media">
                    <div class="cdns-theme-feature-icon">
                        <i class="fa fa-picture-o"></i>
                    </div>
                    <div id="service-2" class="media-body">
                        <h3><?php echo esc_html($cosmca_services_2_title); ?></h3>
                        <p><?php echo esc_html($cosmca_services_2_desc); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 cdns-service-container">
                <div class="media">
                    <div class="cdns-theme-feature-icon">
                        <i class="fa fa-leaf"></i>
                    </div>
                    <div id="service-3" class="media-body">
                        <h3><?php echo esc_html($cosmca_services_3_title); ?></h3>
                        <p><?php echo esc_html($cosmca_services_3_desc); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 cdns-service-container">
                <div class="media">
                    <div class="cdns-theme-feature-icon">
                        <i class="fa fa-rocket"></i>
                    </div>
                    <div id="service-4" class="media-body">
                        <h3><?php echo esc_html($cosmca_services_4_title); ?></h3>
                        <p><?php echo esc_html($cosmca_services_4_desc); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 cdns-service-container">
                <div class="media">
                    <div class="cdns-theme-feature-icon">
                        <i class="fa fa-gift"></i>
                    </div>
                    <div id="service-5" class="media-body">
                        <h3><?php echo esc_html($cosmca_services_5_title); ?></h3>
                        <p><?php echo esc_html($cosmca_services_5_desc); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 cdns-service-container">
                <div class="media">
                    <div class="cdns-theme-feature-icon">
                        <i class="fa fa-tachometer"></i>
                    </div>
                    <div id="service-6" class="media-body">
                        <h3><?php echo esc_html($cosmca_services_6_title); ?></h3>
                        <p><?php echo esc_html($cosmca_services_6_desc); ?></p>
                    </div>
                </div>
            </div>
<?php
}


require_once dirname( __FILE__ ) . '/includes/cosmica-sanitize-cb.php';
require_once dirname( __FILE__ ) . '/includes/cosmica-customizer.php';

function cosmica_sanitize_text( $str ) {
    return sanitize_text_field( $str );
}