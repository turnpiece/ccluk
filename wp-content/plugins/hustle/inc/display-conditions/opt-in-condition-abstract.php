<?php

abstract class Opt_In_Condition_Abstract
{
	protected $module_type;

	protected $args;

	/**
	 * Instance of Opt_In_Condition_Utils
	 *
	 * @var Opt_In_Utils
	 */
	private $_utils;

	/**
	 * Instance of
	 *
	 * @var Opt_In_Geo
	 */
	private $_geo;

	function __construct($args){
		$this->args = (object)$args;
	}

	/**
	 * Instanctiates and returns Opt_In_Condition_Utils
	 *
	 * @return Opt_In_Utils
	 */
	function utils(){
		if( empty( $this->_utils ) ){
			if( empty( $this->_geo ) )
				$this->_geo = new Opt_In_Geo();

			$this->_utils = new Opt_In_Utils( $this->_geo );
		}

		return $this->_utils;
	}

	/**
	 * Sets optin type for the condition
	 *
	 * @param $type
	 */
	function set_type( $type ){
		$this->module_type = $type;
	}

	/**
	 * @return string
	 */
	abstract function label();
}