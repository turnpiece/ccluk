<?php

class Agm_AdminHelp {

	const SETTINGS_SCREEN_ID = 'settings_page_agm_google_maps';

	private $_help;

	static public function instance() {
		static $Instance = null;

		if ( ! $Instance instanceof Agm_AdminHelp ) {
			$Instance = new Agm_AdminHelp();
			$Instance->_add_hooks();
		}
		return $Instance;
	}

	private function __construct() {
		if ( ! class_exists( 'WpmuDev_ContextualHelp' ) ) {
			require_once AGM_INC_DIR . 'external/class_wd_contextual_help.php';
		}
		$this->_help = new WpmuDev_ContextualHelp();
	}

	private function _get_default_tabs() {
		return array(
			array(
				'id'      => 'agm_google_maps-options',
				'title'   => __( 'Options', AGM_LANG ),
				'content' => '' .
					'<p>' . __(
						'This is where you can set up your Google Maps ' .
						'default settings.', AGM_LANG
					) . '</p>'
			),
			array(
				'id'      => 'agm_google_maps-custom_fields',
				'title'   => __( 'Custom fields', AGM_LANG ),
				'content' => '' .
					'<p>' . __(
						'This is where you can set up auto-creation of ' .
						'new Google Maps, triggered by your existing ' .
						'location custom fields.', AGM_LANG
					) . '</p>'
			),
			array(
				'id'      => 'agm_google_maps-addons',
				'title'   => __( 'Add-ons', AGM_LANG ),
				'content' => '' .
					'<p>' . __(
						'These are the optional additions for your Google' .
						'Maps. Activate or deactivate them as needed.', AGM_LANG
					) . '</p>'
			),

		);
	}

	private function _get_default_sidebar() {
		return '' .
			'<h4>' . __( 'Google Maps', AGM_LANG ) . '</h4>' .
			'<ul>' .
				'<li>' .
					'<a href="http://premium.wpmudev.org/project/wordpress-google-maps-plugin/" target="_blank">' .
						__( 'Project page', AGM_LANG ) .
					'</a>' .
				'</li>' .
				'<li>' .
					'<a href="http://premium.wpmudev.org/project/wordpress-google-maps-plugin/#usage" target="_blank">' .
						__( 'Installation and instructions page', AGM_LANG ) .
					'</a>' .
				'</li>' .
				'<li>' .
					'<a href="http://premium.wpmudev.org/forums/tags/google-maps" target="_blank">' .
						__( 'Support forum', AGM_LANG ) .
					'</a>' .
				'</li>' .
			'</ul>' .
		'';
	}

	private function _add_hooks() {
		add_action(
			'admin_init',
			array( $this, 'initialize' )
		);
	}

	public function initialize() {
		$this->_help->add_page(
			self::SETTINGS_SCREEN_ID,
			$this->_get_default_tabs(),
			$this->_get_default_sidebar()
		);
		$this->_help->initialize();
	}

	public function add_tab( $id, $title, $content ) {
		$this->_help->add_tab(
			self::SETTINGS_SCREEN_ID,
			array(
				'id'      => $id,
				'title'   => $title,
				'content' => $content,
			)
		);
	}
};

Agm_AdminHelp::instance();