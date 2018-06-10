<?php

class Hustle_SShare_Model extends Hustle_Module_Model {


	static function instance() {
		return new self;
	}

	static function get_types() {
		return array(
			'floating_social',
			'widget',
			'shortcode',
		);
	}

	function get_sshare_content() {
		return new Hustle_SShare_Content( $this->get_settings_meta( self::KEY_CONTENT, '{}', true ), $this );
	}

	function get_sshare_design() {
		return new Hustle_SShare_Design( $this->get_settings_meta( self::KEY_DESIGN, '{}', true ), $this );
	}

	function get_sshare_display_settings() {
		return new Hustle_SShare_Settings( $this->get_settings_meta( self::KEY_SETTINGS, '{}', true ), $this );
	}

	function get_sshare_display_types() {
		return new Hustle_SShare_Types( $this->get_settings_meta( self::KEY_TYPES, '{}', true ), $this );
	}

	function log_share_stats( $page_id ) {
		$ss_col_instance = Hustle_Module_Collection::instance();
		$ss_col_instance->update_page_share($page_id);
	}

	function is_sshare_type_active($type) {
		$settings = $this->get_sshare_display_settings()->to_array();
		if ( isset( $settings[ $type . '_enabled' ] ) && $settings[ $type . '_enabled' ] == 'true' ) {
			return true;
		} else {
			return false;
		}
	}

}