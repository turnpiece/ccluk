<?php

/**
 * Class WP_Hummingbird_Settings_Page
 */
class WP_Hummingbird_Settings_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * WP_Hummingbird_Settings_Page constructor.
	 *
	 * @param string $slug        The slug name to refer to this menu by (should be unique for this menu).
	 * @param string $page_title  The text to be displayed in the title tags of the page when the menu is selected.
	 * @param string $menu_title  The text to be used for the menu.
	 * @param bool   $parent      Parent or child.
	 * @param bool   $render      Use a callback function.
	 */
	public function __construct( $slug, $page_title, $menu_title, $parent = false, $render = true ) {
		parent::__construct( $slug, $page_title, $menu_title, $parent, $render );

		$this->tabs = array(
			'data' => __( 'Data & Settings', 'wphb' ),
			'main' => __( 'Accessibility', 'wphb' ),
		);
	}

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes() {
		$this->add_meta_box(
			'data',
			__( 'Data & Settings', 'wphb' ),
			array( $this, 'data_metabox' ),
			null,
			array( $this, 'accessibility_metabox_footer' ),
			'data'
		);

		$this->add_meta_box(
			'settings',
			__( 'Accessibility', 'wphb' ),
			array( $this, 'accessibility_metabox' ),
			null,
			array( $this, 'accessibility_metabox_footer' ),
			'main'
		);
	}

	/**
	 * Accessibility meta box.
	 */
	public function accessibility_metabox() {
		$args = array(
			'settings' => WP_Hummingbird_Settings::get_settings( 'settings' ),
		);

		$this->view( 'settings/accessibility-meta-box', $args );
	}

	/**
	 * Accessibility meta box footer.
	 */
	public function accessibility_metabox_footer() {
		$this->view( 'settings/accessibility-meta-box-footer', array() );
	}

	/**
	 * Data & Settings meta box.
	 *
	 * @since 2.0.0
	 */
	public function data_metabox() {
		$args = array(
			'settings' => WP_Hummingbird_Settings::get_settings( 'settings' ),
		);

		$this->view( 'settings/data-meta-box', $args );
	}

}
