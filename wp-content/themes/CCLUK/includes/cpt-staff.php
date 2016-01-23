<?php
function cpt_staff(){
	global $options;
        
	    if ( $options ) {
  $url_rewrite = $options['theme_staff_item_url'];
  if( !$url_rewrite ) { $url_rewrite = 'staff'; }
 } else {
  $url_rewrite = 'staff';
 }
        
	register_post_type('post_staff',
		array(
			'labels' => array(
				'name' => 'Staffs',
				'singular_name' => 'Staff Item',
				'add_new' => 'Add New Staff',
				'add_new_item' => 'Add New Staff Item',
				'edit' => 'Edit',
				'edit_item' => 'Edit Staff Item',
				'new_item' => 'New Staff Item',
				'view' => 'View',
				'view_item' => 'View Staffs Item',
				'search_items' => 'Search Staffs Items',
				'not_found' => 'No Staff items found',
				'not_found_in_trash' => 'No Staff items found in Trash',
				'parent' => 'Parent Staffs Item'
			),
			'description' => 'Easily lets you create some beautiful staff.',
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
function tax_staff() {
	global $options;
        
        
	 if ( $options ) {  
		$url_rewrite = $options['theme_staff_item_type_url'];
		if( !$url_rewrite ) { $url_rewrite = 'staff-category'; }
	} else {
		$url_rewrite = 'staff-category';
	}
        
	register_taxonomy('staff_item_types', 'post_staff', 
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

add_action('init', 'cpt_staff');
add_action('init', 'tax_staff');