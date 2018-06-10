<?php

class Opt_In_Condition_Shown_Less_Than extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{

	function is_allowed(Hustle_Model $module){
		if( !isset( $this->args->less_than ) )
			return true;

		$cookie_key = $this->get_cookie_key($module->module_type) . $module->id;

		$show_count = isset( $_COOKIE[ $cookie_key ] ) ?  (int) $_COOKIE[ $cookie_key ] : 0;
		return $show_count < (int) $this->args->less_than;
	}

	function label()
	{
		return isset( $this->args->less_than ) ? __("Shown less than specific number of times", Opt_In::TEXT_DOMAIN) : null;
	}

	function get_cookie_key( $module_type ) {
		return 'hustle_module_show_count-' . $module_type . '-';
	}
}