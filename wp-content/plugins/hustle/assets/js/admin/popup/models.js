Hustle.define( "Pop_Up.Models",  function(){
	"use strict";
	
	var Base = Module.Model.extend({
		defaults: _.extend( Module.Model.prototype.defaults, {
			module_type: 'popup'
		})
	});
	
	var Content = Hustle.get("Models.M").extend({
		defaults: {
			module_name: '',
			has_title: false,
			title: '',
			sub_title: '',
			main_content: '',
			use_feature_image: false,
			feature_image: '',
			feature_image_location: 'left',
			feature_image_hide_on_mobile: false,
			show_cta: false,
			show_gdpr: false,
			cta_label: '',
			cta_url: '',
			cta_target: 'blank',
			use_email_collection: false,
			save_local_list: false,
			active_email_service: '',
			email_services: '',
			form_elements: '',
			after_successful_submission: 'show_success',
			success_message: '',
			gdpr_message: 'Yes, I agree with the <a href="#" target="_blank" rel="noopener">privacy policy</a>.',
			auto_close_success_message: false,
			auto_close_time: 5,
			auto_close_unit: 'seconds',
			redirect_url: '',
		}
	});
	
	var Design = Hustle.get("Models.M").extend({
		defaults: {
			form_layout: "one",
			feature_image_position: "left",
			feature_image_fit: "contain",
			feature_image_horizontal: "center",
			feature_image_horizontal_px: "-100",
			feature_image_vertical: "center",
			feature_image_vertical_px: "-100",
			style: "cabriolet",
			customize_colors: false,
			
			main_bg_color: "rgba(56,69,78,1)",
			image_container_bg: "rgba(53,65,74,1)",
			form_area_bg: "rgba(93,115,128,1)",

			title_color: "rgba(253,253,253,1)",
			subtitle_color: "rgba(253,253,253,1)",
			content_color: "rgba(173,181,183,1)",
			
			link_static_color: "rgba(56,197,181,1)",
			link_hover_color: "rgba(73,226,209,1)",
			link_active_color: "rgba(73,226,209,1)",

			cta_button_static_bg: "rgba(56,197,181,1)",
			cta_button_hover_bg: "rgba(73,226,209,1)",
			cta_button_active_bg: "rgba(73,226,209,1)",

			cta_button_static_color: "rgba(255,255,255,1)",
			cta_button_hover_color: "rgba(255,255,255,1)",
			cta_button_active_color: "rgba(255,255,255,1)",

			optin_input_static_bg: "rgba(253,253,253,1)",
			optin_input_hover_bg: "rgba(253,253,253,1)",
			optin_input_active_bg: "rgba(253,253,253,1)",

			optin_input_icon: "rgba(173,181,183,1)",

			optin_placeholder_color: "rgba(173,181,183,1)",

			optin_form_field_text_static_color: "rgba(54,59,63,1)",
			optin_form_field_text_hover_color: "rgba(54,59,63,1)",
			optin_form_field_text_active_color: "rgba(54,59,63,1)",

			optin_submit_button_static_bg: "rgba(56,197,181,1)",
			optin_submit_button_hover_bg: "rgba(73,226,209,1)",
			optin_submit_button_active_bg: "rgba(73,226,209,1)",

			optin_submit_button_static_color: "rgba(253,253,253,1)",
			optin_submit_button_hover_color: "rgba(253,253,253,1)",
			optin_submit_button_active_color: "rgba(253,253,253,1)",

			optin_error_text_color: "#F1F1F1",
			optin_error_text_bg: "#EA6464",

			optin_mailchimp_title_color: "rgba(253,253,253,1)",
			optin_mailchimp_labels_color: "rgba(173,181,183,1)",

			optin_check_radio_bg: "rgba(253,253,253,1)",

			optin_check_radio_tick_color: "rgba(56,197,181,1)",

			optin_success_tick_color: "rgba(55,198,181,1)",

			optin_success_content_color: "rgba(253,253,253,1)",

			overlay_bg: "rgba(51,51,51,0.9)",
			
			close_button_static_color: "rgba(56,197,181,1)",
			close_button_hover_color: "rgba(73,226,209,1)",
			close_button_active_color: "rgba(73,226,209,1)",
			
			border: false,
			border_radius: 5,
			border_weight: 3,
			border_type: "solid",
			border_color: "rgba(218,218,218,1)",
			form_fields_border: false,
			form_fields_border_radius: 5,
			form_fields_border_weight: 3,
			form_fields_border_type: "solid",
			form_fields_border_color: "rgba(218,218,218,1)",
			button_border: false,
			button_border_radius: 5,
			button_border_weight: 3,
			button_border_type: "solid",
			button_border_color: "rgba(218,218,218,1)",
			form_fields_icon: "static",
			form_fields_proximity: "joined",
			drop_shadow: false,
			drop_shadow_x: 0,
			drop_shadow_y: 0,
			drop_shadow_blur: 0,
			drop_shadow_spread: 0,
			drop_shadow_color: "rgba(0,0,0,0)",
			customize_size: false,
			custom_height: 300,
			custom_width: 600,
			customize_css: false,
			custom_css: "",
		}
	});
	
	var Triggers = Hustle.get("Models.Trigger");
	var Display_Settings = Hustle.get("Models.M").extend({
		defaults:{
			conditions: "",
			triggers: "",
			animation_in: "",
			animation_out: "",
			after_close: "",
			expiration: 365,
			expiration_unit: "days",
			allow_scroll_page: false,
			not_close_on_background_click: false,
			on_submit: "default" // default|close|ignore|redirect
		},
		initialize: function(data) {
			_.extend( this, data );
			if( ! ( this.get('triggers') instanceof Backbone.Model ) ){
				this.set( 'triggers', new Triggers( this.triggers ) );
			}

			if( ! ( this.get('conditions') instanceof Backbone.Model ) ){
				/**
				 * Make sure conditions is not an array
				 */
				if( _.isEmpty( this.get('conditions') ) && _.isArray( this.get('conditions') )  )
					this.conditions = {};

				var hModel = Hustle.get("Model");
				this.set( 'conditions', new hModel( this.conditions ) );
			}
			this.on( 'change', this.user_has_change, this );

		}
	});

	return {
		Base: Base,
		Content: Content,
		Design: Design,
		Display_Settings: Display_Settings,
	};
});
