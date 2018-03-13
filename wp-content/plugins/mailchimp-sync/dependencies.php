<?php

// Check for MailChimp for WordPress v3.1+ (because of use of Queue class)
if( defined( 'MC4WP_VERSION' ) && version_compare( MC4WP_VERSION, '3.1', '>=' ) ) {
	return true;
}

add_action( 'admin_notices', function() {

	// only show to user with caps
	if( ! current_user_can( 'install_plugins' ) ) {
		return;
	}

	add_thickbox();
	$url = network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=mailchimp-for-wp&TB_iframe=true&width=600&height=550' );

	?>
	<div class="notice notice-warning is-dismissible">
		<p><?php printf( __( 'Please install or update <a href="%s" class="thickbox">%s</a> %s in order to use %s.', 'mailchimp-sync' ), $url, '<strong>MailChimp for WordPress</strong>', '(version 3.1 or higher)', 'MailChimp User Sync' ); ?></p>
	</div>
<?php
} );

// Tell plugin not to proceed
return false;
