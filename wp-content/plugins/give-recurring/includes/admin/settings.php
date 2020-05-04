<?php
/**
 * Give Recurring Settings
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Give_Settings_Recurring_Donations' ) ) :

	/**
	 * Give_Settings_Recurring_Donations.
	 */
	class Give_Settings_Recurring_Donations extends Give_Settings_Page {

		/**
		 * Flag to check if enable saving option for setting page or not
		 *
		 * @since 1.5.4
		 * @var bool
		 */
		protected $enable_save = false;

		/**
		 * Give_Settings_Recurring_Donations constructor.
		 */
		public function __construct() {

			$this->id    = 'recurring';
			$this->label = __( 'Recurring Donations', 'give-recurring' );

			add_action( 'give_admin_field_recurring_docs', array( $this, 'render_recurring_docs_field' ) );

			add_action( 'give_admin_field_recurring_welcome', array( $this, 'render_recurring_welcome_field' ), 10, 2 );

			// Add Subscription Page settings.
			add_filter( 'give_settings_general', array( $this, 'add_subscription_page_setting' ) );
			parent::__construct();
		}

		/**
		 * Default setting tab.
		 *
		 * @param  $setting_tab
		 *
		 * @return string
		 */
		function set_default_setting_tab( $setting_tab ) {
			return 'documentation';
		}


		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			/**
			 * Filter the sections.
			 */
			return apply_filters(
				'give_recurring_get_sections',
				array(
					'documentation' => __( 'Documentation', 'give-recurring' ),
				), $this );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings() {
			$settings = array();

			switch ( give_get_current_setting_section() ) {
				case 'documentation':
					$settings = array(
						array(
							'type' => 'title',
							'id'   => 'give_recurring_documentation',
						),
						array(
							'id'   => 'give_recurring_docs',
							'type' => 'recurring_docs',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'give_recurring_documentation',
						),
					);

			}// End switch().

			/**
			 * Filter the settings.
			 *
			 * @param  array $settings
			 */
			$settings = apply_filters( 'give_recurring_get_settings', $settings );

			// Output.
			return $settings;
		}

		/**
		 * Recurring Welcome
		 *
		 * Displays a welcome message with links and other relevant information
		 *
		 * @since       1.0
		 *
		 * @return      void
		 */
		public function render_recurring_docs_field( $field ) {

			ob_start(); ?>
			<div class="recurring-welcome-wrap">

				<div class="recurring-docs-list">
					<p><?php printf( __( 'The following articles will help you quickly get started accepting recurring donations. Please read and test thoroughly prior to going live. If you have any questions or trouble along
the way, we are <a href="%s" target="_blank">here to help</a>.', 'give-recurring' ), 'http://docs.givewp.com/recurring-support-link/' ); ?></p>

					<a href="http://docs.givewp.com/addon-recurring" target="_blank"
					   class="recurring-main-link">Recurring Donations</a>
					<?php echo $this->give_recurring_docs_get_feed(); ?>

				</div>

			</div>
			<?php
			echo ob_get_clean();
		}


		/**
		 * Recurring Docs Get Feed.
		 *
		 * Gets the documentation feed for recurring.
		 *
		 * @since 1.0
		 * @return string $cache
		 */
		function give_recurring_docs_get_feed() {

			$recurring_docs_debug = false; // set to true to debug
			$cache                = get_transient( 'give_recurring_docs_feed' );

			if ( $cache === false || $recurring_docs_debug === true && WP_DEBUG === true ) {
				$feed = wp_remote_get( 'https://givewp.com/downloads/feed/recurring-docs-feed.php', array(
					'sslverify' => false,
				) );

				if ( ! is_wp_error( $feed ) ) {
					if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
						$cache = wp_remote_retrieve_body( $feed );
						set_transient( 'give_recurring_docs_feed', $cache, 3600 );
					}
				} else {
					$cache = '<div class="give-recurring-notice give-recurring-notice-issue">' . __( 'There was an error retrieving the Give documentation list from the server. Please try again later.', 'give-recurring' ) . '</div>';
				}
			}

			return $cache;
		}


		/**
		 * Adds the Subscription Page setting to the General Settings page
		 *
		 * @param array $settings Admin setting.
		 *
		 * @access       public
		 * @since        1.4
		 * @return      array
		 */
		public function add_subscription_page_setting( $settings ) {
			$give_subscription_page_setting = array(
				array(
					'name'       => __( 'Subscriptions Page', 'give-recurring' ),
					'desc'       => __( 'This is the page donors can access to manage their subscriptions. The <code>[give_subscriptions]</code> shortcode should be on this page.', 'give-recurring' ),
					'id'         => 'subscriptions_page',
					'type'       => 'select',
					'class'      => 'give-select give-select-chosen',
					'options'    => give_cmb2_get_post_options( array(
						'post_type'   => 'page',
						'numberposts' => 30,
					) ),
					'attributes' => array(
						'data-search-type' => 'pages',
						'data-placeholder' => esc_html__( 'Choose a page', 'give-recurring' ),
					)
				),
			);

			return give_settings_array_insert(
				$settings,
				'base_country',
				$give_subscription_page_setting
			);
		}
	}

	return new Give_Settings_Recurring_Donations();

endif;
