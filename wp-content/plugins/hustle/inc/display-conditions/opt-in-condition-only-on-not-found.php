<?php

 /*
  * This functionality has been changed to only affect 404 pages.
  * Name is not changed to keep legacy condition but it now determines
  * if the popup should be displayed on 404 pages rather than only on 404 pages.
  */
class Opt_In_Condition_Only_On_Not_Found extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){
		return is_404();
	}

	function label()
	{
		return __("404 page", Opt_In::TEXT_DOMAIN);
	}
}