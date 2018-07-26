<?php
/**
********************************************************************************
*Import Data Structure
*
*- source_key      <string>  Internal name of the data source
*
********************************************************************************
*
*- source          <string>  Name and version of the source plugin
*
********************************************************************************
*
*- plugin_version  <string>  Version of the source plugin
*
********************************************************************************
*
*- export_time     <yyyy-mm-dd hh:mm>  "2015-01-08 18:37"
*
********************************************************************************
*
*- notes           <string>  Notes to display in the import preview
*
********************************************************************************
*
*- memberships     <array>
*    - id                 <int>  Export-ID
*    - name               <string>  Membership name
*    - description        <string>  Membership description
*    - type               [simple|dripped|base|guest|user]
*    - active             <bool>
*    - private            <bool>
*    - free               <bool>
*
*    If `free` is false:
*    - price              <float>
*    - trial              <bool>
*    - payment_type       [permanent|finite|date|recurring]
*
*    If `payment_type` is 'finite':
*    - period_unit        <int>  Number of days/weeks/months
*    - period_type        [d|w|m|y]
*
*    If `payment_type` is 'recurring':
*    - period_unit        <int>  Number of days/weeks/months
*    - period_type        [d|w|m|y]
*    - period_repetitions <int>  Number of payments before membership ends
*
*    If `payment_type` is 'date':
*    - period_start       <yyyy-mm-dd>
*    - period_end         <yyyy-mm-dd>
*
*    If `trial` is true:
*    - trial_price        <float>
*    - trial_period_unit  <int>  Number of days/weeks/months
*    - trial_period_type  [d|w|m|y]
*
********************************************************************************
*
*- members         <array>
*    - id              <int>  Export-ID
*    - email           <string>  If not found in WP_Users then a new user is created
*    - username        <string>  Login name (if a new user needs to be created)
*    - payment         <array>  Payment details of the user.
*        - stripe_card_exp                <string>
*        - stripe_card_num                <string>
*        - stripe_customer                <string>
*        - authorize_card_exp             <string>
*        - authorize_card_num             <string>
*        - authorize_cim_profile          <string>
*        - authorize_cim_payment_profile  <string>
*
*    - subscriptions   <array of objects> A list of subscriptions (i.e. linked memberships)
*
*        Structure of a subscription object:
*        - id              <int>  Export ID
*        - membership      <int>  Membership Export ID
*        - status          [pending|active|trial|trial_expired|expired|deactivated|canceled]
*        - gateway         [admin|free|manual|paypalsingle|paypalstandard|authorize|stripe]
*        - start           <yyyy-mm-dd>
*        - expire          <yyyy-mm-dd>
*        - trial_finished  <bool>
*        - trial_end       <yyyy-mm-dd> If `trial_finished` is false
*
*        - invoices <array of objects>
*
*            Structure of an invoice object:
*			- id                  <int>  Export ID
*			- invoice_number      <string>  The invoice number
*			- external_id         <string>  Gateway-specific invoice reference
*			- gateway             [paypalsingle|paypalstandard|authorize|stripe]
*			- status              [billed|paid|failed|pending|denied]
*			- coupon              <string>  Coupon code, if applicable
*			- currency            <string>  Currency of invoice
*			- amount              <string>  Membership base-price
*			- discount            <float>  Discounted amount (price, not percentage!)
*			- discount2           <float>  Second discount amount
*			- total               <float>  Totally billed amount
*			- for_trial           <bool> True means this invoice is for a trial period
*			- due                 <yyyy-mm-dd>
*			- notes               <string>
*
********************************************************************************
*
*- settings     <array>
*    - addons      <array>  Change the active state of addons
*        - <addon name>  <bool>  Activate or deactivate one addon
*
********************************************************************************
*
*- coupons         <array>
*
********************************************************************************
**/

/**
 * Class that handles Export functions.
 *
 * @since  1.0.0
 * @package Membership2
 * @subpackage Model
 */
class MS_Model_Export_Settings extends MS_Model {

	/**
	 * Identifier for this Import source
	 *
	 * @since  1.0.0
	 */
	const KEY = 'membership2';

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
		$settings = MS_Factory::load( 'MS_Model_Settings' );
		return ! empty( $settings->import[ self::KEY ] );
	}

	/**
	 * Main entry point: Handles the export action.
	 *
	 * This task will exit the current request as the result will be a download
	 * and no HTML page that is displayed.
	 *
	 * @since  1.0.0
	 */
	public function process() {

		$data 					= (object) array();
		$data->source_key 		= self::KEY;
		$data->type 			= 'settings';
		$data->source 			= 'Membership2';
		$data->plugin_version 	= MS_PLUGIN_VERSION;
		$data->export_time 		= date( 'Y-m-d H:i' );
		$data->notes 			= array(
			__( 'Exported data:', 'membership2' ),
			__( '- Memberships (without protection rules)', 'membership2' ),
			__( '- Members (including Stripe/Authorize payment settings)', 'membership2' ),
			__( '- Subscriptions (link between Members and Memberships)', 'membership2' ),
			__( '- Invoices', 'membership2' ),
		);

		$data->memberships 		= array();

		// Export the base membership (i.e. the Membership2 settings)
		$membership 			= MS_Model_Membership::get_base();
		$data->memberships[] 	= $this->export_membership( $membership );

		// Export all memberships.
		$memberships 			= MS_Model_Membership::get_memberships( array( 'post_parent' => 0 ) );
		foreach ( $memberships as $membership ) {
			$data->memberships[] = $this->export_membership( $membership );
		}

		// Export the members.
		$members 				= MS_Model_Member::get_members();
		$data->members = array();
		foreach ( $members as $member ) {
			if ( ! $member->is_member ) { continue; }
			$data->members[] 	= $this->export_member( $member );
		}

		// Export plugin settings.
		$obj 					= array();
		$data->settings 		= $this->export_settings();

		if ( MS_IS_PRO == true && class_exists( 'MS_Addon_Coupon_Model' ) ){
			// Export Coupons.
			$coupons 			= MS_Addon_Coupon_Model::get_coupons( array( 'nopaging' => true ) );
			$data->coupons 		= array();
			foreach ( $coupons as $coupon ) {
				if ( intval( $coupon->max_uses ) <= intval( $coupon->used ) ) { continue; }
				$data->coupons[] = $this->export_coupon( $coupon );
			}
		}
		$milliseconds = round( microtime( true ) * 1000 );
		mslib3()->net->file_download( json_encode( $data ), $milliseconds . '_membership2-settings-export.json' );
	}


	/**
	 * Export specific data.
	 *
	 * @since  1.0.0
	 * @param  MS_Model_Membership $src
	 * @return object Export data
	 */
	protected function export_membership( $src ) {
		$obj 				= (object) array();
		$obj->id 			= $this->exp_id( 'membership', $src->id );
		$obj->name			= $src->name;
		$obj->description 	= $src->description;
		$obj->type 			= $src->type;
		$obj->active 		= (bool) $src->active;
		$obj->private 		= (bool) $src->private;
		$obj->free 			= (bool) $src->is_free;

		if ( ! $obj->free ) {
			$obj->price = $src->price;
			$obj->trial = (bool) $src->trial_period_enabled;

			switch ( $src->payment_type ) {
				case MS_Model_Membership::PAYMENT_TYPE_FINITE:
					$obj->payment_type 	= 'finite';
					$obj->period_unit 	= $src->period['period_unit'];
					$obj->period_type 	= $src->period['period_type'];
					break;

				case MS_Model_Membership::PAYMENT_TYPE_DATE_RANGE:
					$obj->payment_type 	= 'date';
					$obj->period_start 	= $src->period_date_start;
					$obj->period_end 	= $src->period_date_end;
					break;

				case MS_Model_Membership::PAYMENT_TYPE_RECURRING:
					$obj->payment_type 		= 'recurring';
					$obj->period_unit 		= $src->pay_cycle_period['period_unit'];
					$obj->period_type 		= $src->pay_cycle_period['period_type'];
					$obj->period_repetition = $src->pay_cycle_repetition;
					break;

				default:
					$obj->payment_type 		= 'permanent';
					break;
			}

			if ( $obj->trial ) {
				$obj->trial_price 		= $src->trial_price;
				$obj->trial_period_unit = $src->trial_period['period_unit'];
				$obj->trial_period_type = $src->trial_period['period_type'];
			}
		}

		return apply_filters( 'ms_export/export_membership', $obj, $src, $this );
	}

	/**
	 * Export specific data.
	 *
	 * @since  1.0.0
	 * @param  MS_Model_Member $src
	 * @return object Export data
	 */
	protected function export_member( $src ) {
		$obj 			= (object) array();
		$obj->id 		= $this->exp_id( 'user', $src->username );
		$obj->email 	= $src->email;
		$obj->username 	= $src->username;

		$gw_stripe 		= MS_Gateway_Stripe::ID;
		$gw_auth 		= MS_Gateway_Authorize::ID;
		$obj->payment 	= array(
			// Stripe.
			'stripe_card_exp' 				=> $src->get_gateway_profile( $gw_stripe, 'card_exp' ),
			'stripe_card_num' 				=> $src->get_gateway_profile( $gw_stripe, 'card_num' ),
			'stripe_customer' 				=> $src->get_gateway_profile( $gw_stripe, 'customer_id' ),

			// Authorize.
			'authorize_card_exp' 			=> $src->get_gateway_profile( $gw_auth, 'card_exp' ),
			'authorize_card_num' 			=> $src->get_gateway_profile( $gw_auth, 'card_num' ),
			'authorize_cim_profile' 		=> $src->get_gateway_profile( $gw_auth, 'cim_profile_id' ),
			'authorize_cim_payment_profile' => $src->get_gateway_profile( $gw_auth, 'cim_payment_profile_id' ),
		);

		$obj->subscriptions = array();
		foreach ( $src->subscriptions as $registration ) {
			$obj->subscriptions[] = $this->export_relationship( $registration );
		}

		return apply_filters( 'ms_export/export_member', $obj, $src, $this );
	}

	/**
	 * Export specific data.
	 *
	 * @since  1.0.0
	 * @param  MS_Model_Relationship $src
	 * @return object Export data
	 */
	protected function export_relationship( $src ) {
		$obj 				= (object) array();
		$obj->id 			= $this->exp_id( 'relationship', $src->id );
		$obj->membership 	= $this->exp_id( 'membership', $src->membership_id );
		$obj->status 		= $src->status;
		$obj->gateway 		= $src->gateway_id;
		$obj->start 		= $src->start_date;
		$obj->end 			= $src->expire_date;

		$obj->trial_finished = $src->trial_period_completed;
		if ( ! $obj->trial_finished ) {
			$obj->trial_end = $src->trial_expire_date;
		}

		$obj->invoices 		= array();
		$invoices 			= $src->get_invoices();
		foreach ( $invoices as $invoice ) {
			$obj->invoices[] = $this->export_invoice( $invoice );
		}

		return apply_filters( 'ms_export/export_relationship', $obj, $src, $this );
	}

	/**
	 * Export specific data.
	 *
	 * @since  1.0.0
	 * @param  MS_Model_Invoice $src
	 * @return object Export data
	 */
	protected function export_invoice( $src ) {
		$obj 					= (object) array();
		$obj->id 				= $this->exp_id( 'invoice', $src->id );
		$obj->invoice_number 	= $src->invoice_number;
		$obj->external_id 		= $src->external_id;
		$obj->gateway 			= $src->gateway_id;
		$obj->status 			= $src->status;

		$obj->coupon 			= $this->exp_id( 'coupon', $src->coupon_id );
		$obj->currency 			= $src->currency;
		$obj->amount 			= $src->amount;
		$obj->discount 			= $src->discount;
		$obj->discount2 		= $src->pro_rate;
		$obj->total 			= $src->total;

		$obj->for_trial 		= (bool) $src->trial_period;
		$obj->due 				= $src->due_date;
		$obj->notes 			= $src->notes;

		return apply_filters( 'ms_export/export_invoice', $obj, $src, $this );
	}

	/**
	 * Export specific data.
	 *
	 * @since  1.0.0
	 * @param  object $src
	 * @return object Export data
	 */
	protected function export_coupon( $src ) {
		$obj 			= (object) array();
		$obj->id 		= $this->exp_id( 'coupon', $src->code );
		$obj->code 		= $src->code;
		$obj->type 		= $src->discount_type;
		$obj->discount 	= $src->discount;
		$obj->start 	= $src->start_date;
		$obj->end 		= $src->expire_date;

		if ( $src->membership_id ) {
			$obj->membership = $this->exp_id( 'membership', $src->membership_id );
		}

		$obj->max_uses = intval( $src->max_uses ) - intval( $src->used );

		return apply_filters( 'ms_export/export_coupon', $obj, $src, $this );
	}

	/**
	 * Export specific data.
	 *
	 * @since  1.0.0
	 * @return object Export data
	 */
	protected function export_settings() {
		$src 					= MS_Factory::load( 'MS_Model_settings' );

		$obj 					= (object) array();
		$obj->enabled 			= $src->plugin_enabled;
		$obj->hide_toolbar 		= $src->hide_admin_bar;
		$obj->currency 			= $src->currency;
		$obj->invoice_sender 	= $src->invoice_sender_name;

		return apply_filters( 'ms_export/export_settings', $obj, $src );
	}

	/**
	 * Returns a static export-ID for the given type.
	 *
	 * The export-ID will be same during this request but may change in the next
	 * export. This ID ensures that all links inside the export file are valid
	 * but that we do not use actual WordPress IDs.
	 *
	 * @since  1.0.0
	 * @param  string $type Type
	 * @param  int $internal_id WordPress ID
	 * @return int Export-ID
	 */
	protected function exp_id( $type, $internal_id ) {
		static $Counter = 10000;
		static $Ids 	= array();

		$Ids[$type] 	= mslib3()->array->get( $Ids[$type] );
		if ( ! isset( $Ids[$type][$internal_id] ) ) {
			$Ids[$type][$internal_id] = $Counter;
			$Counter += 1;
		}

		return $Ids[$type][$internal_id];
	}

}
?>