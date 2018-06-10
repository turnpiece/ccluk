<?php

/**
 * Class Hustle_Migration
 *
 * @class Hustle_Migration
 */
class Hustle_Migration
{
	/**
	 * @var $_query WP_Query
	 */
	private $_query;

	/**
	 * @var (object) Opt_In class instance
	 **/
	private $_hustle;

	public function __construct( Opt_In $hustle )
	{
		$this->_hustle = $hustle;

		add_action( 'init', array( $this, 'do_popup_migration' ) );
		add_action( 'init', array( $this, 'do_hustle_20_migration' ) );
	}

	// Migrating from Wordpress Popup
	public function do_popup_migration() {
		$reset = ( isset($_GET['reset_migration']) ) ? (bool) $_GET['reset_migration'] : false;
		$done = get_option( 'hustle_popup_migrated', false );

		if ( false === $done || empty( $done ) || $reset ) {
			$popups = $this->get_all_wordpress_popup($reset);
			array_map( array( __CLASS__, 'migrate_popup' ), $popups );
		}

		update_option( 'hustle_popup_migrated', true );
	}

	// Migrating from Hustle 2.x
	public function do_hustle_20_migration() {
		$reset = ( isset($_GET['reset_migration']) ) ? (bool) $_GET['reset_migration'] : false;
		$done = get_option( 'hustle_20_migrated', false );
		$existed = get_option( 'hustle_popover_pro_migrated', false ) && get_option( 'hustle_popup_migrated', false );

		if ( ( false === $done || empty( $done ) || $reset ) && $existed ) {
			$modules = $this->get_all_hustle_modules();
			array_map( array( __CLASS__, 'migrate_hustle_20' ), $modules );
		}
		update_option( 'hustle_20_migrated', true );
	}

	public function migrate_hustle_20( $module ) {

		if ( $module->optin_provider == 'custom_content' ) {
			$this->_migrate_custom_content($module);
		} else if ( $module->optin_provider == 'social_sharing' && isset( $module->floating_social ) ) {
			$this->_migrate_social_sharing($module);
		} else {
			$this->_migrate_optin($module);
		}
	}

	public function get_all_hustle_modules() {
		$module_collection_instance = Hustle_Module_Collection::instance();
		return $module_collection_instance->get_hustle_20_optins();
	}



	private function _migrate_optin($optin) {


		//don't migrate the modules that don't belong to the blog requesting the migration (useful on MU)
		if( $optin->blog_id == get_current_blog_id() ){

			if ( isset( $optin->settings ) ) {
				$settings = json_decode($optin->settings);
				// create pop-up
				if ( isset( $settings->popup ) ) {
					$module = new Hustle_Module_Model();
					$module->module_type = Hustle_Module_Model::POPUP_MODULE;
					$module->module_name = $optin->optin_name;
					$module->test_mode = $optin->test_mode;
					$module->blog_id = $optin->blog_id;
					if ( isset( $settings->popup->enabled ) ) {
						$module->active = ( $settings->popup->enabled === '1' ) ? '1' : '0';
					}
					$module->save();

					// save to meta table
					$module->add_meta( $this->_hustle->get_const_var( "KEY_CONTENT", $module ), $this->_parse_optin_content($optin) );
					$module->add_meta( $this->_hustle->get_const_var( "KEY_DESIGN", $module ), $this->_parse_optin_design($optin) );
					$module->add_meta( $this->_hustle->get_const_var( "KEY_SETTINGS", $module ), $this->_parse_optin_popup_settings($optin) );
					$module->add_meta( $this->_hustle->get_const_var( "KEY_SHORTCODE_ID", $module ),  $optin->shortcode_id );
					$module->add_meta( 'graph_color', $optin->graph_color );
					$module->add_meta( 'track_types', $optin->track_types );
					$module->add_meta( 'test_types', $optin->test_types );
					$module->add_meta( 'error_logs', $optin->error_logs );

					// pop-up subscriptions
					foreach( $optin->subscription as $subscription) {
						$module->add_meta( 'subscription', $subscription);
					}

					// pop-up views
					foreach( $optin->popup_views as $view ) {
						$module->add_meta( 'popup_view', $view );
					}
					// pop-up conversions
					foreach( $optin->popup_conversions as $conversion ) {
						$module->add_meta( 'popup_conversion', $conversion );
					}
				}
			}
			// create slide-in
			if ( isset( $settings->slide_in ) ) {
				$module = new Hustle_Module_Model();
				$module->module_type = Hustle_Module_Model::SLIDEIN_MODULE;
				$module->module_name = $optin->optin_name;
				$module->test_mode = $optin->test_mode;
				if ( isset( $settings->slide_in->enabled ) ) {
					$module->active = ( $settings->slide_in->enabled === '1' ) ? '1' : '0';
				}
				$module->save();

				// Change slide_in value to slidein.
				$track_types = json_decode($optin->track_types, true);
				if ( isset($track_types['slide_in']) ) {
					$track_types['slidein'] = $track_types['slide_in'];
					unset($track_types['slide_in']);
					$optin->track_types = json_encode($track_types);
				}

				// save to meta table
				$module->add_meta( $this->_hustle->get_const_var( "KEY_CONTENT", $module ), $this->_parse_optin_content($optin) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_DESIGN", $module ), $this->_parse_optin_design($optin) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_SETTINGS", $module ), $this->_parse_optin_slidein_settings($optin) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_SHORTCODE_ID", $module ),  $optin->shortcode_id );
				$module->add_meta( 'graph_color', $optin->graph_color );
				$module->add_meta( 'track_types', $optin->track_types );
				$module->add_meta( 'test_types', $optin->test_types );
				$module->add_meta( 'error_logs', $optin->error_logs );

				// slide-in subscriptions
				foreach( $optin->subscription as $subscription) {
					$module->add_meta( 'subscription', $subscription);
				}
				// slide-in views
				foreach( $optin->slidein_views as $view ) {
					$module->add_meta( 'slidein_view', $view );
				}
				// slide-in conversions
				foreach( $optin->slidein_conversions as $conversion ) {
					$module->add_meta( 'slidein_conversion', $conversion );
				}
			}

			// create embedded
			if ( isset( $settings->shortcode ) || isset( $settings->widget ) || isset( $settings->after_content ) ) {
				$module = new Hustle_Module_Model();
				$module->module_type = Hustle_Module_Model::EMBEDDED_MODULE;
				$module->module_name = $optin->optin_name;
				$module->test_mode = $optin->test_mode;

				if ( isset( $settings->shortcode->enabled ) || isset( $settings->widget->enabled ) || isset( $settings->after_content->enabled ) ) {
					$module->active = ( $settings->shortcode->enabled === 'true' || $settings->widget->enabled === 'true' || $settings->after_content->enabled === 'true' ) ? '1' : '0';
				}

				$module->save();

				// save to meta table
				$module->add_meta( $this->_hustle->get_const_var( "KEY_CONTENT", $module ), $this->_parse_optin_content($optin) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_DESIGN", $module ), $this->_parse_optin_design($optin) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_SETTINGS", $module ), $this->_parse_optin_embed_settings($optin) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_SHORTCODE_ID", $module ),  $optin->shortcode_id );
				$module->add_meta( 'graph_color', $optin->graph_color );
				$module->add_meta( 'track_types', $optin->track_types );
				$module->add_meta( 'test_types', $optin->test_types );
				$module->add_meta( 'error_logs', $optin->error_logs );

				// embed subscriptions
				foreach( $optin->subscription as $subscription) {
					$module->add_meta( 'subscription', $subscription);
				}
				// shortcode views
				foreach( $optin->shortcode_views as $view ) {
					$module->add_meta( 'shortcode_view', $view );
				}
				// shortcode conversions
				foreach( $optin->shortcode_conversions as $conversion ) {
					$module->add_meta( 'shortcode_conversion', $conversion );
				}
				// widget views
				foreach( $optin->widget_views as $view ) {
					$module->add_meta( 'widget_view', $view );
				}
				// widget conversions
				foreach( $optin->widget_conversions as $conversion ) {
					$module->add_meta( 'widget_conversion', $conversion );
				}
				// after content views
				foreach( $optin->after_content_views as $view ) {
					$module->add_meta( 'after_content_view', $view );
				}
				// after content conversions
				foreach( $optin->after_content_conversions as $conversion ) {
					$module->add_meta( 'after_content_conversion', $conversion );
				}
			}
		}
	}

	private function _parse_optin_content($optin) {
		$content = array(
			'module_name' => $optin->optin_name,
			'has_title' => ( !empty( $optin->optin_title ) ) ? true : false,
			'title' => $optin->optin_title,
			'main_content' => $optin->optin_message
		);

		if ( isset( $optin->design ) ) {
			$optin_design = json_decode($optin->design);
			$form_elements = array();
			if ( isset( $optin_design->module_fields ) ) {
				foreach ( $optin_design->module_fields as $field ) {
					$form_elements[ $field->name ]['name'] = $field->name;
					$form_elements[ $field->name ]['label'] = $field->label;
					$form_elements[ $field->name ]['type'] = $field->name === 'first_name' || $field->name === 'last_name' ? 'name' : $field->type;
					$form_elements[ $field->name ]['required'] = $field->required;
					$form_elements[ $field->name ]['placeholder'] = $field->placeholder;
					if( $form_elements[ $field->name ]['type'] == 'email' && !isset($first) && $form_elements[ $field->name ]['required'] === 'true' ){
						$form_elements[ $field->name ]['delete'] = 'false';
						$first = true; // only one email field per form should not be deleted
					}
				}
				$form_elements[ 'submit' ]['name'] = 'submit';
				$form_elements[ 'submit' ]['label'] = $optin_design->cta_button;
				$form_elements[ 'submit' ]['type'] = 'submit';
				$form_elements[ 'submit' ]['required'] = 'true';
				$form_elements[ 'submit' ]['placeholder'] = 'Subscribe';
				$form_elements[ 'submit' ]['delete'] = 'false';


			}

			$email_service_key = $optin->optin_provider;
			$email_services = array();
			$email_service_args = array( 'enabled' => true );
			$provider_args = json_decode($optin->provider_args);

			if ( isset( $optin->api_key ) && !empty( $optin->api_key ) ) {
				$email_service_args['api_key'] = $optin->api_key;
			}

			if ( $email_service_key == 'mailchimp' ) {
				// specific for mailchimp
				$email_service_args['auto_optin'] 	= 'subscribed';
				$email_service_args['list_id'] 		= isset( $provider_args->optin_mail_list ) ? $provider_args->optin_mail_list : $provider_args->email_list;
				if ( isset( $provider_args->group ) && isset( $provider_args->group->id ) && isset( $provider_args->group->selected ) ) {
					$email_service_args['group'] 			= $provider_args->group->id;
					$email_service_args['group_interest'] 	= $provider_args->group->selected;
				}
			} else {
				// other providers
				$provider_args = (array) $provider_args;
				$email_service_args = wp_parse_args( $provider_args, $email_service_args );
			}

			if ( !empty($email_service_key) ) {
				$email_services[$email_service_key] = $email_service_args;
			}

			$design = array(
				'use_feature_image' => ( isset( $optin_design->image_src ) && !empty( $optin_design->image_src ) ) ? true : false,
				'feature_image' => ( isset( $optin_design->image_src ) ) ? $optin_design->image_src : '',
				'feature_image_location' => ( isset( $optin_design->image_location ) ) ? $optin_design->image_location : 'left',
				'feature_image_hide_on_mobile' => '0',
				'use_email_collection' => '1',
				'save_local_list' => ( isset( $optin->save_to_local_collection ) ) ? $optin->save_to_local_collection : false,
				'active_email_service' => $email_service_key,
				'email_services' => $email_services,
				'form_elements' => $form_elements,
				'after_successful_submission' => 'show_success',
				'success_message' => $optin_design->success_message,
				'auto_close_success_message' => '0'
			);
			$content = wp_parse_args( $design, $content );
		}

		return json_encode($content);
	}

	private function _parse_optin_design($optin) {
		$design = array();
		if ( isset( $optin->design ) ) {
			$optin_design = json_decode($optin->design);

			$form_layout = 'one';
			if ( $optin_design->form_location == '1' ) {
				$form_layout = 'two';
			} else if ( $optin_design->form_location == '2' ) {
				$form_layout = 'three';
			} else if ( $optin_design->form_location == '3' ) {
				$form_layout = 'four';
			}

			if ( isset( $optin_design->input_icons ) ) {
				if ( $optin_design->input_icons === 'animated_icon' ) {
					$input_icons = 'animated';
				} elseif ( $optin_design->input_icons === 'no_icon' ) {
					$input_icons = 'none';
				} else {
					$input_icons = 'static';
				}
			} else {
				$input_icons = '';
			}

			// Map CSS to new classes.
			$custom_css = $this->_map_optin_css($optin_design->css);

			$design = array(
				'form_layout' => $form_layout,
				'feature_image_position' => ( isset( $optin_design->image_location ) ) ? $optin_design->image_location : 'left',
				'feature_image_fit' => 'cover',
				'customize_css' => ( isset( $optin_design->customize_css ) ) ? $optin_design->customize_css : '',
				'custom_css' => $custom_css,
				'form_fields_icon' => $input_icons,

			);

			if ( isset( $optin_design->colors ) ) {
				$colors = array(
					'style' => ( isset( $optin_design->colors->palette ) ) ? $optin_design->colors->palette : '',
					'customize_colors' => ( isset( $optin_design->colors->customize ) ) ? $optin_design->colors->customize : '',
					'main_bg_color' => ( isset( $optin_design->colors->main_background ) ) ? $optin_design->colors->main_background : '',
					'form_area_bg' => ( isset( $optin_design->colors->form_background ) ) ? $optin_design->colors->form_background : '',
					'title_color' => ( isset( $optin_design->colors->title_color ) ) ? $optin_design->colors->title_color : '',
					'content_color' => ( isset( $optin_design->colors->content_color ) ) ? $optin_design->colors->content_color : '',
					'link_static_color' => ( isset( $optin_design->colors->link_color ) ) ? $optin_design->colors->link_color : '',
					'link_hover_color' => ( isset( $optin_design->colors->link_hover_color ) ) ? $optin_design->colors->link_hover_color : '',
					'link_active_color' => ( isset( $optin_design->colors->link_active_color ) ) ? $optin_design->colors->link_active_color : '',
					'optin_input_static_bg' => ( isset( $optin_design->colors->fields_background ) ) ? $optin_design->colors->fields_background : '',
					'optin_input_hover_bg' => ( isset( $optin_design->colors->fields_hover_background ) ) ? $optin_design->colors->fields_hover_background : '',
					'optin_input_active_bg' => ( isset( $optin_design->colors->fields_active_background ) ) ? $optin_design->colors->fields_active_background : '',
					'optin_placeholder_color' => ( isset( $optin_design->colors->label_color ) ) ? $optin_design->colors->label_color : '',
					'optin_form_field_text_static_color' => ( isset( $optin_design->colors->fields_color ) ) ? $optin_design->colors->fields_color : '',
					'optin_form_field_text_hover_color' => ( isset( $optin_design->colors->fields_hover_color ) ) ? $optin_design->colors->fields_hover_color : '',
					'optin_form_field_text_active_color' => ( isset( $optin_design->colors->fields_active_color ) ) ? $optin_design->colors->fields_active_color : '',
					'optin_submit_button_static_bg' => ( isset( $optin_design->colors->button_background ) ) ? $optin_design->colors->button_background : '',
					'optin_submit_button_hover_bg' => ( isset( $optin_design->colors->button_hover_background ) ) ? $optin_design->colors->button_hover_background : '',
					'optin_submit_button_active_bg' => ( isset( $optin_design->colors->button_active_background ) ) ? $optin_design->colors->button_active_background : '',
					'optin_submit_button_static_color' => ( isset( $optin_design->colors->button_label ) ) ? $optin_design->colors->button_label : '',
					'optin_submit_button_hover_color' => ( isset( $optin_design->colors->button_hover_label ) ) ? $optin_design->colors->button_hover_label : '',
					'optin_submit_button_active_color' => ( isset( $optin_design->colors->button_active_label ) ) ? $optin_design->colors->button_active_label : '',
					'optin_error_text_color' => ( isset( $optin_design->colors->error_color ) ) ? $optin_design->colors->error_color : '',
					'optin_mailchimp_title_color' => ( isset( $optin_design->colors->mcg_title_color ) ) ? $optin_design->colors->mcg_title_color : '',
					'optin_mailchimp_labels_color' => ( isset( $optin_design->colors->mcg_label_color ) ) ? $optin_design->colors->mcg_label_color : '',
					'optin_check_radio_bg' => ( isset( $optin_design->colors->checkbox_background ) ) ? $optin_design->colors->checkbox_background : '',
					'optin_check_radio_tick_color' => ( isset( $optin_design->colors->checkbox_checked_color ) ) ? $optin_design->colors->checkbox_checked_color : '',
					'optin_success_tick_color' => ( isset( $optin_design->colors->checkmark_color ) ) ? $optin_design->colors->checkmark_color : '',
					'optin_success_content_color' => ( isset( $optin_design->colors->success_color ) ) ? $optin_design->colors->success_color : '',
					'overlay_bg' => ( isset( $optin_design->colors->overlay_background ) ) ? $optin_design->colors->overlay_background : '',
					'close_button_static_color' => ( isset( $optin_design->colors->close_color ) ) ? $optin_design->colors->close_color : '',
					'close_button_hover_color' => ( isset( $optin_design->colors->close_hover_color ) ) ? $optin_design->colors->close_hover_color : '',
					'close_button_active_color' => ( isset( $optin_design->colors->close_active_color ) ) ? $optin_design->colors->close_active_color : ''
				);
				$design = wp_parse_args( $colors, $design );
			}

			if ( isset( $optin_design->borders ) ) {

				$border = isset( $optin_design->borders->corners_radius ) ? $optin_design->borders->corners_radius : '0';
				$button_border = isset( $optin_design->borders->button_corners_radius ) ? $optin_design->borders->button_corners_radius : '0';
				$form_fields_border = isset( $optin_design->borders->fields_corners_radius ) ? $optin_design->borders->fields_corners_radius : '0';
				$borders = array(
					'border' =>  ( $border === '0' ) ? '0' : '1',
					'border_radius' => $border,
					'border_type' => 'none', //set no border in order to avoid weird looking modules because there's no type, weight, nor color options in 2.x
					'button_border' =>  ( $button_border === '0' || $optin_design->borders->fields_style === 'joined' ) ? '0' : '1',
					'button_border_radius' => $button_border,
					'button_border_type' => 'none',
					'form_fields_border' =>  ( $form_fields_border === '0' || $optin_design->borders->fields_style === 'joined' ) ? '0' : '1',
					'form_fields_border_radius' => $form_fields_border,
					'form_fields_border_type' => 'none',
					'form_fields_proximity' => $optin_design->borders->fields_style,
					'drop_shadow' => '1', //there's no option to deactivate drop shadows on 2.x optins
					'drop_shadow_blur' => $optin_design->borders->dropshadow_value,
					'drop_shadow_color' => $optin_design->borders->shadow_color

				);
				$design = wp_parse_args( $borders, $design );
			}

		}

		return json_encode($design);
	}

	private function _parse_optin_popup_settings($optin) {
		$popup_settings = array();
		if ( isset( $optin->settings ) ) {
			$settings = json_decode($optin->settings);
			if ( isset( $settings->popup ) ) {
				$popup = $settings->popup;
				$triggers = array(
					'trigger' => $popup->appear_after,
					'on_time' => ( $popup->trigger_on_time == 'immediately' ) ? false : true,
					'on_time_delay' => $popup->appear_after_time_val,
					'on_time_unit' => $popup->appear_after_time_unit,
					'on_scroll' => $popup->appear_after_scroll,
					'on_scroll_page_percent' => $popup->appear_after_page_portion_val,
					'on_scroll_css_selector' => $popup->appear_after_element_val,
					'on_click_element' => $popup->trigger_on_element_click,
					'on_exit_intent' => $popup->trigger_on_exit,
					'on_exit_intent_per_session' => $popup->on_exit_trigger_once_per_session,
					'on_adblock' => $popup->trigger_on_adblock,
					'on_adblock_delayed' => $popup->trigger_on_adblock_timed,
					'on_adblock_delayed_time' => $popup->trigger_on_adblock_timed_val,
					'on_adblock_delayed_unit' => $popup->trigger_on_adblock_timed_unit
				);
				$popup_settings['triggers'] = $this->_map_trigger_settings($triggers);
				$popup_settings['animation_in'] = $this->_map_animation_settings($popup->animation_in);
				$popup_settings['animation_out'] = $this->_map_animation_settings($popup->animation_out, false);
				$popup_settings['after_close'] = $popup->add_never_see_this_message !== 'false' || $popup->close_button_acts_as_never_see_again !== 'false' ? 'no_show_all' : 'keep_show';
				$popup_settings['expiration'] = $popup->never_see_expiry;
				$popup_settings['expiration_unit'] = 'days';
				$popup_settings['allow_scroll_page'] = ( isset( $popup->allow_scroll_page ) ) ? $popup->allow_scroll_page : '';
				$popup_settings['not_close_on_background_click'] = ( isset( $popup->not_close_on_background_click ) ) ? $popup->not_close_on_background_click : '';
				$popup_settings['conditions'] = ( isset( $popup->conditions ) ) ? $popup->conditions : '';
			}
		}
		return $popup_settings;
	}

	private function _parse_optin_slidein_settings($optin) {
		$slidein_settings = array();
		if ( isset( $optin->settings ) ) {
			$settings = json_decode($optin->settings);
			if ( isset( $settings->slide_in ) ) {
				$slide_in = $settings->slide_in;
				$triggers = array(
					'trigger' => $slide_in->appear_after,
					'on_time' => ( $slide_in->trigger_on_time == 'immediately' ) ? false : true,
					'on_time_delay' => $slide_in->appear_after_time_val,
					'on_time_unit' => $slide_in->appear_after_time_unit,
					'on_scroll' => $slide_in->appear_after_scroll,
					'on_scroll_page_percent' => $slide_in->appear_after_page_portion_val,
					'on_scroll_css_selector' => $slide_in->appear_after_element_val,
					'on_click_element' => $slide_in->trigger_on_element_click,
					'on_exit_intent' => $slide_in->trigger_on_exit,
					'on_exit_intent_per_session' => $slide_in->on_exit_trigger_once_per_session,
					'on_adblock' => $slide_in->trigger_on_adblock,
					'on_adblock_delayed' => $slide_in->trigger_on_adblock_timed,
					'on_adblock_delayed_time' => $slide_in->trigger_on_adblock_timed_val,
					'on_adblock_delayed_unit' => $slide_in->trigger_on_adblock_timed_unit
				);
				$slidein_settings['triggers'] = $this->_map_trigger_settings($triggers);
				$slidein_settings['animation_in'] = ( isset( $slide_in->animation_in ) ) ? $this->_map_animation_settings($slide_in->animation_in) : '';
				$slidein_settings['animation_out'] = ( isset( $slide_in->animation_out ) ) ? $this->_map_animation_settings($slide_in->animation_out, false) : '';
				if ( $slide_in->after_close === 'hide_all' ) {
					$slidein_settings['after_close'] = 'no_show_all';
				} elseif ( $slide_in->after_close === 'no_show' ) {
					$slidein_settings['after_close'] = 'no_show_on_post';
				} else {
					$slidein_settings['after_close'] = 'keep_show';
				}
				$slidein_settings['expiration'] = ( isset( $slide_in->never_see_expiry ) ) ? $slide_in->never_see_expiry : '';
				$slidein_settings['expiration_unit'] = 'days';
				$slidein_settings['allow_scroll_page'] = ( isset( $slide_in->allow_scroll_page ) ) ? $slide_in->allow_scroll_page : '';
				$slidein_settings['not_close_on_background_click'] = ( isset( $slide_in->not_close_on_background_click ) ) ? $slide_in->not_close_on_background_click : '';

				if ( isset( $slide_in->position ) ) {
					switch( $slide_in->position ) {
						case "top_center":
							$slide_in_position =  "n";
						break;
						case "top_right":
							$slide_in_position =  "ne";
						break;
						case "center_right":
							$slide_in_position =  "e";
						break;
						case "bottom_right":
							$slide_in_position =  "se";
						break;
						case "bottom_center":
							$slide_in_position =  "s";
						break;
						case "bottom_left":
							$slide_in_position =  "sw";
						break;
						case "center_left":
							$slide_in_position =  "w";
						break;
						case "top_left":
							$slide_in_position =  "nw";
						break;
						default:
							$slide_in_position = "";
						break;
					};
				} else {
					$slide_in_position = "";
				}
				$slidein_settings['display_position'] = $slide_in_position;
				$slidein_settings['auto_hide'] = $slide_in->hide_after;
				$slidein_settings['auto_hide_time'] = $slide_in->hide_after_val;
				$slidein_settings['auto_hide_unit'] = $slide_in->hide_after_unit;
				$slidein_settings['conditions'] = ( isset( $slide_in->conditions ) ) ? $slide_in->conditions : '';
			}
		}
		return $slidein_settings;

	}

	private function _parse_optin_embed_settings($optin) {
		// copying from pop-up settings
		$embed_settings = array();
		if ( isset( $optin->settings ) ) {
			$settings = json_decode($optin->settings);
			if ( isset( $settings->popup ) ) {
				$popup = $settings->popup;
				$embed_settings['animation_in'] = $this->_map_animation_settings($popup->animation_in);
				$embed_settings['animation_out'] = $this->_map_animation_settings($popup->animation_out, false);
				$embed_settings['after_content_enabled'] = ( isset( $settings->after_content->enabled ) && ( $settings->after_content->enabled === '1' || $settings->after_content->enabled === 'true'  ) ) ? 'true' : 'false';
				$embed_settings['widget_enabled'] = ( isset( $settings->widget->enabled ) ) ? $settings->widget->enabled : 'false';
				$embed_settings['shortcode_enabled'] = ( isset( $settings->shortcode->enabled ) ) ? $settings->shortcode->enabled : 'false';
				$embed_settings['conditions'] = ( isset( $popup->conditions ) ) ? $popup->conditions : '';
			}
		}
		return $embed_settings;
	}

	// Take old classes and replace them with new.
	private function _map_optin_css($custom_css) {
		if ( ! empty( $custom_css ) ) {
			$custom_css = str_replace( '#popup', '', $custom_css );

			$css1 = explode( '}', $custom_css );
			$css1 = array_filter( $css1 );

			foreach( $css1 as $pos => $css2 ) {
				$css1[ $pos ] = substr( $css2, 0, strrpos( $css2, '{') );
			}

			if ( count( $css1 ) > 0 ) {
				foreach ( $css1 as $css3 ) {
					$css4 = explode( ',', $css3 );
					$css4 = array_filter( $css4 );

					foreach ( $css4 as $css ) {
						$selector = $css;
						$css_1 = $css;

						// Main class.
						if ( preg_match( '|.wpoi-hustle|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-hustle', '.hustle-modal', $css_1 );
						}

						// Container.
						if ( preg_match( '|.wpoi-optin|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-optin', '.hustle-modal-body', $css_1 );
						}

						// Form.
						if ( preg_match( '|.wpoi-form|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-form', '.hustle-modal-optin_form', $css_1 );
						}

						// First Name.
						if ( preg_match( '|.wpoi-subscribe-fname|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-subscribe-fname', '.hustle-modal-optin_field input[name="first_name"]', $css_1 );
						}

						// Last Name.
						if ( preg_match( '|.wpoi-subscribe-lname|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-subscribe-lname', '.hustle-modal-optin_field input[name="last_name"]', $css_1 );
						}

						// Email.
						if ( preg_match( '|.wpoi-subscribe-email|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-subscribe-email', '.hustle-modal-optin_field input[name="email"]', $css_1 );
						}

						// Button.
						if ( preg_match( '|.wpoi-subscribe-send|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-subscribe-send', '.hustle-modal-optin_button button', $css_1 );
						}

						// Title.
						if ( preg_match( '|.wpoi-title|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-title', '.hustle-modal-title', $css_1 );
						}

						// Message.
						if ( preg_match( '|.wpoi-message|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-message', '.hustle-modal-article', $css_1 );
						}

						// Layout One.
						if ( preg_match( '|.wpoi-layout-one|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-hustle .wpoi-layout-one', '.hustle-modal-one', $css_1 );
							$css_1 = str_replace( '.wpoi-layout-one', '.hustle-modal-one', $css_1 );
						}

						// Layout Two.
						if ( preg_match( '|.wpoi-layout-two|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-hustle .wpoi-layout-two', '.hustle-modal-two', $css_1 );
							$css_1 = str_replace( '.wpoi-layout-two', '.hustle-modal-two', $css_1 );
						}

						// Layout Three.
						if ( preg_match( '|.wpoi-layout-three|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-hustle .wpoi-layout-three', '.hustle-modal-three', $css_1 );
							$css_1 = str_replace( '.wpoi-layout-three', '.hustle-modal-three', $css_1 );
						}

						// Layout Four.
						if ( preg_match( '|.wpoi-layout-four|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-hustle .wpoi-layout-four', '.hustle-modal-four', $css_1 );
							$css_1 = str_replace( '.wpoi-layout-four', '.hustle-modal-four', $css_1 );
						}

						// Column.
						if ( preg_match( '|.wpoi-col|', $css_1 ) ) {
							$css_1 = str_replace( '.wpoi-container .wpoi-col', '.hustle-modal-body', $css_1 );
							$css_1 = str_replace( '.wpoi-col', '.hustle-modal-body', $css_1 );
						}

						$css = $css_1;

						$custom_css = str_replace( $selector, $css, $custom_css );
					}
				}
			}
		} else {
			$custom_css = '';
		}
		return $custom_css;

	}

	private function _migrate_custom_content($cc) {

		//don't migrate the modules that don't belong to the blog requesting the migration (useful on MU)
		if( $cc->blog_id == get_current_blog_id() ){

			// create pop-up
			if ( isset( $cc->popup ) ) {

				$popup = json_decode($cc->popup);
				$module = new Hustle_Module_Model();
				$module->module_type = Hustle_Module_Model::POPUP_MODULE;
				$module->module_name = $cc->optin_name;
				$module->test_mode = $cc->test_mode;
				$module->blog_id = $cc->blog_id;
				if ( isset( $popup->enabled ) ) {
					$module->active = ( $popup->enabled === '1' ) ? '1' : '0';
				}

				$module->save();

				// save to meta table
				$module->add_meta( $this->_hustle->get_const_var( "KEY_CONTENT", $module ), $this->_parse_cc_content($cc) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_DESIGN", $module ), $this->_parse_cc_design($cc) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_SETTINGS", $module ), $this->_parse_cc_popup_settings($cc) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_SHORTCODE_ID", $module ),  $cc->shortcode_id );
				$module->add_meta( 'graph_color', $cc->graph_color );
				$module->add_meta( 'track_types', $cc->track_types );
				$module->add_meta( 'test_types', $cc->test_types );

				// pop-up views
				foreach( $cc->popup_views as $view ) {
					$module->add_meta( 'popup_view', $view );
				}
				// pop-up conversions
				foreach( $cc->popup_conversions as $conversion ) {
					$module->add_meta( 'popup_conversion', $conversion );
				}

			}

			// create slide-in
			if ( isset( $cc->slide_in ) ) {
				$slide_in = json_decode($cc->slide_in);
				$module = new Hustle_Module_Model();
				$module->module_type = Hustle_Module_Model::SLIDEIN_MODULE;
				$module->module_name = $cc->optin_name;
				$module->test_mode = $cc->test_mode;
				$module->blog_id = $cc->blog_id;
				if ( isset( $slide_in->enabled ) ) {
					$module->active = ( $slide_in->enabled === '1' ) ? '1' : '0';
				}
				$module->save();

				// Change slide_in value to slidein.
				$track_types = json_decode($cc->track_types, true);
				if ( isset($track_types['slide_in']) ) {
					$track_types['slidein'] = $track_types['slide_in'];
					unset($track_types['slide_in']);
					$cc->track_types = json_encode($track_types);
				}

				// save to meta table
				$module->add_meta( $this->_hustle->get_const_var( "KEY_CONTENT", $module ), $this->_parse_cc_content($cc) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_DESIGN", $module ), $this->_parse_cc_design($cc) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_SETTINGS", $module ), $this->_parse_cc_slidein_settings($cc) );
				$module->add_meta( $this->_hustle->get_const_var( "KEY_SHORTCODE_ID", $module ),  $cc->shortcode_id );
				$module->add_meta( 'graph_color', $cc->graph_color );
				$module->add_meta( 'track_types', $cc->track_types );
				$module->add_meta( 'test_types', $cc->test_types );

				// slide-in views
				foreach( $cc->slidein_views as $view ) {
					$module->add_meta( 'slidein_view', $view );
				}
				// slide-in conversions
				foreach( $cc->slidein_conversions as $conversion ) {
					$module->add_meta( 'slidein_conversion', $conversion );
				}
			}

			// create embeds
			if ( isset( $cc->settings ) ) {
				$cc_settings = json_decode($cc->settings);

				if ( isset( $cc_settings->shortcode ) && isset( $cc_settings->widget ) && isset( $cc->after_content ) ) {
					$module = new Hustle_Module_Model();
					$module->module_type = Hustle_Module_Model::EMBEDDED_MODULE;
					$module->module_name = $cc->optin_name;
					$module->test_mode = $cc->test_mode;
					$module->blog_id = $cc->blog_id;
					$cc_after_content = json_decode($cc->after_content);

					if ( isset( $cc_settings->shortcode->enabled ) && isset( $cc_settings->widget->enabled ) && isset( $cc_after_content->enabled ) ) {
						$module->active = ( $cc_settings->shortcode->enabled === 'true' || $cc_settings->widget->enabled === 'true' || $cc_after_content->enabled === 'true' || $cc_after_content->enabled === '1' ) ? true : false;
					}

					$module->save();

					// save to meta table
					$module->add_meta( $this->_hustle->get_const_var( "KEY_CONTENT", $module ), $this->_parse_cc_content($cc) );
					$module->add_meta( $this->_hustle->get_const_var( "KEY_DESIGN", $module ), $this->_parse_cc_design($cc) );
					$module->add_meta( $this->_hustle->get_const_var( "KEY_SETTINGS", $module ), $this->_parse_cc_embed_settings($cc) );
					$module->add_meta( $this->_hustle->get_const_var( "KEY_SHORTCODE_ID", $module ),  $cc->shortcode_id );
					$module->add_meta( 'graph_color', $cc->graph_color );
					$module->add_meta( 'track_types', $cc->track_types );
					$module->add_meta( 'test_types', $cc->test_types );

					// widget views
					foreach( $cc->widget_views as $view ) {
						$module->add_meta( 'widget_view', $view );
					}
					// widget conversions
					foreach( $cc->widget_conversions as $conversion ) {
						$module->add_meta( 'widget_conversion', $conversion );
					}
					// shortcode views
					foreach( $cc->shortcode_views as $view ) {
						$module->add_meta( 'shortcode_view', $view );
					}
					// shortcode conversions
					foreach( $cc->shortcode_conversions as $conversion ) {
						$module->add_meta( 'shortcode_conversion', $conversion );
					}
					// after_content views
					foreach( $cc->after_content_views as $view ) {
						$module->add_meta( 'after_content_view', $view );
					}
					// after_content conversions
					foreach( $cc->after_content_conversions as $conversion ) {
						$module->add_meta( 'after_content_conversion', $conversion );
					}
				}
			}
		}
	}

	private function _parse_cc_content($cc) {
		$content = array(
			'module_name' => $cc->optin_name,
			'has_title' => ( !empty( $cc->optin_title ) ) ? true : false,
			'title' => $cc->optin_title,
			'sub_title' => $cc->subtitle,
			'main_content' => $cc->optin_message
		);

		if ( isset( $cc->design ) ) {
			$cc_design = json_decode($cc->design);
			$cta_url = '';
			if ( isset( $cc_design->cta_url ) ){
				$cta_url = preg_match('(http://|https://)', $cc_design->cta_url) ? $cc_design->cta_url : 'http://' . $cc_design->cta_url ;

			}
			$design = array(
				'use_feature_image' => ( isset( $cc_design->image ) && !empty( $cc_design->image ) ) ? true : false,
				'feature_image' => ( isset( $cc_design->image ) ) ? $cc_design->image : '',
				'feature_image_location' => ( isset( $cc_design->image_position ) ) ? $cc_design->image_position : 'left',
				'feature_image_hide_on_mobile' => ( isset( $cc_design->hide_image_on_mobile ) ) ? $cc_design->hide_image_on_mobile : 'false',
				'show_cta' => ( isset( $cc_design->cta_label ) && !empty( $cc_design->cta_label ) ) ? true : false,
				'cta_label' => ( isset( $cc_design->cta_label ) ) ? $cc_design->cta_label : '',
				'cta_url' => $cta_url,
				'cta_target' => ( isset( $cc_design->cta_target ) && $cc_design->cta_target === '_self' ) ? 'self' : 'blank'
			);
			$content = wp_parse_args( $design, $content );
		}

		return json_encode($content);
	}

	private function _parse_cc_design($cc) {
		$design = array();
		if ( isset( $cc->design ) ) {
			$cc_design = json_decode($cc->design);

			//verifications to apply the colors that are actually being displayed in 2.x
			$customize_colors = isset( $cc_design->customize_colors ) ? $cc_design->customize_colors : 0;
			if ( isset($cc_design->style) ) {
				$main_bg_color = '#ffffff';
				if ( $cc_design->style === 'cabriolet' ){
					$title_color = '#ffffff';
					$subtitle_color = '#ffffff';
				} else {
					$title_color = '#333333';
					$subtitle_color = '#333333';
				}
			} else{
				$main_bg_color = '';
				$title_color = '';
				$subtitle_color = '';
			}

			$custom_css = $this->_map_cc_css($cc_design->custom_css);

			$design = array(
				'feature_image_fit' => 'cover',
				'feature_image_position' => ( isset( $cc_design->image_position ) ) ? $cc_design->image_position : 'left',
				'style' => ( isset( $cc_design->style ) ) ? $cc_design->style : 'simple',
				'customize_colors' => ( isset( $cc_design->customize_colors ) ) ? $cc_design->customize_colors : 0,
				'main_bg_color' => ( isset( $cc_design->main_bg_color ) && $customize_colors === '1' ) ? $cc_design->main_bg_color : $main_bg_color,
				'title_color' => ( isset( $cc_design->title_color ) && $customize_colors === '1' ) ? $cc_design->title_color : $title_color,
				'subtitle_color' => ( isset( $cc_design->subtitle_color )  && $customize_colors === '1' ) ? $cc_design->subtitle_color : $subtitle_color,
				'link_static_color' => ( isset( $cc_design->link_static_color ) ) ? $cc_design->link_static_color : '',
				'link_hover_color' => ( isset( $cc_design->link_hover_color ) ) ? $cc_design->link_hover_color : '',
				'link_active_color' => ( isset( $cc_design->link_active_color ) ) ? $cc_design->link_active_color : '',
				'cta_button_static_bg' => ( isset( $cc_design->cta_static_background ) ) ? $cc_design->cta_static_background : '',
				'cta_button_hover_bg' => ( isset( $cc_design->cta_hover_background ) ) ? $cc_design->cta_hover_background : '',
				'cta_button_active_bg' => ( isset( $cc_design->cta_active_background ) ) ? $cc_design->cta_active_background : '',
				'cta_button_static_color' => ( isset( $cc_design->cta_static_color ) ) ? $cc_design->cta_static_color : '',
				'cta_button_hover_color' => ( isset( $cc_design->cta_hover_color ) ) ? $cc_design->cta_hover_color : '',
				'cta_button_active_color' => ( isset( $cc_design->cta_active_color ) ) ? $cc_design->cta_active_color : '',
				'border' => $cc_design->border === 'true'? '1' : '0',
				'border_radius' => $cc_design->border_radius,
				'border_weight' => $cc_design->border_weight,
				'border_type' => $cc_design->border_type,
				'border_color' => $cc_design->border_static_color,
				'drop_shadow' => $cc_design->drop_shadow,
				'drop_shadow_x' => $cc_design->drop_shadow_x,
				'drop_shadow_y' => $cc_design->drop_shadow_y,
				'drop_shadow_blur' => $cc_design->drop_shadow_blur,
				'drop_shadow_spread' => $cc_design->drop_shadow_spread,
				'drop_shadow_color' => $cc_design->drop_shadow_color,
				'customize_size' => $cc_design->customize_size,
				'custom_height' => $cc_design->custom_height,
				'custom_width' => $cc_design->custom_width,
				'customize_css' => $cc_design->customize_css,
				'custom_css' => $custom_css,
			);
		}


		return json_encode($design);
	}

	private function _parse_cc_popup_settings($cc) {
		$popup_settings = array();
		if ( isset( $cc->popup ) ) {
			$popup = json_decode($cc->popup);
			$triggers = $popup->triggers;
			$triggers->on_time = ( $triggers->on_time == 'immediately' ) ? false : true;
			$triggers->on_exit_intent_per_session = ( isset( $triggers->on_exit_intent_per_session ) && $triggers->on_exit_intent_per_session == '1' ) ? 'true' : 'false';
			$popup_settings['triggers'] = $this->_map_trigger_settings($triggers);
			$popup_settings['animation_in'] = $this->_map_animation_settings($popup->animation_in);
			$popup_settings['animation_out'] = $this->_map_animation_settings($popup->animation_out, false);
			$popup_settings['after_close'] = $popup->add_never_see_link !== 'false' || $popup->close_btn_as_never_see !== 'false' ? 'no_show_all' : 'keep_show';
			$popup_settings['expiration'] = $popup->expiration_days;
			$popup_settings['expiration_unit'] = 'days';
			$popup_settings['allow_scroll_page'] = ( $popup->allow_scroll_page == '1' ) ? 'true' : 'false';
			$popup_settings['not_close_on_background_click'] = ( $popup->not_close_on_background_click == '1' ) ? 'true' : 'false';
			$popup_settings['on_submit'] = $popup->on_submit;
			$popup_settings['conditions'] = ( isset( $popup->conditions ) ) ? $popup->conditions : '';
		}
		return $popup_settings;
	}

	private function _parse_cc_slidein_settings($cc) {
		$slidein_settings = array();
		if ( isset( $cc->slide_in ) ) {
			$slide_in = json_decode($cc->slide_in);
			$triggers = (object) $slide_in->triggers;
			$triggers->on_time = ( ( isset( $triggers->on_time ) ) && $triggers->on_time == 'immediately' ) ? false : true;
			$triggers->on_exit_intent_per_session = (  isset( $triggers->on_exit_intent_per_session ) && $triggers->on_exit_intent_per_session == '1' ) ? 'true' : 'false';
			$slidein_settings['triggers'] = $this->_map_trigger_settings($triggers);

			$slidein_settings['animation_in'] = ( isset( $slide_in->animation_in ) ) ? $this->_map_animation_settings($slide_in->animation_in) : '';
			$slidein_settings['animation_out'] = ( isset( $slide_in->animation_out ) ) ? $this->_map_animation_settings($slide_in->animation_out, false) : '';
			if ( $slide_in->after_close === 'hide_all' ) {
					$slidein_settings['after_close'] = 'no_show_all';
			} elseif ( $slide_in->after_close === 'no_show' ) {
				$slidein_settings['after_close'] = 'no_show_on_post';
			} else {
				$slidein_settings['after_close'] = 'keep_show';
			}
			$slidein_settings['expiration'] = ( isset( $slide_in->expiration_days ) ) ? $slide_in->expiration_days : '';
			$slidein_settings['expiration_unit'] = 'days';
			$slidein_settings['allow_scroll_page'] = ( isset( $slide_in->allow_scroll_page ) ) ? $slide_in->allow_scroll_page : '';
			$slidein_settings['not_close_on_background_click'] = ( isset( $slide_in->not_close_on_background_click ) ) ? $slide_in->not_close_on_background_click : '';
			$slidein_settings['on_submit'] = ( isset( $slide_in->on_submit ) ) ? $slide_in->on_submit : '';

			if ( isset( $slide_in->position ) ) {
				switch( $slide_in->position ) {
					case "top_center":
						$slide_in_position =  "n";
					break;
					case "top_right":
						$slide_in_position =  "ne";
					break;
					case "center_right":
						$slide_in_position =  "e";
					break;
					case "bottom_right":
						$slide_in_position =  "se";
					break;
					case "bottom_center":
						$slide_in_position =  "s";
					break;
					case "bottom_left":
						$slide_in_position =  "sw";
					break;
					case "center_left":
						$slide_in_position =  "w";
					break;
					case "top_left":
						$slide_in_position =  "nw";
					break;
					default:
						$slide_in_position = "";
					break;
				};
			} else {
				$slide_in_position = "";
			}
			$slidein_settings['display_position'] = $slide_in_position;
			$slidein_settings['auto_hide'] = ( isset( $slide_in->hide_after ) ) ? $slide_in->hide_after : '';
			$slidein_settings['auto_hide_time'] = ( isset( $slide_in->hide_after_val ) ) ? $slide_in->hide_after_val : '';
			$slidein_settings['auto_hide_unit'] = ( isset( $slide_in->hide_after_unit ) ) ? $slide_in->hide_after_unit : '';
			$slidein_settings['conditions'] = ( isset( $slide_in->conditions ) ) ? $slide_in->conditions : '';

		}
		return $slidein_settings;
	}

	private function _parse_cc_embed_settings($cc) {
		$embed_settings = array();
		if ( isset( $cc->popup ) ) {
			$popup = json_decode($cc->popup);
			$after_content = json_decode($cc->after_content);
			$settings = json_decode($cc->settings);
			$embed_settings['animation_in'] = $this->_map_animation_settings($popup->animation_in);
			$embed_settings['animation_out'] = $this->_map_animation_settings($popup->animation_out, false);
			$embed_settings['on_submit'] = $popup->on_submit;
			$embed_settings['after_content_enabled'] = ( isset( $after_content->enabled ) && ( $after_content->enabled === '1' || $after_content->enabled === 'true'  ) ) ? 'true' : 'false';
			$embed_settings['widget_enabled'] = ( isset( $settings->widget ) && isset( $settings->widget->enabled ) ) ? $settings->widget->enabled : 'false';
			$embed_settings['shortcode_enabled'] = ( isset( $settings->shortcode ) && isset( $settings->shortcode->enabled ) ) ? $settings->shortcode->enabled : 'false';
			$embed_settings['conditions'] = ( isset( $popup->conditions ) ) ? $popup->conditions : '';

		}
		return $embed_settings;
	}

	// Take old classes and replace them with new.
	private function _map_cc_css($custom_css) {
		if ( ! empty( $custom_css ) ) {
			$custom_css = str_replace( '#popup', '', $custom_css );

			$css1 = explode( '}', $custom_css );
			$css1 = array_filter( $css1 );

			foreach( $css1 as $pos => $css2 ) {
				$css1[ $pos ] = substr( $css2, 0, strrpos( $css2, '{') );
			}

			if ( count( $css1 ) > 0 ) {
				foreach ( $css1 as $css3 ) {
					$css4 = explode( ',', $css3 );
					$css4 = array_filter( $css4 );

					foreach ( $css4 as $css ) {
						$selector = $css;
						$css_1 = $css;

						// Container.
						if ( preg_match( '|.wph-modal.wph-modal-container .wph-modal--content|', $css_1 ) ) {
							$css_1 = str_replace( '.wph-modal.wph-modal-container .wph-modal--content', '.hustle-modal .hustle-modal-body', $css_1 );
						}

						// Title.
						if ( preg_match( '|.wph-modal.wph-modal-container.wph-customize-css h2.wph-modal--title|', $css_1 ) ) {
							$css_1 = str_replace( '.wph-modal.wph-modal-container.wph-customize-css h2.wph-modal--title', '.hustle-modal-title', $css_1 );
						}

						// Subtitle.
						if ( preg_match( '|.wph-modal.wph-modal-container.wph-customize-css .wph-modal--content h4.wph-modal--subtitle|', $css_1 ) ) {
							$css_1 = str_replace( '.wph-modal.wph-modal-container.wph-customize-css .wph-modal--content h4.wph-modal--subtitle', '.hustle-modal-subtitle', $css_1 );
						}

						// Content.
						if ( preg_match( '|.wph-modal .wph-modal--content .wph-modal--message|', $css_1 ) ) {
							$css_1 = str_replace( '.wph-modal .wph-modal--content .wph-modal--message', '.hustle-modal-message', $css_1 );
						}

						// Image Container.
						if ( preg_match( '|.wph-modal .wph-modal--content .wph-modal--image|', $css_1 ) ) {
							$css_1 = str_replace( '.wph-modal .wph-modal--content .wph-modal--image', '.hustle-modal .hustle-modal-image', $css_1 );
						}

						// Image.
						if ( preg_match( '|.wph-modal.wph-modal-container.wph-customize-css .wph-modal--content .wph-modal--image img|', $css_1 ) ) {
							$css_1 = str_replace( '.wph-modal.wph-modal-container.wph-customize-css .wph-modal--content .wph-modal--image img', '.hustle-modal .hustle-modal-image .hustle-modal-feat_image, .hustle-modal .hustle-modal-image img', $css_1 );
						}

						// Button.
						if ( preg_match( '|.wph-modal .wph-modal--cta|', $css_1 ) ) {
							$css_1 = str_replace( '.wph-modal .wph-modal--cta', '.hustle-modal .hustle-modal-optin_form .hustle-modal-optin_button button', $css_1 );
						}

						// Main Container if still unchanged.
						if ( preg_match( '|.wph-modal|', $css_1 ) ) {
							$css_1 = str_replace( '.wph-modal', '.hustle-modal', $css_1 );
						}

						$css = $css_1;

						$custom_css = str_replace( $selector, $css, $custom_css );
					}
				}
			}
		} else {
			$custom_css = '';
		}
		return $custom_css;

	}


	private function _migrate_social_sharing($module) {

		//don't migrate the modules that don't belong to the blog requesting the migration (useful on MU)
		if( $module->blog_id == get_current_blog_id() ){

			// save to modules table
			$ss = new Hustle_SShare_Model();
			$ss->module_name = $module->optin_name;
			$ss->module_type = Hustle_SShare_Model::SOCIAL_SHARING_MODULE;
			$ss->active = (int) $module->active;
			$ss->test_mode = (int) $module->test_mode;
			$ss->blog_id = $module->blog_id;
			$ss->save();

			// save to meta table
			$ss->add_meta( $this->_hustle->get_const_var( "KEY_CONTENT", $ss ), $this->_parse_sshare_content(json_decode($module->services)) );
			$ss->add_meta( $this->_hustle->get_const_var( "KEY_DESIGN", $ss ), $this->_parse_sshare_design(json_decode($module->appearance)) );
			$ss->add_meta( $this->_hustle->get_const_var( "KEY_SETTINGS", $ss ), $this->_parse_sshare_settings( json_decode($module->settings), json_decode($module->floating_social) ) );
			$ss->add_meta( $this->_hustle->get_const_var( "KEY_SHORTCODE_ID", $ss ), $module->shortcode_id );
			$ss->add_meta( 'track_types', $module->track_types );
			$ss->add_meta( 'test_types', $module->test_types );

			// floating social views
			foreach( $module->floating_social_views as $view ) {
				$ss->add_meta( 'floating_social_view', $view );
			}

			// floating social conversions
			foreach( $module->floating_social_conversions as $conversion ) {
				$ss->add_meta( 'floating_social_conversion', $conversion );
			}

			// widget views
			foreach( $module->widget_views as $view ) {
				$ss->add_meta( 'widget_view', $view );
			}

			// widget conversions
			foreach( $module->widget_conversions as $conversion ) {
				$ss->add_meta( 'widget_conversion', $conversion );
			}

			// shortcode views
			foreach( $module->shortcode_views as $view ) {
				$ss->add_meta( 'shortcode_view', $view );
			}

			// shortcode conversions
			foreach( $module->shortcode_conversions as $conversion ) {
				$ss->add_meta( 'shortcode_conversion', $conversion );
			}

			// Page views.
			foreach( $module->page_shares as $page_share ) {
				$ss->add_meta( $page_share->meta_key, $page_share->meta_value );
			}
		}
	}

	private function _parse_sshare_content($services) {
		// Map service type linked to custom.
		if ($services->service_type === 'linked') {
			$services->service_type = 'custom';
		}
		$content = array(
			'module_name' => $services->optin_name,
			'active' => $services->active,
			'test_mode' => $services->test_mode,
			'service_type' => $services->service_type,
			'click_counter' => $services->click_counter,
			'social_icons' => $services->social_icons,
		);
		return json_encode($content);
	}

	private function _parse_sshare_design($appearance) {
		$icon_style = array(
			'one' => 'flat',
			'two' => 'outline',
			'three' => 'rounded',
			'four' => 'squared',
		);
		$design = array(
			'icon_style' => $icon_style[ $appearance->icon_style ],
			'icons_order' => $appearance->icons_order,
			'customize_colors' => $appearance->customize_colors,
			'icon_bg_color' => $appearance->icon_bg_color,
			'icon_color' => $appearance->icon_color,
			'floating_social_bg' => $appearance->floating_social_bg,
			'floating_counter_border' => $appearance->counter_border,
			'floating_counter_color' => $appearance->counter_text,
			'floating_social_animate_icons' => 0,
			'drop_shadow' => $appearance->drop_shadow,
			'drop_shadow_x' => $appearance->drop_shadow_x,
			'drop_shadow_y' => $appearance->drop_shadow_y,
			'drop_shadow_color' => $appearance->drop_shadow_color,
			'drop_shadow_blur' => $appearance->drop_shadow_blur,
			'drop_shadow_spread' => $appearance->drop_shadow_spread,
			'floating_inline_count' => $appearance->floating_inline_count,
			'customize_widget_colors' => $appearance->customize_widget_colors,
			'widget_icon_bg_color' => $appearance->widget_icon_bg_color,
			'widget_icon_color' => $appearance->widget_icon_color,
			'widget_bg_color' => $appearance->widget_bg_color,
			'widget_animate_icons' => 0,
			'widget_drop_shadow' => $appearance->widget_drop_shadow,
			'widget_drop_shadow_x' => $appearance->widget_drop_shadow_x,
			'widget_drop_shadow_y' => $appearance->widget_drop_shadow_y,
			'widget_drop_shadow_blur' => $appearance->widget_drop_shadow_blur,
			'widget_drop_shadow_spread' => $appearance->widget_drop_shadow_spread,
			'widget_drop_shadow_color' => $appearance->widget_drop_shadow_color,
			'widget_inline_count' => $appearance->widget_inline_count,
			'widget_counter_border' => $appearance->counter_border,
			'widget_counter_color' => $appearance->widget_counter_text,
		);
		return json_encode($design);
	}

	private function _parse_sshare_settings($settings, $floating_social) {
		$sshare_settings = array(
			'floating_social_enabled' => ( isset($floating_social->enabled) && $floating_social->enabled === '1' ) ? 'true' : 'false' ,
			'widget_enabled' => 'true',
			'shortcode_enabled' => 'true',
			'conditions' => null, // "conditions" is evaluated and inserted below
			'location_type' => $floating_social->location_type,
			'location_target' => $floating_social->location_target,
			'location_align_x' => $floating_social->location_align_x,
			'location_align_y' => $floating_social->location_align_y,
			'location_top' => $floating_social->location_top,
			'location_bottom' => $floating_social->location_bottom,
			'location_right' => $floating_social->location_right,
			'location_left' => $floating_social->location_left,
		);

		if ( empty($floating_social->conditions) ) {
			unset( $sshare_settings['conditions'] );
		} else {
			$sshare_settings['conditions'] = $floating_social->conditions;
		}

		return json_encode($sshare_settings);
	}


	/* FOR WORDPRESS POP-UPS */


	public function migrate_popup( $popup ) {

		if ( $popup ) {
			$module = new Hustle_Module_Model();
			$module->module_type = Hustle_Module_Model::POPUP_MODULE;
			$module->module_name = $popup->post_title;
			$module->blog_id = get_current_blog_id();
			$module->active = $popup->post_status === 'publish';
			$module->test_mode = 0;
			$module->save();

			// save to meta table
			$module->add_meta( $this->_hustle->get_const_var( "KEY_CONTENT", $module ), $this->_parse_wp_content($popup) );
			$module->add_meta( $this->_hustle->get_const_var( "KEY_DESIGN", $module ), $this->_parse_wp_design($popup) );
			$module->add_meta( $this->_hustle->get_const_var( "KEY_SETTINGS", $module ), $this->_parse_wp_settings($popup) );

			update_post_meta( $popup->ID, "hustle_migrated", true );
		}
	}

	/**
	 * Returns array of all legacy popups
	 *
	 * @param int $posts_per_page
	 * @return array
	 */
	public function get_all_wordpress_popup( $reset = false, $posts_per_page = -1 ){
		$args = array(
			"post_type" => "inc_popup",
			"posts_per_page" => $posts_per_page,
			'suppress_filter' => true,
			'post_status' => 'any',
		);

		if ( !$reset ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'hustle_migrated',
					'compare' => 'NOT EXISTS',
				),
			);
		}

		return get_posts($args);
	}

	/**
	 * Returns popup contents on json format
	 *
	 * @param WP_Post $popup
	 * @return json
	 */
	private function _parse_wp_content( WP_Post $popup ) {
		$popup_id = $popup->ID;
		$heading = $this->_get_heading($popup);
		$sub_heading = $this->_get_subheading($popup);
		$image = get_post_meta( $popup_id, 'po_image', true );
		$image_location = get_post_meta( $popup_id, 'po_image_pos', true );
		$cta_label = get_post_meta( $popup_id, 'po_cta_label', true );
		$cta_url = get_post_meta( $popup_id, 'po_cta_link', true );
		$cta_target = get_post_meta( $popup_id, 'po_cta_target', true );

		if ( preg_match( '%blank%', $cta_target ) ) {
			$cta_target = '_blank';
		}

		$show_image_on_mobile = (bool) get_post_meta( $popup_id, 'po_image_mobile', true );

		$content = array(
			'module_name' => $popup->post_title,
			'has_title' => ( !empty( $heading ) ) ? true : false,
			'title' => $heading,
			'sub_title' => $sub_heading,
			'main_content' => $popup->post_content,
			'use_feature_image' => ( !empty( $image ) ) ? true : false,
			'feature_image' => $image,
			'feature_image_location' => $image_location,
			'feature_image_hide_on_mobile' => !$show_image_on_mobile,
			'show_cta' => ( !empty( $cta_label ) ) ? true : false,
			'cta_label' => $cta_label,
			'cta_url' => $cta_url,
			'cta_target' => $cta_target,
		);

		return json_encode($content);
	}

	/**
	 * Returns popup designs on json format
	 *
	 * @param WP_Post $popup
	 * @return json
	 */
	private function _parse_wp_design( WP_Post $popup ) {
		$popup_id = $popup->ID;
		$border_radius = get_post_meta( $popup_id, 'po_round_corners', true ) ? 5 : 0;

		$customize_colors = get_post_meta( $popup_id, 'po_custom_colors', true ) ? true : false;
		$color1 = '';
		$color2 = '';

		if ( $customize_colors ) {
			$color = get_post_meta( $popup_id, 'po_color', true );

			if ( ! empty( $color ) ) {
				$color1 = $color['col1'];
				$color2 = $color['col2'];
			}
		}

		$custom_size = get_post_meta( $popup_id, 'po_custom_size', true ) ? true : false;
		$width = 0;
		$height = 0;

		if ( $custom_size ) {
			$size = get_post_meta( $popup_id, 'po_size', true );
			$width = intval( $size['width'] );
			$height = intval( $size['height'] );
		}

		$custom_css = get_post_meta( $popup_id, 'po_custom_css', true );

		if ( ! empty( $custom_css ) ) {
			$custom_css = str_replace( '#popup', '', $custom_css );

			$css1 = explode( '}', $custom_css );
			$css1 = array_filter( $css1 );

			foreach( $css1 as $pos => $css2 ) {
				$css1[ $pos ] = substr( $css2, 0, strrpos( $css2, '{') );
			}

			if ( count( $css1 ) > 0 ) {
				foreach ( $css1 as $css3 ) {
					$css4 = explode( ',', $css3 );
					$css4 = array_filter( $css4 );

					foreach ( $css4 as $css ) {
						$selector = $css;
						$css_1 = $css;

						if ( preg_match( '|.popup|', $css ) ) {
							$css_1 = str_replace( '.popup', '.hustle-modal .hustle-modal-body', $css );
						}

						if ( preg_match( '|.wdpu-image|', $css_1 ) ) {
							$css_1 = str_replace( '.wdpu-image', '.hustle-modal .hustle-modal-image .hustle-modal-feat_image, .hustle-modal .hustle-modal-image img', $css_1 );
						}

						if ( preg_match( '|.wdpu-buttons|', $css_1 ) ) {
							$css_1 = str_replace( '.wdpu-buttons', '.hustle-modal .hustle-modal-optin_form .hustle-modal-optin_button button', $css_1 );
						}

						if ( preg_match( '|.wdpu-close|', $css_1 ) ) {
							$css_1 = str_replace( '.wdph-close', '.hustle-modal .hustle-modal-close .hustle-icon', $css_1 );
						}

						// Remove old classes
						$old = array( '.wdpu-inner', '.wdpu-head', '.wdpu-text', '.wdpu-msg-inner' );
						$css_1 = str_replace( $old, '', $css_1 );

						$css = $css_1;

						$custom_css = str_replace( $selector, $css, $custom_css );
					}
				}
			}
		}

		$design = array(
			'feature_image_position' => get_post_meta( $popup_id, 'po_image_pos', true ),
			'style' => get_post_meta( $popup_id, 'po_style', true ),
			'customize_colors' => $customize_colors,
			'title_color' => $color1,
			'subtitle_color' => $color1,
			'link_static_color' => $color1,
			'link_hover_color' => $color1,
			'link_active_color' => $color1,
			'cta_button_static_bg' => $color1,
			'cta_button_hover_bg' => $color1,
			'cta_button_active_bg' => $color1,
			'cta_button_static_color' => $color2,
			'cta_button_hover_color' => $color2,
			'cta_button_active_color' => $color2,
			'border' => get_post_meta( $popup_id, 'po_round_corners', true ),
			'border_radius' => $border_radius,
			'customize_size' => $custom_size,
			'custom_height' => $height,
			'custom_width' => $width,
			'customize_css' => !empty( $custom_css ),
			'custom_css' => $custom_css
		);

		return json_encode($design);
	}

	/**
	 * Returns popup settings on json format
	 *
	 * @param WP_Post $popup
	 * @return json
	 */
	private function _parse_wp_settings( WP_Post $popup ) {
		$popup_id = $popup->ID;
		$popup_settings = array();

		$popup_settings['triggers'] = $this->_get_trigger_settings($popup);
		$popup_settings['animation_in'] = get_post_meta( $popup_id, "po_animation_in", true );
		$popup_settings['animation_out'] = get_post_meta( $popup_id, "po_animation_out", true );
		$popup_settings['expiration'] = (int) get_post_meta( $popup_id, "po_hide_expire", true );
		$popup_settings['expiration_unit'] = 'days';
		$popup_settings['allow_scroll_page'] = get_post_meta( $popup_id, "po_scroll_body", true ) ? true : false;
		$popup_settings['not_close_on_background_click'] = get_post_meta( $popup_id, 'po_overlay_close', true ) ? false : true;
		$popup_settings['on_submit'] = get_post_meta( $popup_id, 'po_form_submit', true );
		$popup_settings['conditions'] = $this->_get_conditions_settings($popup);

		return $popup_settings;
	}

	/**
	 * @param WP_Post $popup
	 * @return string
	 */
	private function _get_heading( WP_Post $popup ){
		return get_post_meta( $popup->ID, 'po_title', true );
	}

	/**
	 * @param WP_Post $popup
	 * @return string
	 */
	private function _get_subheading( WP_Post $popup ){
		return get_post_meta( $popup->ID, 'po_subtitle', true );
	}

	/**
	 * Returns popup trigger settings with keys compatible with Hustle  Custom Content triggers
	 *
	 * @param WP_Post $popup
	 * @return array
	 */
	private function _get_trigger_settings( WP_Post $popup ){
		$popup_id = $popup->ID;
		$saved_settings = (array) maybe_unserialize( get_post_meta( $popup_id, "po_display_data", true ) );
		$triggers = array();

		$display = get_post_meta( $popup_id, 'po_display', true );
		$on_time = true;

		if ( 'delay' == $display ) {
			$display = 'time';
			$delay = (int) $saved_settings['delay'];

			if ( 0 == $delay ) {
				$on_time = false;
			}
		}

		$triggers['trigger'] = 'anchor' == $display ? 'scrolled' : $display;
		$triggers['on_time'] = $on_time;
		$triggers['on_time_delay'] = (int) $saved_settings['delay'];
		$triggers['on_time_unit'] = 's' == $saved_settings['delay_type'] ? 'seconds' : 'minutes';
		$triggers['on_scroll'] = 'anchor' == $display ? 'selector' : 'scrolled';
		$triggers['on_scroll_page_percent'] = (int) $saved_settings['scroll'];
		$triggers['on_scroll_css_selector'] = $saved_settings['anchor'];
		$triggers['on_click_element'] = '';
		$triggers['on_exit_intent'] = false;
		$triggers['on_adblock'] = false;
		$triggers['on_adblock_delayed'] = false;
		$triggers['on_adblock_delayed_time'] = 180;
		$triggers['on_adblock_delayed_unit'] = 'seconds';

		return $triggers;
	}

	private function _get_conditions_settings( WP_Post $popup ){
		$conditions = array();
		$popup_id = $popup->ID;
		$rules = (array) maybe_unserialize( get_post_meta( $popup_id, "po_rule", true ) );
		$rules_data = (array) maybe_unserialize( get_post_meta( $popup_id, "po_rule_data", true ) );

		$map = array(
			"login" => "visitor_logged_in",
			"no_login" => "visitor_not_logged_in",
			"count" => "shown_less_than",
			"mobile" => "only_on_mobile",
			"no_mobile" => "not_on_mobile",
			"referrer" => "from_specific_ref",
			"no_internal" => "not_from_internal_link",
			"searchengine" => "from_search_engine",
			"url" => "on_specific_url",
			"comment" => "visitor_has_commented",
			"country" => "in_a_country",
			"no_referrer" => "not_from_specific_ref",
			"no_url" => "not_on_specific_url",
			"no_comment" => "visitor_has_never_commented",
			"no_country" => "not_in_a_country"
		);

		foreach( $rules as $rule_name ){
			if ( isset( $map[ $rule_name ] ) ) {

				// handling specific urls and referrers
				if ( isset( $rules_data[$rule_name] ) ) {
					if ( $rule_name == "url" || $rule_name == "no_url" ) {
						$url_rules = array(
							"urls" => implode( "\n", $rules_data[$rule_name] )
						);
						$rules_data[$rule_name] = str_replace("\r","",$url_rules);
					}
					if ( $rule_name == "referrer" || $rule_name == "no_referrer" ) {
						$ref_rules = array(
							"refs" => implode( "\n", $rules_data[$rule_name] )
						);
						$rules_data[$rule_name] = str_replace("\r","",$ref_rules);
					}
				}

				$conditions[ $map[ $rule_name ] ] = isset( $rules_data[ $rule_name ] ) ? $rules_data[ $rule_name ]  : true;
			}
		}

		return $conditions;
	}

	/**
 	 * Convert old animation name to New (3.0+).
 	 *
	 * @param string $animation The animation type.
	 * @param boolean $in Animation In or Animation Out (true is in; false is out).
	 * @return string
	 */
	private function _map_animation_settings($animation, $in = true) {
		// Animation In names.
		$in_map = array(
			"fadein" => "fadeIn",
			"slideright" => "slideInRight",
			"slidebottom" => "slideInUp",
			"fall" => "zoomIn",
			"sidefall" => "slideInRight",
			"scaled" => "zoomIn",
			"sign" => "fadeIn",
			"slit" => "fadeIn",
			"flipx" => "fadeIn",
			"flipy" => "fadeIn",
			"rotatex" => "rotateIn",
			"rotatey" => "rotateIn",
			"newspaper" => "newspaperIn",
		);

		// Animation Out names.
		$out_map = array(
			"fadein" => "fadeOut",
			"slideright" => "slideOutRight",
			"slidebottom" => "slideOutUp",
			"scaled" => "zoomOut",
			"sign" => "fadeOut",
			"slit" => "fadeOut",
			"flipx" => "fadeOut",
			"flipy" => "fadeOut",
			"rotatex" => "rotateOut",
			"rotatey" => "rotateOut",
			"newspaper" => "newspaperOut",

		);

		// If animation in...
		if ( $in ) {
			// Take old name and change to new name.
			return isset($in_map[$animation]) ? $in_map[$animation] : 'no_animation';
		// If animation out...
		} else {
			// Take old name and change to new name.
			return isset($out_map[$animation]) ? $out_map[$animation] : 'no_animation';
		}

	}

	/**
 	 * Convert old trigger names to New (3.0+). scrolled => scroll in particular.
 	 *
	 * @param string $trigger The trigger type.
	 * @return string
	 */
	private function _map_trigger_settings($triggers) {
		// If trigger is scrolled, change to scroll for objects or arrays.
		if (gettype($triggers) === 'array' && isset($triggers['trigger']) && $triggers['trigger'] === 'scrolled') {
			$triggers['trigger'] = 'scroll';
		} elseif (gettype($triggers) === 'object' && isset($triggers->trigger) && $triggers->trigger === 'scrolled') {
			$triggers->trigger = 'scroll';
		}

		// Return new object/array.
		return $triggers;
	}

}