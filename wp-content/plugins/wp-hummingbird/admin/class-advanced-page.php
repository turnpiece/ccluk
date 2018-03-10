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
			//'system' => __( 'System Information', 'wphb' ),
		);
	}

	/**
	 * Render the template header.
	 */
	public function render_header() {
		?>
		<div class="wphb-notice hidden" id="wphb-notice-advanced-tools">
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
			'db'
		);

		$this->add_meta_box(
			'advanced/db-settings',
			__( 'Settings', 'wphb' ),
			array( $this, 'db_settings_metabox'),
			null,
			null,
			'db',
			array(
				'box_content_class' => 'box-content no-padding',
				'box_footer_class'  => WP_Hummingbird_Utils::is_member() ? 'box-footer' : 'box-footer wphb-db-cleanup-no-membership',
			)
		);
	}

	/**********************
	 *
	 * Advanced General page meta boxes.
	 *
	 *********************/

	/**
	 * Advanced general meta box.
	 */
	public function advanced_general_metabox() {
		$options = WP_Hummingbird_Settings::get_settings( 'advanced' );

		$prefetch = '';
		foreach( $options['prefetch'] as $url ) {
			$prefetch .= $url . "\r\n";
		}

		$this->view( 'advanced/general-meta-box', array(
			'query_stings' => $options['query_string'],
			'emoji'        => $options['emoji'],
			'prefetch'     => trim( $prefetch ),
		) );
	}

	/**********************
	 *
	 * Advanced Database cleanup page meta boxes.
	 *
	 *********************/

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
	 * Database cleanup settings meta box.
	 */
	public function db_settings_metabox() {
		/* @var WP_Hummingbird_Module_Advanced $adv_module */
		$adv_module = WP_Hummingbird_Utils::get_module( 'advanced' );
		$options = $adv_module->get_options();

		$fields = WP_Hummingbird_Module_Advanced::get_db_fields();
		foreach ( $fields as $type => $field ) {
			$fields[ $type ]['checked'] = isset( $options['db_tables'][ $type ] ) ? $options['db_tables'][ $type ] : false;
		}

		$this->view( 'advanced/db-settings-meta-box', array(
			'fields'    => $fields,
			'schedule'  => $options['db_cleanups'],
			'frequency' => $options['db_frequency'],
		));
	}

}