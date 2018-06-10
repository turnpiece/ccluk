<?php

class Opt_In_Condition_Visitor_Logged_In extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){
		return is_user_logged_in();
	}

	function label()
	{
		return __("Only if user is logged in", Opt_In::TEXT_DOMAIN);
	}
}