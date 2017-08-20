<?php
/**
 * @package WordPress
 * @subpackage BuddyPress User Blog
 */
if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

/**
 * Hook profile Photo grid template into BuddyPress plugins template
 *
 * @since BuddyPress User Blog 1.0.0
 *
 * @uses add_action() To add the content hook
 * @uses bp_core_load_template() To load the plugins template
 */

function sap_user_blog_page() {
	add_action( 'bp_template_content', 'sap_template_blog' );
	bp_core_load_template( apply_filters( 'sap_user_blog_page', 'members/single/plugins' ) );
}

function sap_template_blog() {
	sap_load_template( 'sap-blog-page' );
}

function sap_load_template( $template ) {
	$template .= '.php';
	if ( file_exists( STYLESHEETPATH . '/bp-user-blog/' . $template ) )
		include_once(STYLESHEETPATH . '/bp-user-blog/' . $template);
	else if ( file_exists( TEMPLATEPATH . '/bp-user-blog/' . $template ) )
		include_once (TEMPLATEPATH . '/bp-user-blog/' . $template);
	else {
		$template_dir = apply_filters( 'sap_templates_dir_filter', buddyboss_sap()->templates_dir );
		include_once trailingslashit( $template_dir ) . $template;
	}
}

function sap_load_template_multiple_times( $template ) {
	$template .= '.php';
	if ( file_exists( STYLESHEETPATH . '/bp-user-blog/' . $template ) )
		include(STYLESHEETPATH . '/bp-user-blog/' . $template);
	else if ( file_exists( TEMPLATEPATH . '/bp-user-blog/' . $template ) )
		include (TEMPLATEPATH . '/bp-user-blog/' . $template);
	else {
		$template_dir = apply_filters( 'sap_templates_dir_filter', buddyboss_sap()->templates_dir );
		include trailingslashit( $template_dir ) . $template;
	}
}

function sap_check_template( $template ) {
	$template .= '.php';
	if ( file_exists( STYLESHEETPATH . '/bp-user-blog/' . $template ) )
		$path	 = STYLESHEETPATH . '/bp-user-blog/' . $template;
	else if ( file_exists( TEMPLATEPATH . '/bp-user-blog/' . $template ) )
		$path	 = TEMPLATEPATH . '/bp-user-blog/' . $template;
	else {
		$template_dir	 = apply_filters( 'sap_check_template_filter', buddyboss_sap()->templates_dir );
		$path			 = trailingslashit( $template_dir ) . $template;
	}
	return $path;
}

function sap_get_page_template_permalink( $template ) {

	$pages = get_pages( array(
		'meta_key'	 => '_wp_page_template',
		'meta_value' => $template
	) );

	foreach ( $pages as $page ) {
		return get_permalink( $page->ID );
	}
}

function sap_save_post() {

	check_ajax_referer( 'sap-editor-nonce', 'sap_nonce' );

	$post_title             = $_POST[ 'post_title' ];
	$post_content		= $_POST[ 'post_content' ];
	$post_status		= $_POST[ 'post_status' ];
	$post_content_status    = strip_tags( $post_content[ 'value' ] );
	$post_category		= isset( $_POST[ 'post_cat' ] ) ? $_POST[ 'post_cat' ] : array();
        $post_tags              = $_POST[ 'post_tags' ];
        $post_img               = $_POST[ 'post_img' ];
        $draft_id		= $_POST[ 'draft_pid' ];
        $res			= array();

	if ( 'public' == $post_status ) {
		$post_status = 'publish';
	}

	if ( empty( $post_content_status ) ) {
		$res[ '0' ] = 'Empty';

		echo json_encode( $res );
		die();
	}
        
        global $wp_version;

	$post_data = array( 'post_title' => strip_tags( $post_title[ 'value' ] ), 'post_content' => $post_content[ 'value' ], 'post_status' => $post_status, 'post_author' => get_current_user_id() );
        
        //For wp backward compatibility
        if ( version_compare( $wp_version, '4.3', '>=' ) ) {
            $post_data['comment_status'] = get_default_comment_status( 'post' );
        } else {
            $post_data['comment_status'] = sap_get_default_comment_status( 'post' );
        }

	if ( isset( $draft_id ) && !empty( $draft_id ) ) {
		$post_data[ 'ID' ] = $draft_id;
		$pid = wp_update_post( $post_data );
	} else {
		$pid = wp_insert_post( $post_data );
	}

	if ( empty( $pid ) || is_wp_error( $pid ) ) {
		$res[ '0' ] = 'Failed';

		echo json_encode( $res );
		die();
	}

	/** No Errors *************************************************************/

	if ( isset( $post_img ) && !empty( $post_img ) ) {
		set_post_thumbnail( $pid, $post_img );
                $pid = wp_insert_post( $post_data );//so that thumbnail is updated in bp activity
	}
        
        wp_set_post_categories( $pid, $post_category );
        wp_set_post_tags( $pid, $post_tags );
        
	$res[ '0' ]	 = $pid;
	$res[ '1' ]	 = get_the_permalink( $pid );

	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_sap_save_post', 'sap_save_post' );
add_action( 'wp_ajax_nopriv_sap_save_post', 'sap_save_post' );

function sap_delete_post() {

	check_ajax_referer( 'sap-editor-nonce', 'sap_nonce' );

	$draft_id = $_POST[ 'draft_pid' ];

	if ( isset( $draft_id ) && !empty( $draft_id ) ) {
		wp_delete_post( $draft_id );
	}

	die();
}

add_action( 'wp_ajax_sap_delete_post', 'sap_delete_post' );
add_action( 'wp_ajax_nopriv_sap_delete_post', 'sap_delete_post' );

if ( !function_exists( 'sap_post_create_template' ) ) {
    function sap_post_create_template( $content ){
        if( !is_main_query() )
            return $content;

        $create_new_post_page = buddyboss_sap()->option( 'create-new-post' );

        if ( !empty( $create_new_post_page ) && is_page($create_new_post_page) ) {
            $content = wpautop(apply_filters('sap_post_create_content', $content)) . sap_buffer_template_part( 'sap-post-create', false );
        }
        return $content;
    }
}

//Loaded after wp_head for yoast seo compatibility
if ( !function_exists( 'sap_load_post_create_query' ) ) {
    function sap_load_post_create_query() {
        add_filter( 'the_content', 'sap_post_create_template' );
    }
}
add_action('wp_head','sap_load_post_create_query', 21 );

if ( !function_exists( 'sap_buffer_template_part' ) ) {
    function sap_buffer_template_part( $template, $echo=true ){
        ob_start();

        sap_load_template( $template );
        // Get the output buffer contents
        $output = ob_get_clean();

        // Echo or return the output buffer contents
        if ( true === $echo ) {
            echo $output;
        } else {
            return $output;
        }
    }
}

// Remove title
if ( !function_exists( 'sap_post_create_page_title' ) ) {
    function sap_post_create_page_title($title){
        
        $create_new_post_page = buddyboss_sap()->option( 'create-new-post' );
        if ( !empty( $create_new_post_page ) && is_page($create_new_post_page) && in_the_loop() ) {
            return '';
        }
        
        if ( !function_exists('onesocial_setup') && is_single()  && in_the_loop() ) {
            return $title . sap_edit_post_link($text = null,  get_the_ID() ,$display = true) ;
        }
        
        return $title;
    }
}
add_filter('the_title','sap_post_create_page_title');

// Remove content
if ( !function_exists( 'sap_post_create_page_content' ) ) {
    function sap_post_create_page_content(){
        return '';    
    }
}
add_filter('sap_post_create_content','sap_post_create_page_content');

//Block access to logged-out users
add_action( 'wp', 'sap_post_create_template_access' );

function sap_post_create_template_access() {

	$create_new_post_page = buddyboss_sap()->option( 'create-new-post' );

	if ( !empty( $create_new_post_page ) && $create_new_post_page == get_the_ID() && !is_user_logged_in() ) {
		$login_redirect = wp_login_url( get_permalink( $create_new_post_page ) );
		wp_safe_redirect( $login_redirect );
		exit;
	}
}

// Bookmarks page
add_action( 'pre_get_posts', 'sap_bookmarks_template' );

function sap_bookmarks_template( $query ) {

	$bookmarks_page		 = buddyboss_sap()->option( 'bookmarks-page' );
	$bookmark_post		 = buddyboss_sap()->option( 'bookmark_post' );
	$current_pagename	 = $query->query_vars[ 'pagename' ];
	$bookmark_pagename	 = null;

	if ( !empty( $bookmarks_page ) ) {
		$post               = get_post( $bookmarks_page );
		$bookmark_pagename  = $post->post_name;
	}
        
	if ( !is_admin() && is_user_logged_in() && !empty( $bookmarks_page ) && $current_pagename === $bookmark_pagename && $bookmark_post == 'on' ) {

		$bookmarked = get_user_option( 'sap_user_bookmarks', bp_loggedin_user_id() );
        
		if ( !empty( $bookmarked ) ) {
			// modify query_vars:
			$query->set( 'post_type', 'post' );  // override 'post_type'
			$query->set( 'pagename', null );  // override 'pagename'
			$query->set( 'ignore_sticky_posts', 1 );
			$query->set( 'post__in', $bookmarked );
			$query->set( 'm', '' );

			// Support for paging
			$query->is_page		 = null;
			$query->is_archive	 = true;
			$query->is_singular	 = 0;
		}
	}

	return $query;
}

// Set Bookmarks page title
add_filter( 'get_the_archive_title', 'sap_set_bookmarks_page_title' );

// Bookmark Page Title
function sap_set_bookmarks_page_title( $title ) {
	global $wp_query;

	$bookmarks_page_id = buddyboss_sap()->option( 'bookmarks-page' );

	if ( $bookmarks_page_id == $wp_query->queried_object_id ) {
		$title = get_the_title( $bookmarks_page_id );
	}

	return $title;
}

// Bookmark Page Content
if ( !function_exists( 'sap_show_content_if_no_bookmarks' ) ) {

	function sap_show_content_if_no_bookmarks( $content ) {

		global $wp_query;

		$bookmarks_page_id	 = buddyboss_sap()->option( 'bookmarks-page' );
		$bookmarked			 = array();

		if ( is_user_logged_in() ) {
			$bookmarked = get_user_option( 'sap_user_bookmarks', get_current_user_id() );
		}

		if ( is_user_logged_in() &&
		     !empty( $bookmarks_page_id ) &&
		     empty( $bookmarked ) &&
		     is_page( $bookmarks_page_id ) ) {
			$content = sprintf( __( '<div class="sap-no-bookmarks-content"><p>You haven&apos;t bookmarked any stories yet.</p><p>Save stories to read later by using the <span class="fa bb-helper-icon fa-bookmark"></span> bookmark button.</p></div>', 'bp-user-blog' ));
		}

		return $content;
	}

	add_filter( 'the_content', 'sap_show_content_if_no_bookmarks' );
}

/**
 * Function render category hierarchy
 */
function sap_post_category_html( $post_category ){
    
    $cat_args = apply_filters( 'sap_post_category_args', array(
        'hide_empty' => false,
    ));
    $blog_post_categories = get_terms( 'category', $cat_args );
    
    echo "<div class='post_categories_cont' >";
    sap_render_category_recursive_fun( $blog_post_categories, $post_category, 0 );
    echo "</div>";
}

/**
 * Function render single parent child category
 * @param type $blog_post_categories
 * @param type $loop_parent
 */
function sap_render_category_recursive_fun( $blog_post_categories, $post_category, $loop_parent = 0 ){
    
    echo "<ul class='category_hierarchy_ul parent_$loop_parent' >";
    foreach( $blog_post_categories as $single_category_obj ){

        $cat_id = $single_category_obj->term_id;
        $cat_name = $single_category_obj->name;
        $cat_parent = $single_category_obj->parent;

        if( $cat_parent == $loop_parent ){
            echo "<li class='single_cat cat_$cat_id' >";

                $checked_attr = '';
                if( $post_category && in_array($cat_id, $post_category) ){
                    $checked_attr = 'checked';
                }
            
                echo "<label><input type='checkbox' name='cat[]' class='blogpost_cat' value='$cat_id' $checked_attr /> $cat_name</label>";

                $has_child_cat = get_terms( 'category', array(
                    'parent' => $cat_id,
                    'hide_empty' => false
                ));

                if( $has_child_cat ){
                    sap_render_category_recursive_fun( $blog_post_categories, $post_category, $cat_id );
                }

            echo "</li>";
        }
    }
    echo "</ul>";
}

// Category Tags Widget
function sap_post_category_tags_widget($pid, $post_category) {
        $tags_Arr = array();
        $tags = wp_get_post_tags($pid);
        foreach ( $tags as $tag ) {
            array_push($tags_Arr, $tag->name);
        }
        $tags = implode(",", $tags_Arr);
	?>
	<aside class="widget sap-widget sap-category-widget">
		<h3 class="widgettitle"><?php _e( 'Categories', 'bp-user-blog' ); ?></h3>
		<?php sap_post_category_html($post_category ); ?>
	</aside>

	<aside class="widget sap-widget sap-tags-widget">
		<h3 class="widgettitle"><?php _e( 'Tags', 'bp-user-blog' ); ?></h3>
		<input type="text" value="<?php echo $tags; ?>" class="tags-select selectized" id="input-tags-select" tabindex="-1">
	</aside>
	<?php
}

// Featured Image Widget
function sap_post_featured_img_widget() {
	?>
	<aside class="widget sap-widget sap-featured-img-widget">
		<h3 class="widgettitle"><?php _e( 'Featured Image', 'bp-user-blog' ); ?></h3>
		<input type="hidden" id="featured-img-placeholder-id" value="" />
		<div id="feaured-img-holder-wrap">
			<button type="button" class="featured-img-placeholder" id="featured-img-placeholder" data-reactid=".3.0.0.1.1.1.1.0.0.1.0">
				<svg class="featured-img-gridicon" height="48" width="48" viewBox="0 0 24 24" data-reactid=".3.0.0.1.1.1.1.0.0.1.0.0">
				<g data-reactid=".3.0.0.1.1.1.1.0.0.1.0.0.0">
				<path d="M13 9.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5-.672 1.5-1.5 1.5-1.5-.672-1.5-1.5zM22 6v12c0 1.105-.895 2-2 2H4c-1.105 0-2-.895-2-2V6c0-1.105.895-2 2-2h16c1.105 0 2 .895 2 2zm-2 0H4v7.444L8 9l5.895 6.55 1.587-1.85c.798-.932 2.24-.932 3.037 0L20 15.426V6z" data-reactid=".3.0.0.1.1.1.1.0.0.1.0.0.0.0"></path>
				</g>
				</svg>
				<span class="featured-img-button" data-reactid=".3.0.0.1.1.1.1.0.0.1.0.1"><?php _e( 'Set Featured Image', 'bp-user-blog' ); ?></span>
			</button>
			<img class="featured-img-preview" src="" height="225" width="300" alt="featured-img-preview" />
			<a href="#" class="sap-preview-close"><i class="fa fa-times"></i></a>
		</div>
	</aside>
	<?php
}

add_action( 'wp_ajax_buddyboss_sap_post_photo', 'buddyboss_sap_post_photo' );
add_action( 'wp_ajax_nopriv_buddyboss_sap_post_photo', 'buddyboss_sap_post_photo' );

function buddyboss_sap_post_photo() {
	
	if(!is_user_logged_in()) {
		echo "-1";
	}
	
	check_ajax_referer( 'sap-editor-nonce', 'sap-editor-nonce' );

	if ( !function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
	}

	if ( !function_exists( 'media_handle_upload' ) ) {
		require_once(ABSPATH . 'wp-admin/includes/admin.php');
	}

	$name	 = $url	 = null;


	
	if(isset($_FILES["files"])) {
		global $_FILES;
		foreach($_FILES["files"] as $key => $val) {
			$_FILES["files"][$key] = $val[0];
		}
		$filename = "files";
	} else {
		$filename = "file";
	}

	$aid		 = media_handle_upload( $filename, 0 );
	$attachment	 = get_post( $aid );
	
	if ( ! empty($attachment->ID) ) {
		$name		 = $attachment->post_title;
		$img_size	 = array( 300, 225 );
		$url_nfo	 = wp_get_attachment_image_src( $aid, $img_size );
		$full = wp_get_attachment_image_src( $aid, "full" );
		$url		 = is_array( $url_nfo ) && !empty( $url_nfo ) ? $url_nfo[ 0 ] : null;
	} else {
		//die("-1");
	}

	
	
	$result = array(
		'status'		 => ( $attachment !== null ),
		'attachment_id'	 => (int) $aid,
		'url'			 => esc_url( $url ),
		'name'			 => esc_attr( $name )
	);
	
	if($filename != "files") {
	echo htmlspecialchars( json_encode( $result ), ENT_NOQUOTES );
	} else {
		$result = array("files"=>array(array("url"=>$full[0])));
		echo json_encode($result);
	}
	exit( 0 );
}


add_action( 'wp_ajax_buddyboss_sap_delete_photo', 'buddyboss_sap_delete_photo' );
add_action( 'wp_ajax_nopriv_buddyboss_sap_delete_photo', 'buddyboss_sap_delete_photo' );

function buddyboss_sap_delete_photo() {
	
	if(!is_user_logged_in()) {
		echo "-1";
	}
	
	check_ajax_referer( 'sap-editor-nonce', 'sap-editor-nonce' );

	$file = $_POST["file"];
	
	if(strpos($file,"data:image") !== false) {
		echo "1";
		exit;
	}
	
	global $wpdb;
	
	$attachment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE guid='%s';", $file )); 

	if(!empty($attachment)){
		
		if($attachment->post_author == get_current_user_id()) {
			wp_delete_attachment($attachment->ID,true);
			echo "1";
			exit; 
		}
		
	}
	
	echo "-1";
	exit;
	
}


add_action( 'template_redirect', 'sap_fetch_editor_oembed_data', 9 );

function sap_fetch_editor_oembed_data() {
	if ( !isset( $_GET[ 'sap_oembedfetch' ] ) )
		return;

	$response			 = array(
		"url" => "",
	);
	$response[ 'url' ]	 = isset( $_GET[ 'url' ] ) && !empty( $_GET[ 'url' ] ) ? $_GET[ 'url' ] : '';
	if ( !$response[ 'url' ] ) {
		die( json_encode( $response ) );
	}

	$args = array( 'width' => $_GET[ 'width' ] );

	require_once( ABSPATH . WPINC . '/class-oembed.php' );
	$oembed		 = _wp_oembed_get_object();
	$provider	 = $oembed->get_provider( $response[ 'url' ], $args );
	$data		 = $oembed->fetch( $provider, $response[ 'url' ], $args );
	die( json_encode( $data ) );
}

/**
 * User Profile Blog List
 */
if ( !function_exists( 'sap_posts_ajax_pagination' ) ) {

	function sap_posts_ajax_pagination() {

		$paged	 = $_POST[ 'paged' ];
		$sort	 = (isset( $_POST[ 'sort' ] )) ? $_POST[ 'sort' ] : 'latest';
                $posts_per_page = 20;

		if ( $sort === 'recommended' ) {
			$query_args = array(
                            'author'		=> bp_displayed_user_id(),
                            'post_type'		=> 'post',
                            'post_status'	=> 'publish',
                            'meta_key'          => '_post_like_count',
                            'orderby'		=> 'meta_value_num',
                            'order'		=> 'DESC',
                            'posts_per_page'    => $posts_per_page,
                            'paged'		=> $paged,
			);
		} else {
			$query_args = array(
                            'author'            => bp_displayed_user_id(),
                            'post_type'         => 'post',
                            'post_status'       => 'publish',
                            'posts_per_page'    => $posts_per_page,
                            'paged'             => $paged,
			);
		}

		$posts = new WP_Query( $query_args );

		if ( $posts->have_posts() ) {
			while ( $posts->have_posts() ) {
				$posts->the_post();
				sap_load_template_multiple_times( 'profile-blog-list' );
			}
		}

		wp_reset_postdata();

		die();
	}

	add_action( 'wp_ajax_sap_posts_pagination', 'sap_posts_ajax_pagination' );
	add_action( 'wp_ajax_nopriv_sap_posts_pagination', 'sap_posts_ajax_pagination' );
}

function sap_custom_edit_post_link( $output ) {
    
    if ( is_page() ) {
        return $output;
    }
    $create_new_post_page = buddyboss_sap()->option('create-new-post');
    $href = trailingslashit(get_permalink( $create_new_post_page )) .'?post='.  get_the_ID() ;
    
    if ( ! $post = get_post(get_the_ID() ) )
		return;
    
    if ( $post->post_author != get_current_user_id() )
            return $output;
    
    $output = '<a class="post-edit-link" href="'. $href .'">Edit</a>';
    
    return $output;
}
add_filter('edit_post_link', 'sap_custom_edit_post_link');

function sap_edit_post_link($text = null,$id = 0,$display = false) {
    if ( is_page() || ! $post = get_post( $id ) ) {
		return;
	}

	if ( 'post' != $post->post_type || ! $url = sap_get_edit_post_link( $post->ID ) ) {
		return;
	}

	if ( null === $text ) {
		$text = __( 'Edit', 'bp-user-blog' );
	}

	$link = '<a class="post-edit-link" href="' . $url . '">' . $text . '</a>';

	/**
	 * Filter the post edit link anchor tag.
	 *
	 * @since 2.3.0
	 *
	 * @param string $link    Anchor tag for the edit link.
	 * @param int    $post_id Post ID.
	 * @param string $text    Anchor text.
	 */
        
        if ( $display ) {
            return apply_filters( 'sap_edit_post_link', $link, $post->ID, $text );
        } else {
            echo apply_filters( 'sap_edit_post_link', $link, $post->ID, $text );
        }
}

function sap_get_edit_post_link( $id = 0 ) {
	if ( ! $post = get_post( $id ) )
		return;
        
        $create_new_post_page = buddyboss_sap()->option('create-new-post');
        $href = trailingslashit(get_permalink( $create_new_post_page )) .'?post='.  get_the_ID() ;

	$post_type_object = get_post_type_object( $post->post_type );
	if ( !$post_type_object )
		return;

	if ( !is_user_logged_in() )
		return;
        
        if ( $post->post_author != get_current_user_id() )
            return;

	/**
	 * Filter the post edit link.
	 *
	 * @since 2.3.0
	 *
	 * @param string $link    The edit link.
	 * @param int    $post_id Post ID.
	 * @param string $context The link context. If set to 'display' then ampersands
	 *                        are encoded.
	 */
	return apply_filters( 'sap_get_edit_post_link', $href );
}

add_filter('bp-user-blog_editable_content', 'wptexturize');
add_filter('bp-user-blog_editable_content', 'convert_smilies');
add_filter('bp-user-blog_editable_content', 'convert_chars');
add_filter('bp-user-blog_editable_content', 'wpautop');

add_filter( 'bp-user-blog_editable_content', 'sap_prepare_oembed_for_editor' );
function sap_prepare_oembed_for_editor( $post_content ){
    $pattern = "/<div class=\"medium-insert-embeds medium-insert-embeds-wpoembeds(.+?)>(.+?)<\/div>/is";
    $replacement = "<div class=\"me_oembed_orig_url\" data-url=\"$2\" style=\"display:none\"></div><div class=\"medium-insert-embeds medium-insert-embeds-wpoembeds$1><figure><div class=\"medium-insert-embed\">$2</div></figure></div>";
    $post_content = preg_replace( $pattern, $replacement, $post_content );
    
    global $wp_embed;
    $post_content = $wp_embed->autoembed( $post_content );
    return $post_content;
}

function sap_categorized_blog() {
    if ( false === ( $all_the_cool_cats = get_transient( 'userblog_categories' ) ) ) {
            // Create an array of all the categories that are attached to posts.
            $all_the_cool_cats = get_categories( array(
                    'fields'	 => 'ids',
                    'hide_empty' => 1,
                    // We only need to know if there is more than one category.
                    'number'	 => 2,
            ) );

            // Count the number of categories that are attached to the posts.
            $all_the_cool_cats = count( $all_the_cool_cats );

            set_transient( 'userblog_categories', $all_the_cool_cats );
    }

    if ( $all_the_cool_cats > 1 ) {
            // This blog has more than 1 category so tmsrvd_categorized_blog should return true.
            return true;
    } else {
            // This blog has only 1 category so tmsrvd_categorized_blog should return false.
            return false;
    }
}

function sap_entry_categories() {
    /* translators: used between list items, there is a space after the comma */
    $categories_list = get_the_category_list( __( ', ', 'bp-user-blog' ) );
    if ( $categories_list && sap_categorized_blog() ) {
        return '<span class="cat-links">' . $categories_list . '</span>';
    }
}

add_filter( 'the_content', 'sap_category_display' );
function sap_category_display($content) {
    
    if (function_exists('onesocial') ) {
        return $content;
    }
    
    if ( is_singular( 'post' ) && get_post_status(get_the_ID()) == 'publish' ) {
        
        $button = '<span class="category-container">';
        $button .= __( 'Published in ', 'bp-user-blog' );
        $button .= sap_entry_categories();
        $button .= '</span>';
        
        $content .= $button;
    }
    
    return $content;
    
}

/**
 * Gets the default comment status for a post type.
 *
 * @since 1.0.7
 *
 * @param string $post_type    Optional. Post type. Default 'post'.
 * @param string $comment_type Optional. Comment type. Default 'comment'.
 * @return string Expected return value is 'open' or 'closed'.
 */
function sap_get_default_comment_status( $post_type = 'post', $comment_type = 'comment' ) {
	switch ( $comment_type ) {
		case 'pingback' :
		case 'trackback' :
			$supports = 'trackbacks';
			$option = 'ping';
			break;
		default :
			$supports = 'comments';
			$option = 'comment';
	}

	// Set the status.
	if ( 'page' === $post_type ) {
		$status = 'closed';
	} elseif ( post_type_supports( $post_type, $supports ) ) {
		$status = get_option( "default_{$option}_status" );
	} else {
		$status = 'closed';
	}

	/**
	 * Filter the default comment status for the given post type.
	 */
	return apply_filters( 'sap_get_default_comment_status' , $status, $post_type, $comment_type );
}
