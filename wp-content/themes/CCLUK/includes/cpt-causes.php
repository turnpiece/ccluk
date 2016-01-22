<?php
function cpt_causes(){
	global $options;
        
        
         if ( $options ) {
  $url_rewrite = $options['theme_causes_item_url'];
  if( !$url_rewrite ) { $url_rewrite = 'causes'; }
 } else {
  $url_rewrite = 'causes';
 }

	register_post_type('post_causes',
		array(
			'labels' => array(
				'name' => 'Causes',
				'singular_name' => 'Causes Item',
				'add_new' => 'Add New Cause',
				'add_new_item' => 'Add New Cause Item',
				'edit' => 'Edit',
				'edit_item' => 'Edit Cause Item',
				'new_item' => 'New Cause Item',
				'view' => 'View',
				'view_item' => 'View Causes Item',
				'search_items' => 'Search Causes Items',
				'not_found' => 'No Causes items found',
				'not_found_in_trash' => 'No Causes items found in Trash',
				'parent' => 'Parent Causes Item'
			),
			'description' => 'Easily lets you create some beautiful causes.',
			'public' => true,
			'show_ui' => true, 
			'_builtin' => false,
			'capability_type' => 'page',
			'hierarchical' => true,
			'rewrite' => array('slug' => $url_rewrite),
			'supports' => array('title', 'editor', 'thumbnail', 'comments'),
		)
	); 
	flush_rewrite_rules();
}

function tax_causes() {
	global $options;
        
       if ( $options ) {  
		$url_rewrite = $options['theme_causes_item_type_url'];
		if( !$url_rewrite ) { $url_rewrite = 'causes-category'; }
	} else {
		$url_rewrite = 'causes-category';
	}

	register_taxonomy('causes_item_types', 'post_causes', 
         
		array( 
			'hierarchical' => true, 
			'labels' => array(
				  'name' => 'Item Category',
				  'singular_name' => 'Item Categories',
				  'search_items' =>  'Search Categories',
				  'popular_items' => 'Popular Categories',
				  'all_items' => 'All Categories',
				  'parent_item' => 'Parent Categories',
				  'parent_item_colon' => 'Parent Category:',
				  'edit_item' => 'Edit Category',
				  'update_item' => 'Update Category',
				  'add_new_item' => 'Add New Category',
				  'new_item_name' => 'New Category Name'
			),
			'show_ui' => true,
			'query_var' => true, 
			'rewrite' => array('slug' => $url_rewrite)
		) 
	); 
	flush_rewrite_rules();	
}

add_action('init', 'cpt_causes');
add_action('init', 'tax_causes');