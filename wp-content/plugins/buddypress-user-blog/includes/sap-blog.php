<?php

/**
 * @package WordPress
 * @subpackage BuddyPress User Blog
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'BuddyBoss_SAP_Blog' ) ):

	/**
	 *
	 * BuddyBoss_SAP_Blog
	 * ********************
	 *
	 *
	 */
	class BuddyBoss_SAP_Blog {
	
		/**
		 * empty constructor function to ensure a single instance
		 */
		public function __construct() {
			// leave empty, see singleton below
		}

		public static function instance() {
			static $instance = null;

			if ( null === $instance ) {
				$instance = new BuddyBoss_SAP_Blog;
				$instance->setup();
			}
			return $instance;
		}

		/**
		 * Setup all
		 */
		public function setup() {
			
			$this->templates = array();

			add_action( 'bp_setup_nav', array( $this, 'sap_setup_nav' ), 100 );
			add_action( 'bp_setup_admin_bar', array( $this, 'sap_setup_admin_bar' ), 80 );
            
            add_action( 'pre_get_posts', array( $this, 'access_draft_posts' ) );
            
            $this->sap_filters();
                        
		}
        
        public function sap_filters() {
            // can disable with the 'bp_follow_allow_ajax_on_follow_pages' filter
            if ( apply_filters( 'sap_show_blog_filters', true ) ) {
                // add the "Order by" dropdown filter
                add_action( 'bp_member_plugin_options_nav', array( $this, 'sap_blog_filter') );
            }
        }
        
        public function sap_blog_filter(){
            global $bp;
            $sort = (isset( $_GET[ 'sort' ] )) ? $_GET[ 'sort' ] : 'latest';
            $recommend_post = buddyboss_sap()->option( 'recommend_post' );
            $create_new_post_page = buddyboss_sap()->option( 'create-new-post' );

            if ( bp_is_current_component('blog' ) && ( bp_displayed_user_id() == bp_loggedin_user_id() ) && $create_new_post_page ) {
            ?>
            <li id="add_new_post">
                <a class="sap-new-post-btn" href="<?php echo trailingslashit( get_permalink( $create_new_post_page ) ); ?>"><?php _e( 'Add New Post', 'bp-user-blog' ); ?></a>
            </li>
            <?php
            }

            if($bp->current_action == 'blog' ){
            ?>
            <li id="blog-order-select" class="last filter">
                <form id="sort-posts-form" action="" method="GET">
                    <select name="sort" id="sort">
                        <option value="latest" <?php selected( $sort, 'latest' ); ?>><?php _e( 'Latest Stories', 'bp-user-blog' ); ?></option>
                        <?php if ( $recommend_post == 'on' ) { ?>
                            <option value="recommended" <?php selected( $sort, 'recommended' ); ?>><?php _e( 'Most Recommended Stories', 'bp-user-blog' ); ?></option>
                        <?php } ?>
                    </select>
                    <button type="submit" id="sort-submit">Sort</button>
                </form>
            </li>
            <?php
            }
        }

		public function sap_setup_nav() {
                    
                        $post_count_query = new WP_Query(
                            array(
                                'author' => bp_displayed_user_id(),
                                'post_type' => 'post',
                                'posts_per_page' => 1,
                                'post_status' => 'publish'
                            )
                        );
                    
                        $sap_post_count = $post_count_query->found_posts;
                        wp_reset_postdata();
                        
                        bp_core_new_nav_item( array(
				'name' => apply_filters( 'sap_blog_name',sprintf( __( 'Blog <span class="count">%s</span>', 'bp-user-blog' ), $sap_post_count )),
				'slug' => 'blog',
				'screen_function' => 'sap_user_blog_page',
				'position' => 70,
				'default_subnav_slug' => 'blog'
			) );
                        
                        $displayed_user_id = bp_displayed_user_id();
                        $user_domain = ( ! empty( $displayed_user_id ) ) ? bp_displayed_user_domain() : bp_loggedin_user_domain();

                        $blog_link = trailingslashit( $user_domain . 'blog' );
                        
                        // Add subnav items
                        
                        if ( !is_user_logged_in() || bp_displayed_user_id() != get_current_user_id() ) {
                            $name = __( 'Articles', 'bp-user-blog' );
                        } else {
                            $name = __( 'Published', 'bp-user-blog' );
                        }
                        
                        bp_core_new_subnav_item( array(
                            'name' => $name,
                            'slug' => 'blog',
                            'parent_url' => $blog_link,
                            'parent_slug' => 'blog',
                            'screen_function' => 'sap_user_blog_page',
                            'position' => 10,
                        ) );
                        
                        // Add subnav items
                        if ( is_user_logged_in() && bp_displayed_user_id() == get_current_user_id() ) {
                        
                            $publish_post = buddyboss_sap()->option( 'publish_post' );
                            
                            if ( !$publish_post ) {
                               bp_core_new_subnav_item( array(
                                    'name' => __( 'In Review', 'bp-user-blog' ),
                                    'slug' => 'pending',
                                    'parent_url' => $blog_link,
                                    'parent_slug' => 'blog',
                                    'screen_function' => 'sap_user_blog_page',
                                    'position' => 20,
                                ) ); 
                            }

                            bp_core_new_subnav_item( array(
                                'name' => __( 'Bookmarks', 'bp-user-blog' ),
                                'slug' => 'bookmarks',
                                'parent_url' => $blog_link,
                                'parent_slug' => 'blog',
                                'screen_function' => 'sap_user_blog_page',
                                'position' => 30,
                            ) );

                            bp_core_new_subnav_item( array(
                                'name' => __( 'Drafts', 'bp-user-blog' ),
                                'slug' => 'drafts',
                                'parent_url' => $blog_link,
                                'parent_slug' => 'blog',
                                'screen_function' => 'sap_user_blog_page',
                                'position' => 40,
                            ) );


                            
                        }
		}
		
		/**
		 * Adds the user's navigation in WP Admin Bar
		 */
		public function sap_setup_admin_bar( $wp_admin_nav = array() ) {
			global $wp_admin_bar;
			
			$blog_slug = bp_loggedin_user_domain().'blog';
			$in_review_slug = bp_loggedin_user_domain().'blog/pending';
			$bookmarks_slug = bp_loggedin_user_domain().'blog/bookmarks';
			$drafts_slug = bp_loggedin_user_domain().'blog/drafts';
                        
                        $publish_post = buddyboss_sap()->option( 'publish_post' );

			// Menus for logged in user
			if ( is_user_logged_in() ) {

                $wp_admin_bar->add_menu( array(
                    'parent' => 'my-account-buddypress',
                    'id' => 'my-account-blog',
                    'title' => __( 'Blog', 'bp-user-blog' ),
                    'href' => trailingslashit( $blog_slug )
                ) );

                $create_new_post_page = buddyboss_sap()->option('create-new-post');
                $href = trailingslashit(get_permalink( $create_new_post_page ));

                //Keeping addnew post same if network activated
                if (is_multisite()) {
                    if (!function_exists('is_plugin_active_for_network'))
                        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
                    if (is_plugin_active_for_network(basename(constant('BP_PLUGIN_DIR')) . '/bp-loader.php') && is_plugin_active_for_network(basename(constant('BUDDYBOSS_SAP_PLUGIN_DIR')) . '/bp-user-blog.php') ) {
                        $href = trailingslashit(get_blog_permalink( 1,$create_new_post_page ));
                    }
                }

                // Add add-new submenu
                $wp_admin_bar->add_menu( array(
                    'parent' => 'my-account-blog',
                    'id'     => 'my-account-blog-'.'posts',
                    'title'  => __( 'Published', 'bp-user-blog' ),
                    'href'   => trailingslashit( $blog_slug )
                ) );

                $wp_admin_bar->add_menu( array(
                    'parent' => 'my-account-blog',
                    'id'     => 'my-account-blog-bookmarks',
                    'title'  => __( 'Bookmarks', 'bp-user-blog' ),
                    'href'   => $bookmarks_slug
                ) );

                if ( !$publish_post ) {
                    $wp_admin_bar->add_menu( array(
                        'parent' => 'my-account-blog',
                        'id'     => 'my-account-blog'.'-'. __( 'pending', 'bp-user-blog' ),
                        'title'  => __( 'In Review', 'bp-user-blog' ),
                        'href'   => $in_review_slug
                    ) );
                }
                $wp_admin_bar->add_menu( array(
                    'parent' => 'my-account-blog',
                    'id'     => 'my-account-blog'.'-'. __( 'drafts', 'bp-user-blog' ),
                    'title'  => __( 'Drafts', 'bp-user-blog' ),
                    'href'   => $drafts_slug
                ) );

                if ( $create_new_post_page ) {
                    $wp_admin_bar->add_menu( array(
                            'parent' => 'my-account-blog',
                            'id'     => 'my-account-blog'.'-'. __( 'add-new', 'bp-user-blog' ),
                            'title'  => __( 'Add New', 'bp-user-blog' ),
                            'href'   => $href
                    ) );
                }
				
			}
		}
        
        public function access_draft_posts( $query ){
            if ( !is_admin() && $query->is_main_query() && is_user_logged_in() ) {
                if( $query->is_single && isset( $query->query['p'] ) ){
                    $post_id = $query->query['p'];
                    if( (int)$post_id ===0 )
                        return;
                    
                    global $wpdb;
                    $post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID=%d", $post_id ) );
                    if(is_wp_error( $post ) || empty( $post ) )
                        return;
                    if( $post->post_status == 'draft' && $post->post_type=='post' && $post->post_author==get_current_user_id() ){
                        $query->set( 'post_status', array( 'publish', 'draft' ) );
                    }
                }
            }
        }

	}

	// End class BuddyBoss_SAP_Blog

	BuddyBoss_SAP_Blog::instance();
	
	
endif;

