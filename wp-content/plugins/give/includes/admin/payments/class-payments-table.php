<?php
/**
 * Payment History Table Class
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, Give
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Give_Payment_History_Table Class
 *
 * Renders the Payment History table on the Payment History page
 *
 * @since 1.0
 */
class Give_Payment_History_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 1.0.1
	 */
	public $base_url;

	/**
	 * Total number of payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $total_count;

	/**
	 * Total number of complete payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $complete_count;

	/**
	 * Total number of pending payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $pending_count;

	/**
	 * Total number of processing payments
	 *
	 * @var int
	 * @since 1.8.9
	 */
	public $processing_count;

	/**
	 * Total number of refunded payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $refunded_count;

	/**
	 * Total number of failed payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $failed_count;

	/**
	 * Total number of revoked payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $revoked_count;

	/**
	 * Total number of cancelled payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $cancelled_count;

	/**
	 * Total number of abandoned payments
	 *
	 * @var int
	 * @since 1.6
	 */
	public $abandoned_count;

	/**
	 * Get things started.
	 *
	 * @since 1.0
	 * @uses  Give_Payment_History_Table::get_payment_counts()
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {

		// Set parent defaults.
		parent::__construct( array(
			'singular' => give_get_forms_label_singular(),    // Singular name of the listed records.
			'plural'   => give_get_forms_label_plural(),      // Plural name of the listed records.
			'ajax'     => false,                              // Does this table support ajax?
		) );

		$this->process_bulk_action();
		$this->get_payment_counts();
		$this->base_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' );
	}

	/**
	 * Add donation search filter.
	 *
	 * @return void
	 */
	public function advanced_filters() {
		$start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] ) ? sanitize_text_field( $_GET['end-date'] ) : null;
		$status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
		$donor      = isset( $_GET['donor'] ) ? sanitize_text_field( $_GET['donor'] ) : '';
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$form_id    = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		?>
		<div id="give-payment-filters" class="give-filters">
			<?php $this->search_box( esc_html__( 'Search', 'give' ), 'give-payments' ); ?>
			<div id="give-payment-date-filters">
				<div class="give-filter give-filter-half">
					<label for="start-date"
					       class="give-start-date-label"><?php esc_html_e( 'Start Date', 'give' ); ?></label>
					<input type="text" id="start-date" name="start-date" class="give_datepicker"
					       value="<?php echo $start_date; ?>" placeholder="mm/dd/yyyy" />
				</div>
				<div class="give-filter give-filter-half">
					<label for="end-date" class="give-end-date-label"><?php esc_html_e( 'End Date', 'give' ); ?></label>
					<input type="text" id="end-date" name="end-date" class="give_datepicker"
					       value="<?php echo $end_date; ?>" placeholder="mm/dd/yyyy" />
				</div>
			</div>
			<div id="give-payment-form-filter" class="give-filter">
				<label for="give-donation-forms-filter"
				       class="give-donation-forms-filter-label"><?php esc_html_e( 'Form', 'give' ); ?></label>
				<?php
				// Filter Donations by Donation Forms.
				echo Give()->html->forms_dropdown( array(
					'name'     => 'form_id',
					'id'       => 'give-donation-forms-filter',
					'class'    => 'give-donation-forms-filter',
					'selected' => $form_id, // Make sure to have $form_id set to 0, if there is no selection.
					'chosen'   => true,
					'number'   => - 1,
				) );
				?>
			</div>

			<?php if ( ! empty( $status ) ) : ?>
				<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>" />
			<?php endif; ?>

			<div class="give-filter">
				<?php submit_button( __( 'Apply', 'give' ), 'secondary', '', false ); ?>
				<?php
				// Clear active filters button.
				if ( ! empty( $start_date ) || ! empty( $end_date ) || ! empty( $donor ) || ! empty( $search ) || ! empty( $status ) || ! empty( $form_id ) ) : ?>
					<a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' ); ?>"
					   class="button give-clear-filters-button"><?php esc_html_e( 'Clear Filters', 'give' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Show the search field
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $text     Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<div class="give-filter give-filter-search" role="search">
			<?php
			/**
			 * Fires in the payment history search box.
			 *
			 * Allows you to add new elements before the search box.
			 *
			 * @since 1.7
			 */
			do_action( 'give_payment_history_search' );
			?>
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array(
				'ID' => 'search-submit',
			) ); ?><br />
		</div>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since  1.0
	 * @return array $views All the views available
	 */
	public function get_views() {

		$current = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$views   = array();
		$tabs    = array(
			'all'        => array(
				'total_count',
				esc_html__( 'All', 'give' ),
			),
			'publish'    => array(
				'complete_count',
				esc_html__( 'Completed', 'give' ),
			),
			'pending'    => array(
				'pending_count',
				esc_html__( 'Pending', 'give' ),
			),
			'processing' => array(
				'processing_count',
				esc_html__( 'Processing', 'give' ),
			),
			'refunded'   => array(
				'refunded_count',
				esc_html__( 'Refunded', 'give' ),
			),
			'revoked'    => array(
				'revoked_count',
				esc_html__( 'Revoked', 'give' ),
			),
			'failed'     => array(
				'failed_count',
				esc_html__( 'Failed', 'give' ),
			),
			'cancelled'  => array(
				'cancelled_count',
				esc_html__( 'Cancelled', 'give' ),
			),
			'abandoned'  => array(
				'abandoned_count',
				esc_html__( 'Abandoned', 'give' ),
			),
		);

		foreach ( $tabs as $key => $tab ) {
			$count_key = $tab[0];
			$name      = $tab[1];
			$count     = $this->$count_key;

			/**
			 * Filter can be used to show all the status inside the donation tabs.
			 *
			 * Filter can be used to show all the status inside the donation submenu tabs return true to show all the tab.
			 *
			 * @since 1.8.12
			 *
			 * @param string $key   Current view tab value.
			 * @param int    $count Number of donation inside the tab.
			 */
			if ( 'all' === $key || $key === $current || apply_filters( 'give_payments_table_show_all_status', 0 < $count, $key, $count ) ) {

				$views[ $key ] = sprintf(
					'<a href="%s" %s >%s&nbsp;<span class="count">(%s)</span></a>',
					esc_url( ( 'all' === (string) $key ) ? remove_query_arg( array( 'status', 'paged' ) ) : add_query_arg( array( 'status' => $key, 'paged' => false ) ) ),
					( ( 'all' === $key && empty( $current ) ) ) ? 'class="current"' : ( $current == $key ) ? 'class="current"' : '',
					$name,
					$count
				);
			}
		}

		return apply_filters( 'give_payments_table_views', $views );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since  1.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox" />', // Render a checkbox instead of text.
			'donation'      => esc_html__( 'Donation', 'give' ),
			'donation_form' => esc_html__( 'Donation Form', 'give' ),
			'status'        => esc_html__( 'Status', 'give' ),
			'date'          => esc_html__( 'Date', 'give' ),
			'amount'        => esc_html__( 'Amount', 'give' ),
			'details'       => esc_html__( 'Details', 'give' ),
		);

		return apply_filters( 'give_payments_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since  1.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		$columns = array(
			'donation'      => array( 'ID', true ),
			'donation_form' => array( 'donation_form', false ),
			'status'        => array( 'status', false ),
			'amount'        => array( 'amount', false ),
			'date'          => array( 'date', false ),
		);

		return apply_filters( 'give_payments_table_sortable_columns', $columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'donation';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param Give_Payment $payment     Payment ID.
	 * @param string       $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $payment, $column_name ) {

		$single_donation_url = esc_url( add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details' ) ) );
		$row_actions         = $this->get_row_actions( $payment );

		switch ( $column_name ) {
			case 'donation' :
				$value = sprintf( '<a href="%1$s" data-tooltip="%2$s">#%3$s</a>&nbsp;%4$s&nbsp;%5$s<br>', $single_donation_url, sprintf( esc_attr__( 'View Donation #%s', 'give' ), $payment->ID ), $payment->ID, esc_html__( 'by', 'give' ), $this->get_donor( $payment ) );
				$value .= $this->get_donor_email( $payment );
				$value .= $this->row_actions( $row_actions );
				break;

			case 'amount' :
				$amount = ! empty( $payment->total ) ? $payment->total : 0;
				$value  = give_currency_filter( give_format_amount( $amount, array( 'sanitize' => false ) ), give_get_payment_currency_code( $payment->ID ) );
				$value  .= sprintf( '<br><small>%1$s %2$s</small>', __( 'via', 'give' ), give_get_gateway_admin_label( $payment->gateway ) );
				break;

			case 'donation_form' :
				$form_title = empty( $payment->form_title ) ? sprintf( __( 'Untitled (#%s)', 'give' ), $payment->form_id ) : $payment->form_title;
				$value      = '<a href="' . admin_url( 'post.php?post=' . $payment->form_id . '&action=edit' ) . '">' . $form_title . '</a>';
				$level      = give_get_payment_form_title( $payment->meta, true );

				if ( ! empty( $level ) ) {
					$value .= $level;
				}

				break;

			case 'date' :
				$date  = strtotime( $payment->date );
				$value = date_i18n( give_date_format(), $date );
				break;

			case 'status' :
				$value = $this->get_payment_status( $payment );
				break;

			case 'details' :
				$value = sprintf( '<div class="give-payment-details-link-wrap"><a href="%1$s" class="give-payment-details-link button button-small" data-tooltip="%2$s" aria-label="%2$s"><span class="dashicons dashicons-visibility"></span></a></div>', $single_donation_url, sprintf( esc_attr__( 'View Donation #%s', 'give' ), $payment->ID ) );
				break;

			default:
				$value = isset( $payment->$column_name ) ? $payment->$column_name : '';
				break;

		}// End switch().

		return apply_filters( 'give_payments_table_column', $value, $payment->ID, $column_name );
	}

	/**
	 * Get donor email html.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param  Give_Payment $payment Contains all the data of the payment
	 *
	 * @return string                Data shown in the Email column
	 */
	public function get_donor_email( $payment ) {

		$email = give_get_payment_user_email( $payment->ID );

		if ( empty( $email ) ) {
			$email = esc_html__( '(unknown)', 'give' );
		}

		$value = '<a href="mailto:' . $email . '" data-tooltip="' . esc_attr__( 'Email donor', 'give' ) . '">' . $email . '</a>';

		return apply_filters( 'give_payments_table_column', $value, $payment->ID, 'email' );
	}

	/**
	 * Get Row Actions
	 *
	 * @since 1.6
	 *
	 * @param Give_Payment $payment
	 *
	 * @return array $actions
	 */
	function get_row_actions( $payment ) {

		$actions = array();
		$email   = give_get_payment_user_email( $payment->ID );

		// Add search term string back to base URL.
		$search_terms = ( isset( $_GET['s'] ) ? trim( $_GET['s'] ) : '' );
		if ( ! empty( $search_terms ) ) {
			$this->base_url = add_query_arg( 's', $search_terms, $this->base_url );
		}

		if ( give_is_payment_complete( $payment->ID ) && ! empty( $email ) ) {

			$actions['email_links'] = sprintf( '<a class="resend-single-donation-receipt" href="%1$s" aria-label="%2$s">%3$s</a>', wp_nonce_url( add_query_arg( array(
				'give-action' => 'email_links',
				'purchase_id' => $payment->ID,
			), $this->base_url ), 'give_payment_nonce' ), sprintf( esc_attr__( 'Resend Donation %s Receipt', 'give' ), $payment->ID ), esc_html__( 'Resend Receipt', 'give' ) );

		}

		$actions['delete'] = sprintf( '<a class="delete-single-donation" href="%1$s" aria-label="%2$s">%3$s</a>', wp_nonce_url( add_query_arg( array(
			'give-action' => 'delete_payment',
			'purchase_id' => $payment->ID,
		), $this->base_url ), 'give_donation_nonce' ), sprintf( esc_attr__( 'Delete Donation %s', 'give' ), $payment->ID ), esc_html__( 'Delete', 'give' ) );

		return apply_filters( 'give_payment_row_actions', $actions, $payment );
	}


	/**
	 *  Get payment status html.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param  Give_Payment $payment Contains all the data of the payment
	 *
	 * @return string                Data shown in the Email column
	 */
	function get_payment_status( $payment ) {
		$value = '<div class="give-donation-status status-' . sanitize_title( give_get_payment_status( $payment, true ) ) . '"><span class="give-donation-status-icon"></span> ' . give_get_payment_status( $payment, true ) . '</div>';
		if ( $payment->mode == 'test' ) {
			$value .= ' <span class="give-item-label give-item-label-orange give-test-mode-transactions-label" data-tooltip="' . esc_attr__( 'This donation was made in test mode.', 'give' ) . '">' . esc_html__( 'Test', 'give' ) . '</span>';
		}

		return $value;
	}

	/**
	 * Get checkbox html.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param  Give_Payment $payment Contains all the data for the checkbox column.
	 *
	 * @return string Displays a checkbox.
	 */
	public function column_cb( $payment ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', 'payment', $payment->ID );
	}

	/**
	 * Get payment ID html.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param  Give_Payment $payment Contains all the data for the checkbox column.
	 *
	 * @return string Displays a checkbox.
	 */
	public function get_payment_id( $payment ) {
		return '<span class="give-payment-id">' . give_get_payment_number( $payment->ID ) . '</span>';
	}

	/**
	 * Get donor html.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param  Give_Payment $payment Contains all the data of the payment
	 *
	 * @return string Data shown in the User column
	 */
	public function get_donor( $payment ) {

		$donor_id           = give_get_payment_donor_id( $payment->ID );
		$donor_billing_name = give_get_donor_name_by( $payment->ID, 'donation' );
		$donor_name         = give_get_donor_name_by( $donor_id, 'donor' );

		$value = '';
		if ( ! empty( $donor_id ) ) {

			// Check whether the donor name and WP_User name is same or not.
			if ( sanitize_title( $donor_billing_name ) != sanitize_title( $donor_name ) ) {
				$value .= $donor_billing_name . ' (';
			}

			$value .= '<a href="' . esc_url( admin_url( "edit.php?post_type=give_forms&page=give-donors&view=overview&id=$donor_id" ) ) . '">' . $donor_name . '</a>';

			// Check whether the donor name and WP_User name is same or not.
			if ( sanitize_title( $donor_billing_name ) != sanitize_title( $donor_name ) ) {
				$value .= ')';
			}
		} else {
			$email = give_get_payment_user_email( $payment->ID );
			$value .= '<a href="' . esc_url( admin_url( "edit.php?post_type=give_forms&page=give-payment-history&s=$email" ) ) . '">' . esc_html__( '(donor missing)', 'give' ) . '</a>';
		}

		return apply_filters( 'give_payments_table_column', $value, $payment->ID, 'donor' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since  1.0
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'                 => __( 'Delete', 'give' ),
			'set-status-publish'     => __( 'Set To Completed', 'give' ),
			'set-status-pending'     => __( 'Set To Pending', 'give' ),
			'set-status-processing'  => __( 'Set To Processing', 'give' ),
			'set-status-refunded'    => __( 'Set To Refunded', 'give' ),
			'set-status-revoked'     => __( 'Set To Revoked', 'give' ),
			'set-status-failed'      => __( 'Set To Failed', 'give' ),
			'set-status-cancelled'   => __( 'Set To Cancelled', 'give' ),
			'set-status-abandoned'   => __( 'Set To Abandoned', 'give' ),
			'set-status-preapproval' => __( 'Set To Preapproval', 'give' ),
			'resend-receipt'         => __( 'Resend Email Receipts', 'give' ),
		);

		return apply_filters( 'give_payments_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function process_bulk_action() {
		$ids    = isset( $_GET['payment'] ) ? $_GET['payment'] : false;
		$action = $this->current_action();

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		if ( empty( $action ) ) {
			return;
		}

		foreach ( $ids as $id ) {

			// Detect when a bulk action is being triggered.
			switch ( $this->current_action() ) {

				case'delete':
					give_delete_donation( $id );
					break;

				case 'set-status-publish':
					give_update_payment_status( $id, 'publish' );
					break;

				case 'set-status-pending':
					give_update_payment_status( $id, 'pending' );
					break;

				case 'set-status-processing':
					give_update_payment_status( $id, 'processing' );
					break;

				case 'set-status-refunded':
					give_update_payment_status( $id, 'refunded' );
					break;
				case 'set-status-revoked':
					give_update_payment_status( $id, 'revoked' );
					break;

				case 'set-status-failed':
					give_update_payment_status( $id, 'failed' );
					break;

				case 'set-status-cancelled':
					give_update_payment_status( $id, 'cancelled' );
					break;

				case 'set-status-abandoned':
					give_update_payment_status( $id, 'abandoned' );
					break;

				case 'set-status-preapproval':
					give_update_payment_status( $id, 'preapproval' );
					break;

				case 'resend-receipt':
					give_email_donation_receipt( $id, false );
					break;
			}// End switch().

			/**
			 * Fires after triggering bulk action on payments table.
			 *
			 * @since 1.7
			 *
			 * @param int    $id             The ID of the payment.
			 * @param string $current_action The action that is being triggered.
			 */
			do_action( 'give_payments_table_do_bulk_action', $id, $this->current_action() );
		}// End foreach().

	}

	/**
	 * Retrieve the payment counts
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function get_payment_counts() {

		$args = array();

		if ( isset( $_GET['user'] ) ) {
			$args['user'] = urldecode( $_GET['user'] );
		} elseif ( isset( $_GET['donor'] ) ) {
			$args['donor'] = absint( $_GET['donor'] );
		} elseif ( isset( $_GET['s'] ) ) {
			$is_user = strpos( $_GET['s'], strtolower( 'user:' ) ) !== false;
			if ( $is_user ) {
				$args['user'] = absint( trim( str_replace( 'user:', '', strtolower( $_GET['s'] ) ) ) );
				unset( $args['s'] );
			} else {
				$args['s'] = sanitize_text_field( $_GET['s'] );
			}
		}

		if ( ! empty( $_GET['start-date'] ) ) {
			$args['start-date'] = urldecode( $_GET['start-date'] );
		}

		if ( ! empty( $_GET['end-date'] ) ) {
			$args['end-date'] = urldecode( $_GET['end-date'] );
		}

		$args['form_id'] = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : null;

		$payment_count          = give_count_payments( $args );
		$this->complete_count   = $payment_count->publish;
		$this->pending_count    = $payment_count->pending;
		$this->processing_count = $payment_count->processing;
		$this->refunded_count   = $payment_count->refunded;
		$this->failed_count     = $payment_count->failed;
		$this->revoked_count    = $payment_count->revoked;
		$this->cancelled_count  = $payment_count->cancelled;
		$this->abandoned_count  = $payment_count->abandoned;

		foreach ( $payment_count as $count ) {
			$this->total_count += $count;
		}
	}

	/**
	 * Retrieve all the data for all the payments.
	 *
	 * @access public
	 * @since  1.0
	 * @return array  objects in array containing all the data for the payments
	 */
	public function payments_data() {

		$per_page   = $this->per_page;
		$orderby    = isset( $_GET['orderby'] ) ? urldecode( $_GET['orderby'] ) : 'ID';
		$order      = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$user       = isset( $_GET['user'] ) ? $_GET['user'] : null;
		$donor      = isset( $_GET['donor'] ) ? $_GET['donor'] : null;
		$status     = isset( $_GET['status'] ) ? $_GET['status'] : give_get_payment_status_keys();
		$meta_key   = isset( $_GET['meta_key'] ) ? $_GET['meta_key'] : null;
		$year       = isset( $_GET['year'] ) ? $_GET['year'] : null;
		$month      = isset( $_GET['m'] ) ? $_GET['m'] : null;
		$day        = isset( $_GET['day'] ) ? $_GET['day'] : null;
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] ) ? sanitize_text_field( $_GET['end-date'] ) : $start_date;
		$form_id    = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : null;

		if ( ! empty( $search ) ) {
			$status = 'any'; // Force all payment statuses when searching.
		}

		$args = array(
			'output'     => 'payments',
			'number'     => $per_page,
			'page'       => isset( $_GET['paged'] ) ? $_GET['paged'] : null,
			'orderby'    => $orderby,
			'order'      => $order,
			'user'       => $user,
			'donor'      => $donor,
			'status'     => $status,
			'meta_key'   => $meta_key,
			'year'       => $year,
			'month'      => $month,
			'day'        => $day,
			's'          => $search,
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'give_forms' => $form_id,
		);

		if ( is_string( $search ) && false !== strpos( $search, 'txn:' ) ) {
			$args['search_in_notes'] = true;
			$args['s']               = trim( str_replace( 'txn:', '', $args['s'] ) );
		}

		$p_query = new Give_Payments_Query( $args );

		return $p_query->get_payments();

	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  1.0
	 * @uses   Give_Payment_History_Table::get_columns()
	 * @uses   Give_Payment_History_Table::get_sortable_columns()
	 * @uses   Give_Payment_History_Table::payments_data()
	 * @uses   WP_List_Table::get_pagenum()
	 * @uses   WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {

		wp_reset_vars( array( 'action', 'payment', 'orderby', 'order', 's' ) );

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns.
		$sortable = $this->get_sortable_columns();
		$data     = $this->payments_data();
		$status   = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		$this->_column_headers = array( $columns, $hidden, $sortable );

		switch ( $status ) {
			case 'publish':
				$total_items = $this->complete_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'processing':
				$total_items = $this->processing_count;
				break;
			case 'refunded':
				$total_items = $this->refunded_count;
				break;
			case 'failed':
				$total_items = $this->failed_count;
				break;
			case 'revoked':
				$total_items = $this->revoked_count;
				break;
			case 'cancelled':
				$total_items = $this->cancelled_count;
				break;
			case 'abandoned':
				$total_items = $this->abandoned_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
			default:
				// Retrieve the count of the non-default-Give status.
				$count       = wp_count_posts( 'give_payment' );
				$total_items = isset( $count->{$status} ) ? $count->{$status} : 0;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			// We have to calculate the total number of items.
			'per_page'    => $this->per_page,
			// We have to determine how many items to show on a page.
			'total_pages' => ceil( $total_items / $this->per_page ),
			// We have to calculate the total number of pages.
		) );
	}
}
