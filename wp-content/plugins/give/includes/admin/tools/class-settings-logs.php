<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Logs
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Logs' ) ) :

	/**
	 * Give_Settings_Logs.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_Logs extends Give_Settings_Page {
		/**
		 * Flag to check if enable saving option for setting page or not
		 *
		 * @since 1.8.17
		 * @var bool
		 */
		protected $enable_save = false;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'logs';
			$this->label = __( 'Logs', 'give' );

			$this->default_tab = 'sales';

			parent::__construct();

		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			// Get settings.
			$settings = apply_filters( 'give_settings_logs', array(
				array(
					'id'         => 'give_tools_logs',
					'type'       => 'title',
					'table_html' => false,
				),
				array(
					'id'   => 'logs',
					'name' => __( 'Log', 'give' ),
					'type' => 'logs',

				),
				array(
					'id'         => 'give_tools_logs',
					'type'       => 'sectionend',
					'table_html' => false,
				),
			) );

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters( 'give_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Get sections.
		 *
		 * @since 1.8
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'sales'          => __( 'Donations', 'give' ),
				'gateway_errors' => __( 'Payment Errors', 'give' ),
				'api_requests'   => __( 'API Requests', 'give' ),
				'updates'   => __( 'Updates', 'give' ),
			);

			$sections = apply_filters( 'give_log_views', $sections );

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}
	}

endif;

return new Give_Settings_Logs();
