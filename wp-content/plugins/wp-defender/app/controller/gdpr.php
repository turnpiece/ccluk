<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Controller;

use WP_Defender\Controller;

class GDPR extends Controller {
	public function __construct() {
		$this->add_filter( 'wp_get_default_privacy_policy_content', 'addPolicy' );
	}

	public function addPolicy( $content ) {
		$pluginName = wp_defender()->isFree ? __( "Defender", wp_defender()->domain ) : __( "Defender Pro", wp_defender()->domain );
		$content    .= '<h3>' . sprintf( __( 'Plugin: %s', wp_defender()->domain ), $pluginName ) . '</h3>';
		$content    .= '<p><strong>' . __( "Third parties", wp_defender()->domain ) . '</strong></p>';
		$content    .= '<p>' . __( "This site may be using WPMU DEV third-party cloud storage to store backups of its audit logs where personal information is collected.", wp_defender()->domain ) . '</p>';
		$content    .= '<p><strong>' . __( "Additional data", wp_defender()->domain ) . '</strong></p>';
		$content    .= '<p>' . __( "This site creates and stores an activity log that capture the IP address, username, email address and tracks user activity (like when a user makes a comment). Information will be stored locally for 30 days and remotely for 1 year. Information on remote logs cannot be cleared for security purposes.", wp_defender()->domain ) . '</p>';
		return $content;
	}
}