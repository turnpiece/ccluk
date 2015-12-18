<?php

/**
 * Foundation Setup
 */
if ( ! function_exists( 'politics_enqueue_foundation' ) ) :

	function politics_enqueue_foundation() {
		wp_enqueue_style( 'politics-foundation-style', get_template_directory_uri() . '/app.css' );
		wp_enqueue_script( 'politics-foundation-js', get_template_directory_uri() . '/js/foundation.js', array( 'jquery' ), '5.5.2', true );
		wp_enqueue_script( 'politics-modernizr', get_template_directory_uri() . '/js/modernizr.js', array(), '2.8.3', false );
	}

endif; // politics_enqueue_foundation
add_action( 'wp_enqueue_scripts', 'politics_enqueue_foundation', 10 );

if ( ! function_exists( 'politics_admin_bar_nav' ) ) :

	// Fixes admin bar overlap
	function politics_admin_bar_nav() {
	  if ( is_admin_bar_showing() ) { ?>
	    <style>
	    .fixed, .stick { margin-top: 32px; }
	    @media screen and (max-width: 600px){
	    	.fixed { margin-top: 46px; }
	    	#wpadminbar { position: fixed !important; }
	    }
	    </style>
	  <?php }
	}

endif; // politics_admin_bar_nav
add_action('wp_head', 'politics_admin_bar_nav');
