<?php
/**
 * Imports data from WPMU DEV Membership plugin.
 *
 * @since  1.0.0
 * @package Membership2
 * @subpackage Model
 */
class MS_Model_Import_Membership extends MS_Model_Import {

	/**
	 * Identifier for this Import source
	 *
	 * @since  1.0.0
	 */
	const KEY = 'membership';

	/**
	 * Stores the result of present() call
	 *
	 * @since  1.0.0
	 *
	 * @var bool
	 */
	static protected $is_present = null;

	/**
	 * The import data object
	 *
	 * @since  1.0.0
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Checks if the user did import data from this source before.
	 *
	 * This information is not entirely reliable, since data could have been
	 * deleted again after import.
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	static public function did_import() {
		$settings 	= MS_Factory::load( 'MS_Model_Settings' );
		$did_import = ! empty( $settings->import[ self::KEY ] );

		/**
		 * Allow users to manually declare that some M2 subscriptions were
		 * imported from old Membership plugin.
		 *
		 * As a result M2 will additionally listen to the old M1 IPN URL for
		 * PayPal payment notifications.
		 *
		 * @since  1.0.2.4
		 * @param  bool $did_import
		 */
		return apply_filters(
			'ms_did_import_m1_data',
			$did_import
		);
	}

	/**
	 * This function parses the Import source (i.e. an file-upload) and returns
	 * true in case the source data is valid. When returning true then the
	 * $source property of the model is set to the sanitized import source data.
	 *
	 * @since  1.0.0
	 *
	 * @return bool
	 */
	public function prepare() {
		self::_message( 'preview', false );

		$this->prepare_import_struct();
		$this->data = $this->validate_object( $this->data );

		if ( empty( $this->data ) ) {
			self::_message( 'error', __( 'Hmmm, we could not import the Membership data...', 'membership2' ) );
			return false;
		}

		$this->source = $this->data;
		return true;
	}

	/**
	 * Returns true if the specific import-source is present and can be used
	 * for import.
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	static public function present() {
		if ( null === self::$is_present ) {
			self::$is_present = false;

			// Check for one core table of the plugin.
			global $wpdb;
			$rule_table = $wpdb->prefix . 'm_membership_rules';

			$sql = 'SHOW TABLES LIKE %s;';
			$sql = $wpdb->prepare( $sql, $rule_table );
			self::$is_present = $wpdb->get_var( $sql ) == $rule_table;
		}

		return self::$is_present;
	}

	/**
	 * Returns a valid import object that contains the Membership details
	 *
	 * @since  1.0.0
	 * @return object
	 */
	protected function prepare_import_struct() {
		$this->data 			= (object) array();

		$this->data->source_key = self::KEY;
		$this->data->source 	= sprintf(
			'%s (%s)',
			'Membership Premium',
			'WPMUDEV'
		);
		$this->data->plugin_version = '3.5.x';
		$this->data->export_time 	= date( 'Y-m-d H:i' );
		$this->data->notes 			= array(
			__( 'Exported data:', 'membership2' ),
			__( '- Subscription Plans (without level rules)', 'membership2' ),
			__( '- Members', 'membership2' ),
			__( '- Registrations (link between Members and Subscription Plans)', 'membership2' ),
			__( '- Transactions', 'membership2' ),
			__( 'Each Subscription-Level is imported as a individual Membership.', 'membership2' ),
			__( 'Transactions are converted to invoices. Data like tax-rate or applied coupons are not available.', 'membership2' ),
			__( 'Please note that we cannot import recurring 2Checkout subscriptions to Membership2!', 'membership2' ),
		);

		$this->data->memberships 	= array();
		$this->data->members 		= array();
		$this->data->settings 		= array();

		$this->add_memberships();
		$this->add_members();

		return $this->data;
	}

	/**
	 * Generates a list of all default membership objects that can be imported.
	 * The Membership2 base membership is not included!
	 *
	 * @since  1.0.0
	 */
	protected function add_memberships() {
		global $wpdb;

		/*
		 * Notes:
		 * Child memberships are not possible
		 * Trial period is not possible
		 */

		$sql = "
		SELECT DISTINCT
			subsc.id * 1000 + suble.level_id AS id,
			TRIM( CONCAT( subsc.sub_name, ': ', memle.level_title ) ) AS `name`,
			subsc.sub_description AS `description`,
			'simple' AS `type`,
			subsc.sub_active AS `active`,
			IF ( subsc.sub_public = 0, 1, 0 ) AS `private`,
			IF ( suble.level_price = 0, 1, 0 ) AS `free`,
			'' AS `dripped`,
			'' AS `special`,
			suble.level_price AS `price`,
			0 AS `trial`,
			CASE suble.sub_type
				WHEN 'indefinite' THEN 'permanent'
				WHEN 'finite' THEN 'finite'
				WHEN 'serial' THEN 'recurring'
				ELSE 'permanent'
			END AS `payment_type`,
			suble.level_period AS `period_unit`,
			suble.level_period_unit AS `period_type`
		FROM `{$wpdb->prefix}m_subscriptions` subsc
			INNER JOIN `{$wpdb->prefix}m_subscriptions_levels` suble ON suble.sub_id = subsc.id
			INNER JOIN `{$wpdb->prefix}m_membership_levels` memle ON memle.id = suble.level_id
		";
		$res = $wpdb->get_results( $sql );

		foreach ( $res as $mem ) {
			$this->data->memberships[] = $mem;
		}
	}

	/**
	 * Generates a list of all members that have a membership
	 *
	 * @since  1.0.0
	 */
	protected function add_members() {
		global $wpdb;

		$sql = "
		SELECT DISTINCT
			wpuser.id AS `id`,
			wpuser.user_email AS `email`,
			wpuser.user_login AS `username`
		FROM {$wpdb->prefix}m_membership_relationships member
		INNER JOIN {$wpdb->users} wpuser ON wpuser.id = member.user_id
		";
		$res = $wpdb->get_results( $sql );

		foreach ( $res as $mem ) {
			$this->data->members[$mem->id] = $mem;
			$this->add_subscriptions( $mem->id );
		}
	}

	/**
	 * Generates a list of subscriptions for the specified member
	 *
	 * @since  1.0.0
	 * @param int $member_id The member-ID
	 */
	protected function add_subscriptions( $member_id ) {
		global $wpdb;

		$sql = "
		SELECT DISTINCT
			CONCAT( member.user_id * 10, member.sub_id * 1000 + member.level_id ) AS `id`,
			member.sub_id AS `sub_id`,
			member.level_id AS `level_id`,
			member.sub_id * 1000 + member.level_id AS membership,
			CASE
				WHEN member.expirydate >= CURRENT_DATE() THEN 'active'
				ELSE 'expired'
			END AS `status`,
			CASE member.usinggateway
				WHEN 'paypalsolo' THEN 'paypalsingle'
				WHEN 'paypalexpress' THEN 'paypalstandard'
				WHEN 'twocheckout' THEN 'manual'
				WHEN 'freesubscriptions' THEN 'free'
				WHEN 'authorizenetarb' THEN 'authorize'
				WHEN 'authorizenetaim' THEN 'authorize'
				WHEN 'authorize' THEN 'authorize'
				ELSE 'admin'
			END AS `gateway`,
			DATE_FORMAT( member.startdate, '%%Y-%%m-%%d' ) AS `start`,
			DATE_FORMAT( member.expirydate, '%%Y-%%m-%%d' ) AS `end`
		FROM {$wpdb->prefix}m_membership_relationships member
		WHERE member.user_id = %s
		";
		$sql = $wpdb->prepare( $sql, $member_id );
		$res = $wpdb->get_results( $sql );

		$this->data->members[$member_id]->subscriptions = array();
		foreach ( $res as $sub ) {
			$this->data->members[$member_id]->subscriptions[$sub->id] = $sub;
			$this->add_invoices( $member_id, $sub->sub_id, $sub->id );
		}
	}

	/**
	 * Generates a list of invoices for the specified subscription
	 *
	 * @since  1.0.0
	 * @param int $member_id The member-ID
	 * @param int $subscription The subscription plan-ID
	 * @param int $exp_id The export ID of the subscription object
	 */
	protected function add_invoices( $member_id, $subscription, $exp_id ) {
		global $wpdb;

		$sql = "
		SELECT DISTINCT
			CONCAT( member.user_id * 10, member.sub_id * 1000 + member.level_id ) AS `subscription_id`,
			inv.transaction_id AS `id`,
			CONCAT( member.user_id, '-', LPAD( inv.transaction_id, 3, '0') ) AS `invoice_number`,
			inv.transaction_paypal_ID AS `external_id`,
			CASE inv.transaction_gateway
				WHEN 'paypalsolo' THEN 'paypalsingle'
				WHEN 'paypalexpress' THEN 'paypalstandard'
				WHEN 'twocheckout' THEN 'manual'
				WHEN 'freesubscriptions' THEN 'free'
				WHEN 'authorizenetarb' THEN 'authorize'
				WHEN 'authorizenetaim' THEN 'authorize'
				WHEN 'authorize' THEN 'authorize'
				ELSE 'admin'
			END AS `gateway`,
			CASE inv.transaction_status
				WHEN 'Partially-Refunded' THEN 'failed'
				WHEN 'Refunded' THEN 'failed'
				WHEN 'Reversed' THEN 'failed'
				WHEN 'Pending' THEN 'pending'
				WHEN 'In-Progress' THEN 'pending'
				WHEN 'Denied' THEN 'denied'
				WHEN 'Completed' THEN 'paid'
				WHEN 'Processed' THEN 'paid'
				ELSE 'paid'
			END AS `status`,
			inv.transaction_currency AS `currency`,
			inv.transaction_total_amount / 100 AS `amount`,
			inv.transaction_total_amount / 100 AS `total`,
			FROM_UNIXTIME( inv.transaction_stamp, '%%Y-%%m-%%d' ) AS `due`,
			inv.transaction_note AS `notes`
		FROM {$wpdb->prefix}m_membership_relationships member
		INNER JOIN {$wpdb->prefix}m_subscription_transaction inv ON inv.transaction_user_id = member.user_id AND inv.transaction_subscription_ID = member.sub_id
		WHERE
			member.user_id = %s
			AND member.sub_id = %s
		";
		$sql = $wpdb->prepare( $sql, $member_id, $subscription );
		$res = $wpdb->get_results( $sql );

		$this->data->members[$member_id]->subscriptions[$exp_id]->invoices = array();
		foreach ( $res as $inv ) {
			$this->data->members[$member_id]->subscriptions[$exp_id]->invoices[] = $inv;
		}
	}

	/**
	 * Adds activation details for a single add-on to the import object
	 *
	 * @since  1.0.0
	 * @param  string $name The add-on name
	 */
	protected function activate_addon( $name ) {
		$this->data->settings['addons'] 		= mslib3()->array->get( $this->data->settings['addons'] );
		$this->data->settings['addons'][$name] 	= true;
	}

}