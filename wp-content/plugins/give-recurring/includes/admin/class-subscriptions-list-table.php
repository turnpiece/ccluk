<?php
/**
 * Subscription List Table Class.
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

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Give Subscriptions List Table Class.
 *
 * @access      private
 */
class Give_Subscription_Reports_Table extends WP_List_Table {

	/**
	 * Give_Subscription Object
	 *
	 * @var Give_Subscription
	 *
	 * @since       1.0
	 */
	public $subscription;

	/**
	 * Number of results to show per page.
	 *
	 * @since       1.2
	 */
	public $per_page = 30;

	/**
	 * statuses Count.
	 *
	 * @since       1.5.8
	 *
	 * @var array
	 */
	public $statuses_array = array(
		'total_count' => 0
	);

	/**
	 * statuses.
	 *
	 * @since       1.5.8
	 *
	 * @var array
	 */
	public $statuses = array();


	/**
	 * Get things started.
	 *
	 * @access      private
	 * @since       1.0
	 */
	function __construct() {

		$this->statuses = give_recurring_get_subscription_statuses();

		foreach ( $this->statuses as $status_key => $status_name ) {
			$this->statuses_array[ $status_key . '_count' ] = 0;
		}

		// Get all the status

		// Set parent defaults
		parent::__construct( array(
			'singular' => 'subscription',
			'plural'   => 'subscriptions',
			'ajax'     => false,
		) );

		$this->process_bulk_action();
		$this->get_subscription_counts();

	}

	/**
	 * Retrieve the view types.
	 *
	 * @access public
	 * @since  1.1.2
	 * @return array $views All the views available.
	 */
	public function get_views() {

		$current = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$views   = array();
		$tabs    = array(
			'all' => array(
				'total_count',
				esc_html__( 'All', 'give-recurring' ),
			)
		);

		foreach ( $this->statuses as $status_key => $status_name ) {
			$tabs[ $status_key ] = array(
				$status_key . '_count',
				$status_name,
			);
		}

		foreach ( $tabs as $key => $tab ) {
			$count_key = $tab[0];
			$name      = $tab[1];
			$count     = $this->statuses_array[ $count_key ];

			/**
			 * Filter can be used to show all the statuses inside regardless of whether they are empty or not.
			 * By default empty statuses are hidden.
			 *
			 * @since 1.4
			 *
			 * @param string $key   Current view tab value.
			 * @param int    $count Number of donation inside the tab.
			 */
			if ( 'all' === $key || $key === $current || apply_filters( 'give_subscriptions_table_show_all_status', 0 < $count, $key, $count ) ) {

				$views[ $key ] = sprintf(
					'<a href="%s" %s >%s&nbsp;<span class="count">(%s)</span></a>',
					esc_url( ( 'all' === (string) $key ) ? remove_query_arg( array(
						'status',
						'paged'
					) ) : add_query_arg( array(
						'status' => $key,
						'paged'  => false,
					) ) ),
					( ( 'all' === $key && empty( $current ) ) ) ? 'class="current"' : ( $current == $key ) ? 'class="current"' : '',
					$name,
					$count
				);
			}
		}

		return apply_filters( 'give_recurring_subscriptions_table_views', $views );

	}

	/**
	 * Add donation search filter.
	 *
	 * @since 1.4
	 *
	 * @return void
	 */
	public function advanced_filters() {
		$start_date = isset( $_GET['start-date'] ) ? strtotime( give_clean( $_GET['start-date'] ) ) : null;
		$end_date   = isset( $_GET['end-date'] ) ? strtotime( give_clean( $_GET['end-date'] ) ) : null;
		$status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
		$donor      = isset( $_GET['donor'] ) ? sanitize_text_field( $_GET['donor'] ) : '';
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$form_id    = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		?>
		<div id="give-subscriptions-filters" class="give-filters">
			<?php $this->search_box( esc_html__( 'Search', 'give-recurring' ), 'give-subscriptions' ); ?>
			<div id="give-payment-date-filters">
				<div class="give-filter give-filter-half">
					<label for="start-date"
						   class="give-start-date-label"><?php _e( 'Start Date', 'give-recurring' ); ?></label>
					<input type="text"
						   id="start-date"
						   name="start-date"
						   class="give_datepicker"
						   autocomplete="off"
						   value="<?php echo $start_date ? date_i18n( give_date_format(), $start_date ) : ''; ?>"
						   data-standard-date="<?php echo $start_date ? date( 'Y-m-d', $start_date ) : $start_date; ?>"
						   placeholder="<?php _e( 'Start Date', 'give-recurring' ); ?>"
					/>
				</div>
				<div class="give-filter give-filter-half">
					<label for="end-date" class="give-end-date-label"><?php _e( 'End Date', 'give-recurring' ); ?></label>
					<input type="text"
						   id="end-date"
						   name="end-date"
						   class="give_datepicker"
						   autocomplete="off"
						   value="<?php echo $end_date ? date_i18n( give_date_format(), $end_date ) : ''; ?>"
						   data-standard-date="<?php echo $end_date ? date( 'Y-m-d', $end_date ) : $end_date; ?>"
						   placeholder="<?php _e( 'End Date', 'give-recurring' ); ?>"
					/>
				</div>
			</div>
			<div id="give-payment-form-filter" class="give-filter">
				<label for="give-donation-forms-filter"
					   class="give-donation-forms-filter-label"><?php esc_html_e( 'Form', 'give-recurring' ); ?></label>
				<?php
				// Filter Donations by Donation Forms.
				echo Give()->html->forms_dropdown( array(
					'name'     => 'form_id',
					'id'       => 'give-donation-forms-filter',
					'class'    => 'give-donation-forms-filter',
					'selected' => $form_id, // Make sure to have $form_id set to 0, if there is no selection.
					'chosen'   => true,
					'number'   => 30,
				) );
				?>
			</div>

			<?php if ( ! empty( $status ) ) : ?>
				<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>"/>
			<?php endif; ?>

			<div class="give-filter">
				<?php submit_button( __( 'Apply', 'give-recurring' ), 'secondary', '', false ); ?>
				<?php
				// Clear active filters button.
				if ( ! empty( $start_date ) || ! empty( $end_date ) || ! empty( $donor ) || ! empty( $search ) || ! empty( $status ) || ! empty( $form_id ) ) : ?>
					<a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions' ); ?>"
					   class="button give-clear-filters-button"><?php esc_html_e( 'Clear Filters', 'give-recurring' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<?php
	}


	/**
	 * Show the search field
	 *
	 * @since  1.4
	 * @access public
	 *
	 * @param string $text     Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<p class="search-box" role="search">
			<?php
			/**
			 * Fires in the payment history search box.
			 *
			 * Allows you to add new elements before the search box.
			 *
			 * @since 1.3
			 */
			do_action( 'give_recurring_subscriptions_search_box' );
			?>
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" placeholder="<?php esc_attr_e( 'Name, Email, or ID', 'give-recurring' ); ?>"
			       value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array(
				'ID' => 'search-submit',
			) ); ?><br/>
		</p>
		<?php
	}

	/**
	 * Render most columns.
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	function column_default( $item, $column_name ) {
		return $item->$column_name;
	}

	/**
	 * Customer column.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	function column_subscription( $item ) {

		$this->subscription = new Give_Subscription( $item->id );
		$subscriber         = new Give_Recurring_Subscriber( $item->customer_id );

		$email_link = ! empty( $subscriber->email ) ? '<a href="mailto:' . $subscriber->email . '" data-tooltip="' . __( 'Email donor', 'give-recurring' ) . '">' . $subscriber->email . '</a>' : __( '(email unknown)', 'give-recurring' );

		return sprintf(
			'<a href="%1$s" data-tooltip="%2$s">%3$d</a>&nbsp;%4$s&nbsp;<a href="%5$s">%6$s</a><br>%7$s',
			esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&id=' . $item->id ) ),
			esc_attr( __( 'View Details', 'give-recurring' ) ),
			$item->id,
			__( 'by', 'give-recurring' ),
			esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $subscriber->id ) ),
			$subscriber->name,
			$email_link
		);

	}

	/**
	 * Initial amount column.
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_cycle( $item ) {
		$args = array(
			'currency_code' => give_get_payment_currency_code( $item->parent_payment_id )
		);

		$interval = ! empty( $item->frequency ) ? $item->frequency : 1;

		return sprintf(
			'%1$s&nbsp;/&nbsp;%2$s',
			give_currency_filter( give_format_amount( $item->recurring_amount, array( 'currency' => $args['currency_code'] ) ), $args ),
			give_recurring_pretty_subscription_frequency( $item->period, false, false, $interval )
		);
	}


	/**
	 * Status column.
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_status( $item ) {
		return give_recurring_get_pretty_subscription_status( $this->subscription->get_status() );
	}

	/**
	 * Billing Times column.
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_bill_times( $item ) {
		return $this->subscription->get_subscription_progress();
	}

	/**
	 * Renewal date column.
	 *
	 * @access      private
	 * @since       1.2
	 * @return      string
	 */
	function column_renewal_date( $item ) {
		return $renewal_date = $item->get_renewal_date();
	}

	/**
	 * Payment column.
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_parent_payment_id( $item ) {
		return '<a href="' . esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $item->parent_payment_id ) ) . '">' .
		       give_get_payment_number( $item->parent_payment_id ) . '</a>';
	}

	/**
	 * Product ID column.
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_form_id( $item ) {
		return '<a href="' . esc_url( admin_url( 'post.php?action=edit&post=' . $item->form_id ) ) . '">' . esc_html( get_the_title( $item->form_id ) ) . '</a>';
	}

	/**
	 * Render the edit column.
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_actions( $item ) {
		return '<a href="' . esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&id=' . $item->id ) ) . '" data-tooltip="' . esc_attr( __( 'View Details', 'give-recurring' ) ) . '" class="button button-small"><span class="dashicons dashicons-visibility"></span></a>';
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @access      private
	 * @since       1.0
	 * @return      array
	 */
	function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />', // Render a checkbox instead of text.
			'subscription' => __( 'Subscription', 'give-recurring' ),
			'status'       => __( 'Status', 'give-recurring' ),
			'cycle'        => __( 'Billing Cycle', 'give-recurring' ),
			'bill_times'   => __( 'Progress', 'give-recurring' ),
			'renewal_date' => __( 'Renewal Date', 'give-recurring' ),
			'form_id'      => __( 'Form', 'give-recurring' ),
			'actions'      => __( 'Details', 'give-recurring' ),

		);

		return apply_filters( 'give_report_subscription_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since  1.8.2
	 *
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		$columns = array(
			'subscription' => array( 'ID', true ),
			'status'       => array( 'status', false ),
			'cycle'        => array( 'recurring_amount', false ),
			'renewal_date' => array( 'expiration', false ),
			'form_id'      => array( 'product_id', false ),
		);

		return apply_filters( 'give_subscriptions_table_sortable_columns', $columns );
	}

	/**
	 * Retrieve the current page number.
	 *
	 * @access      private
	 * @since       1.0
	 * @return      int
	 */
	function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Retrieves the search query string.
	 *
	 * @access public
	 * @since  1.3
	 * @return mixed string If search is present, false otherwise.
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}

	/**
	 * Retrieve the subscription counts.
	 *
	 * @access public
	 * @since  1.3
	 * @return void
	 */
	public function get_subscription_counts() {

		$db = new Give_Subscriptions_DB();

		$start_date = ! empty( $_GET['start-date'] ) ? urldecode( $_GET['start-date'] ) : '';
		$end_date   = ! empty( $_GET['end-date'] ) ? urldecode( $_GET['end-date'] ) : '';

		$default_args = array(
			'form_id' => ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : null,
			'search'  => ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '',
			'date'    => array( 'start' => $start_date, 'end' => $end_date ),

		);

		$this->statuses_array['total_count'] = $db->count( $default_args );

		$result = $db->count( array_merge( $default_args, array(
				'status'  => $this->statuses,
				'groupBy' => 'status',
			) )
		);

		if ( $result ) {
			foreach ( $this->statuses as $status_key => $status_name ) {
				$key                          = "{$status_key}_count";
				$this->statuses_array[ $key ] = ! empty( $result[ $status_key ] ) ? $result[ $status_key ] : 0;

			}
		}
	}

	/**
	 * Retrieves the donor data from db.
	 *
	 * @access public
	 * @since  1.3
	 *
	 * @return array $data The Donor data.
	 */
	public function subscriptions_data() {

		$order      = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
		$orderby    = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id';
		$status     = isset( $_GET['status'] ) ? $_GET['status'] : 'any';
		$year       = isset( $_GET['year'] ) ? $_GET['year'] : null;
		$month      = isset( $_GET['m'] ) ? $_GET['m'] : null;
		$day        = isset( $_GET['day'] ) ? $_GET['day'] : null;
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] ) ? sanitize_text_field( $_GET['end-date'] ) : $start_date;
		$form_id    = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : null;

		$args = array(
			'number'  => $this->per_page,
			'offset'  => $this->per_page * ( $this->get_paged() - 1 ),
			'order'   => $order,
			'orderby' => $orderby,
			'year'    => $year,
			'month'   => $month,
			'day'     => $day,
			'search'  => $search,
			'date'    => array( 'start' => $start_date, 'end' => $end_date ),
			'form_id' => $form_id,
		);

		if ( 'any' !== $status ) {
			$args['status'] = $status;
		}

		$db = new Give_Subscriptions_DB();

		$data = $db->get_subscriptions( $args );

		return apply_filters( 'give_subscriptions_column_query_data', $data );
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @access      private
	 * @since       1.0
	 * @uses        $this->_column_headers
	 * @uses        $this->items
	 * @uses        $this->get_columns()
	 * @uses        $this->get_sortable_columns()
	 * @uses        $this->set_pagination_args()
	 *
	 * @return      array
	 */
	public function prepare_items() {

		wp_reset_vars( array( 'action', 'subscription', 'orderby', 'order', 's' ) );

		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns.
		$status                = isset( $_GET['status'] ) ? $_GET['status'] : 'any';
		$sortable              = $this->get_sortable_columns();
		$data                  = $this->subscriptions_data();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
		$statuses_key          = $this->statuses;

		if ( ! empty( $status ) && array_key_exists( $status, $statuses_key ) ) {
			foreach ( $statuses_key as $status_key => $status_name ) {
				if ( $status === $status_key ) {
					$key         = $status_key . '_count';
					$total_items = $this->statuses_array[ $key ];
				}
			}
		} else {
			$total_items = $this->statuses_array['total_count'];
		}

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			// We have to calculate the total number of items.
			'per_page'    => $this->per_page,
			// We have to determine how many items to show on a page.
			'total_pages' => ceil( $total_items / $this->per_page ),
			// We have to calculate the total number of pages.
		) );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'give-recurring' ),
		);

		foreach ( $this->statuses as $status_key => $status_name ) {
			$actions[ $status_key ] = sprintf( __( 'Set To %s', 'give-recurring' ), $status_name );
		}

		return apply_filters( 'give_recurring_subscriptions_table_bulk_actions', $actions );
	}

	/**
	 * Get checkbox html.
	 *
	 * @param object $item Contains all the data for the checkbox column.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return string Displays a checkbox.
	 */
	public function column_cb( $item ) {

		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', 'subscription', $item->id );
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return void
	 */
	public function process_bulk_action() {
		$ids = isset( $_GET['subscription'] ) ? $_GET['subscription'] : false;

		$action = $this->current_action();

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		if ( empty( $action ) ) {
			return;
		}

		$statuses_key   = $this->statuses;
		$current_action = $this->current_action();

		foreach ( $ids as $id ) {

			// Detect when a bulk action is being triggered.
			if ( ! empty( $current_action ) && array_key_exists( $current_action, $statuses_key ) ) {
				foreach ( $statuses_key as $status_key => $status_name ) {
					if ( $status_key === $current_action ) {
						give_recurring_update_subscription_status( $id, $status_key );
					}
				}
			} elseif ( ! empty( $current_action ) && 'delete' === $current_action ) {
				give_recurring_subscription_delete( $id );
			}

			/**
			 * Fires after triggering bulk action on subscriptions table.
			 *
			 * @param int    $id             The ID of the subscriptions.
			 * @param string $current_action The action that is being triggered.
			 *
			 * @since 1.7
			 */
			do_action( 'give_recurring_subscriptions_table_do_bulk_action', $id, $this->current_action() );
		}// End foreach().
	}
}
