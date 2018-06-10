<?php

/**
 * Interface to check display condition
 *
 * Class Opt_In_Condition_Interface
 */
interface Opt_In_Condition_Interface
{
	function is_allowed( Hustle_Model $optin );
}