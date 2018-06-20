<?php

/**
 * Class WP_Hummingbird_Advanced_Tools_Page
 *
 * @since 1.8
 */
class WP_Hummingbird_Advanced_Tools_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * Function triggered when the page is loaded before render any content.
	 */
	public function on_load() {
		// Init the tabs.
		$this->tabs = array(
			'main'   => __( 'General', 'wphb' ),
			'db'     => __( 'Database Cleanup', 'wphb' ),
			'system' => __( 'System Information', 'wphb' ),
		);
	}

	/**
	 * Render the template header.
	 */
	public function render_header() {
		?>
		<div class="sui-notice-top sui-notice-success sui-hidden" id="wphb-notice-advanced-tools">
			<p><?php esc_html_e( 'Settings updated', 'wphb' ); ?></p>
		</div>
		<?php

		parent::render_header();
	}

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes() {
		/**
		 * General meta box.
		 */
		$this->add_meta_box(
			'advanced/general',
			__( 'General', 'wphb' ),
			array( $this, 'advanced_general_metabox' ),
			null,
			null,
			'main'
		);

		/**
		 * Database cleanup meta boxes.
		 */
		$this->add_meta_box(
			'advanced/db',
			__( 'Database Cleanup', 'wphb' ),
			array( $this, 'advanced_db_metabox' ),
			null,
			null,
			'db',
			array(
				'box_footer_class'  => WP_Hummingbird_Utils::is_member() ? 'sui-box-footer' : 'sui-box-footer wphb-db-cleanup-no-membership',
			)
		);

		/**
		 * System information meta box.
		 */
		$this->add_meta_box(
			'advanced/system-info',
			__( 'System Information', 'wphb' ),
			array( $this, 'system_info_metabox' ),
			null,
			null,
			'system'
		);

		if ( is_multisite() && ! is_network_admin() ) {
			return;
		}

		$this->add_meta_box(
			'advanced/db-settings',
			__( 'Settings', 'wphb' ),
			null,
			null,
			null,
			'db',
			array(
				'box_content_class' => 'sui-box-body sui-upsell-items',
				'box_footer_class'  => 'sui-box-footer wphb-db-cleanup-no-membership',
			)
		);
	}

	/**
	 * *************************
	 * Advanced General page meta boxes.
	 ***************************/

	/**
	 * Advanced general meta box.
	 */
	public function advanced_general_metabox() {
		$options = WP_Hummingbird_Settings::get_settings( 'advanced' );

		$prefetch = '';
		foreach ( $options['prefetch'] as $url ) {
			$prefetch .= $url . "\r\n";
		}

		$this->view( 'advanced/general-meta-box', array(
			'query_stings' => $options['query_string'],
			'emoji'        => $options['emoji'],
			'prefetch'     => trim( $prefetch ),
		) );
	}

	/**
	 * *************************
	 * Advanced Database cleanup page meta boxes.
	 ***************************/

	/**
	 * Database cleanup meta box.
	 */
	public function advanced_db_metabox() {
		$fields = WP_Hummingbird_Module_Advanced::get_db_fields();
		$data = WP_Hummingbird_Module_Advanced::get_db_count();
		foreach ( $fields as $type => $field ) {
			$fields[ $type ]['value'] = $data->$type;
		}

		$this->view( 'advanced/db-meta-box', compact( 'fields' ) );
	}

	/**
	 * *************************
	 * System Information page meta boxes.
	 ***************************/

	/**
	 * System Information meta box.
	 */
	public function system_info_metabox() {
		$php_info    = WP_Hummingbird_Module_Advanced::get_php_info();
		$db_info     = WP_Hummingbird_Module_Advanced::get_db_info();
		$wp_info     = WP_Hummingbird_Module_Advanced::get_wp_info();
		$server_info = WP_Hummingbird_Module_Advanced::get_server_info();

		$system_info = array(
			'php'    => $php_info,
			'db'     => $db_info,
			'wp'     => $wp_info,
			'server' => $server_info,
		);

		$this->view( 'advanced/system-info-meta-box', array(
			'system_info' => $system_info,
		) );
	}

}