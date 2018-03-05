<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Mail Helper function
 **/

/**
 * Set the message variables
 *
 * @since 1.0
 * @param $embed_id
 * @param $embed_title
 * @param $embed_url
 * @param $user_name
 * @param $user_email
 * @param $user_login
 *
 * @return array
 */
function forminator_set_message_vars( $embed_id, $embed_title, $embed_url, $user_name, $user_email, $user_login ) {
	$message_vars = array();
	$message_vars['user_ip'] 		= Forminator_Geo::get_user_ip();
	$message_vars['date_mdy'] 		= date("m/d/Y");
	$message_vars['date_dmy']		= date("d/m/Y");
	$message_vars['embed_id'] 		= $embed_id;
	$message_vars['embed_title'] 	= $embed_title;
	$message_vars['embed_url']  	= $embed_url;
	$message_vars['user_agent']  	= isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : 'none';
	$message_vars['refer_url']   	= isset ( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : $embed_url;
	$message_vars['http_refer']   	= isset ( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : $embed_url;
	$message_vars['user_name']   	= $user_name;
	$message_vars['user_email']  	= $user_email;
	$message_vars['user_login']		= $user_login;
	return $message_vars;
}