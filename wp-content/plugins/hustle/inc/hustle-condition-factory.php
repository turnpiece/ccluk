<?php

/**
 * Factory to instantiate display conditions
 *
 * Class Hustle_Condition_Factory
 */
class Hustle_Condition_Factory
{

	/**
	 * Callback to use with preg_replace_callback in self::build
	 *
	 * @param $matches
	 * @return string
	 */
	private static function _preg_replace_callback( $matches ){
		return $matches[1] . ucfirst($matches[2]);
	}

	/**
	 * Instantiates and returns instance of Opt_In_Condition_Interface
	 *
	 * @param $condition_key
	 * @param $args
	 * @return Opt_In_Condition_Interface | Opt_In_Condition_Abstract
	 */
	public static function build( $condition_key, $args ){
		$class = "Opt_In_Condition_" . preg_replace_callback("/(\\_)([A-Za-z]+)/ui",
				array(__CLASS__, "_preg_replace_callback"),
				ucfirst( $condition_key )
			);

		return ( class_exists($class) )
			? new $class( $args )
			: false
		;
	}
}