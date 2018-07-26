<?php
/**
 * Add-On controller for: Coupons
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Controller
 */
class MS_Addon_Coupon extends MS_Addon {

	/**
	 * The Add-on ID
	 *
	 * @since  1.0.0
	 */
	const ID = 'coupon';

	/**
	 * The menu slug for the admin page to manage invitation codes.
	 *
	 * @since  1.0.0
	 */
	const SLUG = 'coupons';

	/**
	 * Checks if the current Add-on is enabled
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	static public function is_active() {
		return MS_Model_Addon::is_enabled( self::ID );
	}

	/**
	 * Returns the Add-on ID (self::ID).
	 *
	 * @since  1.0.1.0
	 * @return string
	 */
	public function get_id() {
		return self::ID;
	}

	/**
	 * Saves a reference to the currently processed coupon in the registration
	 * form.
	 *
	 * @since  1.0.0
	 *
	 * @var MS_Addon_Coupon_Model
	 */
	private static $the_coupon = null;

	/**
	 * Initializes the Add-on. Always executed.
	 *
	 * @since  1.0.0
	 */
	public function init() {
		if ( self::is_active() ) {
			$hook = 'membership-2_page_' . MS_Controller_Plugin::MENU_SLUG . '-' . self::SLUG;

			$this->add_action( 'load-' . $hook, 'admin_manager' );
			$this->add_action( 'admin_print_scripts-' . $hook, 'enqueue_scripts' );
			$this->add_action( 'admin_print_styles-' . $hook, 'enqueue_styles' );

			// Add Coupon menu item to Membership2 menu (Admin)
			$this->add_filter(
				'ms_plugin_menu_pages',
				'menu_item',
				10, 3
			);

			// Handle the submenu item - display the add-on page.
			$this->add_filter(
				'ms_route_submenu_request',
				'route_submenu_request'
			);

			// Tell Membership2 about the Coupon Post Type
			$this->add_filter(
				'ms_plugin_register_custom_post_types',
				'register_ms_posttypes'
			);

			$this->add_filter(
				'ms_rule_cptgroup_model_get_ms_post_types',
				'update_ms_posttypes'
			);

			// Show Coupon columns in the billing list (Admin)
			$this->add_filter(
				'ms_helper_listtable_billing_get_columns',
				'billing_columns',
				10, 2
			);

			/*$this->add_filter(
				'ms_helper_listtable_billing-column_amount',
				'billing_column_value',
				10, 3
			);*/

			$this->add_filter(
				'ms_helper_listtable_billing-column_discount',
				'billing_column_value',
				10, 3
			);

			// Show Coupon form in the payment-form (Frontend)
			$this->add_action(
				'ms_view_frontend_payment_after_total_row',
				'payment_coupon_form',
				6, 3
			);

			// Update Coupon-Counter when invoice is paid
			$this->add_action(
				'ms_model_invoice_changed-paid',
				'invoice_paid',
				10, 2
			);

			// Apply Coupon-Discount to invoice
			$this->add_filter(
				'ms_signup_payment_details',
				'apply_discount',
				10, 2
			);

			// Add/Remove coupon discount in the payment table frontend.
			$this->add_filter(
				'ms_view_frontend_payment_data',
				'process_payment_table',
				10, 4
			);

			$this->add_filter(
				'ms_model_relationship_get_payment_description/recurring',
				'payment_description_recurring',
				10, 6
			);

		}
	}

	/**
	 * Sets or gets the coupon model that is processed in the current
	 * registration form.
	 *
	 * @since  1.0.0
	 * @param  MS_Addon_Coupon_Model $new_value
	 * @return MS_Addon_Coupon_Model
	 */
	static private function the_coupon( $new_value = null ) {
		if ( null !== $new_value ) {
			self::$the_coupon = $new_value;
		} else {
			if ( null === self::$the_coupon ) {
				self::$the_coupon = MS_Factory::load( 'MS_Addon_Coupon_Model' );
			}
		}

		return self::$the_coupon;
	}

	/**
	 * Registers the Add-On
	 *
	 * @since  1.0.0
	 * @param  array $list The Add-Ons list.
	 * @return array The updated Add-Ons list.
	 */
	public function register( $list ) {
		$list[ self::ID ] = (object) array(
			'name' => __( 'Coupon', 'membership2' ),
			'description' => __( 'Enable discount coupons.', 'membership2' ),
			'icon' => 'wpmui-fa wpmui-fa-ticket',
		);

		return $list;
	}

	/**
	 * Add the Coupons menu item to the Membership2 menu.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $items List of the current admin menu items.
	 * @param  bool $limited_mode True means either First-Setup or site-admin
	 *         in network wide protection.
	 * @param  MS_Controller $controller
	 * @return array The modified menu array.
	 */
	public function menu_item( $items, $limited_mode, $controller ) {
		if ( ! $limited_mode ) {
			$menu_item = array(
				self::ID => array(
					'title' => __( 'Coupons', 'membership2' ),
					'slug' => self::SLUG,
				),
			);
			mslib3()->array->insert( $items, 'before', 'addon', $menu_item );
		}

		return $items;
	}

	/**
	 * Handles all sub-menu clicks. We check if the menu item of our add-on was
	 * clicked and if it was we display the correct page.
	 *
	 * The $handler value is ONLY changed when the current menu is displayed.
	 * If another menu item was clicked then don't do anythign here!
	 *
	 * @since  1.0.0
	 * @param  array $handler {
	 *         Menu-item handling information.
	 *
	 *         0 .. any|network|site  The admin-area that can handle our menu item.
	 *         1 .. callable          A callback to handle the menu item.
	 * @return array Menu-item handling information.
	 */
	public function route_submenu_request( $handler ) {
		if ( MS_Controller_Plugin::is_page( self::SLUG ) ) {
			$handler = array(
				'network',
				array( $this, 'admin_coupon' ),
			);
		}

		return $handler;
	}

	/**
	 * Register the Coupon Post-Type; this is done in MS_Plugin.
	 *
	 * @since  1.0.0
	 * @param  array $cpts
	 * @return array
	 */
	public function register_ms_posttypes( $cpts ) {
		$pt = MS_Addon_Coupon_Model::get_post_type();
		$cpts[ $pt ] = MS_Addon_Coupon_Model::get_register_post_type_args();

		return $cpts;
	}

	/**
	 * Add the Coupon Post-Type to the list of internal post-types
	 *
	 * @since  1.0.0
	 * @param  array $cpts
	 * @return array
	 */
	public function update_ms_posttypes( $cpts ) {
		$cpts[] = MS_Addon_Coupon_Model::get_post_type();

		return $cpts;
	}

	/**
	 * Manages coupon actions.
	 *
	 * Verifies GET and POST requests to manage billing.
	 *
	 * @since  1.0.0
	 */
	public function admin_manager() {
		$edit_fields = array( 'submit', 'action', 'coupon_id' );
		$action_fields = array( 'action', 'coupon_id' );
		$bulk_fields = array( 'coupon_id' );
		$redirect = false;

		if ( self::validate_required( $edit_fields, 'POST', false )
			&& 'edit' == $_POST['action']
			&& $this->verify_nonce()
			&& $this->is_admin_user()
		) {
			// Save coupon add/edit
			$msg = $this->save_coupon( $_POST );
			$redirect = add_query_arg(
				array( 'msg' => $msg ),
				remove_query_arg( array( 'coupon_id' ) )
			);
			$redirect = esc_url_raw( $redirect );
		} elseif ( self::validate_required( $action_fields, 'GET' )
			&& $this->verify_nonce( $_GET['action'], 'GET' )
			&& $this->is_admin_user()
		) {
			// Execute table single action.
			$msg = $this->coupon_do_action( $_GET['action'], array( $_GET['coupon_id'] ) );
			$redirect = esc_url_raw(
				add_query_arg(
					array( 'msg' => $msg ),
					remove_query_arg( array( 'coupon_id', 'action', '_wpnonce' ) )
				)
			);
		} elseif ( self::validate_required( $bulk_fields )
			&& $this->verify_nonce( 'bulk' )
			&& $this->is_admin_user()
		) {
			// Execute bulk actions.
			$action = (-1 != $_POST['action'] ? $_POST['action'] : $_POST['action2']);
			$msg = $this->coupon_do_action( $action, $_POST['coupon_id'] );
			$redirect = esc_url_raw( add_query_arg( array( 'msg' => $msg ) ) );
		}

		if ( $redirect ) {
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Perform actions for each coupon.
	 *
	 *
	 * @since  1.0.0
	 * @param string $action The action to perform on selected coupons
	 * @param int[] $coupons The list of coupons ids to process.
	 */
	public function coupon_do_action( $action, $coupon_ids ) {
		if ( ! $this->is_admin_user() ) {
			return;
		}

		if ( is_array( $coupon_ids ) ) {
			foreach ( $coupon_ids as $coupon_id ) {
				switch ( $action ) {
					case 'delete':
						$coupon = MS_Factory::load( 'MS_Addon_Coupon_Model', $coupon_id );
						$coupon->delete();
						break;
				}
			}
		}
	}

	/**
	 * Render the Coupon admin manager.
	 *
	 * @since  1.0.0
	 */
	public function admin_coupon() {
		$isset = array( 'action', 'coupon_id' );

		if ( self::validate_required( $isset, 'GET', false )
			&& 'edit' == $_GET['action']
		) {
			// Edit action view page request
			$coupon_id = ! empty( $_GET['coupon_id'] ) ? $_GET['coupon_id'] : 0;
			$data['coupon'] = MS_Factory::load( 'MS_Addon_Coupon_Model', $coupon_id );
			$data['memberships'] = array( __( 'Any', 'membership2' ) );
			$data['memberships'] += MS_Model_Membership::get_membership_names(
				array( 'include_guest' => 0 )
			);
			$data['action'] = $_GET['action'];

			$view = MS_Factory::create( 'MS_Addon_Coupon_View_Edit' );
			$view->data = apply_filters( 'ms_addon_coupon_view_edit_data', $data );
			$view->render();
		} else {
			// Coupon admin list page
			$view = MS_Factory::create( 'MS_Addon_Coupon_View_List' );
			$view->render();
		}
	}

	/**
	 * Save coupon using the coupon model.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed $fields Coupon fields
	 * @return boolean True in success saving.
	 */
	private function save_coupon( $fields ) {
		$coupon = null;
		$msg = false;

		if ( $this->is_admin_user() ) {
			if ( is_array( $fields ) ) {
				$coupon_id = ( $fields['coupon_id'] ) ? $fields['coupon_id'] : 0;
				$coupon = MS_Factory::load( 'MS_Addon_Coupon_Model', $coupon_id );

				foreach ( $fields as $field => $value ) {
					$coupon->$field = $value;
				}

				$coupon->save();
				$msg = true;
			}
		}

		return apply_filters(
			'ms_addon_coupon_model_save_coupon',
			$msg,
			$fields,
			$coupon,
			$this
		);
	}

	/**
	 * Load Coupon specific styles.
	 *
	 * @since  1.0.0
	 */
	public function enqueue_styles() {
		if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
			mslib3()->ui->add( 'jquery-ui' );
		}

		do_action( 'ms_addon_coupon_enqueue_styles', $this );
	}

	/**
	 * Load Coupon specific scripts.
	 *
	 * @since  1.0.0
	 */
	public function enqueue_scripts() {
		if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
			$plugin_url = MS_Plugin::instance()->url;

			wp_enqueue_script( 'jquery-validate' );
			mslib3()->ui->add( 'jquery-ui' );

			wp_enqueue_script(
				'ms-view-coupon-edit',
				$plugin_url . '/app/addon/coupon/assets/js/edit.js',
				array( 'jquery' )
			);
		}

		do_action( 'ms_addon_coupon_enqueue_scripts', $this );
	}

	/**
	 * Insert Discount columns in the invoice table.
	 *
	 * @since  1.0.0
	 * @param  array $columns
	 * @param  string $currency
	 * @return array
	 */
	public function billing_columns( $columns, $currency ) {
		$new_columns = array(
			//'amount' => __( 'Amount', 'membership2' ),
			'discount' => __( 'Discount', 'membership2' ),
		);

		mslib3()->array->insert( $columns, 'after', 'status', $new_columns );

		return $columns;
	}

	/**
	 * Return the column value for the custom billing columns.
	 *
	 * @since  1.0.0
	 * @param  MS_Model $item List item that is parsed.
	 * @param  string $column_name Column that is parsed.
	 * @return string HTML code to display in the cell.
	 */
	public function billing_column_value( $default, $item, $column_name ) {
		if ( property_exists( $item, $column_name ) ) {
			$value = $item->$column_name;
		} else {
			$value = '';
		}
		$currency = $item->currency;

		$html = '';

		if ( empty( $value ) ) {
			if ( 'discount' == $column_name && empty( $value ) ) {
				$html = '-';
			}
		} else {
			$value = MS_Helper_Billing::format_price( $value );
			$html = sprintf(
				'%1$s <small>%2$s</small>',
				$value,
				$currency
			);
		}

		return $html;
	}

	/**
	 * Output a form where the member can enter a coupon code
	 *
	 * @since  1.0.0
	 * @param  MS_Model_Relationship $subscription
	 * @param  MS_Model_Invoice $invoice
	 * @param  MS_View $view The parent view that renders the payment form.
	 * @return string HTML code
	 */
	public function payment_coupon_form( $subscription, $invoice, $view ) {
		$data = $view->data;
		$coupon = $data['coupon'];
		$coupon_message = '';
		$fields = array();

		if ( ! empty( $data['coupon_valid'] ) ) {
			$fields = array(
				'coupon_code' => array(
					'id' => 'coupon_code',
					'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
					'value' => $coupon->code,
				),
				'remove_coupon_code' => array(
					'id' => 'remove_coupon_code',
					'type' => MS_Helper_Html::INPUT_TYPE_SUBMIT,
					'value' => __( 'Remove', 'membership2' ),
					'label_class' => 'inline-label',
					'title' => $coupon->coupon_message,
					'button_value' => 1,
				),
			);
		} else {
			$fields = array(
				'coupon_code' => array(
					'id' => 'coupon_code',
					'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
					'value' => $coupon->code,
				),
				'apply_coupon_code' => array(
					'id' => 'apply_coupon_code',
					'type' => MS_Helper_Html::INPUT_TYPE_SUBMIT,
					'value' => __( 'Apply Coupon', 'membership2' ),
				),
			);
			$coupon_message = $coupon->coupon_message;
		}

		$fields['membership_id'] = array(
			'id' => 'membership_id',
			'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
			'value' => $data['membership']->id,
		);
		$fields['move_from_id'] = array(
			'id' => 'move_from_id',
			'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
			'value' => $data['ms_relationship']->move_from_id,
		);
		$fields['step'] = array(
			'id' => 'step',
			'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
			'value' => MS_Controller_Frontend::STEP_PAYMENT_TABLE,
		);

		if ( ! empty( $data['coupon_valid'] ) ) {
			$class = 'ms-alert-success';
		} else {
			$class = 'ms-alert-error';
		}

		?>
		<tr class="ms-invitation-code">
		<td colspan="2">
		<div class="membership-coupon">
			<div class="membership_coupon_form couponbar">
				<form method="post">
					<?php if ( $coupon_message ) : ?>
						<p class="ms-alert-box <?php echo esc_attr( $class ); ?>">
						<?php
						if ( ! empty( $_POST['remove_coupon_code'] ) ) {
							printf(
								__( 'Coupon removed: "%s"', 'membership2' ),
								$coupon->code
							);
						} else {
							echo $coupon_message;
						}
						?></p>
					<?php endif; ?>
					<div class="coupon-entry">
						<?php if ( ! isset( $data['coupon_valid'] ) ) : ?>
							<div class="coupon-question"><?php
							_e( 'Have a coupon code?', 'membership2' );
							?></div>
						<?php endif; ?>

						<?php
						foreach ( $fields as $field ) {
							MS_Helper_Html::html_element( $field );
						}
						?>
					</div></form></div></div>
		</td>
		</tr>
		<?php
	}

	/**
	 * When an invoice is paid, check if it did use a coupon. If yes, then update
	 * the coupon counter.
	 *
	 * @since  1.0.0
	 * @param  MS_Model_Invoice $invoice
	 */
	public function invoice_paid( $invoice, $member ) {
		if ( $invoice->coupon_id ) {
			$coupon = MS_Factory::load( 'MS_Addon_Coupon_Model', $invoice->coupon_id );
			$coupon->remove_application( $member->id, $invoice->membership_id );
			$coupon->used = $coupon->used + 1;
			$coupon->save();
		}
	}

	/**
	 * Called by MS_Model_Invoice before a new invoice is saved. We apply the
	 * coupon discount to the total amount, if a coupon was used.
	 *
	 * @since  1.0.0
	 * @param  MS_Model_Invoice $invoice
	 * @param  MS_Model_Relationship $subscription
	 * @return MS_Model_Invoice
	 */
	public function apply_discount( $invoice, $subscription ) {
		$membership = $subscription->get_membership();
		$member = MS_Factory::load( 'MS_Model_Member', $subscription->user_id );

		if ( isset( $_POST['apply_coupon_code'] ) ) {
			$coupon = apply_filters(
				'ms_addon_coupon_model',
				MS_Addon_Coupon_Model::load_by_code( $_POST['coupon_code'] )
			);

			$coupon->save_application( $subscription );
		} else {
			$coupon = MS_Addon_Coupon_Model::get_application(
				$member->id,
				$membership->id
			);

			if ( ! empty( $_POST['remove_coupon_code'] ) ) {
				$note = sprintf(
					__( 'Remove Coupon "%s"', 'membership2' ),
					$coupon->code
				);
				$invoice->add_notes( $note );

				$coupon->remove_application( $member->id, $membership->id );
				$coupon = false;
			}
		}
		self::the_coupon( $coupon );

		if ( $coupon && $coupon->is_valid( $membership->id ) ) {
			$discount = $coupon->get_discount_value( $subscription );
			$invoice->coupon_id = $coupon->id;
			$invoice->discount = $discount;
			$invoice->duration = $coupon->duration;

			$note = sprintf(
				__( 'Apply Coupon "%s": Discount %s %s!', 'membership2' ),
				$coupon->code,
				$invoice->currency,
				$discount
			);

			$invoice->add_notes( $note );
		} else {
			$invoice->coupon_id = '';
			$invoice->discount = 0;
		}

		return $invoice;
	}

	/**
	 * Add/Remove Coupon from the membership price in the frontend payment table.
	 *
	 * @since  1.0.0
	 * @param  array $data
	 * @param  int $membership_id
	 * @param  MS_Model_Relationship $subscription
	 * @param  MS_Model_Member $member
	 */
	public function process_payment_table( $data, $membership_id, $subscription, $member ) {
		$data['coupon'] = self::the_coupon();
		$data['coupon_valid'] = false;

		if ( ! empty( $_POST['coupon_code'] ) ) {
			$coupon = MS_Addon_Coupon_Model::get_application(
				$member->id,
				$membership_id
			);
			self::the_coupon( $coupon );

			if ( $coupon ) {
				$data['coupon_valid'] = $coupon->was_valid();
				$data['coupon'] = $coupon;
			}
		}

		return $data;
	}

	/**
	 * Sets the payment description on checkout page if a valid coupon is applied
	 *
	 * @since  1.0.3.5
	 * @param  String $desc
	 * @param  Boolean $short
	 * @param  String $currency
	 * @param  String $total_price Price where discount has already been applied
	 * @param  MS_Model_Membership $membership
	 * @param  MS_Model_Invoice $invoice
	 * @return String Payment description
	 */
	public function payment_description_recurring( $desc, $short, $currency, $total_price, $membership, $invoice ){

		if ( 1 == $membership->pay_cycle_repetitions ) return $desc;

		if ( isset( $_POST['apply_coupon_code'] ) && ! empty( $_POST['coupon_code'] ) && ! empty( $_REQUEST['membership_id'] ) ) {

			$coupon = apply_filters(
				'ms_addon_coupon_model',
				MS_Addon_Coupon_Model::load_by_code( $_POST['coupon_code'] )
			);

			if ( ! $coupon || ! $coupon->is_valid( $membership->id ) ) return $desc;

			if ( MS_Model_Membership::PAYMENT_TYPE_RECURRING === $membership->payment_type && MS_Addon_Coupon_Model::DURATION_ONCE !== $invoice->duration ) return $desc;

			$lbl = '';
			if ( $membership->pay_cycle_repetitions > 1 ) {
				// Fixed number of payments (more than 1)
				if ( $short ) {
					$lbl = __( '<span class="price">%1$s %2$s</span> first time and then <span class="price">%1$s %3$s</span> (each %4$s)', 'membership2' );
				} else {
					$lbl = __( 'First payment <span class="price">%1$s %2$s</span> and then you will make %5$s payments of <span class="price">%1$s %3$s</span>, one each %4$s.', 'membership2' );
				}
			} else {
				// Indefinite number of payments
				if ( $short ) {
					$lbl = __( '<span class="price">%1$s %2$s</span> first time and then <span class="price">%1$s %3$s</span> (each %4$s)', 'membership2' );
				} else {
					$lbl = __( 'You will pay <span class="price">%1$s %2$s</span> first time and then <span class="price">%1$s %3$s</span> each %4$s.', 'membership2' );
				}
			}

			$desc = sprintf(
				$lbl,
				$currency,
				$total_price,
				$membership->price,
				MS_Helper_Period::get_period_desc( $membership->pay_cycle_period ),
				$membership->pay_cycle_repetitions - 1
			);

		}

		return $desc;
	}
}