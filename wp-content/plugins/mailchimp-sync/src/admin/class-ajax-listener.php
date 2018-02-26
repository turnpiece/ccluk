<?php

namespace MC4WP\Sync;

class AjaxListener {

	/**
	 * @var UserHandler
	 */
	protected $user_handler;

	/**
	 * @var Users
	 */
	protected $users;
	

	/**
	 * Constructor
	 *
	 * @param UserHandler $user_handler
	 * @param Users $users
	 */
	public function __construct( UserHandler $user_handler, Users $users  ) {
		$this->user_handler = $user_handler;
		$this->users = $users;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'wp_ajax_mcs_wizard', array( $this, 'route' ) );
		add_action( 'wp_ajax_mcs_autocomplete_user_field', array( $this, 'autocomplete_user_field' ) );
	}

	/**
	 * Route the AJAX call to the correct method
	 */
	public function route() {
		// make sure user is allowed to make the AJAX call
		if( ! current_user_can( 'manage_options' )
		    || empty( $_REQUEST['mcs_action'] ) ) {
			die( '-1' );
		}

		// check if method exists and is allowed
		$allowed_actions = array(
			'get_users',
			'handle_user',
			'get_user_count'
		);

		$requested_action = $_REQUEST['mcs_action'];
		if( in_array( $requested_action, $allowed_actions ) ) {
			$this->{$requested_action}();
		}

		exit;
	}

	/**
	 * Get user count
	 */
	protected function get_user_count() {
		$count = $this->users->count();
		$this->respond( $count );
	}

	/**
	 * Responds with an array of all user ID's
	 */
	protected function get_users() {
		$offset = ( isset( $_REQUEST['offset'] ) ? intval( $_REQUEST['offset'] ) : 0 );
		$limit = ( isset( $_REQUEST['limit'] ) ? intval( $_REQUEST['limit'] ) : 0 );

		// get users
		$users = $this->users->get( array( 'fields' => array( 'ID', 'user_login', 'user_email' ), 'offset' => $offset, 'number' => $limit ));

		// send response
		$this->respond( $users );
	}

	/**
	 * Subscribes the provided user ID
	 */
	protected function handle_user() {

		$user_id = (int) $_REQUEST['user_id'];
		$result = $this->user_handler->handle_user( $user_id );

		// TODO: Catch exception here.
		if( ! $result ) {
			$this->respond(
				array(
					'success' => 0,
					'message' =>  $this->user_handler->error,
				)
			);
		}

		$data = array(
			'success' => 1,
			'message' => sprintf( __( 'Updated user %d', 'mailchimp-sync' ), $user_id ),
		);

		$this->respond( $data );
	}

	/**
	 * Autocomplete user meta key
	 */
	public function autocomplete_user_field() {
		global $wpdb;
		$query = sanitize_text_field( $_GET['q'] );

		// query database
		$sql = $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta um WHERE um.meta_key LIKE '%s' GROUP BY um.meta_key", '%' . $query . '%' );
		$meta_key_values = $wpdb->get_col( $sql );

		// query custom fields
		$custom_values = array( 'role', 'user_login', 'ID' );
		$custom_values = array_filter( $custom_values, function( $value ) use( $query ) { return strpos( $value, $query ) !== false; });

		// combine sources
		$values = $meta_key_values + $custom_values;

		// TODO: filter response
		$this->respond( join( PHP_EOL, $values ) );
	}

	/**
	 * Send a JSON response
	 *
	 * @param $data
	 * @return void
	 */
	private function respond( $data ) {

		send_origin_headers();
		@header( 'X-Content-Type-Options: nosniff' );
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		send_nosniff_header();
		nocache_headers();

		// clear output, some plugins might have thrown errors by now.
		if( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		wp_send_json( $data );
		exit;
	}

}
