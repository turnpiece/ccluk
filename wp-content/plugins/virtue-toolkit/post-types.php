<?php
// Custom post types
function kad_portfolio_post_init() {
  $portfoliolabels = array(
    'name' =>  __('Portfolio', 'virtue-toolkit'),
    'singular_name' => __('Portfolio Item', 'virtue-toolkit'),
    'add_new' => __('Add New', 'virtue-toolkit'),
    'add_new_item' => __('Add New Portfolio Item', 'virtue-toolkit'),
    'edit_item' => __('Edit Portfolio Item', 'virtue-toolkit'),
    'new_item' => __('New Portfolio Item', 'virtue-toolkit'),
    'all_items' => __('All Portfolio', 'virtue-toolkit'),
    'view_item' => __('View Portfolio Item', 'virtue-toolkit'),
    'search_items' => __('Search Portfolio', 'virtue-toolkit'),
    'not_found' =>  __('No Portfolio Item found', 'virtue-toolkit'),
    'not_found_in_trash' => __('No Portfolio Items found in Trash', 'virtue-toolkit'),
    'parent_item_colon' => '',
    'menu_name' => __('Portfolio', 'virtue-toolkit')
  );

  $portargs = array(
    'labels' => $portfoliolabels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    //'rewrite'  => array( 'slug' => 'portfolio', 'feeds' => true),
    'has_archive' => false, 
    'capability_type' => 'post', 
    'hierarchical' => false,
    'menu_position' => 8,
    'menu_icon' =>  'dashicons-format-gallery',
    'supports' => array( 'title', 'editor', 'excerpt', 'author', 'page-attributes', 'thumbnail', 'custom-fields', 'comments' )
  ); 
  // Initialize Taxonomy Labels
	$worklabels = array(
		'name' => __( 'Portfolio Type', 'virtue-toolkit' ),
		'singular_name' => __( 'Type', 'virtue-toolkit' ),
		'search_items' =>  __( 'Search Type', 'virtue-toolkit' ),
		'all_items' => __( 'All Type', 'virtue-toolkit' ),
		'parent_item' => __( 'Parent Type', 'virtue-toolkit' ),
		'parent_item_colon' => __( 'Parent Type:', 'virtue-toolkit' ),
		'edit_item' => __( 'Edit Type', 'virtue-toolkit' ),
		'update_item' => __( 'Update Type', 'virtue-toolkit' ),
		'add_new_item' => __( 'Add New Type', 'virtue-toolkit' ),
		'new_item_name' => __( 'New Type Name', 'virtue-toolkit' ),
	);
    $portfolio_type_slug = apply_filters('kadence_portfolio_type_slug', 'portfolio-type');
	// Register Custom Taxonomy
	register_taxonomy('portfolio-type',array('portfolio'), array(
		'hierarchical' => true, // define whether to use a system like tags or categories
		'labels' => $worklabels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite'  => array( 'slug' => $portfolio_type_slug )
	));

  register_post_type( 'portfolio', $portargs );
}
add_action( 'init', 'kad_portfolio_post_init', 1 );
function kad_portfolio_permalink_init(){
global $wp_rewrite;
$port_rewrite = apply_filters('kadence_portfolio_permalink_slug', 'portfolio');
$portfolio_structure = '/'.$port_rewrite.'/%portfolio%';
$wp_rewrite->add_rewrite_tag("%portfolio%", '([^/]+)', "portfolio=");
$wp_rewrite->add_permastruct('portfolio', $portfolio_structure, false);
}
add_action( 'init', 'kad_portfolio_permalink_init', 2 );

// Add filter to plugin init function
add_filter('post_type_link', 'kad_portfolio_permalink', 10, 3);   

function kad_portfolio_permalink($permalink, $post_id, $leavename) {
    $post = get_post($post_id);
    $rewritecode = array(
        '%year%',
        '%monthnum%',
        '%day%',
        '%hour%',
        '%minute%',
        '%second%',
        $leavename? '' : '%postname%',
        '%post_id%',
        '%category%',
        '%author%',
        $leavename? '' : '%pagename%',
    );
 
    if ( '' != $permalink && !in_array($post->post_status, array('draft', 'pending', 'auto-draft')) ) {
        $unixtime = strtotime($post->post_date);
     
        $category = '';
        if ( strpos($permalink, '%category%') !== false ) {
            $cats = wp_get_post_terms($post->ID, 'portfolio-type', array( 'orderby' => 'parent', 'order' => 'DESC' ));
            if ( $cats ) {
                //usort($cats, '_usort_terms_by_ID'); // order by ID
                $category = $cats[0]->slug;
            }
            // show default category in permalinks, without
            // having to assign it explicitly
            if ( empty($category) ) {
                $category = 'portfolio-category';
            }
        }
     
        $author = '';
        if ( strpos($permalink, '%author%') !== false ) {
            $authordata = get_userdata($post->post_author);
            $author = $authordata->user_nicename;
        }
     
        $date = explode(" ",date('Y m d H i s', $unixtime));
        $rewritereplace =
        array(
            $date[0],
            $date[1],
            $date[2],
            $date[3],
            $date[4],
            $date[5],
            $post->post_name,
            $post->ID,
            $category,
            $author,
            $post->post_name,
        );
        $permalink = str_replace($rewritecode, $rewritereplace, $permalink);
    } else { // if they're not using the fancy permalink option
    }
    return $permalink;
}

