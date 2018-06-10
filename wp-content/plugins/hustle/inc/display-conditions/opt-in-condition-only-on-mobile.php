<?php

class Opt_In_Condition_Only_On_Mobile extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){
		return wp_is_mobile();
	}

	function label()
	{
		return __("Only on mobile", Opt_In::TEXT_DOMAIN);
	}
}