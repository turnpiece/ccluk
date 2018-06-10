<?php

class Hustle_SShare_Settings extends Hustle_Meta
{
	var $defaults = array(
		'floating_social_enabled' => true,
		'widget_enabled' => true,
		'shortcode_enabled' => true,
		'conditions' => '',
		'location_type' => 'screen',
		'location_target' => '',
		'location_align_x' => 'left',
		'location_align_y' => 'top',
		'location_top' => 0,
		'location_bottom' => 0,
		'location_right' => 0,
		'location_left' => 0
	);
}