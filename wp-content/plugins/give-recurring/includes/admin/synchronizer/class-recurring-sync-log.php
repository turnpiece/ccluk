<?php
/**
 * Give Recurring Sync Log
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Give_Recurring_Sync_Log
 */
class Give_Recurring_Sync_Log extends WP_List_Table {

	/**
	 * @var int
	 */
	private $per_page = 30;

	/**
	 * @var Give_Subscription
	 */
	public $subscription;

	/**
	 * Give_Recurring_Email_Log constructor.
	 */
	public function __construct() {

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'recurring_sync_log',  //singular name of the listed records
			'plural'   => 'recurring_sync_logs', //plural name of the listed records
			'ajax'     => false              //does this table support ajax?
		) );

	}

	/**
	 * Setup columns
	 *
	 * @access      public
	 * @since       1.3
	 * @return      array
	 */
	function get_columns() {

		$columns = array(
			'log_id'          => __( 'Log ID', 'give-recurring' ),
			'sync_date'       => __( 'Date', 'give-recurring' ),
			'subscription_id' => __( 'Subscription ID', 'give-recurring' ),
			'gateway'         => __( 'Gateway', 'give-recurring' ),
			'action'          => __( 'View Log', 'give-recurring' ),
		);

		return $columns;
	}

	/**
	 * Output the ID of the sync log.
	 *
	 * @param WP_Post $item
	 *
	 * @since       1.3
	 * @return      string
	 */
	function column_log_id( $item ) {
		return '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-tools&section=recurring_sync_logs&tab=logs&log-id=' . $item->ID ) . '" class="give-item-label give-item-label-gray">' . $item->ID . '</a>';
	}

	/**
	 * Output the date column
	 *
	 * @param WP_Post $item
	 *
	 * @since       1.3
	 * @return      string
	 */
	function column_sync_date( $item ) {
		return $item->post_date;
	}

	/**
	 * Output the Subscription ID column.
	 *
	 * @param WP_Post $item
	 *
	 * @since       1.3
	 * @return      string
	 */
	function column_subscription_id( $item ) {

		$sub_id = give_get_meta( $item->ID, '__give_recurring_sync_log_subscription_id', true );

		if ( ! empty( $sub_id ) ) {
			return '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&id=' . $sub_id ) . '" class="give-item-label give-item-label-gray">' . $item->ID . '</a>';
		}

		return __( 'Not found', 'give-recurring' );
	}


	/**
	 * Output the Gateway column.
	 *
	 * @param WP_Post $item
	 *
	 * @since       1.3
	 * @return      string
	 */
	function column_gateway( $item ) {

		$meta = give_get_meta( $item->ID, '__give_recurring_sync_log_gateway', true );

		return ! empty( $meta ) ? ucfirst( $meta ) : __( 'Not found', 'give-recurring' );
	}

	/**
	 * Output the date column
	 *
	 * @param WP_Post $item
	 *
	 * @since       1.3
	 * @return      string
	 */
	function column_action( $item ) {
		return '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-tools&section=recurring_sync_logs&tab=logs&log-id=' . $item->ID ) . '" class="button button-small"><span class="dashicons dashicons-visibility"></span></a>';
	}


	/**
	 * Display Tablenav (extended)
	 *
	 * Display the table navigation above or below the table even when no items in the logs, so nav doesn't disappear
	 *
	 * @see    : https://github.com/impress-org/give/issues/564
	 *
	 * @since  1.1
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {

		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">

            <div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
            </div>
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

            <br class="clear"/>
        </div>
		<?php
	}


	/**
	 * Retrieve the current page number
	 *
	 * @access      public
	 * @since       1.0
	 * @return      int
	 */
	function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Outputs the log views
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		give_log_views();
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access      public
	 * @since       1.0
	 * @return      int
	 */
	function count_total_items() {

		$args = array(
			'post_type' => 'give_recur_sync_log',
			'fields'    => 'ids',
			'nopaging'  => true
		);

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			return $query->post_count;
		}

		return 0;
	}

	/**
	 * Query database for license data and prepare it for the table
	 *
	 * @access      public
	 * @since       1.0
	 * @return      array
	 */
	function logs_data() {

		$args = array(
			'post_type'      => 'give_recur_sync_log',
			'post_status'    => array( 'publish', 'future' ),
			'posts_per_page' => $this->per_page,
			'paged'          => $this->get_paged(),
		);

		return get_posts( $args );

	}

	/**
	 * Sets up the list table items
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	function prepare_items() {

		/**
		 * First, lets decide how many records per page to show
		 */
		$columns = $this->get_columns();

		$this->_column_headers = array( $columns, array(), array() );

		$this->items = $this->logs_data();

		$total_items = $this->count_total_items();

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $total_items / $this->per_page )
		) );

	}

}