<?php

class Kadence_Page_Templater_Pinnacle {


	protected $plugin_slug;
        private static $instance;
        protected $templates;

        public static function get_instance() {

                if( null == self::$instance ) {
                        self::$instance = new Kadence_Page_Templater_Pinnacle();
                } 
                return self::$instance;
        } 
        private function __construct() {

                $this->templates = array();

                add_filter('page_attributes_dropdown_pages_args', array( $this, 'register_project_templates' ) );

                add_filter('wp_insert_post_data', array( $this, 'register_project_templates' ) );

                add_filter('template_include', array( $this, 'view_project_template') );

                add_filter('theme_page_templates', array( $this, 'register_project_templates_update') );

                $this->templates = array(
                        'template-contact.php' => __('Contact', 'virtue-toolkit'),
                );
				
        }

        public function register_project_templates( $atts ) {

                // Create the key used for the themes cache
                $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		       $templates = wp_get_theme()->get_page_templates();

                if ( empty( $templates ) ) {
                        $templates = array();
                } 
                // New cache, therefore remove the old one
                wp_cache_delete( $cache_key , 'themes');

                // Now add our template to the list of templates by merging our templates
                // with the existing templates array from the cache.
                $templates = array_merge( $templates, $this->templates );

                // Add the modified cache to allow WordPress to pick it up for listing
                // available templates
                wp_cache_add( $cache_key, $templates, 'themes', 1800 );

                return $atts;

        } 
        public function register_project_templates_update($templates ) {
                if ( ! version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {

                    $templates = array_merge( $templates, $this->templates );

                }
                return $templates;

        } 
        /**
         * Checks if the template is assigned to the page
         */
        public function view_project_template( $template ) {

                global $post;

                if ( !isset( $post ) ) return $template;

                if (!isset($this->templates[get_post_meta( $post->ID, '_wp_page_template', true )] ) ) {
					
                        return $template;
						
                } 

                $file = plugin_dir_path(__FILE__). get_post_meta( $post->ID, '_wp_page_template', true );
				
                // Just to be safe, we check if the file exist first
                if( file_exists( $file ) ) {
                        return $file;
                } else { 
                        echo $file; 
                }

                return $template;

        } 

}
$the_theme = wp_get_theme();
if( ($the_theme->get( 'Name' ) == 'Pinnacle' && $the_theme->get( 'Version') >= '1.0.6' ) || ($the_theme->get( 'Template') == 'pinnacle') ) {
        add_action( 'plugins_loaded', array( 'Kadence_Page_Templater_Pinnacle', 'get_instance' ) );
}
class Kadence_Page_Templater_Virtue {


        protected $plugin_slug;
        private static $instance;
        protected $templates;

        public static function get_instance() {

                if( null == self::$instance ) {
                        self::$instance = new Kadence_Page_Templater_Virtue();
                } 
                return self::$instance;
        } 
        private function __construct() {

                $this->templates = array();

                add_filter('wp_dropdown_pages', array( $this, 'register_project_templates' ) );

                add_filter('wp_insert_post_data', array( $this, 'register_project_templates' ) );

                add_filter('template_include', array( $this, 'view_project_template') );

                add_filter('theme_page_templates', array( $this, 'register_project_templates_update') );

                $this->templates = array(
                        'page-contact.php' => __('Contact', 'virtue-toolkit'),
                );
                                
        }

        public function register_project_templates( $atts ) {

                // Create the key used for the themes cache
                $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

                $templates = wp_get_theme()->get_page_templates();

                if ( empty( $templates ) ) {
                        $templates = array();
                } 
                // New cache, therefore remove the old one
                wp_cache_delete( $cache_key , 'themes');

                // Now add our template to the list of templates by merging our templates
                // with the existing templates array from the cache.
                $templates = array_merge( $templates, $this->templates );

                // Add the modified cache to allow WordPress to pick it up for listing
                // available templates
                wp_cache_add( $cache_key, $templates, 'themes', 1800 );

                return $atts;

        } 
        public function register_project_templates_update($templates ) {
                if ( ! version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {

                    $templates = array_merge( $templates, $this->templates );

                }
                return $templates;

        } 

        /**
         * Checks if the template is assigned to the page
         */
        public function view_project_template( $template ) {

                global $post;

                if ( !isset( $post ) ) return $template;

                if (!isset($this->templates[get_post_meta( $post->ID, '_wp_page_template', true )] ) ) {
                                        
                        return $template;
                                                
                } 

                $file = plugin_dir_path(__FILE__). get_post_meta( $post->ID, '_wp_page_template', true );
                                
                // Just to be safe, we check if the file exist first
                if( file_exists( $file ) ) {
                        return $file;
                } else { 
                        echo $file; 
                }

                return $template;

        } 

}
$the_theme = wp_get_theme();
if( ($the_theme->get( 'Name' ) == 'Virtue' && $the_theme->get( 'Version') >= '2.3.5') || ($the_theme->get( 'Template') == 'virtue') ) {
        add_action( 'plugins_loaded', array( 'Kadence_Page_Templater_Virtue', 'get_instance' ) );
}