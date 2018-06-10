<?php

class Opt_In_Condition_Not_From_Internal_Link extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){
		$internal = preg_replace( '#^https?://#', '', get_option( 'home' ) );
		return ! $this->utils()->test_referrer( $internal );
	}

	function label()
	{
		return __("Not from internal link", Opt_In::TEXT_DOMAIN);
	}
}