<?php

class Opt_In_Condition_Not_In_A_Country extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){
		return isset( $this->args->countries ) ?  !( $this->utils()->test_country( $this->args->countries ) ) : true;
	}

	function label()
	{
		return __("Not from certain countries", Opt_In::TEXT_DOMAIN);
	}
}