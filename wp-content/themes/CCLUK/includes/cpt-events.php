<?php
function cpt_events(){
	global $options;
            
         if ( $options ) {
  $url_rewrite = $options['theme_events_item_url'];
  if( !$url_rewrite ) { $url_rewrite = 'events'; }
 } else {
  $url_rewrite = 'events';
 }

	register_post_type('post_events',
		array(
			'labels' => array(
				'name' => 'Events',
				'singular_name' => 'Events Item',
				'add_new' => 'Add New Event',
				'add_new_item' => 'Add New Event Item',
				'edit' => 'Edit',
				'edit_item' => 'Edit Event Item',
				'new_item' => 'New Event Item',
				'view' => 'View',
				'view_item' => 'View Events Item',
				'search_items' => 'Search Events Items',
				'not_found' => 'No Event items found',
				'not_found_in_trash' => 'No Event items found in Trash',
				'parent' => 'Parent Events Item'
			),
			'description' => 'Easily lets you create some beautiful events.',
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
function tax_events() {
	global $options;

         if ( $options ) {  
		$url_rewrite = $options['theme_events_item_type_url'];
		if( !$url_rewrite ) { $url_rewrite = 'events-category'; }
	} else {
		$url_rewrite = 'events-category';
	}

        
	register_taxonomy('events_item_types', 'post_events', 
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

add_action('init', 'cpt_events');
add_action('init', 'tax_events');