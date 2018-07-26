<?php
/**
 * Controller for managing Plugin Shortcodes.
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Controller
 */
class MS_Controller_Shortcode extends MS_Controller {

	/**
	 * Prepare the shortcode hooks.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		parent::__construct();
		$this->run_action( 'init', 'init' );
        // Enqueue scripts.
		$this->add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );
	}

	/**
	 * Initialize the Shortcodes after we have determined the current user.
	 *
	 * @since  1.0.0
	 */
	public function init() {
		// By default assume no content for the protected-content code
		add_shortcode(
			MS_Helper_Shortcode::SCODE_PROTECTED,
			array( $this, '__return_null' )
		);

		if ( MS_Plugin::is_enabled() ) {
			add_shortcode(
				MS_Helper_Shortcode::SCODE_REGISTER_USER,
				array( $this, 'membership_register_user' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_SIGNUP,
				array( $this, 'membership_signup' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_MS_TITLE,
				array( $this, 'membership_title' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_MS_PRICE,
				array( $this, 'membership_price' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_MS_BUY,
				array( $this, 'membership_buy' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_MS_DETAILS,
				array( $this, 'membership_details' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_LOGIN,
				array( $this, 'membership_login' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_LOGOUT,
				array( $this, 'membership_logout' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_MS_ACCOUNT,
				array( $this, 'membership_account' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_MS_ACCOUNT_LINK,
				array( $this, 'membership_account_link' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_MS_INVOICE,
				array( $this, 'membership_invoice' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_NOTE,
				array( $this, 'ms_note' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_GREEN_NOTE,
				array( $this, 'ms_green_note' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_RED_NOTE,
				array( $this, 'ms_red_note' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_USER,
				array( $this, 'show_to_user' )
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_MEMBER_INFO,
				array( $this, 'ms_member_info' )
			);

			if ( MS_Model_Member::is_normal_admin() ) {
				add_shortcode(
					MS_Rule_Shortcode_Model::PROTECT_CONTENT_SHORTCODE,
					array( 'MS_Rule_Shortcode_Model', 'debug_protect_content_shortcode')
				);
			}
		} else {
			$shortcodes = array(
				MS_Helper_Shortcode::SCODE_REGISTER_USER,
				MS_Helper_Shortcode::SCODE_SIGNUP,
				MS_Helper_Shortcode::SCODE_MS_TITLE,
				MS_Helper_Shortcode::SCODE_MS_PRICE,
				MS_Helper_Shortcode::SCODE_MS_DETAILS,
				MS_Helper_Shortcode::SCODE_LOGIN,
				MS_Helper_Shortcode::SCODE_LOGOUT,
				MS_Helper_Shortcode::SCODE_MS_ACCOUNT,
				MS_Helper_Shortcode::SCODE_MS_ACCOUNT_LINK,
				MS_Helper_Shortcode::SCODE_MS_INVOICE,
				MS_Helper_Shortcode::SCODE_NOTE,
				MS_Helper_Shortcode::SCODE_GREEN_NOTE,
				MS_Helper_Shortcode::SCODE_RED_NOTE,
			);

			foreach ( $shortcodes as $shortcode ) {
				add_shortcode( $shortcode, array( $this, 'ms_no_value' ) );
			}

			add_shortcode(
				MS_Rule_Shortcode_Model::PROTECT_CONTENT_SHORTCODE,
				array( $this, 'hide_shortcode')
			);

			add_shortcode(
				MS_Helper_Shortcode::SCODE_USER,
				array( $this, 'hide_shortcode')
			);
		}
	}

	/**
	 * Set up the protected-content shortcode to display the protection message.
	 *
	 * This function is only called from the Frontend-Controller when the
	 * Membership Page "Membership2" is displayed.
	 *
	 * @since  1.0.0
	 */
	public function page_is_protected() {
		remove_shortcode(
			MS_Helper_Shortcode::SCODE_PROTECTED,
			array( $this, '__return_null' )
		);

		add_shortcode(
			MS_Helper_Shortcode::SCODE_PROTECTED,
			array( $this, 'protected_content' )
		);
	}


	/*========================================*\
	============================================
	==                                        ==
	==           SHORTCODE HANDLERS           ==
	==                                        ==
	============================================
	\*========================================*/



	/**
	 * Membership register callback function.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function membership_register_user( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_REGISTER_USER );

		mslib3()->array->equip_request(
			'first_name',
			'last_name',
			'username',
			'email',
			'membership_id'
		);

		$data = apply_filters(
			'ms_controller_shortcode_membership_register_user_atts',
			shortcode_atts(
				array(
					'first_name' 		=> substr( trim( $_REQUEST['first_name'] ), 0, 50 ),
					'last_name' 		=> substr( trim( $_REQUEST['last_name'] ), 0, 50 ),
					'username' 			=> substr( trim( $_REQUEST['username'] ), 0, 50 ),
					'email' 			=> substr( trim( $_REQUEST['email'] ), 0, 50 ),
					'membership_id' 	=> trim( $_REQUEST['membership_id'] ),
					'label_first_name' 	=> __( 'First Name', 'membership2' ),
					'label_last_name' 	=> __( 'Last Name', 'membership2' ),
					'label_username' 	=> __( 'Choose a Username', 'membership2' ),
					'label_email' 		=> __( 'Email Address', 'membership2' ),
					'label_password' 	=> __( 'Password', 'membership2' ),
					'label_password2' 	=> __( 'Confirm Password', 'membership2' ),
					'label_register' 	=> __( 'Register My Account', 'membership2' ),
					'hint_first_name' 	=> '',
					'hint_last_name' 	=> '',
					'hint_username' 	=> '',
					'hint_email' 		=> '',
					'hint_password' 	=> '',
					'hint_password2' 	=> '',
					'title' 			=> __( 'Create an Account', 'membership2' ),
					'loginlink' 		=> true,
				),
				$atts
			)
		);
		$data['action'] 	= 'register_user';
		$data['step'] 		= MS_Controller_Frontend::STEP_REGISTER_SUBMIT;
		$data['loginlink'] 	= mslib3()->is_true( $data['loginlink'] );

		$view 				= MS_Factory::create( 'MS_View_Shortcode_RegisterUser' );
		$view->data 		= apply_filters( 'ms_view_shortcode_registeruser_data', $data, $this );

		return $view->to_html();
	}

	/**
	 * Membership signup callback function.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function membership_signup( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_SIGNUP );

		$data = apply_filters(
			'ms_controller_shortcode_membership_signup_atts',
			shortcode_atts(
				array(
					MS_Helper_Membership::MEMBERSHIP_ACTION_SIGNUP . '_text' 	=> __( 'Signup', 'membership2' ),
					MS_Helper_Membership::MEMBERSHIP_ACTION_MOVE . '_text' 		=> __( 'Change', 'membership2' ),
					MS_Helper_Membership::MEMBERSHIP_ACTION_CANCEL . '_text' 	=> __( 'Cancel', 'membership2' ),
					MS_Helper_Membership::MEMBERSHIP_ACTION_RENEW . '_text' 	=> __( 'Renew', 'membership2' ),
					MS_Helper_Membership::MEMBERSHIP_ACTION_PAY . '_text' 		=> __( 'Complete Payment', 'membership2' ),
				),
				$atts
			)
		);

		$member 				= MS_Model_Member::get_current_member();
		$data['member'] 		= $member;
		$data['subscriptions'] 	= array();
		$exclude 				= array();

		if ( $member->is_valid() ) {
			// Get member's memberships, including pending relationships.
			$data['subscriptions'] = MS_Model_Relationship::get_subscriptions(
				array(
					'user_id' 	=> $data['member']->id,
					'status' 	=> 'valid',
				)
			);

			foreach ( $data['subscriptions'] as $key => $subscription ) {
				$exclude[] = $subscription->membership_id;
				if ( ! $member->can_subscribe_to( $subscription->membership_id ) ) {
					unset( $data['subscriptions'][$key] );
				}
			}
		}

		$memberships = MS_Model_Membership::get_signup_membership_list(
			null,
			$exclude
		);

		if ( ! $member->is_valid() || ! $member->has_membership() ) {
			foreach( $memberships as $key => $membership ) {
				if( isset( $membership->update_denied['guest'] ) && mslib3()->is_true( $membership->update_denied['guest'] ) ) {
					unset( $memberships[$key] );
				}
			}
		}


		$data['memberships'] = $memberships;
		$move_from_ids = array();

		// When Multiple memberships is not enabled, a member should move to another membership.
		if ( ! MS_Model_Addon::is_enabled( MS_Model_Addon::ADDON_MULTI_MEMBERSHIPS ) ) {
			// Membership Relationship status which can move to another one
			$valid_status = array(
				MS_Model_Relationship::STATUS_TRIAL,
				MS_Model_Relationship::STATUS_ACTIVE,
				MS_Model_Relationship::STATUS_EXPIRED,
			);

			foreach ( $data['member']->subscriptions as $subscription ) {
				if ( $subscription->is_system() ) { continue; }

				if ( in_array( $subscription->status, $valid_status ) ) {
					$move_from_ids[] = $subscription->membership_id;
				}
			}
			foreach ( $data['memberships'] as $key => $membership ) {
				$data['memberships'][$key]->_move_from = $move_from_ids;
			}
		} else {
			foreach ( $data['memberships'] as $key => $membership ) {
				$move_from_ids = $member->cancel_ids_on_subscription(
					$membership->id
				);

				$data['memberships'][$key]->_move_from = $move_from_ids;
			}
		}

		$data['action'] = MS_Helper_Membership::MEMBERSHIP_ACTION_SIGNUP;
		$data['step'] 	= MS_Controller_Frontend::STEP_PAYMENT_TABLE;

		$view 		= MS_Factory::create( 'MS_View_Shortcode_MembershipSignup' );
		$view->data = apply_filters(
			'ms_view_shortcode_membershipsignup_data',
			$data,
			$this
		);

		return $view->to_html();
	}

	/**
	 * Membership title shortcode callback function.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function membership_title( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_MS_TITLE );

		$code = '';

		$data = apply_filters(
			'ms_controller_shortcode_membership_title_atts',
			shortcode_atts(
				array(
					'id' 	=> 0,
					'label' => __( 'Membership title:', 'membership2' ),
					'title' => '', // deprecated @since  1.0.0
				),
				$atts
			)
		);
		extract( $data );

		if ( ! empty( $id ) ) {
			$membership = MS_Factory::load( 'MS_Model_Membership', $id );
			$code 		= sprintf(
				'%1$s %2$s',
				$label,
				$membership->name
			);

			$code = trim( $code );
		} else {
			$code = $title;
		}

		$code = sprintf(
			'<span class="ms-membership-title ms-membership-%1$s">%2$s</span>',
			esc_attr( $id ),
			$code
		);

		return apply_filters(
			'ms_controller_shortcode_membership_title',
			$code,
			$atts,
			$this
		);
	}

	/**
	 * Membership price shortcode callback function.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function membership_price( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_MS_PRICE );

		$code = 0;

		$data = apply_filters(
			'ms_controller_shortcode_membership_price_atts',
			shortcode_atts(
				array(
					'id' 		=> 0,
					'currency' 	=> true,
					'label' 	=> __( 'Membership price:', 'membership2' ),
				),
				$atts
			)
		);
		extract( $data );

		if ( ! empty( $id ) ) {
			if ( mslib3()->is_true( $currency ) ) {
				$settings = MS_Factory::load( 'MS_Model_Settings' );
				$currency = $settings->currency;
			} else {
				$currency = '';
			}

			$membership = MS_Factory::load( 'MS_Model_Membership', $id );
			$code = sprintf(
				'%1$s %2$s %3$s',
				$label,
				$currency,
				$membership->total_price
			);

			$code = trim( $code );
		}

		$code = sprintf(
			'<span class="ms-membership-price ms-membership-%1$s price">%2$s</span>',
			esc_attr( $id ),
			$code
		);

		do_action( 'ms_show_prices' );
		return apply_filters(
			'ms_controller_shortcode_membership_price',
			$code,
			$atts,
			$this
		);
	}

	/**
	 * Buy membership button.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function membership_buy( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_MS_BUY );

		$code = '';

		$data = apply_filters(
			'ms_controller_shortcode_membership_buy_atts',
			shortcode_atts(
				array(
					'id' 	=> 0,
					'label' => __( 'Signup', 'membership2' ),
				),
				$atts
			)
		);
		extract( $data );

		if ( ! empty( $id ) ) {
			$membership = MS_Factory::load( 'MS_Model_Membership', $id );

			if ( ! $membership->active ){
				return __( 'Sorry! The membership you are trying to register is not active.', 'membership2' );
			}

			$data['action'] = MS_Helper_Membership::MEMBERSHIP_ACTION_SIGNUP;
			$data['step'] 	= MS_Controller_Frontend::STEP_PAYMENT_TABLE;

			$view 		= MS_Factory::create( 'MS_View_Shortcode_MembershipSignup' );
			$view->data = apply_filters(
				'ms_view_shortcode_membershipsignup_data',
				$data,
				$this
			);

			$code = $view->signup_form( $membership, $label );
		}

		$code = sprintf(
			'<span class="ms-membership-buy ms-membership-%1$s">%2$s</span>',
			esc_attr( $id ),
			$code
		);

		return apply_filters(
			'ms_controller_shortcode_membership_buy',
			$code,
			$atts,
			$this
		);
	}

	/**
	 * Membership details shortcode callback function.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function membership_details( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_MS_DETAILS );

		$code = '';

		$data = apply_filters(
			'ms_controller_shortcode_membership_details_atts',
			shortcode_atts(
				array(
					'id' 	=> 0,
					'label' => __( 'Membership details:', 'membership2' ),
				),
				$atts
			)
		);
		extract( $data );

		if ( ! empty( $id ) ) {
			$membership = MS_Factory::load( 'MS_Model_Membership', $id );
			$code = sprintf(
				'%1$s %2$s',
				$label,
				$membership->get_description()
			);

			$code = trim( $code );
		}

		$code = sprintf(
			'<span class="ms-membership-details ms-membership-%1$s">%2$s</span>',
			esc_attr( $id ),
			$code
		);

		return apply_filters(
			'ms_controller_shortcode_membership_details',
			$code,
			$atts,
			$this
		);
	}

	/**
	 * Display the "Membership2" message.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function protected_content( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_PROTECTED );

		global $post;

		$setting 	= MS_Plugin::instance()->settings;
		$member 	= MS_Model_Member::get_current_member();

		if ( isset( $_REQUEST['membership_id'] ) ) {

			$has_protection_sc = MS_Helper_Shortcode::has_shortcode(
				MS_Helper_Shortcode::SCODE_PROTECTED,
				$post->post_content
			);

			if ( $has_protection_sc ) {

				$protection_msg = $setting->get_protection_message(
					MS_Model_Settings::PROTECTION_MSG_SHORTCODE,
					$_REQUEST['membership_id']
				);

			} else {

				$protection_msg = $setting->get_protection_message(
					MS_Model_Settings::PROTECTION_MSG_CONTENT,
					$_REQUEST['membership_id']
				);

			}

		} else {
			if ( count( $member->subscriptions ) ) {

				$sub = $member->get_subscription( 'priority' );
				$protection_msg = $setting->get_protection_message(
					MS_Model_Settings::PROTECTION_MSG_CONTENT,
					$sub->membership_id
				);
			} else {
				$protection_msg = $setting->get_protection_message(
					MS_Model_Settings::PROTECTION_MSG_CONTENT
				);
			}
		}

		$html = '<div class="ms-protected-content">';
		if ( ! empty( $protection_msg ) ) {
			$html .= $protection_msg;
		}

		if ( ! MS_Model_Member::is_logged_in() ) {
			$has_login_form = MS_Helper_Shortcode::has_shortcode(
				MS_Helper_Shortcode::SCODE_LOGIN,
				$post->post_content
			);

			if ( ! $has_login_form ) {
				$scode = '[' . MS_Helper_Shortcode::SCODE_LOGIN . ']';
				$html .= do_shortcode( $scode );
			}
		}
		$html .= '</div>';

		return apply_filters(
			'ms_controller_shortcode_protected_content',
			$html,
			$this
		);
	}

	/**
	 * Membership login shortcode callback function.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function membership_login( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_LOGIN );

		$data = apply_filters(
			'ms_controller_shortcode_membership_login_atts',
			shortcode_atts(
				array(
					'holder'          => 'div',
					'holderclass'     => 'ms-login-form',
					'item'            => '',
					'itemclass'       => '',
					'postfix'         => '',
					'prefix'          => '',
					'wrapwith'        => '',
					'wrapwithclass'   => '',
					'redirect_login'  => MS_Model_Pages::get_url_after_login(),
					'redirect_logout' => MS_Model_Pages::get_url_after_logout(),
					'header'          => true,
					'register'        => true,
					'title'           => '',
					'show_note'       => true,   // Show the "you are not logged in" note?
					'form'            => '',  // [login|lost|reset|logout]
					'show_labels'     => false,
					'autofocus'       => true,
					'nav_pos'         => 'top', // [top|bottom]

					// form="login"
					'show_remember'   => true,
					'label_username'  => __( 'Username', 'membership2' ),
					'label_password'  => __( 'Password', 'membership2' ),
					'label_remember'  => __( 'Remember Me', 'membership2' ),
					'label_log_in'    => __( 'Log In', 'membership2' ),
					'id_login_form'   => 'loginform',
					'id_username'     => 'user_login',
					'id_password'     => 'user_pass',
					'id_remember'     => 'rememberme',
					'id_login'        => 'wp-submit',
					'value_username'  => '',
					'value_remember'  => false,

					// form="lost"
					'label_lost_username' => __( 'Username or E-mail', 'membership2' ),
					'label_lostpass'      => __( 'Reset Password', 'membership2' ),
					'id_lost_form'        => 'lostpasswordform',
					'id_lost_username'    => 'user_login',
					'id_lostpass'         => 'wp-submit',
					'value_username'      => '',
				),
				$atts
			)
		);

		$data['header'] 		= mslib3()->is_true( $data['header'] );
		$data['register'] 		= mslib3()->is_true( $data['register'] );
		$data['show_note'] 		= mslib3()->is_true( $data['show_note'] );
		$data['show_labels'] 	= mslib3()->is_true( $data['show_labels'] );
		$data['show_remember'] 	= mslib3()->is_true( $data['show_remember'] );
		$data['value_remember'] = mslib3()->is_true( $data['value_remember'] );
		$data['autofocus'] 		= mslib3()->is_true( $data['autofocus'] );

		$view = MS_Factory::create( 'MS_View_Shortcode_Login' );
		$view->data = apply_filters( 'ms_view_shortcode_login_data', $data, $this );

		return $view->to_html();
	}

	/**
	 * Membership logout shortcode callback function.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function membership_logout( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_LOGOUT );

		$data = apply_filters(
			'ms_controller_shortcode_membership_logout_atts',
			shortcode_atts(
				array(
					'holder'        => 'div',
					'holderclass'   => 'ms-logout-form',
					'redirect'      => MS_Model_Pages::get_url_after_logout(),
				),
				$atts
			)
		);

		// The form attribute triggers the logout-link to be displayed.
		$data['form'] 				= 'logout';
		$data['redirect_logout'] 	= $data['redirect'];

		$view = MS_Factory::create( 'MS_View_Shortcode_Login' );
		$view->data = apply_filters( 'ms_view_shortcode_logout_data', $data, $this );

		return $view->to_html();
	}

	/**
	 * Membership account page shortcode callback function.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function membership_account( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_MS_ACCOUNT );

		$data = apply_filters(
			'ms_controller_shortcode_membership_account_atts',
			shortcode_atts(
				array(
					'show_membership' 			=> true,
					'show_membership_change' 	=> true,
					'membership_title' 			=> __( 'Your Membership', 'membership2' ),
					'membership_change_label' 	=> __( 'Change', 'membership2' ),
					'show_profile' 				=> true,
					'show_profile_change' 		=> true,
					'profile_title' 			=> __( 'Personal details', 'membership2' ),
					'profile_change_label' 		=> __( 'Edit', 'membership2' ),
					'show_invoices' 			=> true,
					'limit_invoices' 			=> 10,
					'show_all_invoices' 		=> true,
					'invoices_title' 			=> __( 'Invoices', 'membership2' ),
					'invoices_details_label' 	=> __( 'View all', 'membership2' ),
					'show_activity' 			=> true,
					'limit_activities' 			=> 10,
					'show_all_activities' 		=> true,
					'activity_title'			=> __( 'Activities', 'membership2' ),
					'activity_details_label' 	=> __( 'View all', 'membership2' ),
				),
				$atts
			)
		);

		$data['show_membership'] 		= mslib3()->is_true( $data['show_membership'] );
		$data['show_membership_change'] = mslib3()->is_true( $data['show_membership_change'] );
		$data['show_profile'] 			= mslib3()->is_true( $data['show_profile'] );
		$data['show_profile_change'] 	= mslib3()->is_true( $data['show_profile_change'] );
		$data['show_invoices'] 			= mslib3()->is_true( $data['show_invoices'] );
		$data['show_all_invoices'] 		= mslib3()->is_true( $data['show_all_invoices'] );
		$data['show_activity'] 			= mslib3()->is_true( $data['show_activity'] );
		$data['show_all_activities'] 	= mslib3()->is_true( $data['show_all_activities'] );

		$data['limit_invoices'] 		= absint( $data['limit_invoices'] );
		$data['limit_activities'] 		= absint( $data['limit_activities'] );

		if( ! isset( $data['member'] ) || $data['member']->id != '' ){
			$data['member'] 	= MS_Model_Member::get_current_member();
			$data['membership'] = array();
		}

		$subscriptions = MS_Model_Relationship::get_subscriptions(
			array(
				'user_id' 	=> $data['member']->id,
				'status' 	=> 'all',
			)
		);
		if ( is_array( $subscriptions ) && !empty( $subscriptions ) ) {
			foreach ( $subscriptions as $subscription ) {
				// Do not display system-memberships in Account
				if ( $subscription->is_system() ) { continue; }

				// Do not display deactivated memberships in Account
				if ( $subscription->get_status() == MS_Model_Relationship::STATUS_DEACTIVATED ) { continue; }

				$data['subscription'][] = $subscription;
			}
		}

		$data['invoices'] = MS_Model_Invoice::get_public_invoices(
			$data['member']->id,
			$data['limit_invoices']
		);

		$data['events'] = MS_Model_Event::get_events(
			array(
				'author' 			=> $data['member']->id,
				'posts_per_page' 	=> $data['limit_activities'],
			)
		);

		$view = MS_Factory::create( 'MS_View_Shortcode_Account' );
		$view->data = apply_filters(
			'ms_view_shortcode_account_data',
			$data,
			$this
		);

		return $view->to_html();
	}

	/**
	 * Link to the Membership account page shortcode.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function membership_account_link( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_MS_ACCOUNT_LINK );

		$html = '';

		$data = apply_filters(
			'ms_controller_shortcode_membership_account_link_atts',
			shortcode_atts(
				array(
					'label' => __( 'Visit your account page for more information.', 'membership2' ),
				),
				$atts
			)
		);

		$html = sprintf(
			'<a href="%1$s">%2$s</a>',
			MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_ACCOUNT ),
			$data['label']
		);

		return apply_filters(
			'ms_controller_shortcode_protected_content',
			$html,
			$this
		);
	}

	/**
	 * Membership invoice shortcode callback function.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function membership_invoice( $atts ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_MS_INVOICE );

		$data = apply_filters(
			'ms_controller_shortcode_invoice_atts',
			shortcode_atts(
				array(
					'post_id' 		=> 0,
					'id' 			=> 0,
					'pay_button' 	=> 1,
				),
				$atts,
				MS_Helper_Shortcode::SCODE_MS_INVOICE
			)
		);

		if ( ! empty( $data['id'] ) ) {
			$data['post_id'] = $data['id'];
		}

		if ( ! empty( $data['post_id'] ) ) {
			$invoice 					= MS_Factory::load( 'MS_Model_Invoice', $data['post_id'] );
			$subscription 				= MS_Factory::load( 'MS_Model_Relationship', $invoice->ms_relationship_id );

			$data['invoice'] 			= $invoice;
			$data['member'] 			= MS_Factory::load( 'MS_Model_Member', $invoice->user_id );
			$data['ms_relationship'] 	= $subscription;
			$data['membership'] 		= $subscription->get_membership();
			$data['gateway'] 			= MS_Model_Gateway::factory( $invoice->gateway_id );

			$view = MS_Factory::create( 'MS_View_Shortcode_Invoice' );
			$view->data = apply_filters(
				'ms_view_shortcode_invoice_data',
				$data,
				$this
			);

			return $view->to_html();
		}
	}

	/**
	 * Text note shortcode callback function.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function ms_note( $atts, $content = '' ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_NOTE );

		mslib3()->ui->css( 'ms-styles' );

		$atts = apply_filters(
			'ms_controller_shortcode_note_atts',
			shortcode_atts(
				array(
					'type' 	=> 'info',
					'class' => '',
				),
				$atts,
				MS_Helper_Shortcode::SCODE_NOTE
			)
		);

		$class = $atts['class'];

		switch ( $atts['type'] ) {
			case 'info':
			case 'success':
				$class .= ' ms-alert-success';
				break;

			case 'error':
			case 'warning':
				$class .= ' ms-alert-error';
				break;
		}

		/**
		 * The $content of the notice is translated!
		 * This gives translators the option to translate even custom messages
		 * that are entered into the shortcode!
		 *
		 * @since  1.0.0
		 */
		$content = sprintf(
			'<p class="ms-alert-box %1$s">%2$s</p> ',
			$class,
			__( $content, 'membership2' )
		);

		$content = do_shortcode( $content );

		return apply_filters(
			'ms_controller_shortcode_ms_note',
			$content,
			$this
		);
	}

	/**
	 * Display a green text note.
	 *
	 * @since  1.0.0
	 * @deprecated  since 1.0.4.5
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function ms_green_note( $atts, $content = '' ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_GREEN_NOTE );

		$content = $this->ms_note( array( 'type' => 'info' ), $content );

		return apply_filters(
			'ms_controller_shortcode_ms_green_note',
			$content,
			$this
		);
	}

	/**
	 * Display a red text note.
	 *
	 * @since  1.0.0
	 * @deprecated  since 1.0.4.5
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function ms_red_note( $atts, $content = '' ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_RED_NOTE );

		$content = $this->ms_note( array( 'type' => 'warning' ), $content );

		return apply_filters(
			'ms_controller_shortcode_ms_red_note',
			$content,
			$this
		);
	}

	/**
	 * Shortcode callback: Show message only to certain users.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function show_to_user( $atts, $content = '' ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_USER );

		$data = apply_filters(
			'ms_controller_shortcode_show_to_user_atts',
			shortcode_atts(
				array(
					'type'     => 'loggedin',
					'msg'      => '',
				),
				$atts
			)
		);

		extract( $data );
		$access = false;

		$user_type = 'guest';
		if ( is_user_logged_in() ) {
			$user_type = 'loggedin';

			if ( MS_Model_Member::is_admin_user() ) {
				$user_type = 'admin';
			}
		}

		$class = 'ms-user-is-' . $user_type;

		switch ( $type ) {
			case 'all':
				$access = true;
				break;

			case 'loggedin':
			case 'login':
				$access = in_array( $user_type, array( 'loggedin', 'admin' ) );
				break;

			case 'guest':
				$access = ($user_type == 'guest' );
				break;

			case 'admin':
				$access = ( $user_type == 'admin' );
				break;

			case 'non-admin':
				$access = ( $user_type != 'admin' );
				break;
		}

		if ( ! $access ) {
			$content = $msg;
			$class .= ' ms-user-not-' . $type;
		}

		$content = sprintf(
			'<div class="ms-user %1$s">%2$s</div>',
			esc_attr( $class ),
			do_shortcode( $content )
		);

		return apply_filters(
			'ms_controller_shortcode_show_to_user',
			$content,
			$this
		);
	}

	/**
	 * Shortcode callback: Show member infos.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed[] $atts Shortcode attributes.
	 */
	public function ms_member_info( $atts, $content = '' ) {
		MS_Helper_Shortcode::did_shortcode( MS_Helper_Shortcode::SCODE_MEMBER_INFO );

		$data = apply_filters(
			'ms_controller_shortcode_member_info_atts',
			shortcode_atts(
				array(
					'value' 			=> 'fullname',
					'before' 			=> '<span>',
					'after' 			=> '</span>',
					'default' 			=> '',
					'custom_field' 		=> '',  // used for: custom
					'list_before' 		=> '', // used for: membership
					'list_after' 		=> '', // used for: membership
					'list_separator' 	=> ', ', // used for: membership
					'user' 				=> 0, // user-id; 0 = current user
				),
				$atts
			)
		);

		$data['user'] = intval( $data['user'] );
		if ( $data['user'] < 1 ) {
			$data['user'] = get_current_user_id();
		}
		$member = MS_Factory::load( 'MS_Model_Member', $data['user'] );
		$content = '';

		switch ( $data['value'] ) {
			case 'email':
				$content = $member->email;
				break;

			case 'firstname':
			case 'first name':
				$content = $member->first_name;
				break;

			case 'lastname':
			case 'last name':
				$content = $member->last_name;
				break;

			case 'fullname':
			case 'full name':
				$content = $member->first_name . ' ' . $member->last_name;
				break;

			case 'memberships':
			case 'membership':
				$ids = $member->get_membership_ids();
				$content = array();
				foreach ( $ids as $id ) {
					$membership = MS_Factory::load( 'MS_Model_Membership', $id );
					if ( $membership->is_system() ) { continue; }
					$content[] = $membership->name;
				}
				break;

			case 'custom':
				$content = $member->get_custom_data( $data['custom_field'] );
				break;
		}

		if ( is_array( $content ) ) {
			if ( $content ) {
				$content =
					$data['list_before'] .
					implode( $data['list_separator'], $content ) .
					$data['list_after'];
			} else {
				$content = '';
			}
		} else {
			$content = (string) $content;
		}

		$content = trim( $content );
		if ( $content ) {
			$content = $data['before'] .
				$content .
				$data['after'];
		} else {
			$content = $data['default'];
		}

		return apply_filters(
			'ms_controller_shortcode_member_info',
			$content,
			$this
		);
	}


	/**
	 * Special Shortcode Callback: Replace shortcodes with empty value.
	 *
	 *     All Shortcodes use this callback function
	 *     when Content Protection is DISABLED!
	 *
	 * @since  1.0.0
	 * @param  mixed[] $atts Shortcode attributes.
	 * @param  string $content
	 * @return string
	 */
	public function ms_no_value( $atts, $content = '' ) {
		static $Done = false;

		if ( $Done ) { return ''; }
		$Done = true;

		if ( MS_Model_Member::is_admin_user() ) {
			$content = sprintf(
				'<p class="ms-alert-box ms-alert-error ms-unprotected">%s<br /><br /><em>(%s)</em></p>',
				__(
					'Content Protection is disabled. Please enable the protection to see this shortcode.',
					'membership2'
				),
				__(
					'This message is only displayed to Site Administrators',
					'membership2'
				)
			);
		} else {
			$content = '';
		}

		return apply_filters(
			'ms_controller_shortcode_ms_no_value',
			$content,
			$this
		);
	}

	/**
	 * Special Shortcode Callback: Strip the shortcode tag without changing the
	 * original content.
	 *
	 * This is used for Admin users to strip all content-protection tags.
	 *
	 * @since  1.0.0
	 * @param  mixed[] $atts Shortcode attributes.
	 * @param  string $content
	 * @return string
	 */
	public function hide_shortcode( $atts, $content = '' ) {
		return do_shortcode( $content );
	}

    public function enqueue_scripts() {
        $data['ms_init'][] = 'frontend_register';
	    mslib3()->ui->data( 'ms_data', $data );
    }

}