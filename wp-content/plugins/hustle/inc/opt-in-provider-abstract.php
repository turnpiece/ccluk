<?php


abstract class Opt_In_Provider_Abstract{

	const LISTS = "lists";

	/**
	 * Sets argument to the provider class
	 *
	 * @param $field
	 * @param $value
	 */
	function set_arg( $field, $value ){
		$this->{$field} = $value;
	}

	/**
	 * Updates provider option with the new value
	 *
	 * @uses update_site_option
	 * @param $option_key
	 * @param $option_value
	 * @return bool
	 */
	abstract function update_option( $option_key, $option_value );

	/**
	 * Retrieves provider option from db
	 *
	 * @uses get_site_option
	 * @param $option_key
	 * @return mixed
	 */
	abstract function get_option( $option_key, $default );

}