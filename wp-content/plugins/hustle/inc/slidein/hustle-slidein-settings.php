<?php

class Hustle_Slidein_Settings extends Hustle_Meta
{
	var $defaults = array(
		'animation_in' => '',
		'animation_out' => '',
		'after_close' => 'keep_show',
		'expiration' => 365,
		'expiration_unit' => 'days',
		'allow_scroll_page' => 0,
		'not_close_on_background_click' => 0,
		'on_submit' => '',
	);
}