<?php

//* Enqueue scripts for after entry fadein effect
add_action( 'genesis_footer', 'ns_single_scripts', 7 );
function ns_single_scripts() {

	wp_enqueue_script( 'ns-fadein', get_bloginfo( 'stylesheet_directory' ) . '/js/fadein.js', array( 'jquery' ), '1.0.0' );
	wp_enqueue_script( 'ns-waypoints', get_bloginfo( 'stylesheet_directory' ) . '/js/jquery.waypoints.min.js', array( 'jquery' ), '1.0.0' );

}

//* Add featured image before entry header
add_action( 'genesis_entry_header', 'ns_single_featured_image', 1 );
function ns_single_featured_image() {

	if ( ! genesis_get_option( 'content_archive_thumbnail' ) ) {
		return;
	}
	
	if ( $image = genesis_get_image( array( 'format' => 'url', 'size' => 'ns-single' ) ) ) {
		printf( '<div class="featured-image"><img src="%s" alt="%s" /></div>', $image, the_title_attribute( 'echo=0' ) );
	}
	
}

//* Add after entry widget area
add_action( 'genesis_after_entry', 'ns_after_entry_widget', 9 );
function ns_after_entry_widget() {

	genesis_widget_area( 'after-entry', array(
		'before' => '<div class="after-entry fadein hidden">',
		'after'  => '</div>',
	) );

}

//* Run the Genesis function
genesis();
