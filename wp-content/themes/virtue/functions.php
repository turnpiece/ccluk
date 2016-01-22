<?php
/*-----------------------------------------------------------------------------------*/
/* Include Theme Functions */
/*-----------------------------------------------------------------------------------*/

function virtue_lang_setup() {
	load_theme_textdomain('virtue', get_template_directory() . '/languages');
}
add_action( 'after_setup_theme', 'virtue_lang_setup' );
require_once locate_template('/themeoptions/framework.php');        // Options framework
require_once locate_template('/themeoptions/options.php');     		// Options framework
require_once locate_template('/themeoptions/options/virtue_extension.php'); // Options framework extension
require_once locate_template('/lib/utils.php');           			// Utility functions
require_once locate_template('/lib/init.php');            			// Initial theme setup and constants
require_once locate_template('/lib/sidebar.php');         			// Sidebar class
require_once locate_template('/lib/config.php');          			// Configuration
require_once locate_template('/lib/cleanup.php');        			// Cleanup
require_once locate_template('/lib/nav.php');            			// Custom nav modifications
require_once locate_template('/lib/metaboxes.php');     			// Custom metaboxes
require_once locate_template('/lib/comments.php');        			// Custom comments modifications
require_once locate_template('/lib/widgets.php');         			// Sidebars and widgets
require_once locate_template('/lib/aq_resizer.php');      			// Resize on the fly
require_once locate_template('/lib/scripts.php');        			// Scripts and stylesheets
require_once locate_template('/lib/custom.php');          			// Custom functions
require_once locate_template('/lib/admin_scripts.php');          	// Icon functions
require_once locate_template('/lib/authorbox.php');         		// Author box
require_once locate_template('/lib/custom-woocommerce.php'); 		// Woocommerce functions
require_once locate_template('/lib/virtuetoolkit-activate.php'); 	// Plugin Activation
require_once locate_template('/lib/custom-css.php'); 			    // Fontend Custom CSS
