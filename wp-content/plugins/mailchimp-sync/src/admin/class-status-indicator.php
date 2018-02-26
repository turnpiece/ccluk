<?php

namespace MC4WP\Sync\Admin;

use MC4WP\Sync\Users;

class StatusIndicator {

	/**
	 * @var bool Boolean indicating whether all users are subscribed to the selected list
	 */
	public $status = false;

	/**
	 * @var int Percentage of users subscribed to list
	 */
	public $progress = 0;

	/**
	 * @var int Number of registered WP users
	 */
	public $user_count = 0;

	/**
	 * @var int Number of WP Users on the selected list (according to local meta value)
	 */
	public $subscriber_count = 0;

	/**
	 * @var Users
	 */
	protected $users;

	/**
	 * @param Users $users
	 */
	public function __construct( Users $users ) {
		$this->users = $users;
	}

	/**
	 * Runs calculations.
	 */
	public function check() {
		$this->user_count = $this->users->count();
		$this->subscriber_count = $this->users->count_subscribers();

		$this->status = ( $this->user_count === $this->subscriber_count );
		$this->progress = ( $this->user_count > 0 ) ? ceil( $this->subscriber_count / $this->user_count * 100 ) : 0;
	}



}