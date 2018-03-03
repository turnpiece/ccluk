<?php

/**
 * @package WordPress
 * @subpackage BuddyPress User Blog
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'BuddyBoss_SAP_BP_Component' ) ):

	/**
	 *
	 * BuddyPress User Blog BuddyPress Component
	 * ***********************************
	 */
	class BuddyBoss_SAP_BP_Component extends BP_Component {

            /**
             * INITIALIZE CLASS
             *
             * @since BuddyPress User Blog 1.0
             */
            public function __construct() {
                    $slug = $this->slug = 'sap';

            parent::start(
                $slug,
                __( 'SAP', 'bp-user-blog' ),
                dirname( __FILE__ )
            );

            // register our component as an active component in BP
            buddypress()->active_components[$this->id] = '1';
		}

		/**
		 * Convenince method for getting main plugin options.
		 *
		 * @since BuddyPress User Blog (1.0.0)
		 */
		public function option( $key ) {
			return buddyboss_sap()->option( $key );
		}

		/**
		 * SETUP BUDDYPRESS GLOBAL OPTIONS
		 *
		 * @since	BuddyPress User Blog 1.0
		 */
		public function setup_globals( $args = array() ) {
                    parent::setup_globals( array(
                        'has_directory'			=> false,
                        'notification_callback' => array( $this, 'format_notifications' ),
                    ) );
		}

		/**
		 * SETUP ACTIONS
		 *
		 * @since  BuddyPress User Blog 1.0
		 */
		public function setup_actions() {
			// Add body class
			add_filter( 'body_class', array( $this, 'body_class' ) );

			// Front End Assets
			if ( !is_admin() && !is_network_admin() ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
			}

			// Back End Assets
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
            
            /**
             * BP Reorder Tabs plugin.
             * Allow admins to set 'blog' as default tab of user profile.
             */
            add_filter( 'bp_r_t_profile_default_options',   array( $this, 'profile_default_option' ) );

            if (bp_is_active('notifications') ) {
                add_action( 'transition_post_status',   array( $this, 'catch_transition_post_type_status' ), 10, 3 );
                add_action( 'template_redirect',        array( $this, 'mark_notification_read' ) );
                add_action( 'post_recommended',         'sap_post_recommended_add_notification', 10, 1 );
                add_action( 'wp',                       'sap_mark_post_recommended_notifications_by_item', 10 );
            }

            parent::setup_actions();
		}

		/**
		 * Add active SAP class
		 *
		 * @since BuddyPress User Blog (0.1.1)
		 */
		public function body_class( $classes ) {
                    
                        global $is_IE;
                        if ( $is_IE ) {
                            $classes[] = 'userblog-ie';
                        }
                        
			$classes[] = apply_filters( 'buddyboss_sap_body_class', 'bp-user-blog' );
			return $classes;
		}

		/**
		 * Load CSS/JS
		 * @return void
		 */
		public function assets() {

			//if ( is_page_template('sap-post-create-template.php') ) {
			//Bower component css
			wp_enqueue_style( 'bp-user-blog-medium-editor', buddyboss_sap()->bower_components . '/medium-editor/dist/css/medium-editor.min.css', array(), '5.23.2', 'all' );
			wp_enqueue_style( 'bp-user-blog-medium-editor-theme', buddyboss_sap()->bower_components . '/medium-editor/dist/css/themes/default.min.css', array(), '5.23.2', 'all' );
			wp_enqueue_style( 'bp-user-blog-medium-editor-insert', buddyboss_sap()->bower_components . '/medium-editor-insert-plugin/dist/css/medium-editor-insert-plugin.min.css', array(), '1.0.0', 'all' );
			wp_enqueue_style( 'bp-user-blog-medium-editor-tables', buddyboss_sap()->bower_components . '/medium-editor-tables/css/medium-editor-tables.min.css', array(), '1.0.0', 'all' );

			//Bower component JS
            wp_enqueue_script( 'jquery-ui-sortable' );

            wp_enqueue_script( 'jquery-ui-widget' );
            wp_enqueue_script( 'buddyboss-bower-iframe-transport', buddyboss_sap()->bower_components . '/blueimp-file-upload/js/jquery.iframe-transport.js', array(), '9.19.0', true );
            wp_enqueue_script( 'buddyboss-bower-fileupload', buddyboss_sap()->bower_components . '/blueimp-file-upload/js/jquery.fileupload.js', array(), '9.19.0', true );

            wp_enqueue_script( 'buddyboss-bower-handlebars', buddyboss_sap()->bower_components . '/handlebars/handlebars.runtime.min.js', array(), '4.0.10', true );
			wp_enqueue_script( 'buddyboss-bower-medium-editor', buddyboss_sap()->bower_components . '/medium-editor/dist/js/medium-editor.min.js', array(), '5.23.2', true );
			wp_enqueue_script( 'buddyboss-bower-medium-autolist', buddyboss_sap()->bower_components . '/medium-editor-autolist/autolist.min.js', array(), '1.0.0', true );
			wp_enqueue_script( 'buddyboss-bower-medium-editor-tables', buddyboss_sap()->bower_components . '/medium-editor-tables/js/medium-editor-tables.min.js', array(), '1.0.0', true );
            wp_enqueue_script( 'buddyboss-bower-medium-editor-insert', buddyboss_sap()->bower_components . '/medium-editor-insert-plugin/dist/js/medium-editor-insert-plugin.min.js', array(), '2.4.1', true );
			
			// FontAwesome icon fonts. If browsing on a secure connection, use HTTPS.
            $recent_fwver = (isset(wp_styles()->registered["fontawesome"]))?wp_styles()->registered["fontawesome"]->ver:"0";
            $current_fwver = "4.7.0";
			if(version_compare($current_fwver, $recent_fwver , '>')) {
				wp_deregister_style( 'fontawesome' );
				wp_register_style( 'fontawesome', "//maxcdn.bootstrapcdn.com/font-awesome/{$current_fwver}/css/font-awesome.min.css", false, $current_fwver);
				wp_enqueue_style( 'fontawesome' );
			}

			// Stylesheet
			//wp_enqueue_style( 'bp-user-blog-main', buddyboss_sap()->assets_url . '/css/bp-user-blog.css', array(), '1.0.3', 'all' );
			wp_enqueue_style( 'bp-user-blog-main', buddyboss_sap()->assets_url . '/css/bp-user-blog.min.css', array(), BUDDYBOSS_SAP_PLUGIN_VERSION, 'all' );
			// Scripts
//			wp_enqueue_script( 'bp-user-blog-main', buddyboss_sap()->assets_url . '/js/bp-user-blog.js', array( 'jquery' ), '1.0.3', true );
			wp_enqueue_script( 'bp-user-blog-main', buddyboss_sap()->assets_url . '/js/bp-user-blog.min.js', array( 'jquery' ), BUDDYBOSS_SAP_PLUGIN_VERSION, true );
			
                        // Localize the script with new data
			$translation_array = array(
				'saving_string'             => __( 'Saving...', 'bp-user-blog' ),
				'saved_string'              => __( 'Save', 'bp-user-blog' ),
				'inreview_string'           => __( 'In Review', 'bp-user-blog' ),
				'draft_string'              => __( 'Draft', 'bp-user-blog' ),
				'review_string'             => __( 'Your post has been submitted for review.', 'bp-user-blog' ),
				'failed_string'             => __( 'Failed', 'bp-user-blog' ),
				'empty_title'               => __( 'Post title cannot be empty', 'bp-user-blog' ),
				'editor_title'              => __( 'Title', 'bp-user-blog' ),
				'empty_content'             => __( 'Post content cannot be empty', 'bp-user-blog' ),
				'content_placeholder'       => __( 'Tell your story', 'bp-user-blog' ),
				'video_placeholder'   	    => __( 'Paste a YouTube, Vimeo, Facebook, Twitter or Instagram link and press Enter', 'bp-user-blog' ),
				'incorrect_url_format' 	    => __( 'Incorrect URL format specified', 'bp-user-blog' ),
				'bold' 	                    => __( 'bold', 'bp-user-blog' ),
				'italic' 	                => __( 'italic', 'bp-user-blog' ),
				'underline' 	            => __( 'underline', 'bp-user-blog' ),
				'anchor' 	                => __( 'anchor', 'bp-user-blog' ),
				'h2' 	                    => __( 'h2', 'bp-user-blog' ),
				'h3' 	                    => __( 'h3', 'bp-user-blog' ),
				'orderedlist' 	            => __( 'orderedlist', 'bp-user-blog' ),
				'unorderedlist' 	        => __( 'unorderedlist', 'bp-user-blog' ),
				'create_table'              => __( 'create table', 'bp-user-blog' ),
				'quote' 	                => __( 'quote', 'bp-user-blog' ),
				'add' 	                    => __( 'Add', 'bp-user-blog' ),
				'remove' 	                => __( 'Remove', 'bp-user-blog' ),
                'min_words_alert'           => sprintf( __( 'The story should be of at least %d words to get it Published otherwise it will be saved as Draft', 'bp-user-blog' ), buddyboss_sap()->option( 'min_words_limit' ) ),
                'max_words_alert'           => sprintf( __( 'The story should not be longer than %d words to get it Published otherwise it will be save as Draft', 'bp-user-blog' ), buddyboss_sap()->option( 'max_words_limit' ) ),
                'exceed_max_files_per_batch' => sprintf( __( 'You can upload a maximum of %s photos in one', 'bp-user-blog' ), buddyboss_sap()->option( 'files_per_batch' ) ),
			);
                        $create_new_post_page = buddyboss_sap()->option('create-new-post');
                        if(!empty( $create_new_post_page ) && $create_new_post_page == get_the_ID()) {
                            //Js and css for tags
                            wp_enqueue_script( 'buddyboss-selectize-js', buddyboss_sap()->assets_url . '/js/selectize.min.js', array(), '1.0.0', true );
                            wp_enqueue_style( 'buddyboss-selectize-css', buddyboss_sap()->assets_url . '/css/selectize.css', array(), '1.0.0', 'all' );
                            wp_enqueue_style( 'buddyboss-selectize-css-default', buddyboss_sap()->assets_url . '/css/selectize.default.css', array(), '1.0.0', 'all' );
                            
                            //for featured image
                            //wp_enqueue_media();
                            
                            wp_enqueue_script('plupload-all');
                            
                            $translation_array['add_new_post'] = __( 'true', 'bp-user-blog' );
                            
                        }
                        
                        $config = array(
                            'loading_image'             => trailingslashit( BUDDYBOSS_SAP_PLUGIN_URL ). '/assets/images/loading.gif',
                            'home_url'                  => trailingslashit( home_url() ),
                            'post_autosave'             => buddyboss_sap()->option('post_autosave'),
                            'max_files_per_batch'       => buddyboss_sap()->option( 'files_per_batch' ),
                            'min_words'                 => buddyboss_sap()->option( 'min_words_limit' ),
                            'max_words'                 => buddyboss_sap()->option( 'max_words_limit' ),
                        );

                        $translation_array['config'] = $config;
                        
                        wp_localize_script( 'bp-user-blog-main', 'sap_loading_string', $translation_array );
            
                        //wp_enqueue_script( 'buddyboss-bower-medium-editor-insert', buddyboss_sap()->bower_components . '/medium-editor-insert-plugin/dist/js/medium-editor-insert-plugin.js', array(), '1.0.1', true );
                        //wp_enqueue_script( 'buddyboss-bower-medium-editor-insert', buddyboss_sap()->bower_components . '/medium-editor-insert-plugin/dist/js/medium-editor-insert-plugin.min.js', array(), '1.0.1', true );
        }

        /**
         * Admin assets
         *
         */
        public function admin_assets() {

            global $current_screen;

            if ( 'buddyboss_page_bb-bp-user-blog' == $current_screen->id ) {
                wp_enqueue_script( 'buddyboss-select2-js', buddyboss_sap()->assets_url . '/js/select2.min.js', array(), '4.0.3', true );
                wp_enqueue_style( 'buddyboss-select2-css', buddyboss_sap()->assets_url . '/css/select2.css', array(), '4.0.3', 'all' );
            }
        }
        
        public function profile_default_option( $options=array() ){
            $options[] = 'blog';
            return $options;
        }

        public function catch_transition_post_type_status( $new_status, $old_status, $post ) {
            if ( 'post' != $post->post_type ) {
                return;
            }

            // This is an edit.
            if ( $new_status === $old_status ) {
                //do nothing
                return;
            }

            // Publishing a previously unpublished post.
            if ( 'publish' === $new_status && 'pending' === $old_status ) {
                //add a new notification
                $this->_add_notification_post_publish( $post );
            // Unpublishing a previously published post.
            } elseif ( 'publish' === $old_status ) {
                // delete related notification
                $this->_delete_notification_post_publish( $post );
            }
        }
        
        protected function _add_notification_post_publish( WP_Post $post ){
            $args = array(
                'user_id'           => $post->post_author,
                'item_id'           => $post->ID,
                'secondary_item_id' => '',
                'component_name'    => 'sap',
                'component_action'  => 'post_approved',
            );
            
            if( is_multisite() ){
                $args['secondary_item_id'] = get_current_blog_id();
            }
            
            bp_notifications_add_notification( $args );
        }
        
        protected function _delete_notification_post_publish( WP_Post $post ){
            $secondary_item_id = 0;
            if( is_multisite() ){
                $secondary_item_id = get_current_blog_id();
            }
            bp_notifications_delete_all_notifications_by_type( $post->ID, 'sap', 'post_approved', $secondary_item_id );
        }
        
        public function format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format='string', $id = 0 ) {
            if ( $action == 'post_approved' ) {
                if( is_multisite() && $secondary_item_id ){
                    switch_to_blog( $secondary_item_id );
                }
                
                $post_permalink = get_permalink( $item_id );
                
                if( is_multisite() && $secondary_item_id ){
                    restore_current_blog();
                }
                
                if( $post_permalink ){
                    $post_permalink = add_query_arg( array(  
                        'action'    => 'bp_sap_mark_read',
                        '_wpnonce'  => wp_create_nonce( 'sap_notif_mark_read' ),
                    ), $post_permalink );
                    
                    $text = apply_filters( "sap_post_approved_notification_text", __( "Your story was approved and published", "bp-user-blog" ) );

                    if( 'string'==$format ){
                        return sprintf( "<a href='%s'>%s</a>", esc_url( $post_permalink ), $text );
                    } else {
                        return array(
                            'text' => $text,
                            'link' => $post_permalink,
                        );
                    }
                }
            }

            if ( $action == 'post_recommended' ) {

                $blog_id = bp_notifications_get_meta( $id, 'sap_post_blog_id', true );

                if ( is_multisite() && $blog_id ) {
                    switch_to_blog( $blog_id );
                }

                $post_permalink = get_permalink( $item_id );
                $post_title     = get_the_title( $item_id );

                if ( is_multisite() && $blog_id ) {
                    restore_current_blog();
                }

                if ( $post_permalink ) {

                    $text = apply_filters( "sap_post_recommended_notification_text", sprintf( __( "%s liked your story %s", "bp-user-blog" ), bp_core_get_username( $secondary_item_id ), $post_title ) );

                    if( 'string'==$format ){
                        return sprintf( "<a href='%s'>%s</a>", esc_url( $post_permalink ), $text );
                    } else {
                        return array(
                            'text' => $text,
                            'link' => $post_permalink,
                        );
                    }
                }

            }
        }
        
        public function mark_notification_read(){
            if( is_admin() || !is_singular( 'post' ) )
                return;
            
            if( !isset( $_GET['action'] ) || $_GET['action'] != 'bp_sap_mark_read' )
                return;
            
            if( ! wp_verify_nonce( $_GET['_wpnonce'], 'sap_notif_mark_read' ) )
                return;
            
            $secondary_item_id = 0;
            if( is_multisite() ){
                $secondary_item_id = get_current_blog_id();
            }
            
            //mark notification read
            bp_notifications_mark_notifications_by_item_id( get_the_author(), get_the_ID(), 'sap', 'post_approved', $secondary_item_id, 0 );
        }
	}



	 //End of class BuddyBoss_SAP_BP_Component


endif;