<?php

class Opt_In_Condition_Visitor_Has_Never_Commented extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){
		return !$this->utils()->has_user_commented();
	}

	function label()
	{
		return __("Only if user has never commented", Opt_In::TEXT_DOMAIN);
	}
}