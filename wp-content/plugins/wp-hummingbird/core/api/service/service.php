<?php

abstract class WP_Hummingbird_API_Service {

	protected $name = '';

	/**
	 * @var null|WP_Hummingbird_API_Request
	 */
	protected $request = null;

	/**
	 * Get the Service Name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

}