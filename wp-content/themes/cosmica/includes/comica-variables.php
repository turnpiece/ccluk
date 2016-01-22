<?php
function cosmica_get_theme_var()
{
$cosmica_theme_data = array(
	'cosmica_hide_demo_slider' 			=>	get_theme_mod('cosmica_hide_demo_slider' ,false),
	'social_link_open_in_new_tab' 		=>	get_theme_mod('social_link_open_in_new_tab',true),
	'social_link_facebook' 				=>	get_theme_mod('social_link_facebook' , esc_url('#')),
	'social_link_google' 				=>	get_theme_mod('social_link_google',esc_url('#')),
	'social_link_youtube' 				=>	get_theme_mod('social_link_youtube' ,esc_url('#')),
	'social_link_twitter' 				=>	get_theme_mod('social_link_twitter' ,esc_url('#')),
	'social_link_linkedin' 				=>	get_theme_mod('social_link_linkedin' ,esc_url('#')),
	'contact_email' 					=>	get_theme_mod('contact_email' ,'email@exapmle.com'),
	'contact_phone' 					=>	get_theme_mod('contact_phone' ,'000-000-0000'),
	'cosmica_show_logo'					=>	get_theme_mod('cosmica_show_logo', false),
	'cosmca_copyright_text' 			=>	get_theme_mod('cosmca_copyright_text' , '<div class="copyright"> ' .esc_html('&copy '.date("Y")). ' <a href="'. esc_url(get_site_url()).'" title="'. esc_attr(get_bloginfo('name')).'"><span>'. esc_html(get_bloginfo('name')).'</span></a> |  '.__('Theme by', 'cosmica').': <a href="'. esc_url('http://www.codeins.org').'" target="_blank" title="Codeins"><span>Codeins</span></a> | '. __('Proudly Powered by', 'cosmica').': <a href="'. esc_url('http://WordPress.org').'" target="_blank" title="WordPress"><span>WordPress</span></a> </div>'),
	'cosmca_call_header_text' 			=>	get_theme_mod('cosmca_call_header_text', __('Work Speaks Thousand Words','cosmica')),
	'cosmca_call_desc_text' 			=>	get_theme_mod('cosmca_call_desc_text' ,__('We are a group of passionate designers and developers who really love to create awesome wordpress themes & support.','cosmica')),
	'cosmca_call_bt1_text' 				=>	get_theme_mod('cosmca_call_bt1_text' ,__('Purchase Theme','cosmica')),
	'cosmca_call_bt1_link' 				=>	get_theme_mod('cosmca_call_bt1_link' ,esc_url('#')),
	'cosmca_call_bt2_text' 				=>	get_theme_mod('cosmca_call_bt2_text' ,__('See Details','cosmica')),
	'cosmca_call_bt2_link' 				=>	get_theme_mod('cosmca_call_bt2_link' ,esc_url('#')),
	'cosmca_services_header_text' 		=>	get_theme_mod('cosmca_services_header_text',__('Awesome Services','cosmica')),
	'cosmca_services_desc_text' 		=>	get_theme_mod('cosmca_services_desc_text',__('We are a group of passionate designers and developers who really love to create awesome WordPress themes & support','cosmica')),
	'cosmca_services_1_title' 			=>	get_theme_mod('cosmca_services_1_title' ,__('Lorem ipsum','cosmica')),
	'cosmca_services_1_desc' 			=>	get_theme_mod('cosmca_services_1_desc' ,__('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica')),
	'cosmca_services_2_title' 			=>	get_theme_mod('cosmca_services_2_title' ,__('Lorem ipsum','cosmica')),
	'cosmca_services_2_desc' 			=>	get_theme_mod('cosmca_services_2_desc' ,__('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica')),
	'cosmca_services_3_title' 			=>	get_theme_mod('cosmca_services_3_title' ,__('Lorem ipsum','cosmica')),
	'cosmca_services_3_desc' 			=>	get_theme_mod('cosmca_services_3_desc' ,__('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica')),
	'cosmca_services_4_title' 			=>	get_theme_mod('cosmca_services_4_title' ,__('Lorem ipsum','cosmica')),
	'cosmca_services_4_desc' 			=>	get_theme_mod('cosmca_services_4_desc' ,__('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica')),
	'cosmca_services_5_title' 			=>	get_theme_mod('cosmca_services_5_title' ,__('Lorem ipsum','cosmica')),
	'cosmca_services_5_desc' 			=>	get_theme_mod('cosmca_services_5_desc' ,__('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica')),
	'cosmca_services_6_title' 			=>	get_theme_mod('cosmca_services_6_title' ,__('Lorem ipsum','cosmica')),
	'cosmca_services_6_desc' 			=>	get_theme_mod('cosmca_services_6_desc' ,__('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica')),
	
	//slider
	'cosmica_slide_1_heading'			=>	get_theme_mod('cosmica_slide_1_heading', __('Awesome Responsive Theme','cosmica')),
	'cosmica_slide_1_description'		=>	get_theme_mod('cosmica_slide_1_description', __('Cosmica is responsive WordPress theme for any business purpose. Cosmica have Theme Options where you can set social media links and other customization to theme.','cosmica')),
	'cosmica_slide_1_bt_1_link'			=>	get_theme_mod('cosmica_slide_1_bt_1_link','#'),
	'cosmica_slide_1_bt_2_link'			=>	get_theme_mod('cosmica_slide_1_bt_2_link','#'),

	'cosmica_slide_2_heading'			=>	get_theme_mod('cosmica_slide_2_heading', __('Awesome Responsive Theme','cosmica')),
	'cosmica_slide_2_description'		=>	get_theme_mod('cosmica_slide_2_description', __('Cosmica is responsive WordPress theme for any business purpose. Cosmica have Theme Options where you can set social media links and other customization to theme.','cosmica')),
	'cosmica_slide_2_bt_1_link'			=>	get_theme_mod('cosmica_slide_2_bt_1_link','#'),
	'cosmica_slide_2_bt_2_link'			=>	get_theme_mod('cosmica_slide_2_bt_2_link','#'),

	'cosmica_slide_3_heading'			=>	get_theme_mod('cosmica_slide_3_heading', __('Awesome Responsive Theme','cosmica')),
	'cosmica_slide_3_description'		=>	get_theme_mod('cosmica_slide_3_description', __('Cosmica is responsive WordPress theme for any business purpose. Cosmica have Theme Options where you can set social media links and other customization to theme.','cosmica')),
	'cosmica_slide_3_bt_1_link'			=>	get_theme_mod('cosmica_slide_3_bt_1_link','#'),
	'cosmica_slide_3_bt_2_link'			=>	get_theme_mod('cosmica_slide_3_bt_2_link','#'),

	'cosmica_slide_4_heading'			=>	get_theme_mod('cosmica_slide_4_heading', __('Awesome Responsive Theme','cosmica')),
	'cosmica_slide_4_description'		=>	get_theme_mod('cosmica_slide_4_description', __('Cosmica is responsive WordPress theme for any business purpose. Cosmica have Theme Options where you can set social media links and other customization to theme.','cosmica')),
	'cosmica_slide_4_bt_1_link'			=>	get_theme_mod('cosmica_slide_4_bt_1_link','#'),
	'cosmica_slide_4_bt_2_link'			=>	get_theme_mod('cosmica_slide_4_bt_2_link','#'),










);
return $cosmica_theme_data;
}
?>