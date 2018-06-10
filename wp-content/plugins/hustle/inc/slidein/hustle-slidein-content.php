<?php

class Hustle_Slidein_Content extends Hustle_Meta
{
	var $defaults = array(
		'module_name' => '',
		'has_title' => 0,
		'title' => '',
		'sub_title' => '',
		'main_content' => '',
		'use_feature_image' => 0,
		'feature_image' => '',
		'feature_image_location' => 'left',
		'feature_image_hide_on_mobile' => 0,
		'show_cta' => 0,
		'show_gdpr' => 0,
		'cta_label' => '',
		'cta_url' => '',
		'cta_target' => 'blank',
		'use_email_collection' => 0,
		'save_local_list' => 0,
		'active_email_service' => '',
		'email_services' => '',
		'form_elements' => '',
		'after_successful_submission' => 'show_success',
		'success_message' => '',
		'gdpr_message' => 'Yes, I agree with the <a href="">privacy policy</a>.',
		'auto_close_success_message' => 0,
		'auto_close_time' => 5,
		'auto_close_unit' => 'seconds',
		'redirect_url' => '',
	);
}