<?php

class Opt_In_Condition_From_Specific_Ref extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){
		return isset( $this->args->refs ) ?  $this->utils()->test_referrer( $this->args->refs ) : true;
	}

	function label(){
		return __("From certain referrers", Opt_In::TEXT_DOMAIN);
	}
}