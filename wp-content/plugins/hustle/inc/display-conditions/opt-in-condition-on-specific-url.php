<?php

class Opt_In_Condition_On_Specific_Url extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){
		return isset( $this->args->urls ) ? $this->utils()->check_url( $this->utils()->get_current_actual_url(), explode(PHP_EOL,$this->args->urls) ) : true;
	}

	function label()
	{
		return __("Only on specific URLs", Opt_In::TEXT_DOMAIN);
	}
}