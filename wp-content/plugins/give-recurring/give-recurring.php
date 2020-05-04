<?php
/**
 * Plugin Name: Give - Recurring Donations
 * Plugin URI:  https://givewp.com/addons/recurring-donations/
 * Description: Adds support for recurring (subscription) donations to the GiveWP donation plugin.
 * Version:     1.10.1
 * Author:      GiveWP
 * Author URI:  https://givewp.com
 * Text Domain: give-recurring
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
if ( ! defined( 'GIVE_RECURRING_VERSION' ) ) {
	define( 'GIVE_RECURRING_VERSION', '1.10.1' );
}
if ( ! defined( 'GIVE_RECURRING_MIN_GIVE_VERSION' ) ) {
	define( 'GIVE_RECURRING_MIN_GIVE_VERSION', '2.5.5' );
}
if ( ! defined( 'GIVE_RECURRING_PLUGIN_FILE' ) ) {
	define( 'GIVE_RECURRING_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'GIVE_RECURRING_PLUGIN_DIR' ) ) {
	define( 'GIVE_RECURRING_PLUGIN_DIR', plugin_dir_path( GIVE_RECURRING_PLUGIN_FILE ) );
}
if ( ! defined( 'GIVE_RECURRING_PLUGIN_URL' ) ) {
	define( 'GIVE_RECURRING_PLUGIN_URL', plugin_dir_url( GIVE_RECURRING_PLUGIN_FILE ) );
}
if ( ! defined( 'GIVE_RECURRING_PLUGIN_BASENAME' ) ) {
	define( 'GIVE_RECURRING_PLUGIN_BASENAME', plugin_basename( GIVE_RECURRING_PLUGIN_FILE ) );
}

// Activation and install functionality.
if ( file_exists( GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/give-recurring-install.php' ) ) {
	require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/give-recurring-install.php';
}

/**
 * Class Give_Recurring
 */
final class Give_Recurring {

	/** Singleton *************************************************************/

	/**
	 * Plugin Path.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var string
	 */
	static $plugin_path;

	/**
	 * Plugin Directory.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var string
	 */
	static $plugin_dir;

	/**
	 * Gateways.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var array
	 */
	public static $gateways = array();

	/**
	 * Give_Recurring instance
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var Give_Recurring The one true Give_Recurring
	 */
	private static $instance;

	/**
	 * Give_Recurring_Emails Object
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var Give_Recurring_Emails
	 */
	public $emails;

	/**
	 * Give_Recurring_Cron Object
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var Give_Recurring_Cron
	 */
	public $cron;

	/**
	 * Notices (array).
	 *
	 * @since 1.2.3
	 *
	 * @var array
	 */
	public $notices = array();

	/**
	 * Gateway Factory instance
	 *
	 * @var Give_Recurring_Gateway_Factory
	 */
	public $gateway_factory;

	/**
	 * Subscription Synchronizer
	 *
	 * @var Give_Subscription_Synchronizer
	 */
	public $synchronizer;

	/**
	 * @var Give_Subscriptions_API
	 */
	public $api;

	/**
	 * Give subscription meta Object
	 *
	 * @var Give_Recurring_DB_Subscription_Meta $subscription_meta
	 */
	public $subscription_meta;

	/**
	 * Main Give_Recurring Instance
	 *
	 * Insures that only one instance of Give_Recurring exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since     1.0
	 * @access    public
	 *
	 * @staticvar array $instance
	 *
	 * @return    Give_Recurring
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Give_Recurring();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Constructor -- prevent new instances
	 *
	 * @since 1.4
	 */
	private function __construct() {
		// You shall not pass.
	}

	/**
	 * Initialize Recurring.
	 *
	 * Sets up globals, loads text domain, loads includes, initializes actions
	 * and filters, starts recurring class.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function init() {

		self::includes_global();

		if ( is_admin() ) {
			self::includes_admin();
		}

		self::load_textdomain();
		self::actions();
		self::filters();

		self::$instance->api               = new Give_Subscriptions_API();
		self::$instance->emails            = new Give_Recurring_Emails();
		self::$instance->cron              = new Give_Recurring_Cron();
		self::$instance->gateway_factory   = new Give_Recurring_Gateway_Factory();
		self::$instance->synchronizer      = new Give_Subscription_Synchronizer();
		self::$instance->subscription_meta = new Give_Recurring_DB_Subscription_Meta();

		self::$gateways = apply_filters(
			'give_recurring_available_gateways',
			array(
				'authorize'         => 'Give_Recurring_Authorize',
				'authorize_echeck'  => 'Give_Recurring_Authorize_eCheck',
				'manual'            => 'Give_Recurring_Manual_Payments',
				'paypal'            => 'Give_Recurring_PayPal',
				'paypalpro'         => 'Give_Recurring_PayPal_Website_Payments_Pro',
				'paypalpro_rest'    => 'Give_Recurring_PayPal_Pro_REST',
				'paypalpro_payflow' => 'Give_Recurring_PayPal_Pro_Payflow',
				'stripe'            => 'Give_Recurring_Stripe',
				'stripe_checkout'   => 'Give_Recurring_Stripe_Checkout',
				'stripe_ach'        => 'Give_Recurring_Stripe_ACH',
				'stripe_ideal'      => 'Give_Recurring_Stripe_Ideal',
				'stripe_google_pay' => 'Give_Recurring_Stripe_Google_Pay',
				'stripe_apple_pay'  => 'Give_Recurring_Stripe_Apple_Pay',
				'stripe_sepa'       => 'Give_Recurring_Stripe_Sepa',
				'stripe_becs'       => 'Give_Recurring_Stripe_Becs',
				'razorpay'          => 'Give_Recurring_RazorPay',
			)
		);
	}

	/**
	 * Load global files.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @return bool
	 */
	private function includes_global() {

		// We need Give to continue.
		if ( ! class_exists( 'Give' ) ) {
			return false;
		}

		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-subscriptions-db.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-db-subscription-meta.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-cache.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-subscription.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-subscriptions-api.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-post-types.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-shortcodes.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-subscriber.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-template.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-helpers.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-functions.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-scripts.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-emails.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-renewals.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-expirations.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-cron.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-gateway-factory.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/synchronizer/class-subscription-synchronizer.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/give-recurring-ajax.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/deprecated/deprecated-classes.php';

		// Load Payment Gateway files for recurring support.
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-gateway.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-manual.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-paypal.php';

		// Load PayPal Pro files for recurring support.
		if ( defined( 'GIVEPP_VERSION' ) ) {
			require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-paypalpro.php';
			require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-paypalpro_payflow.php';
			require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-paypalpro_rest.php';
		}

		// Load Authorize.NET files for recurring support.
		if ( defined( 'GIVE_AUTHORIZE_VERSION' ) ) {
			require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-authorize.php';
			require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-authorize_echeck.php';
		}

		// Load RazorPay files for recurring support.
		if (
            defined( 'GIVE_RAZORPAY_VERSION' ) &&
            version_compare( '1.3.0', GIVE_RAZORPAY_VERSION, '<=' )
        ) {
			require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/razorpay/class-give-recurring-razorpay.php';
			require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/razorpay/class-give-recurring-razorpay-webhooks.php';
		}

		// Add Stripe support for Recurring.
		$this->include_stripe_files();

	}

	/**
	 * This function is used to include Stripe files for recurring support.
     *
     * @since  1.9.4
     * @access public
     *
     * @return void
	 */
	public function include_stripe_files() {

	    // Bailout, if all the Stripe payment methods is inactive.
		if (
			function_exists( 'give_stripe_is_any_payment_method_active' ) &&
			! give_stripe_is_any_payment_method_active()
		) {
			return;
		}

        // Load Stripe files for recurring support.
        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/stripe/give-recurring-stripe-helpers.php';
        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/stripe/class-give-recurring-stripe-gateway.php';
        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/stripe/class-give-recurring-stripe-webhooks.php';
        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/stripe/class-give-recurring-stripe-subscription.php';

        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-stripe.php';
        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-stripe_checkout.php';
        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-stripe_ach.php';
        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-stripe_apple_pay.php';
        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-stripe_google_pay.php';
        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-stripe_ideal.php';
        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-stripe_sepa.php';
        require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/gateways/give-recurring-stripe_becs.php';

    }

	/**
	 * Load admin files.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @return bool
	 */
	private function includes_admin() {

		// We need Give to continue.
		if ( ! class_exists( 'Give' ) ) {
			return false;
		}

		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/give-recurring-filters.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/give-recurring-activation.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/give-recurring-donors.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/export-donation.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/class-subscriptions-list-table.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/class-admin-notices.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/class-shortcode-generator.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/subscriptions-details.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/subscriptions.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/metabox.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/reset-tool.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/class-donor-subscription-details.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/tools/export-actions.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-exports.php';
		require_once GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/tools/class-give-export-subscriptions-history.php';

	}

	/**
	 * Loads the plugin language files.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @return void
	 */
	private function load_textdomain() {

		// Set filter for plugin's languages directory.
		$give_lang_dir = dirname( GIVE_RECURRING_PLUGIN_BASENAME ) . '/languages/';
		$give_lang_dir = apply_filters( 'give_recurring_languages_directory', $give_lang_dir );

		// Traditional WordPress plugin locale filter.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'give-recurring' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'give-recurring', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $give_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/give-recurring/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/give-recurring folder.
			load_textdomain( 'give-recurring', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/give-recurring/languages/ folder.
			load_textdomain( 'give-recurring', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'give-recurring', false, $give_lang_dir );
		}

	}

	/**
	 * Add our actions.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @return void
	 */
	private function actions() {

		// Environment checks.
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_menu', array( $this, 'subscriptions_list' ), 10 );

		// Register our post status.
		add_action( 'wp_loaded', array( $this, 'register_post_statuses' ) );

		// Tell Give to include subscription payments in Payment History.
		add_action( 'give_pre_get_payments', array( $this, 'enable_child_payments' ), 100 );

	}


	/**
	 * Add Recurring filters.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @return void
	 */
	private function filters() {
		// Register settings.
		add_filter( 'give-settings_get_settings_pages', array( $this, 'register_settings' ) );

		// Register our new payment statuses.
		add_filter( 'give_payment_statuses', array( $this, 'register_recurring_statuses' ) );

		// Set the payment stati.
		add_filter( 'give_is_payment_complete', array( $this, 'is_payment_complete' ), 10, 3 );

		// Include subscription payments in the calculation of earnings.
		add_filter( 'give_get_total_earnings_args', array( $this, 'earnings_query' ) );
		add_filter( 'give_get_earnings_by_date_args', array( $this, 'earnings_query' ) );
		add_filter( 'give_get_sales_by_date_args', array( $this, 'earnings_query' ) );
		add_filter( 'give_stats_earnings_args', array( $this, 'earnings_query' ) );
		add_filter( 'give_get_sales_by_date_args', array( $this, 'earnings_query' ) );
		add_filter( 'give_get_users_donations_args', array( $this, 'has_donated_query' ) );

		// Allow give_subscription to run a refund to the gateways.
		add_filter( 'give_should_process_refunded', array( $this, 'maybe_process_refund' ), 10, 2 );

		// Deleted renewals decreases donor stats.
		add_filter( 'give_decrease_donor_statuses', array( $this, 'reduce_stats_when_renewal_deleted' ) );

		// Allow PDF Invoices to be downloaded for subscription payments.
		add_filter( 'give_pdfi_is_invoice_link_allowed', array( $this, 'is_invoice_allowed' ), 10, 2 );

		// Require registration or login
		add_filter( 'give_show_register_form', array( $this, 'show_register_form' ), 1, 2 );
		add_filter( 'give_register_account_fields_before', array( $this, 'give_add_logged_meta_filter' ), 1, 2 );
		add_filter( 'give_register_account_fields_after', array( $this, 'give_remove_logged_meta_filter' ), 1, 2 );

		// Modify the gateway data before it goes to the gateway.
		add_filter( 'give_donation_data_before_gateway', array( $this, 'modify_donation_data' ), 10, 2 );

		// Register emails and email tags.
		add_filter( 'give_email_notifications', array( $this, 'register_emails' ) );
		add_filter( 'give_email_tags', array( $this, 'register_email_tags' ) );
		add_filter( 'give_email_preview_template_tags', array( $this, 'give_recurring_email_preview_template_tags' ) );
	}

	/**
	 * Register settings.
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	public function register_settings( $settings ) {
		$settings[] = include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/settings.php';

		return $settings;
	}


	/**
	 * Allow this class and other classes to add notices.
	 *
	 * @since 1.2.3
	 *
	 * @param $slug
	 * @param $class
	 * @param $message
	 */
	public function add_admin_notice( $slug, $class, $message ) {
		$this->notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message,
		);
	}

	/**
	 * Handles the displaying of any notices in the admin area.
	 *
	 * @since  1.1.3
	 * @access public
	 * @return mixed
	 */
	public function admin_notices() {
		foreach ( (array) $this->notices as $notice_key => $notice ) {
			echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
			echo wp_kses(
				$notice['message'],
				array(
					'a' => array(
						'href' => array(),
					),
				)
			);
			echo '</p></div>';
		}
	}

	/**
	 * Modify Payment Data.
	 *
	 * The function modifies the payment data prior to being sent to payment gateways.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $payment_meta
	 * @param  $valid_data
	 *
	 * @return mixed
	 */
	public function modify_donation_data( $payment_meta, $valid_data ) {

		if ( isset( $payment_meta['post_data'] ) ) {
			$form_id  = isset( $payment_meta['post_data']['give-form-id'] ) ? $payment_meta['post_data']['give-form-id'] : 0;
			$price_id = isset( $payment_meta['post_data']['give-price-id'] ) ? $payment_meta['post_data']['give-price-id'] : 0;
		} else {
			$form_id  = isset( $payment_meta['form_id'] ) ? $payment_meta['form_id'] : 0;
			$price_id = isset( $payment_meta['price_id'] ) ? $payment_meta['price_id'] : 0;
		}

		$is_recurring = $this->is_donation_recurring( $payment_meta );

		// Is this even recurring?
		if ( ! $is_recurring ) {
			// nope, bounce out.
			return $payment_meta;
		} elseif ( empty( $form_id ) ) {
			return $payment_meta;
		}

		// Add times and period to payment data.
		$set_or_multi   = give_get_meta( $form_id, '_give_price_option', true );
		$recurring_type = give_get_meta( $form_id, '_give_recurring', true );

		// Period functionality.
		$period_functionality = give_get_meta( $form_id, '_give_period_functionality', true );

		/**
		 * Donor's choice Recurring Donation + Donor's Choice Recurring Period
		 *
		 * a. "Recurring Period" option is set to "Donor's Choice".
		 * b. "Recurring Donation" option is set to "Donor's Choice".
		 * c. $_POST request is present with donor's actual choice.
		 */
		if (
			'yes_donor' === $recurring_type
			&& 'donors_choice' === $period_functionality
			&& isset( $_POST['give-recurring-period-donors-choice'] )
		) {
			$payment_meta['period']    = $_POST['give-recurring-period-donors-choice'];
			$payment_meta['times']     = give_get_meta( $form_id, '_give_times', true );
			$payment_meta['frequency'] = give_get_meta( $form_id, '_give_period_interval', true, 1 );
		} // Multi-level admin chosen recurring.
		elseif (
			give_has_variable_prices( $form_id )
			&& 'multi' === $set_or_multi
			&& 'yes_admin' === $recurring_type
		) {
			$payment_meta['period']    = self::get_period( $form_id, $price_id );
			$payment_meta['times']     = self::get_times( $form_id, $price_id );
			$payment_meta['frequency'] = self::get_interval( $form_id, $price_id );
		} // Single & multilevel basic.
		else {
			$payment_meta['period']    = give_get_meta( $form_id, '_give_period', true );
			$payment_meta['times']     = give_get_meta( $form_id, '_give_times', true );
			$payment_meta['frequency'] = give_get_meta( $form_id, '_give_period_interval', true, 1 );
		}

		return apply_filters( 'give_recurring_modify_donation_data', $payment_meta );

	}

	/**
	 * Registers the cancelled post status.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function register_post_statuses() {

		register_post_status(
			'give_subscription',
			array(
				'label'                     => _x( 'Renewal', 'Subscription payment status', 'give-recurring' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Renewal <span class="count">(%s)</span>', 'Subscription <span class="count">(%s)</span>', 'give-recurring' ),
			)
		);
	}

	/**
	 * Register our Subscriptions submenu
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function subscriptions_list() {
		add_submenu_page(
			'edit.php?post_type=give_forms',
			__( 'Subscriptions', 'give-recurring' ),
			__( 'Subscriptions', 'give-recurring' ),
			'view_give_reports',
			'give-subscriptions',
			'give_subscriptions_page'
		);
	}

	/**
	 * Is Payment Complete.
	 *
	 * Returns true or false depending on payment status.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $ret
	 * @param  $payment_id
	 * @param  $status
	 *
	 * @return bool
	 */
	public function is_payment_complete( $ret, $payment_id, $status ) {

		if ( 'cancelled' == $status ) {

			$ret = true;

		} elseif ( 'give_subscription' == $status ) {

			$parent = get_post_field( 'post_parent', $payment_id );
			if ( give_is_payment_complete( $parent ) ) {
				$ret = true;
			}
		}

		return $ret;
	}

	/**
	 * Register Recurring Statuses.
	 *
	 * Tells Give about our new payment status.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $stati
	 *
	 * @return array
	 */
	public function register_recurring_statuses( $stati ) {
		$stati['give_subscription'] = __( 'Renewal', 'give-recurring' );
		$stati['cancelled']         = __( 'Cancelled', 'give-recurring' );

		return $stati;
	}

	/**
	 * Set up the time period IDs and labels
	 *
	 * @since  1.0
	 * @since  1.6.0 Update Periods label.
	 * @static
	 *
	 * @param int    $number
	 * @param string $period
	 *
	 * @return array
	 */
	static function periods( $number = 1, $period = '' ) {

		$periods = apply_filters(
			'give_recurring_periods',
			array(
				// translators: placeholder is number of days. (e.g. "Bill this every day / 4 days")
				'day'     => sprintf( _nx( 'day', '%s days', $number, 'Recurring billing period.', 'give-recurring' ), $number ),
				// translators: placeholder is number of weeks. (e.g. "Bill this every week / 4 weeks")
				'week'    => sprintf( _nx( 'week', '%s weeks', $number, 'Recurring billing period.', 'give-recurring' ), $number ),
				// translators: placeholder is number of months. (e.g. "Bill this every month / 4 months")
				'month'   => sprintf( _nx( 'month', '%s months', $number, 'Recurring billing period.', 'give-recurring' ), $number ),
				// translators: placeholder is number of quarters. (e.g. "Bill this every quarter / 4 times in a year")
				'quarter' => sprintf( _nx( 'quarter', '%s quarters', $number, 'Recurring billing period.', 'give-recurring' ), $number ),
				// translators: placeholder is number of years. (e.g. "Bill this every year / 4 years")
				'year'    => sprintf( _nx( 'year', '%s years', $number, 'Recurring billing period.', 'give-recurring' ), $number ),
			),
			$number
		);

		return ! empty( $periods[ $period ] ) ? $periods[ $period ] : $periods;

	}

	/**
	 * Get billing times.
	 *
	 * @since 1.6.0
	 *
	 * @param string $billing_period
	 *
	 * @return array
	 */
	static function times( $billing_period = '' ) {
		$periods = self::give_recurring_ranges();

		$periods = apply_filters( 'give_recurring_times', $periods );

		if ( ! empty( $billing_period ) ) {
			return $periods[ $billing_period ];
		} else {
			return $periods;
		}
	}

	/**
	 * Returns an array of Recurring lengths.
	 *
	 * PayPal Standard Allowable Ranges
	 * D – for days; allowable range is 1 to 90
	 * W – for weeks; allowable range is 1 to 52
	 * M – for months; allowable range is 1 to 24
	 * Y – for years; allowable range is 1 to 5
	 *
	 * @since 1.6.0
	 */
	static function give_recurring_ranges() {
		$periods = array_keys( self::periods() );

		foreach (  $periods as $period ) {

			$subscription_lengths = array(
				_x( 'Ongoing', 'Subscription length', 'give-recurring' ),
			);

			switch ( $period ) {
				case 'day':
					$subscription_lengths[] = _x( '1 day', 'Subscription lengths. e.g. "For 1 day..."', 'give-recurring' );
					$subscription_range     = range( 2, 90 );
					break;
				case 'week':
					$subscription_lengths[] = _x( '1 week', 'Subscription lengths. e.g. "For 1 week..."', 'give-recurring' );
					$subscription_range     = range( 2, 52 );
					break;
				case 'month':
					$subscription_lengths[] = _x( '1 month', 'Subscription lengths. e.g. "For 1 month..."', 'give-recurring' );
					$subscription_range     = range( 2, 24 );
					break;
				case 'quarter':
					$subscription_lengths[] = _x( '1 quarter', 'Subscription lengths. e.g. "For 1 quarter..."', 'give-recurring' );
					$subscription_range     = range( 2, 12 );
					break;
				case 'year':
					$subscription_lengths[] = _x( '1 year', 'Subscription lengths. e.g. "For 1 year..."', 'give-recurring' );
					$subscription_range     = range( 2, 5 );
					break;
			}

			foreach ( $subscription_range as $number ) {
				$subscription_range[ $number ] = self::periods( $number, $period );
			}

			// Add the possible range to all time range
			$subscription_lengths += $subscription_range;

			$subscription_ranges[ $period ] = $subscription_lengths;
		}

		return $subscription_ranges;
	}

	/**
	 * Set up the interval label.
	 *
	 * @since 1.6.0
	 * Return an i18n'ified associative array of all possible subscription periods.
	 *
	 * @param string (optional) An interval in the range 1-6
	 *
	 * @return mixed
	 */
	static function interval( $interval = '' ) {

		$intervals = array( 1 => _x( 'every', 'period interval (eg "$10 _every_ 2 weeks")', 'give-recurring' ) );

		foreach ( range( 2, 6 ) as $i ) {
			// translators: period interval, placeholder is ordinal (eg "$10 every _2nd/3rd/4th_", etc)
			$intervals[ $i ] = sprintf( _x( 'every %s', 'period interval with ordinal number (e.g. "every 2nd"', 'give-recurring' ), self::give_recurring_append_numeral_suffix( $i ) );
		}

		$intervals = apply_filters( 'give_recurring_interval', $intervals );

		if ( empty( $interval ) ) {
			return $intervals;
		} else {
			return $intervals[ $interval ];
		}
	}

	/**
	 * Takes a number and returns the number with its relevant suffix appended, eg. for 2, the function returns 2nd
	 *
	 * @param int $number
	 *
	 * @since 1.6.0
	 *
	 * @return string
	 */
	static function give_recurring_append_numeral_suffix( $number ) {

		// Handle teens: if the tens digit of a number is 1, then write "th" after the number. For example: 11th, 13th, 19th, 112th, 9311th. http://en.wikipedia.org/wiki/English_numerals
		if ( strlen( $number ) > 1 && 1 == substr( $number, - 2, 1 ) ) {
			// translators: placeholder is a number, this is for the teens
			$number_string = sprintf( __( '%sth', 'give-recurring' ), $number );
		} else { // Append relevant suffix
			switch ( substr( $number, - 1 ) ) {
				case 1:
					// translators: placeholder is a number, numbers ending in 1
					$number_string = sprintf( __( '%sst', 'give-recurring' ), $number );
					break;
				case 2:
					// translators: placeholder is a number, numbers ending in 2
					$number_string = sprintf( __( '%snd', 'give-recurring' ), $number );
					break;
				case 3:
					// translators: placeholder is a number, numbers ending in 3
					$number_string = sprintf( __( '%srd', 'give-recurring' ), $number );
					break;
				default:
					// translators: placeholder is a number, numbers ending in 4-9, 0
					$number_string = sprintf( __( '%sth', 'give-recurring' ), $number );
					break;
			}
		}

		return apply_filters( 'give_recurring_numeral_suffix', $number_string, $number );
	}

	/**
	 * Get Period.
	 *
	 * Get the time period for a variable priced donation.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @param  $form_id
	 * @param  $price_id
	 *
	 * @return bool|string
	 */
	public static function get_period( $form_id, $price_id = 0 ) {

		$recurring_option = give_get_meta( $form_id, '_give_recurring', true );

		// Is this a variable price form & admin's choice?
		if ( give_has_variable_prices( $form_id ) && 'yes_admin' === $recurring_option ) {

			if ( 'custom' === $price_id ) {
				return give_get_meta( $form_id, '_give_recurring_custom_amount_period', true, 'month' );
			} else {
				$levels = give_get_meta( $form_id, '_give_donation_levels', true );

				foreach ( $levels as $price ) {

					// Check that this indeed the recurring price.
					if ( $price_id == $price['_give_id']['level_id']
						 && isset( $price['_give_recurring'] )
						 && 'yes' === $price['_give_recurring']
						 && isset( $price['_give_period'] )
					) {
						return isset( $price['_give_period'] ) ? $price['_give_period'] : 'month';
					}
				}
			}
		} else {

			$recurring_period = give_get_meta( $form_id, '_give_period_functionality', true, 'admin_choice' );

			// This is either a Donor's Choice multi-level or set donation form.
			$period = give_get_meta( $form_id, '_give_period', true );

			if ( 'donors_choice' === $recurring_period ) {
				$period = give_get_meta( $form_id, '_give_period_default_donor_choice', true, 'month' );
			}

			if ( $period ) {
				return $period;
			}
		}

		return false;
	}

	/**
	 * Get Interval.
	 *
	 * Get the period interval for a variable priced donation.
	 *
	 * @since  1.6.0
	 * @access public
	 * @static
	 *
	 * @param  $form_id
	 * @param  $price_id
	 *
	 * @return bool|string
	 */
	public static function get_interval( $form_id, $price_id = 0 ) {

		$recurring_option = give_get_meta( $form_id, '_give_recurring', true );

		// Is this a variable price form & admin's choice?
		if ( give_has_variable_prices( $form_id ) && 'yes_admin' === $recurring_option ) {

			if ( 'custom' === $price_id ) {
				return give_get_meta( $form_id, '_give_recurring_custom_amount_interval', true, '1' );
			} else {
				$levels = give_get_meta( $form_id, '_give_donation_levels', true );

				foreach ( $levels as $price ) {

					// Check that this indeed the recurring price.
					if ( $price_id == $price['_give_id']['level_id']
						 && isset( $price['_give_recurring'] )
						 && 'yes' === $price['_give_recurring']
						 && isset( $price['_give_period'] )
					) {
						return isset( $price['_give_period_interval'] ) ? $price['_give_period_interval'] : 1;
					}
				}
			}
		} else {

			// This is either a Donor's Choice multi-level or set donation form.
			$period = give_get_meta( $form_id, '_give_period_interval', true, 1 );

			if ( $period ) {
				return $period;
			}
		}

		return false;
	}

	/**
	 * Get Times.
	 *
	 * Get the number of times a price ID recurs.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @param  $form_id
	 * @param  $price_id
	 *
	 * @return int
	 */
	public static function get_times( $form_id, $price_id = 0 ) {

		$recurring_option = give_get_meta( $form_id, '_give_recurring', true );

		// is this a single or multi-level form?
		if ( give_has_variable_prices( $form_id ) && 'yes_admin' === $recurring_option ) {

			if ( 'custom' === $price_id ) {
				return give_get_meta( $form_id, '_give_recurring_custom_amount_times', true, 0 );
			} else {
				$levels = maybe_unserialize( give_get_meta( $form_id, '_give_donation_levels', true ) );

				foreach ( $levels as $price ) {

					// Check that this indeed the recurring price.
					if (
						$price_id == $price['_give_id']['level_id'] &&
						isset( $price['_give_recurring'] ) &&
						'yes' === $price['_give_recurring'] &&
						isset( $price['_give_times'] )
					) {
						return isset( $price['_give_times'] ) ? intval( $price['_give_times'] ) : 0;
					}
				}
			}
		} else {

			$times = give_get_meta( $form_id, '_give_times', true, 0 );

			if ( $times ) {
				return $times;
			}
		}

		return 0;

	}

	/**
	 * Get the number of times a single-price donation form recurs.
	 *
	 * @since  1.0
	 * @static
	 *
	 * @param  $form_id
	 *
	 * @return int|mixed
	 */
	static function get_times_single( $form_id ) {

		$times = give_get_meta( $form_id, '_give_times', true );

		if ( $times ) {
			return $times;
		}

		return 0;
	}

	/**
	 * Is Donation Form Recurring?
	 *
	 * Check if a donation form is recurring.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @param  int $form_id  The donation form ID.
	 * @param  int $level_id The multi-level ID.
	 *
	 * @return bool
	 */
	public static function is_recurring( $form_id, $level_id = 0 ) {

		$is_recurring     = false;
		$levels           = maybe_unserialize( give_get_meta( $form_id, '_give_donation_levels', true ) );
		$recurring_option = give_get_meta( $form_id, '_give_recurring', true );
		$period           = self::get_period( $form_id, $level_id );

		// If it's multi level with admin choice with admin does not choice recurring for that level.
		if ( empty( $period ) ) {
			return false;
		}

		/**
		 * Check multi-level forms whether any level is recurring
		 *
		 * Conditions:
		 * a. Form has variable price
		 * b. The form has a recurring option enabled.
		 */
		if (
			give_has_variable_prices( $form_id )
			&& ( empty( $recurring_option ) || 'no' !== $recurring_option )
		) {

			switch ( $recurring_option ) {

				// Is this a multi-level donor's choice?
				case 'yes_donor':
					return true;
					break;

				case 'yes_admin':
					if ( 'custom' === $level_id ) {
						return true;
					} else {

						// Loop through levels and see if a level is recurring.
						foreach ( $levels as $level ) {

							// Is price recurring?
							$level_recurring = ( isset( $level['_give_recurring'] ) && $level['_give_recurring'] == 'yes' );

							// check that this price is indeed recurring:
							if ( $level_id == $level['_give_id']['level_id'] && $level_recurring && false !== $period ) {

								$is_recurring = true;

							} elseif ( empty( $level_id ) && $level_recurring ) {
								// Checking for ANY recurring level - empty $level_id param.
								$is_recurring = true;

							}
						}
					}
					break;
			}
		} elseif ( ! empty( $recurring_option ) && 'no' !== $recurring_option ) {

			// Single level donation form.
			$is_recurring = true;

		}

		return $is_recurring;
	}

	/**
	 * Is the donation recurring.
	 *
	 * Determines if a donation is a recurring donation; should be used only at time of making the donation.
	 * Use Give_Recurring_Subscriber->has_subscription() to determine after subscription is made if it is in fact
	 * recurring.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $payment_meta
	 *
	 * @return bool
	 */
	public function is_donation_recurring( $payment_meta ) {

		// Ensure we have proper vars set
		if ( isset( $payment_meta['post_data'] ) ) {
			$form_id  = isset( $payment_meta['post_data']['give-form-id'] ) ? $payment_meta['post_data']['give-form-id'] : 0;
			$price_id = isset( $payment_meta['post_data']['give-price-id'] ) ? $payment_meta['post_data']['give-price-id'] : 0;
		} else {
			// fallback
			$form_id  = isset( $payment_meta['form_id'] ) ? $payment_meta['form_id'] : 0;
			$price_id = isset( $payment_meta['price_id'] ) ? $payment_meta['price_id'] : 0;
		}

		// Check for donor's choice option
		$user_choice             = isset( $payment_meta['post_data']['give-recurring-period'] ) ? $payment_meta['post_data']['give-recurring-period'] : '';
		$user_custom_amount      = isset( $payment_meta['post_data']['give-price-id'] ) ? $payment_meta['post_data']['give-price-id'] : '';
		$recurring_enabled       = give_get_meta( $form_id, '_give_recurring', true );
		$custom_amount           = give_get_meta( $form_id, '_give_custom_amount', true );
		$custom_amount_recurring = give_get_meta( $form_id, '_give_recurring_custom_amount_period', true, 'month' );

		// If not empty this is a recurring donation (checkbox is checked)
		if ( ! empty( $user_choice ) ) {
			return true;
		} elseif (
			( empty( $user_choice ) && 'yes_donor' === $recurring_enabled ) ||
			(
				empty( $user_choice ) &&
				'yes_admin' === $recurring_enabled &&
				'once' === $custom_amount_recurring &&
				'custom' === $user_custom_amount
			)
		) {
			// User only wants to give once
			return false;
		}

		// Admin choice: check fields
		if ( give_has_variable_prices( $form_id ) || ( 'yes_admin' === $recurring_enabled && give_is_setting_enabled( $custom_amount ) ) ) {
			// get default selected price ID
			return self::is_recurring( $form_id, $price_id );
		} else {
			// Set level
			return self::is_recurring( $form_id );
		}

	}

	/**
	 * Deprecated. Use is_donation_recurring() instead.
	 *
	 * @param $payment_meta
	 *
	 * @return bool
	 */
	public function is_purchase_recurring( $payment_meta ) {
		return $this->is_donation_recurring( $payment_meta );
	}

	/**
	 * Make sure subscription payments get included in earning reports.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args
	 *
	 * @return array
	 */
	public function earnings_query( $args ) {
		$args['post_status'] = array( 'publish', 'give_subscription' );
		$args['status']      = array( 'publish', 'give_subscription' );

		return $args;
	}

	/**
	 * Make sure subscription payments get included in has user donated query.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args
	 *
	 * @return array
	 */
	public function has_donated_query( $args ) {
		$args['status'] = array( 'publish', 'revoked', 'cancelled', 'give_subscription' );

		return $args;
	}

	/**
	 * Tells Give to include child payments in queries.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $query Give_Payments_Query
	 *
	 * @return void
	 */
	public function enable_child_payments( $query ) {

		$allow_child_payments = true;

		$page        = isset( $_GET['page'] ) ? $_GET['page'] : false;
		$post_status = isset( $query->args['post_status'] ) ? $query->args['post_status'] : false;

		if ( $post_status ) {
			// Don't show renewals when "completed" filter is active within donations list view.
			if ( 'publish' === $post_status && 'give-payment-history' === $page ) {
				$allow_child_payments = false;
			};
		}

		if ( $allow_child_payments ) {
			$query->__set( 'post_parent', null );
		}

		if ( isset( $query->args['post_status'] ) && 'publish' === $query->args['post_status'] ) {
			$query->__set( 'post_status', array( 'publish', 'give_subscription' ) );
		}

	}

	/**
	 * Instruct Give PDF Receipts that subscription payments are eligible for Invoices.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  bool $ret
	 * @param  int  $payment_id
	 *
	 * @return bool
	 */
	public function is_invoice_allowed( $ret, $payment_id ) {

		$payment_status = get_post_status( $payment_id );

		if ( 'give_subscription' == $payment_status ) {

			$parent = get_post_field( 'post_parent', $payment_id );
			if ( give_is_payment_complete( $parent ) ) {
				$ret = true;
			}
		}

		return $ret;
	}

	/**
	 * Get User ID from customer recurring ID.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $recurring_id
	 *
	 * @return int|null|string
	 */
	public function get_user_id_by_recurring_customer_id( $recurring_id = '' ) {

		global $wpdb;

		$user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '_give_recurring_id' AND meta_value = %s LIMIT 1", $recurring_id ) );

		if ( $user_id != null ) {
			return $user_id;
		}

		return 0;

	}

	/**
	 * Maybe Process Refund.
	 *
	 * Checks the payment status during the refund process and allows
	 * it to be processed through the gateway if it's a give_subscription.
	 *
	 * @since  1.2
	 * @access public
	 *
	 * @param  bool   $process_refund The current status of if a refund should be processed.
	 * @param  object $payment        The Give_Payment object of the refund being processed.
	 *
	 * @return bool                   If the payment should be processed as a refund.
	 */
	public function maybe_process_refund( $process_refund, $payment ) {

		if ( 'give_subscription' === $payment->old_status ) {
			$process_refund = true;
		}

		return $process_refund;

	}

	/**
	 * Ensure that when renewals are deleted the donor's stats are updated.
	 *
	 * @since 1.4
	 *
	 * @param $statuses
	 *
	 * @return array
	 */
	public function reduce_stats_when_renewal_deleted( $statuses ) {

		$statuses[] = 'give_subscription';

		return $statuses;
	}

	/**
	 * Update Meta value of logged only.
	 *
	 * @since 1.5.5
	 *
	 * @param $meta_value
	 * @param $form_id
	 * @param $meta_key
	 *
	 * @return string $meta_value
	 */
	function give_logged_in_only_meta_value( $meta_value, $form_id, $meta_key ) {
		if ( '_give_logged_in_only' === $meta_key ) {
			$meta_value = 'disabled';
		}

		return $meta_value;
	}

	/**
	 * Add action if the Donation from is recurring and email access is disable.
	 *
	 * @since 1.5.5
	 *
	 * @param $form_id
	 */
	function give_add_logged_meta_filter( $form_id ) {
		// Only required if email access not on & recurring enabled.
		if (
			give_is_form_recurring( $form_id )
			&& ! give_is_setting_enabled( give_get_option( 'email_access' ) )
		) {
			add_filter( 'give_get_meta', array( $this, 'give_logged_in_only_meta_value' ), 1, 3 );
		}
	}

	/**
	 * Remove the filter that alter the logged in meta value.
	 *
	 * @since 1.5.5
	 *
	 * @param $form_id
	 */
	function give_remove_logged_meta_filter( $form_id ) {
		remove_filter( 'give_get_meta', array( $this, 'give_logged_in_only_meta_value' ), 1, 3 );
	}

	/**
	 * Show Registration Form.
	 *
	 * Filter the give_show_register_form to return both login and
	 * registration fields for recurring donations if email access not enabled;
	 * if enabled, then it will respect donation form's settings.
	 *
	 * @access public
	 *
	 * @param  $value
	 * @param  $form_id
	 *
	 * @return string
	 */
	public function show_register_form( $value, $form_id ) {

		if (
			give_is_form_recurring( $form_id )
			&& ! give_is_setting_enabled( give_get_option( 'email_access' ) )
		) {
			return 'both';
		} else {
			return $value;
		}

	}

	/**
	 * Does Subscriber have email access.
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @return bool
	 */
	public function subscriber_has_email_access() {

		// Initialize because this is hooked upon init.
		if ( class_exists( 'Give_Email_Access' ) ) {
			$email_access = new Give_Email_Access();
			$email_access->init();
			$email_access_option  = give_get_option( 'email_access' );
			$email_access_granted = ( ! empty( $email_access->token_exists ) && give_is_setting_enabled( $email_access_option ) );
		} else {
			$email_access_granted = false;
		}

		return $email_access_granted;
	}

	/**
	 * Get gateway class name.
	 *
	 * @param string $gateways_id
	 *
	 * @return object|string
	 */
	public static function get_gateway_class( $gateways_id ) {
		return array_key_exists( $gateways_id, self::$gateways ) ?
			self::$gateways[ $gateways_id ] :
			'';
	}

	/**
	 * Register Email Notifications.
	 *
	 * @param array $emails
	 *
	 * @return array
	 */
	public function register_emails( $emails ) {
		$emails[] = include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/class-renewal-receipt-email.php';
		$emails[] = include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/class-renewal-receipt-admin-email.php';
		$emails[] = include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/class-subscription-cancelled-email.php';
		$emails[] = include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/class-subscription-cancelled-admin-email.php';
		$emails[] = include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/class-subscription-completed-email.php';
		$emails[] = include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/class-subscription-reminder-email.php';
		$emails[] = include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/class-subscriptions-email-access.php';
		$emails[] = include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/class-give-recurring-subscription-payment-failed-email.php';

		return $emails;
	}

	/**
	 * Register email tags.
	 *
	 * @param $email_tags
	 *
	 * @return array
	 */
	function register_email_tags( $email_tags ) {
		$email_tags = array_merge(
			$email_tags,
			array(
				array(
					'tag'         => 'renewal_link',
					'description' => esc_html__( 'The link to the form through which donation was made.', 'give-recurring' ),
					'function'    => array( Give_Recurring()->emails, 'filter_email_tags' ),
					'context'     => 'subscription',
				),
				array(
					'tag'         => 'subscription_frequency',
					'description' => esc_html__( 'Displays the subscription frequency based on its period and times. For instance, "Monthly for 3 Months", or simply "Monthly" if bill times is 0.', 'give-recurring' ),
					'function'    => array( Give_Recurring()->emails, 'filter_email_tags' ),
					'context'     => 'subscription',
				),
				array(
					'tag'         => 'subscriptions_completed',
					'description' => esc_html__( 'Displays the number of subscriptions completed with the total bill times. For instance "1/3" or "1 / Until cancelled".', 'give-recurring' ),
					'function'    => array( Give_Recurring()->emails, 'filter_email_tags' ),
					'context'     => 'subscription',
				),
				array(
					'tag'         => 'cancellation_date',
					'description' => esc_html__( 'The date the donation was cancelled.', 'give-recurring' ),
					'function'    => array( Give_Recurring()->emails, 'filter_email_tags' ),
					'context'     => 'subscription',
				),
				array(
					'tag'         => 'completion_date',
					'description' => esc_html__( 'The date the donation was completed.', 'give-recurring' ),
					'function'    => array( Give_Recurring()->emails, 'filter_email_tags' ),
					'context'     => 'subscription',
				),
				array(
					'tag'         => 'renewal_date',
					'description' => esc_html__( 'The date of renewal.', 'give-recurring' ),
					'function'    => array( Give_Recurring()->emails, 'filter_email_tags' ),
					'context'     => 'subscription',
				),
				array(
					'tag'         => 'expiration_date',
					'description' => esc_html__( 'The date of expiration.', 'give-recurring' ),
					'function'    => array( Give_Recurring()->emails, 'filter_email_tags' ),
					'context'     => 'subscription',
				),
			)
		);

		return $email_tags;
	}


	/**
	 * Callback for preview of subscription related tags.
	 *
	 * @param string $message The message of the email.
	 *
	 * @since 1.7.3
	 *
	 * @return string
	 */
	public function give_recurring_email_preview_template_tags( $message ) {

		$action        = isset( $_GET['give_action'] ) ? give_clean( $_GET['give_action'] ) : '';
		$send_preview  = ( 'send_preview_email' === $action ) ? true : false;
		$payment_id    = isset( $_GET['preview_id'] ) ? give_clean( $_GET['preview_id'] ) : '';
		$show_defaults = ( $send_preview || ! isset( $_GET['preview_id'] ) || '0' === $payment_id );
		$frequency     = give_recurring_pretty_subscription_frequency( 1, 0, false );

		/* translators: abbreviation for "not applicable" */
		$not_applicable       = __( 'n/a', 'give-recurring' );
		$progress             = $not_applicable;
		$expiration_timestamp = $not_applicable;
		$renewal_date         = $not_applicable;
		$expiration_date      = $not_applicable;

		if ( ! empty( $payment_id ) && '0' !== $payment_id ) {
			$payment_meta    = give_get_payment_meta( $payment_id );
			$is_subscription = array_key_exists( '_give_subscription_payment', $payment_meta );

			if ( $is_subscription ) {
				$subscription         = give_recurring_get_subscription_by( 'payment', $payment_id );
				$interval             = ! empty( $subscription->frequency ) ? $subscription->frequency : 1;
				$progress             = $subscription->get_subscription_progress();
				$times                = intval( $subscription->bill_times ) * intval( $interval );
				$frequency            = give_recurring_pretty_subscription_frequency( $subscription->period, $times, false, $interval );
				$expiration_timestamp = strtotime( $subscription->expiration );
				$renewal_date         = date( give_date_format(), strtotime( $subscription->expiration ) );
				$expiration_date      = date( give_date_format(), strtotime( $subscription->expiration ) );
			}
		}

		$message = str_replace(
			'{subscription_frequency}',
			( $show_defaults ) ? __( 'Monthly', 'give-recurring' ) : $frequency,
			$message
		);

		$message = str_replace(
			'{subscriptions_completed}',
			( $show_defaults ) ? __( '2 / Ongoing', 'give-recurring' ) : $progress,
			$message
		);

		$message = str_replace(
			'{completion_date}',
			( $show_defaults ) ? __( 'September 2, 2018', 'give-recurring' ) : $expiration_timestamp,
			$message
		);

		$message = str_replace(
			'{cancellation_date}',
			__( 'August 12, 2018', 'give-recurring' ),
			$message
		);

		$message = str_replace(
			'{renewal_date}',
			( $show_defaults ) ? __( 'June 2, 2018', 'give-recurring' ) : $renewal_date,
			$message
		);

		$message = str_replace(
			'{expiration_date}',
			( $show_defaults ) ? __( 'July 2, 2018', 'give-recurring' ) : $expiration_date,
			$message
		);

		return $message;
	}

	/**
	 * Show the subscriptions management UI.
	 *
	 * @since 1.7
	 *
	 * @param string $action Optional. Which view to show. Options: update|list. If not set, $_GET[ 'action ] or "list"
	 *                       is used.
	 *
	 * @return string
	 */
	public function subscriptions_view( $action = '' ) {

		if ( empty( $action ) ) {
			$action = ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) ? give_clean( $_GET['action'] ) : 'list';
		}

		ob_start(); ?>

		<?php if ( give_get_errors() ) : ?>
			<div class="error settings-error">
				<?php
				$errors = give_get_errors();
				Give_Notices::print_frontend_errors( $errors );
				give_clear_errors();
				?>
			</div>
		<?php endif; ?>

		<?php
		$email_access = give_get_option( 'email_access' );
		$canAccessView = Give_Recurring_Subscriber::canAccessView();
		$subscriber = Give_Recurring_Subscriber::getSubscriber();
		$subscriptionIsNotValid = $subscriber && $subscriber->id <= 0 ?
			Give_Notices::print_frontend_notice(
				__( 'You have not made any recurring donations.', 'give-recurring' ),
				false,
				'warning'
			) : null;

		// Handle list view. list is also a default view.
		if( ! in_array( $action,  [ 'edit_subscription', 'update'] ) && ( $canAccessView || Give()->session->get_session_expiration() ) ) {
			// Validate subscriber.
			if( $subscriptionIsNotValid ) {
				echo $subscriptionIsNotValid;

				return ob_get_clean();
			}
			give_get_template_part( 'shortcode', 'subscriptions' );

		} elseif ( $canAccessView ) {
			// Validate subscriber.
			if( $subscriptionIsNotValid ) {
				echo $subscriptionIsNotValid;

				return ob_get_clean();
			}

			// Sanity Check: Subscription ID should be valid.
			if ( ! isset( $_GET['subscription_id'] ) ) {
				Give_Notices::print_frontend_notice(
						__( 'Subscription ID is Invalid.', 'give-recurring' ),
						true,
						'warning'
				);

				return ob_get_clean();
			}

			// Sanity Check: Subscription ID should be valid.
			$subscription_id = absint( $_GET['subscription_id'] );
			$subscription  = new Give_Subscription( $subscription_id );

			// Show login form if subscription does not belongs to logged in donor.
			if( ! Give_Recurring_Subscriber::doesSubscriptionBelongsTo( $subscription, $subscriber) ) {

				$donor_mismatch_text = apply_filters(
					'give_subscription_donor_mismatch_notice_text',
					__( 'The subscription you are looking for either doesn\'t exist or belongs to a different donor. Please contact a site administrator for assistance.', 'give-recurring' )
				);

				echo Give_Notices::print_frontend_notice(
					$donor_mismatch_text,
					false,
					'error'
				);

				return ob_get_clean();
			}

			switch ( $action ) {
				case 'update':
					give_get_template_part( 'shortcode', 'subscription-update' );
					break;
				case 'edit_subscription':
					give_get_template_part( 'shortcode', 'subscription-edit' );
					break;
			}

		} elseif ( give_is_setting_enabled( $email_access ) && ! Give_Recurring()->subscriber_has_email_access() ) {
			// Email Access Enabled & no valid token.
			give_get_template_part( 'email-login-form' );

		} else {
			//No email access, user access denied.
			Give_Notices::print_frontend_notice(
					__( 'You must be logged in to view your subscriptions.', 'give-recurring' ),
					true,
					'warning'
			);

			echo give_login_form( give_get_current_page_url() );

		}

		return ob_get_clean();

	}
}

/**
 * The main function responsible for returning the one true Give_Recurring instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $recurring = Give_Recurring(); ?>
 *
 * @since 1.0
 *
 * @return Give_Recurring|bool
 */

function Give_Recurring() {

	if ( ! give_recurring_check_environment() ) {
		return false;
	}

	return Give_Recurring::instance();
}

add_action( 'plugins_loaded', 'Give_Recurring', 100 );
