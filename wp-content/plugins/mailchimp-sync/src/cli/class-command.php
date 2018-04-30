<?php

namespace MC4WP\Sync\CLI;

use MC4WP\Sync\Users;
use MC4WP\Sync\UserHandler;
use WP_CLI, WP_CLI_Command;
use MC4WP\Sync\UserSubscriber;

class Command extends WP_CLI_Command {

	/**
	 * @var array
	 */
	protected $options;

	/** 
	 * @var Users
	 */
	protected $users;

	/**
	 * @var UserSubscriber
	 */
	protected $user_subscriber;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = $GLOBALS['mailchimp_sync']->options;

		$list_id = $this->options['list'];
		$this->users = new Users( $list_id, $this->options['role'] );
		$this->user_handler = new UserHandler( $list_id, $this->users, $this->options );

		parent::__construct();
	}

	/**
	 * Synchronize all users (with a given role)
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * ## OPTIONS
	 *
	 * <role>
	 * : User role to synchronize
	 *
	 * ## EXAMPLES
	 *
	 *     wp mailchimp-sync all --role=administrator
	 *
	 * @synopsis [--role=<role>]
	 *
	 * @subcommand all
	 */
	public function all( $args, $assoc_args ) {

		$user_query_args = array();

		// allow overriding user role with --role
		// by default, the stored setting will be used.
		$user_role = ( isset( $assoc_args['role'] ) ) ? $assoc_args['role'] : null;
		if( ! is_null( $user_role ) ) {
			$user_query_args['role'] = $user_role;
		}

		// start by counting all users
		$users = $this->users->get( $user_query_args );
		$count = count( $users );

		WP_CLI::line( "$count users found." );

		if( $count <= 0 ) {
			return;
		}

		// show progress bar
		$notify = \WP_CLI\Utils\make_progress_bar( 'Working', $count );
		$user_ids = wp_list_pluck( $users, 'ID' );
		$errors = array();

		foreach( $user_ids as $user_id ) {
			$result = $this->user_handler->handle_user( $user_id );

			if( ! $result ) {
				$errors[$user_id] = $this->user_handler->error;
			}

			$notify->tick();
		}

		$notify->finish();

		if( ! empty( $errors ) ) {
			$errors_msg =  'The following errors occurred: ';
			foreach( $errors as $user_id => $e ) {
				$errors_msg .= PHP_EOL . sprintf( " - User #%d: %s", $user_id, $e );
			}
			WP_CLI::warning( $errors_msg );
		}

		WP_CLI::success( sprintf( "Synchronized %d users.", $count ) );
	}

	/**
	 * Synchronize a single user
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * ## OPTIONS
	 *
	 * <user_id>
	 * : ID of the user to synchronize
	 *
	 * ## EXAMPLES
	 *
	 *     wp mailchimp-sync user 5
	 *
	 * @synopsis <user_id>
	 *
	 * @subcommand user
	 */
	public function user( $args, $assoc_args ) {

		$user_id = absint( $args[0] );

		$result = $this->user_handler->handle_user( $user_id );

		if( $result ) {
			WP_CLI::success( sprintf( "User %d synchronized.", $user_id ) );
		} else {
			WP_CLI::error( $this->user_handler->error );
		}

	}

	/**
	 * @deprecated 1.4
	 * @subcommand sync-user
	 */
	public function sync_user( $args, $assoc_args ) {
		$this->user( $args, $assoc_args );
	}

	/**
	 * @deprecated 1.4
	 * @subcommand sync-all
	 */
	public function sync_all( $args, $assoc_args ) {
		$this->all( $args, $assoc_args );
	}

	/**
	* @subcommand process-queue
	*/
	public function process_queue( $args, $assoc_args ) {
		do_action( 'mailchimp_user_sync_run' );	
	}	
}
