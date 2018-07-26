<?php
/**
 * Creates the controller for Membership/User registration.
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Controller
 */
class MS_Controller_Frontend extends MS_Controller {

	/**
	 * Signup/register process step constants.
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	const STEP_CHOOSE_MEMBERSHIP 	= 'choose_membership';
	const STEP_REGISTER_FORM 		= 'register';
	const STEP_REGISTER_FORM_ALT 	= 'register_form';
	const STEP_REGISTER_SUBMIT 		= 'register_submit';
	const STEP_PAYMENT_TABLE 		= 'payment_table';
	const STEP_GATEWAY_FORM 		= 'gateway_form';
	const STEP_PROCESS_PURCHASE 	= 'process_purchase';

	/**
	 * AJAX action constants.
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	const ACTION_EDIT_PROFILE 			= 'edit_profile';
	const ACTION_VIEW_INVOICES 			= 'view_invoices';
	const ACTION_VIEW_ACTIVITIES		= 'view_activities';
	const ACTION_VIEW_RESETPASS 		= 'rp';
	const ACTION_VIEW_ACTIVATEACCOUNT 	= 'ac'; //activate account

	/**
	 * Whether Membership2 will handle the registration process or not.
	 * This should not be changed directly but via filter ms_frontend_handle_registration
	 *
	 * @since  1.0.0
	 *
	 * @var bool
	 */
	static public $handle_registration = true;

	/**
	 * User registration errors.
	 * This variable is used by the class MS_View_Shortcode_RegisterUser.
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	static public $register_errors;

	/**
	 * Allowed actions to execute in template_redirect hook.
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	private $allowed_actions = array( 'signup_process', 'register_user', 'check_email' );

	/**
	 * Prepare for Member registration.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		parent::__construct();

		if ( MS_Plugin::is_enabled() ) {
			do_action( 'ms_controller_frontend_construct', $this );

			// Process actions like register new account.
			$this->add_action( 'template_redirect', 'process_actions', 1 );

			// Check if the current page is a Membership Page.
			$this->add_action( 'template_redirect', 'check_for_membership_pages', 2 );

			// Propagates SSL cookies when user logs in.
			$this->add_action( 'wp_login', 'propagate_ssl_cookie', 10, 2 );

			// Enqueue scripts.
			$this->add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );

			// Add classes for all memberships the user is registered to.
			$this->add_filter( 'body_class', 'body_class' );

			// Clears the shortcode memory at the beginning of the_content
			$this->add_filter( 'the_content', 'clear_content_memory', 1 );

			// Compact code for output on the front end.
			add_filter(
				'ms_compact_code',
				array( 'MS_Helper_Html', 'compact_code' )
			);

			/**
			 * This allows WordPress to provide the default register form.
			 *
			 * Set the filter response to FALSE to stop Membership2 from
			 * handling the registration process. WordPress or other plugins can
			 * register users in that case.
			 *
			 * @since  1.0.0
			 */
			self::$handle_registration = apply_filters(
				'ms_frontend_handle_registration',
				true
			);

			if ( self::$handle_registration ) {
				// Set the registration URL to the 'Register' Membership Page.
				if ( !is_multisite() && !is_admin())
					$this->add_filter( 'wp_signup_location', 'signup_location', 999 );
				$this->add_filter( 'register_url', 'signup_location', 999 );
			}

			// Redirect users to their Account page after login.
			$this->add_filter( 'login_redirect', 'login_redirect', 10, 3 );
			$this->add_action( 'wp_logout', 'logout_redirect', 10 );

			if ( ! defined( 'DOING_AJAX' ) ) {
				//Normal WordPress login check
				$this->add_action( 'wp_login', 'handle_verification_code', 10, 2 );
			}

			$this->add_filter( 'login_message', 'login_message', 10, 1 );
		}
	}

	/**
	 * Handle URI actions for registration.
	 *
	 * Matches returned 'action' to method to execute.
	 *
	 * Related Action Hooks:
	 * - template_redirect
	 *
	 * @since  1.0.0
	 */
	public function process_actions() {
		$action = $this->get_action();

		/**
		 * If $action is set, then call relevant method.
		 *
		 * Methods:
		 * @see $allowed_actions property
		 *
		 */
		if ( ! empty( $action )
			&& method_exists( $this, $action )
			&& in_array( $action, $this->allowed_actions )
		) {
			$this->$action();
		}
	}

	/**
	 * Check pages for the presence of Membership special pages.
	 *
	 * Related Action Hooks:
	 * - template_redirect
	 *
	 * @since  1.0.0
	 */
	public function check_for_membership_pages() {
		global $post, $wp_query;

		// For invoice page purchase process
		$fields = array( 'gateway', 'ms_relationship_id', 'step' );

		if ( ! empty( $post )
			&& isset( $post->post_type )
			&& $post->post_type == MS_Model_Invoice::get_post_type()
			&& self::validate_required( $fields )
			&& self::STEP_PROCESS_PURCHASE == $_POST['step']
		) {
			do_action(
				'ms_controller_frontend_signup_process_purchase',
				$this
			);
		}

		$the_page = MS_Model_Pages::current_page();

		if ( $the_page ) {
			// Fix the main query flags for best theme support:
			// Our Membership-Pages are always single pages...

			$wp_query->is_single 	= false;
			$wp_query->is_page 		= true;
			$wp_query->is_singular 	= true;
			$wp_query->is_home 		= false;
			$wp_query->is_frontpage = false;
			$wp_query->tax_query 	= null;

			$the_type = MS_Model_Pages::get_page_type( $the_page );
			switch ( $the_type ) {
				case MS_Model_Pages::MS_PAGE_MEMBERSHIPS:
					if ( ! MS_Model_Member::is_logged_in() ) {
						wp_safe_redirect(
							MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_REGISTER )
						);
						exit;
					}
					if ( MS_Helper_Membership::MEMBERSHIP_ACTION_CANCEL == $this->get_action() ) {
						$this->membership_cancel();
					} else {
						$this->signup_process();
					}
					break;

				case MS_Model_Pages::MS_PAGE_REGISTER:
					$step = $this->get_signup_step();
					if ( self::STEP_CHOOSE_MEMBERSHIP == $step && MS_Model_Member::is_logged_in() ) {
						wp_safe_redirect(
							MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_MEMBERSHIPS )
						);
						exit;
					}
					if ( MS_Helper_Membership::MEMBERSHIP_ACTION_CANCEL == $this->get_action() ) {
						$this->membership_cancel();
					} else {
						$this->signup_process();
					}
					break;

				case MS_Model_Pages::MS_PAGE_ACCOUNT:
					$this->user_account_manager();
					break;

				case MS_Model_Pages::MS_PAGE_PROTECTED_CONTENT:
					// Set up the protection shortcode.
					$scode = MS_Plugin::instance()->controller->controllers['membership_shortcode'];
					$scode->page_is_protected();
					break;

				case MS_Model_Pages::MS_PAGE_REG_COMPLETE:
					// Do nothing...
					break;

				default:
					// Do nothing...
					break;
			}
		}
	}

	/**
	 * Appends classes to the HTML body that identify all memberships that the
	 * current user is registered to. This allows webdesigners to adjust layout
	 * or hide elements based on the membership a user has.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $class Class-names to attach to the body.
	 * @return array Modified class-names to attach to the body.
	 */
	public function body_class( $classes ) {
		$member = MS_Model_Member::get_current_member();
		if ( ! $member->is_logged_in() ) {
			$classes[] = 'ms-guest';
		} else {
			$classes[] = 'ms-member';
			$classes[] = 'ms-member-' . $member->id;
		}

		$info = MS_Plugin::instance()->controller->get_access_info();
		foreach ( $info['memberships'] as $membership_id ) {
			$classes[] = 'ms-' . absint( $membership_id );
		}

		return $classes;
	}

	/**
	 * Clears the shortcode memory at the beginning of each call to the_content.
	 *
	 * This is required when there are several parts of the page that are
	 * rendered via the_content, e.g. a main content and a footer area (this
	 * is a theme-specific scenario). Or if the page contains an excerpt and a
	 * main content block, ...
	 *
	 * @since  1.0.0
	 * @param  string $content The page content
	 * @return string Value of $content (unmodified)
	 */
	public function clear_content_memory( $content ) {
		global $wp_current_filter;
		$reset = false;

		// Correctly handle nesting.
		foreach ( $wp_current_filter as $filter ) {
			if ( 'the_content' === $filter ) {
				if ( $reset ) {
					/*
					 * the_content is called inside the_content.
					 * Don't reset again!
					 * This can happen for example: A shortcode parses the
					 * return code via apply_filters( 'the_content' )
					 */
					$reset = false;
					break;
				} else {
					$reset = true;
				}
			}
		}

		if ( $reset ) {
			MS_Helper_Shortcode::reset_shortcode_usage();
		}

		return $content;
	}

	/**
	 * Handle entire signup process.
	 *
	 * @since  1.0.0
	 */
	public function signup_process() {
		$step 	= $this->get_signup_step();
		$member = MS_Model_Member::get_current_member();

		do_action( 'ms_frontend_register-' . $step );

		switch ( $step ) {
			/**
			 * Initial state.
			 */
			case self::STEP_CHOOSE_MEMBERSHIP:
				// Nothing, simply display Membership page.
				break;

			/**
			 * If not registered.
			 */
			case self::STEP_REGISTER_FORM:
			case self::STEP_REGISTER_FORM_ALT:
				$this->add_filter( 'the_content', 'register_form', 1 );
				break;

			/**
			 * Process user registration.
			 */
			case self::STEP_REGISTER_SUBMIT:
				$this->register_user();
				break;

			/**
			 * Show payment table.
			 *
			 * The payment table is only available if the current user has
			 * permission to subscribe to the specified membership.
			 */
			case self::STEP_PAYMENT_TABLE:
				$add_filter = false;
				if ( ! empty( $_REQUEST['membership_id'] ) ) {
					$membership_id = $_REQUEST['membership_id'];
					if ( $member->can_subscribe_to( $membership_id ) ) {
						$add_filter = true;
					}
				}

				if ( $add_filter ) {
					$this->add_filter( 'the_content', 'payment_table', 1 );
				} else {
					wp_safe_redirect(
						esc_url_raw(
							add_query_arg(
								array( 'step' => self::STEP_CHOOSE_MEMBERSHIP )
							)
						)
					);
				}
				break;

			/**
			 * Show gateway extra form.
			 * Handled by MS_Controller_Gateway.
			 */
			case self::STEP_GATEWAY_FORM:
				do_action(
					'ms_controller_frontend_signup_gateway_form',
					$this
				);
				break;

			/**
			 * Process the purchase action.
			 * Handled by MS_Controller_Gateway.
			 */
			case self::STEP_PROCESS_PURCHASE:
				do_action(
					'ms_controller_frontend_signup_process_purchase',
					$this
				);
				break;

			default:
				MS_Helper_Debug::debug_log( "No handler for step: $step" );
				break;
		}
	}

	/**
	 * Get signup process step (multi step form).
	 *
	 * @since  1.0.0
	 *
	 * @return string The current signup step after validation.
	 */
	private function get_signup_step() {
		static $Valid_Steps = null;
		static $Login_Steps = null;

		if ( empty( $Valid_Steps ) ) {
			$Valid_Steps = apply_filters(
				'ms_controller_frontend_signup_steps',
				array(
					self::STEP_CHOOSE_MEMBERSHIP,
					self::STEP_REGISTER_FORM,
					self::STEP_REGISTER_FORM_ALT,
					self::STEP_REGISTER_SUBMIT,
					self::STEP_PAYMENT_TABLE,
					self::STEP_GATEWAY_FORM,
					self::STEP_PROCESS_PURCHASE,
				)
			);

			// These steps are only available to logged-in users.
			$Login_Steps = apply_filters(
				'ms_controller_frontend_signup_steps_private',
				array(
					self::STEP_PAYMENT_TABLE,
					self::STEP_GATEWAY_FORM,
					self::STEP_PROCESS_PURCHASE,
				)
			);
		}

		mslib3()->array->equip_request( 'step', 'membership_id' );

		if ( in_array( $_REQUEST['step'], $Valid_Steps ) ) {
			$step = $_REQUEST['step'];
		} else {
			// Initial step
			$step = self::STEP_CHOOSE_MEMBERSHIP;
		}

		if ( self::STEP_PAYMENT_TABLE == $step ) {
			if ( ! MS_Model_Membership::is_valid_membership( $_REQUEST['membership_id'] ) ) {
				$step = self::STEP_CHOOSE_MEMBERSHIP;
			}
		}

		if ( self::STEP_CHOOSE_MEMBERSHIP == $step && ! empty( $_GET['membership_id'] ) ) {
			$step = self::STEP_PAYMENT_TABLE;
		}

		if ( ! MS_Model_Member::is_logged_in() && in_array( $step, $Login_Steps ) ) {
			$step = self::STEP_REGISTER_FORM_ALT;
		}

		return apply_filters(
			'ms_controller_frontend_get_signup_step',
			$step,
			$this
		);
	}

	/**
	 * Returns the URL to user registration page.
	 * If Membership2 handles registration we can provide the registration
	 * step via function param $step.
	 *
	 * @since  1.0.0
	 * @param  string $step Empty uses default step (choose_membership).
	 *                      'choose_membership' show list of memberships.
	 *                      'register' shows the registration form.
	 * @return string URL to the registration page.
	 */
	static public function get_registration_url( $step = null ) {
		$url = wp_registration_url();

		if ( self::$handle_registration && ! empty( $step ) ) {
			$url = esc_url_raw( add_query_arg( 'step', $step, $url ) );
		}

		return $url;
	}

	/**
	 * Show register user form.
	 *
	 * Related Filter Hooks:
	 * - the_content
	 *
	 * @since  1.0.0
	 *
	 * @param string $content The page content to filter.
	 * @return string The filtered content.
	 */
	public function register_form( $content ) {
		// Check if the WordPress settings allow user registration.
		if ( ! MS_Model_Member::can_register() ) {
			return __( 'Registration is currently not allowed.', 'membership2' );
		}

		// Do not parse the form when building the excerpt.
		global $wp_current_filter;
		if ( in_array( 'get_the_excerpt', $wp_current_filter ) ) {
			return '';
		}

		/**
		 * Add-ons or other plugins can use this filter to define a completely
		 * different registration form. If this filter returns any content, then
		 * the default form will not be generated
		 *
		 * @since  1.0.0
		 * @var string
		 */
		$custom_code = apply_filters(
			'ms_frontend_custom_registration_form',
			'',
			self::$register_errors,
			$this
		);

		if ( $custom_code ) {
			$content = $custom_code;
		} else {
			remove_filter( 'the_content', 'wpautop' );

			$did_form = MS_Helper_Shortcode::has_shortcode(
				MS_Helper_Shortcode::SCODE_REGISTER_USER,
				$content
			);

			if ( ! $did_form ) {
				$scode = sprintf(
					'[%s]',
					MS_Helper_Shortcode::SCODE_REGISTER_USER
				);
				$reg_form = do_shortcode( $scode );

				if ( ! MS_Model_Member::is_logged_in() ) {
					$content = $reg_form;
				} else {
					$content .= $reg_form;
				}
			}
		}

		return apply_filters(
			'ms_controller_frontend_register_form_content',
			$content,
			$this
		);
	}

	/**
	 * Handles register user submit.
	 *
	 * On validation errors, step back to register form.
	 *
	 * @since  1.0.0
	 */
	public function register_user() {
		do_action( 'ms_controller_frontend_register_user_before', $this );

		if ( ! $this->verify_nonce() ) {
			return;
		}

		try {
			$user = MS_Factory::create( 'MS_Model_Member' );

			// Default WP registration filter
			$fields = apply_filters( 'signup_user_init', $_REQUEST );
			foreach ( $fields as $field => $value ) {
				$user->$field = $value;
			}

			$user->save();

			// Default WP action hook
			do_action( 'signup_finished' );
			do_action( 'ms_controller_frontend_register_user_before_login', $user, $_REQUEST, $this );

			if ( MS_Model_Event::save_event( MS_Model_Event::TYPE_MS_REGISTERED, $user ) ) {
				if ( ! defined( 'MS_DISABLE_WP_NEW_USER_NOTIFICATION' ) ) {
					wp_new_user_notification( $user->id );
				}
			}

			$settings = MS_Factory::load( 'MS_Model_Settings' );
			if ( !$settings->force_registration_verification ) {
				$user->signon_user();

				do_action( 'ms_controller_frontend_register_user_complete', $user, $_REQUEST, $this );

				// Go to membership signup payment form.
				if ( empty( $_REQUEST['membership_id'] ) ) {
					$redirect = esc_url_raw(
						add_query_arg(
							array(
								'step' => self::STEP_CHOOSE_MEMBERSHIP,
							)
						)
					);
				} else {
					$redirect = esc_url_raw(
						add_query_arg(
							array(
								'step' => self::STEP_PAYMENT_TABLE,
								'membership_id' => absint( $_REQUEST['membership_id'] ),
							),
							MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_REGISTER )
						)
					);
				}

				wp_safe_redirect( $redirect );
				exit;
			} else {

				MS_Model_Event::save_event( MS_Model_Event::TYPE_MS_VERIFYACCOUNT, $user );

				if ( empty( $_REQUEST['membership_id'] ) ) {
					$after_redirect = esc_url_raw(
						add_query_arg(
							array(
								'step' => self::STEP_CHOOSE_MEMBERSHIP,
							)
						)
					);
				} else {
					$after_redirect = esc_url_raw(
						add_query_arg(
							array(
								'step' => self::STEP_PAYMENT_TABLE,
								'membership_id' => absint( $_REQUEST['membership_id'] ),
							),
							MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_REGISTER )
						)
					);
				}

				update_user_meta( $user->ID, '_ms_user_activation_redirect_url', $after_redirect );

				$redirect = esc_url_raw(
					add_query_arg(
						array(
							'action' => 'check_email'
						),
						MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_REGISTER )
					)
				);

				wp_safe_redirect( $redirect );
				exit;
			}
		}
		catch( Exception $e ) {
			self::$register_errors = $e->getMessage();

			// step back
			$this->add_action( 'the_content', 'register_form', 10 );
			do_action(
				'ms_controller_frontend_register_user_error',
				self::$register_errors
			);
		}
	}

	/**
	 * Action to notify user to check email for the verification link
	 *
	 * @since 1.1.3
	 *
	 * @return string
	 */
	public function check_email() {
		$this->add_action( 'the_content', 'verification_notification' );
	}


	/**
	 * Show message to notify the user to verify
	 *
	 * Related action hooks:
	 *
	 * @since  1.1.3
	 *
	 * @return string
	 */
	public function verification_notification( $content ) {
		return apply_filters(
			'ms_controller_gateway_purchase_error_content',
			__( 'Please check your email for further instructions to verify your email.', 'membership2' ),
			$content,
			$this
		);
	}

	/**
	 * Render membership payment information.
	 *
	 * Related Filter Hooks:
	 * - the_content
	 *
	 * @since  1.0.0
	 *
	 * @param string $content The page content to filter.
	 * @return string The filtered content.
	 */
	public function payment_table( $content ) {
		$data 			= array();
		$subscription 	= null;
		$member 		= MS_Model_Member::get_current_member();
		$membership_id 	= 0;

		mslib3()->array->equip_request( 'membership_id', 'move_from_id', 'ms_relationship_id' );

		if ( ! empty( $_POST['ms_relationship_id'] ) ) {
			// Error path, showing payment table again with error msg
			$subscription = MS_Factory::load(
				'MS_Model_Relationship',
				absint( intval( $_POST['ms_relationship_id'] ) )
			);
			$membership 	= $subscription->get_membership();
			$membership_id 	= $membership->id;

			if ( ! empty( $_POST['error'] ) ) {
				mslib3()->array->strip_slashes( $_POST, 'error' );
				$data['error'] = $_POST['error'];
			}
		} elseif ( ! empty( $_REQUEST['membership_id'] ) ) {
			// First time loading
			$membership_id 	= intval( $_REQUEST['membership_id'] );
			$membership 	= MS_Factory::load( 'MS_Model_Membership', $membership_id );
			$move_from_id 	= absint( $_REQUEST['move_from_id'] );
			$subscription 	= MS_Model_Relationship::create_ms_relationship(
				$membership_id,
				$member->id,
				'',
				$move_from_id
			);
		} else {
			MS_Helper_Debug::debug_log( 'Error: missing POST params' );
			MS_Helper_Debug::debug_log( $_POST );
			return $content;
		}

		// Invalid membership ID was specified: Redirect to membership list.
		if ( ! $subscription ) {
			MS_Model_Pages::redirect_to( MS_Model_Pages::MS_PAGE_MEMBERSHIPS );
		}

		$invoice = $subscription->get_current_invoice();

		/**
		 * Notify Add-ons that we are preparing payment details for a membership
		 * subscription.
		 *
		 * E.g. Coupon discount is applied by this hook.
		 *
		 * @since  1.0.0
		 */
		$invoice = apply_filters(
			'ms_signup_payment_details',
			$invoice,
			$subscription,
			$membership
		);
		$invoice->save();

		$data['invoice'] 			= $invoice;
		$data['membership'] 		= $membership;
		$data['member'] 			= $member;
		$data['ms_relationship'] 	= $subscription;

		$view = MS_Factory::load( 'MS_View_Frontend_Payment' );
		$view->data = apply_filters(
			'ms_view_frontend_payment_data',
			$data,
			$membership_id,
			$subscription,
			$member,
			$this
		);

		return apply_filters(
			'ms_controller_frontend_payment_table',
			$view->to_html(),
			$this
		);
	}

	/**
	 * Handles membership_cancel action.
	 *
	 * @since  1.0.0
	 */
	public function membership_cancel() {
		if ( ! empty( $_REQUEST['membership_id'] ) && $this->verify_nonce( null, 'any' ) ) {
			$membership_id 	= absint( $_REQUEST['membership_id'] );
			$member 		= MS_Model_Member::get_current_member();
			$member->cancel_membership( $membership_id );
			$member->save();

			$url = MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_REGISTER );
			wp_safe_redirect( $url );
			exit;
		}
	}

	/**
	 * Manage user account actions.
	 *
	 * @since  1.0.0
	 * @internal
	 */
	public function user_account_manager() {
		$action = $this->get_action();
		$member = MS_Model_Member::get_current_member();

		/**
		 * These actions are always executed when any user account page loads.
		 *
		 * @since  1.0.1.0
		 */
		do_action(
			'ms_frontend_user_account_manager-' . $action,
			$this
		);
		do_action(
			'ms_frontend_user_account_manager',
			$action,
			$this
		);

		if ( $this->verify_nonce() ) {
			/**
			 * The following two actions are only executed when a form was
			 * submitted on a user account page.
			 *
			 * @since  1.0.1.0
			 */
			do_action(
				'ms_frontend_user_account_manager_submit-' . $action,
				$this
			);
			do_action(
				'ms_frontend_user_account_manager_submit',
				$action,
				$this
			);
		}

		switch ( $action ) {
			case self::ACTION_EDIT_PROFILE:
				$data = array();

				if ( $this->verify_nonce() ) {
					if ( is_array( $_POST ) ) {
						foreach ( $_POST as $field => $value ) {
                            $member->$field = $value;
						}
					}

					try {
						$member->validate_member_info();
						$member->save();
						do_action( 'ms_model_member_update_user', $member );
						wp_safe_redirect(
							esc_url_raw( remove_query_arg( 'action' ) )
						);
						exit;

					}
					catch ( Exception $e ) {
						$data['errors']  = $e->getMessage();
					}
				}
				$view = MS_Factory::create( 'MS_View_Frontend_Profile' );
				$data['member'] = $member;
				$data['action'] = $action;
				$view->data = apply_filters( 'ms_view_frontend_profile_data', $data, $this );
				$view->add_filter( 'the_content', 'to_html', 10 );
				break;

			case self::ACTION_VIEW_INVOICES:
				$data['invoices'] = MS_Model_Invoice::get_public_invoices(
					$member->id
				);

				$view = MS_Factory::create( 'MS_View_Frontend_Invoices' );
				$view->data = apply_filters(
					'ms_view_frontend_frontend_invoices',
					$data,
					$this
				);
				$view->add_filter( 'the_content', 'to_html', 10 );
				break;

			case self::ACTION_VIEW_ACTIVITIES:
				$data['events'] = MS_Model_Event::get_events(
					array(
						'author' => $member->id,
						'posts_per_page' => -1,
					)
				);

				$view = MS_Factory::create( 'MS_View_Frontend_Activities' );
				$view->data = apply_filters(
					'ms_view_frontend_frontend_activities',
					$data,
					$this
				);
				$view->add_filter( 'the_content', 'to_html', 10 );
				break;

			case self::ACTION_VIEW_RESETPASS:
				/**
				 * Reset password action.
				 * This action is accessed via the password-reset email
				 * @see  class-ms-controller-dialog.php
				 *
				 * The action is targeted to the Account-page but actually calls
				 * the Login-Shortcode.
				 */
				$view = MS_Factory::create( 'MS_View_Shortcode_Login' );
				$view->data = array( 'action' => 'resetpass' );

				$view->add_filter( 'the_content', 'to_html', 10 );
				break;
			case self::ACTION_VIEW_ACTIVATEACCOUNT:
				/**
				 * activate account action
				 * Verify the key and show login form
				 *
				 * @since 1.1.3
				 */
				$redirect_to		= false;
				$view 				= MS_Factory::create( 'MS_View_Shortcode_Login' );
				$data_defaults 		= array(
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
				);
				$verification_key 	= wp_unslash( $_GET['key'] );
				$user_id 			= MS_Model_Member::verification_account_id( $verification_key  );
				$message 			= MS_Model_Member::verify_activation_code( $user_id  );
				if ( $user_id ) {
					$redirect_to 	= get_user_meta( $user_id, '_ms_user_activation_redirect_url', true );
				}
				if ( !$redirect_to ) {
					$redirect_to 	= MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_ACCOUNT );
				} else {
					delete_user_meta( $user_id, '_ms_user_activation_redirect_url' );
				}
				delete_user_meta( $user_id, '_ms_user_force_activation_status' );
				$redirect_to						= apply_filters( 'ms_front_after_login_redirect', $redirect_to );
				$data_defaults['error_message'] 	= $message;
				$data_defaults['redirect_login'] 	= $redirect_to;
				$view->data 						= apply_filters( 'ms_view_shortcode_login_data', $data_defaults, $this );
				$view->add_filter( 'the_content', 'to_html', 10 );
				break;

			default:
				// Do nothing...
				break;
		}
	}

	/**
	 * Get the URL the user used to register for a subscription.
	 *
	 * Uses the default registration page unless the registration was embedded
	 * on another page (e.g. using a shortcode).
	 *
	 * Related Filter Hooks:
	 * - wp_signup_location
	 * - register_url
	 *
	 * @since  1.0.0
	 *
	 * @param string $url The url to filter.
	 * @return The new signup url.
	 */
	public function signup_location( $url ) {

		//Set to false to use default signup url
		//Set to true to use membership url
		$change_signup = apply_filters( 'ms_frontend_controller_change_signup_url', true );
		if ( $change_signup )
			$url = MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_REGISTER );

		return apply_filters(
			'ms_controller_frontend_signup_location',
			$url,
			$this
		);
	}

	/**
	 * Propagates SSL cookies when user logs in.
	 *
	 * Related Action Hooks:
	 * - wp_login
	 *
	 * @since  1.0.0
	 *
	 * @param type $login The login info.
	 * @param WP_User $user The user to login.
	 */
	public function propagate_ssl_cookie( $login, $user = null ) {
		if ( empty( $user ) || ! is_a( $user, 'WP_User' ) ) {
			$user = get_user_by( 'login', $login );
		}

		if ( is_a( $user, 'WP_User' ) ) {
			wp_set_auth_cookie( $user->ID, true, true );
		}

		do_action(
			'ms_controller_frontend_propagate_ssl_cookie',
			$login,
			$user,
			$this
		);
	}

	/**
	 * Redirect user to account page.
	 *
	 * Only redirect when no previous redirect_to is set or when going to /wp-admin/.
	 *
	 * @since  1.0.0
	 *
	 * @param string $redirect_to URL to redirect to.
	 * @param string $request URL the user is coming from.
	 * @param object $user Logged user's data.
	 * @return string The redirect url.
	 */
	public function login_redirect( $redirect_to, $request, $user ) {
		if ( ! empty( $user->ID )
			&& ! MS_Model_Member::is_admin_user( $user->ID )
			&& ( empty( $redirect_to ) || admin_url() == $redirect_to )
		) {
			$redirect_to = MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_ACCOUNT );
		}

		return apply_filters(
			'ms_controller_frontend_login_redirect',
			$redirect_to,
			$request,
			$user,
			$this
		);
	}

	/**
	 * Redirect user to page.
	 *
	 * @since  1.0.2.4
	 *
	 * @return void
	 */
	public function logout_redirect() {
		wp_redirect( MS_Model_Pages::get_url_after_logout() );
		exit;
	}

	/**
	 * Adds CSS and JS for Membership special pages used in the front end.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		do_action(
			'ms_controller_frontend_enqueue_scripts',
			$this->get_signup_step(),
			$this->get_action(),
			$this
		);

		$is_ms_page = MS_Model_Pages::is_membership_page();
		$is_profile = self::ACTION_EDIT_PROFILE == $this->get_action()
			&& MS_Model_Pages::is_membership_page( null, MS_Model_Pages::MS_PAGE_ACCOUNT );
		$load_on_front_pages = apply_filters( 'ms_controller_frontend_resources_load', true, $is_ms_page );
		if ( $load_on_front_pages ) {
			$data = array(
				'ms_init' => array( 'shortcode' ),
				'cancel_msg' => __( 'Are you sure you want to cancel?', 'membership2' ),
			);

			mslib3()->ui->css( 'ms-styles' );
			mslib3()->ui->js( 'jquery-validate' );
			mslib3()->ui->js( 'ms-public' );
			MS_Controller_Plugin::translate_jquery_validator();

			if ( $is_profile ) {
				$data['ms_init'][] = 'frontend_profile';
			}

			mslib3()->ui->data( 'ms_data', $data );
		}
	}

	/**
	 * Check for verification on normal login
	 *
	 * @since 1.1.3
	 *
	 * @param string $login - the user login
	 * @param WP_User $user - the user
	 */
	function handle_verification_code( $login, $user ) {
		$user_activation_status 	= get_user_meta( $user->ID, '_ms_user_force_activation_status', true );
		$verification_cutoff_date 	= '2018-04-11 23:59:59';
		// Require verification only for new accounts
		if ( $user->user_registered < $verification_cutoff_date && !$user_activation_status ) {
			return;
		}

		$settings = MS_Factory::load( 'MS_Model_Settings' );
		if ( $settings->force_registration_verification ) {
			if ( !MS_Model_Member::is_admin_user( $user->ID ) ) {
				$user_activation_status = get_user_meta( $user->ID, '_ms_user_activation_status', true );
				$user_activation_status = empty( $user_activation_status ) ? 0 : $user_activation_status;
				if ( $user_activation_status != 1 ) {
					wp_destroy_current_session();
					wp_clear_auth_cookie();
					$login_url = wp_login_url();
					$login_url = add_query_arg( array(
						'ms_error' 	=> true,
					), $login_url );
					wp_redirect( $login_url );
					exit;
				}
			}
		}
	}

	/**
	 * Login Message
	 * Set our verification message
	 *
	 * @since 1.1.3
	 *
	 * @param string $message - the login message
	 *
	 * @return string $message
	 */
	function login_message( $message ) {
		if ( isset( $_GET['ms_error'] ) ) {
			$msg = __( 'Account not verified. Please check your email for a verification link', 'membership' );
			$msg = htmlspecialchars( $msg, ENT_QUOTES, 'UTF-8' );
			$message .= '<p class="login message">'. $msg . '</p>';
		}
		return $message;
	}
}