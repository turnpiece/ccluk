<?php
/**
 * Plugin Name: Forminator Pro
 * Version: 1.0.5
 * Plugin URI:  https://premium.wpmudev.org/project/forminator/
 * Description: Capture user information (as detailed as you like), engage users with interactive polls that show real-time results and graphs, “no wrong answer” Facebook-style quizzes and knowledge tests.
 * Author: WPMU DEV
 * Author URI: http://premium.wpmudev.org
 * Text Domain: forminator
 * Domain Path: /languages/
 * WDP ID: 2097296
*/
/*
Copyright 2009-2018 Incsub (http://incsub.com)
Author – Cvetan Cvetanov (cvetanov)
Contributors –

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 – GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! defined( 'FORMINATOR_VERSION' ) ) {
	define( 'FORMINATOR_VERSION', '1.0.5' );
}

/**
 * Class Forminator
 *
 * Main class. Initialize plugin
 *
 * @since 1.0
 */
if ( ! class_exists( 'Forminator' ) ) {
	class Forminator {

		const DOMAIN = 'forminator';

		/**
		 * Plugin instance
		 *
		 * @var null
		 */
		private static $instance = null;

		/**
		 * @var Forminator_Core
		 */
		public $forminator;

		/**
		 * Return the plugin instance
		 *
		 * @since 1.0
		 * @return Forminator
		 */
		public static function get_instance() {
			if ( self::$instance == null ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Forminator constructor.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			$this->includes();
			$this->include_vendors();
			$this->init();
			$this->load_textdomain();
		}

		/**
		 * Load plugin files
		 *
		 * @since 1.0
		 */
		private function includes() {
			// Core files.
			/* @noinspection PhpIncludeInspection */
			include_once( forminator_plugin_dir() . 'library/class-core.php' );
		}

		/**
		 * Init the plugin
		 *
		 * @since 1.0
		 */
		private function init() {
			// Initialize plugin core
			$this->forminator = Forminator_Core::get_instance();

			/**
			 * Triggered when plugin is loaded
			 */
			do_action( 'forminator_loaded' );
		}

		/**
		 * Include Vendors
		 *
		 * @since 1.0
		 */
		private function include_vendors() {
			if ( file_exists( forminator_plugin_dir() . 'library/lib/dash-notice/wpmudev-dash-notification.php' ) ) {
				//load dashboard notice
				global $wpmudev_notices;
				$wpmudev_notices[] = array(
					'id'      => 2097296,
					'name'    => 'Forminator',
					'screens' => array(
						'toplevel_page_forminator',
						'toplevel_page_forminator-network',
						'forminator_page_forminator-cform',
						'forminator_page_forminator-cform-network',
						'forminator_page_forminator-poll',
						'forminator_page_forminator-poll-network',
						'forminator_page_forminator-quiz',
						'forminator_page_forminator-quiz-network',
						'forminator_page_forminator-settings',
						'forminator_page_forminator-settings-network',
						'forminator_page_forminator-cform-wizard',
						'forminator_page_forminator-cform-wizard-network',
						'forminator_page_forminator-cform-view',
						'forminator_page_forminator-cform-view-network',
						'forminator_page_forminator-poll-wizard',
						'forminator_page_forminator-poll-wizard-network',
						'forminator_page_forminator-poll-view',
						'forminator_page_forminator-poll-view-network',
						'forminator_page_forminator-nowrong-wizard',
						'forminator_page_forminator-nowrong-wizard-network',
						'forminator_page_forminator-knowledge-wizard',
						'forminator_page_forminator-knowledge-wizard-network',
						'forminator_page_forminator-quiz-view',
						'forminator_page_forminator-quiz-view-network',
					)
				);
				/** @noinspection PhpIncludeInspection */
				include_once( forminator_plugin_dir() . 'library/lib/dash-notice/wpmudev-dash-notification.php' );
			}
		}

		/**
		 * Load language files
		 *
		 * @since 1.0
		 */
		private function load_textdomain() {
			load_plugin_textdomain( 'forminator', false, 'forminator/languages' );
		}
	}
}

if ( ! function_exists( 'forminator' ) ) {
	function forminator() {
		return Forminator::get_instance();
	}

	/**
	 * Init the plugin and load the plugin instance
	 *
	 * @since 1.0
	 */
	add_action( 'plugins_loaded', 'forminator' );
}

if ( ! function_exists( 'forminator_plugin_url' ) ) {
	/**
	 * Return plugin URL
	 *
	 * @since 1.0
	 * @return string
	 */
	function forminator_plugin_url() {
		return trailingslashit( plugin_dir_url( __FILE__ ) );
	}
}

if ( ! function_exists( 'forminator_plugin_dir' ) ) {
	/**
	 * Return plugin path
	 *
	 * @since 1.0
	 * @return string
	 */
	function forminator_plugin_dir() {
		return trailingslashit( plugin_dir_path( __FILE__ ) );
	}
}