<div id="wph-modal-palette" class="wpmudev-box-gray {{ ( _.isTrue(customize_colors) ) ? 'wpmudev-show' : 'wpmudev-hidden' }}">

	<h5><?php _e( "Basic", Opt_In::TEXT_DOMAIN ); ?></h5>

	<div class="wpmudev-row" style="z-index: 9;">

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Main background", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 3;"><input id="popup_main_background" class="wpmudev-color_picker" type="text"  value="{{main_bg_color}}" data-attribute="main_bg_color" data-alpha="true" /></div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Image container BG", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 3;"><input id="popup_image_background" class="wpmudev-color_picker" type="text"  value="{{image_container_bg}}" data-attribute="image_container_bg" data-alpha="true" /></div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Form area BG", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 1;"><input id="popup_form_background" class="wpmudev-color_picker" type="text"  value="{{form_area_bg}}" data-attribute="form_area_bg" data-alpha="true" /></div>

		</div>

	</div>

	<div class="wpmudev-row" style="z-index: 8;">

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Title color", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 2;"><input id="popup_title_color" class="wpmudev-color_picker" type="text"  value="{{title_color}}" data-attribute="title_color" data-alpha="true" /></div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Subtitle color", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 1;"><input id="popup_subtitle_color" class="wpmudev-color_picker" type="text"  value="{{subtitle_color}}" data-attribute="subtitle_color" data-alpha="true" /></div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Content color", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 3;"><input id="popup_content_color" class="wpmudev-color_picker" type="text"  value="{{content_color}}" data-attribute="content_color" data-alpha="true" /></div>

		</div>

	</div>

	<div class="wpmudev-row" style="z-index: 7;">

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Link color", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-pickers" style="z-index: 2;">
				<div class="wpmudev-picker"><input id="popup_link_color" class="wpmudev-color_picker" type="text"  value="{{link_static_color}}" data-attribute="link_static_color" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_link_color_hover" class="wpmudev-color_picker" type="text"  value="{{link_hover_color}}" data-attribute="link_hover_color" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_link_color_focus" class="wpmudev-color_picker" type="text"  value="{{link_active_color}}" data-attribute="link_active_color" data-alpha="true" /></div>
			</div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "CTA Button BG", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-pickers" style="z-index: 3;">
				<div class="wpmudev-picker"><input id="popup_cta_backgrounds" class="wpmudev-color_picker" type="text"  value="{{cta_button_static_bg}}" data-attribute="cta_button_static_bg" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_cta_backgrounds_hover" class="wpmudev-color_picker" type="text"  value="{{cta_button_hover_bg}}" data-attribute="cta_button_hover_bg" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_cta_backgrounds_focus" class="wpmudev-color_picker" type="text"  value="{{cta_button_active_bg}}" data-attribute="cta_button_active_bg" data-alpha="true" /></div>
			</div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "CTA Button color", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-pickers" style="z-index: 3;">
				<div class="wpmudev-picker"><input id="popup_cta_color" class="wpmudev-color_picker" type="text"  value="{{cta_button_static_color}}" data-attribute="cta_button_static_color" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_cta_color_hover" class="wpmudev-color_picker" type="text"  value="{{cta_button_hover_color}}" data-attribute="cta_button_hover_color" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_cta_color_focus" class="wpmudev-color_picker" type="text"  value="{{cta_button_active_color}}" data-attribute="cta_button_active_color" data-alpha="true" /></div>
			</div>

		</div>

	</div>

	<h5><?php _e( "Opt In Form", Opt_In::TEXT_DOMAIN ); ?></h5>

	<div class="wpmudev-row" style="z-index: 6;">

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Input field BG", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-pickers" style="z-index: 3;">
				<div class="wpmudev-picker"><input id="popup_field_background" class="wpmudev-color_picker" type="text"  value="{{optin_input_static_bg}}" data-attribute="optin_input_static_bg" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_field_background_hover" class="wpmudev-color_picker" type="text"  value="{{optin_input_hover_bg}}" data-attribute="optin_input_hover_bg" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_field_background_focus" class="wpmudev-color_picker" type="text"  value="{{optin_input_active_bg}}" data-attribute="optin_input_active_bg" data-alpha="true" /></div>
			</div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Input icon color", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 2;"><input id="popup_field_icon" class="wpmudev-color_picker" type="text"  value="{{optin_input_icon}}" data-attribute="optin_input_icon" data-alpha="true" /></div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Placeholder", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 1;"><input id="popup_placeholder_color" class="wpmudev-color_picker" type="text"  value="{{optin_placeholder_color}}" data-attribute="optin_placeholder_color" data-alpha="true" /></div>

		</div>

	</div>

	<div class="wpmudev-row" style="z-index: 5;">

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Input field text", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-pickers" style="z-index: 3;">
				<div class="wpmudev-picker"><input id="popup_field_color" class="wpmudev-color_picker" type="text"  value="{{optin_form_field_text_static_color}}" data-attribute="optin_form_field_text_static_color" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_field_color_hover" class="wpmudev-color_picker" type="text"  value="{{optin_form_field_text_hover_color}}" data-attribute="optin_form_field_text_hover_color" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_field_color_focus" class="wpmudev-color_picker" type="text"  value="{{optin_form_field_text_active_color}}" data-attribute="optin_form_field_text_active_color" data-alpha="true" /></div>
			</div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Submit button BG", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-pickers" style="z-index: 2;">
				<div class="wpmudev-picker"><input id="popup_button_backgrounds" class="wpmudev-color_picker" type="text"  value="{{optin_submit_button_static_bg}}" data-attribute="optin_submit_button_static_bg" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_button_backgrounds_hover" class="wpmudev-color_picker" type="text"  value="{{optin_submit_button_hover_bg}}" data-attribute="optin_submit_button_hover_bg" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_button_backgrounds_focus" class="wpmudev-color_picker" type="text"  value="{{optin_submit_button_active_bg}}" data-attribute="optin_submit_button_active_bg" data-alpha="true" /></div>
			</div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Submit button color", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-pickers" style="z-index: 1;">
				<div class="wpmudev-picker"><input id="popup_button_color" class="wpmudev-color_picker" type="text"  value="{{optin_submit_button_static_color}}" data-attribute="optin_submit_button_static_color" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_button_color_hover" class="wpmudev-color_picker" type="text"  value="{{optin_submit_button_hover_color}}" data-attribute="optin_submit_button_hover_color" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_button_color_focus" class="wpmudev-color_picker" type="text"  value="{{optin_submit_button_active_color}}" data-attribute="optin_submit_button_active_color" data-alpha="true" /></div>
			</div>

		</div>

	</div>

	<div class="wpmudev-row" style="z-index: 4;">

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Error text", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 3;"><input id="popup_error_color" class="wpmudev-color_picker" type="text"  value="{{optin_error_text_color}}" data-attribute="optin_error_text_color" data-alpha="true" /></div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Error text BG", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 3;"><input id="popup_error_bg" class="wpmudev-color_picker" type="text"  value="{{optin_error_text_bg}}" data-attribute="optin_error_text_bg" data-alpha="true" /></div>

		</div>


	</div>

	<h5><?php _e( "Mailchimp Extras", Opt_In::TEXT_DOMAIN ); ?></h5>

	<div class="wpmudev-row" style="z-index: 3;">

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Mailchimp title", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 2;"><input id="popup_mc_title_color" class="wpmudev-color_picker" type="text"  value="{{optin_mailchimp_title_color}}" data-attribute="optin_mailchimp_title_color" data-alpha="true" /></div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Mailchimp labels", Opt_In::TEXT_DOMAIN ); ?></label>

		    <div class="wpmudev-picker" style="z-index: 1;"><input id="popup_mc_label_color" class="wpmudev-color_picker" type="text"  value="{{optin_mailchimp_labels_color}}" data-attribute="optin_mailchimp_labels_color" data-alpha="true" /></div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Check / Radio BG", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 2;">
				<input id="popup_radio_background" class="wpmudev-color_picker" type="text"  value="{{optin_check_radio_bg}}" data-attribute="optin_check_radio_bg" data-alpha="true" />
			</div>

		</div>

	</div>

	<div class="wpmudev-row" style="z-index: 3;">

		<div class="wpmudev-col col-12">

			<label><?php _e( "Check / Radio tick", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 1;"><input id="popup_tick_color" class="wpmudev-color_picker" type="text"  value="{{optin_check_radio_tick_color}}" data-attribute="optin_check_radio_tick_color" data-alpha="true" /></div>

		</div>

	</div>

	<h5><?php _e( "Additional Styles", Opt_In::TEXT_DOMAIN ); ?></h5>

	<div class="wpmudev-row" style="z-index: 2;">

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Success tick", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 3;"><input id="popup_success_tick_color" class="wpmudev-color_picker" type="text"  value="{{optin_success_tick_color}}" data-attribute="optin_success_tick_color" data-alpha="true" /></div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Success content", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 2;"><input id="popup_success_content_color" class="wpmudev-color_picker" type="text"  value="{{optin_success_content_color}}" data-attribute="optin_success_content_color" data-alpha="true" /></div>

		</div>

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Pop-up overlay", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-picker" style="z-index: 1;"><input id="popup_overlay_color" class="wpmudev-color_picker" type="text"  value="{{overlay_bg}}" data-attribute="overlay_bg" data-alpha="true" /></div>

		</div>

	</div>

	<div class="wpmudev-row" style="z-index: 1;">

		<div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

			<label><?php _e( "Close (x) btn color", Opt_In::TEXT_DOMAIN ); ?></label>

			<div class="wpmudev-pickers" style="z-index: 3;">
				<div class="wpmudev-picker"><input id="popup_close_color" class="wpmudev-color_picker" type="text"  value="{{close_button_static_color}}" data-attribute="close_button_static_color" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_close_color_hover" class="wpmudev-color_picker" type="text"  value="{{close_button_hover_color}}" data-attribute="close_button_hover_color" data-alpha="true" /></div>
				<div class="wpmudev-picker"><input id="popup_close_color_focus" class="wpmudev-color_picker" type="text"  value="{{close_button_active_color}}" data-attribute="close_button_active_color" data-alpha="true" /></div>
			</div>

		</div>

	</div>

	<button class="wph-reset-color-palette wpmudev-button wpmudev-button-sm wpmudev-button-ghost"><?php _e( "Reset palette", Opt_In::TEXT_DOMAIN ); ?></button>

</div><?php // #wph-modal-palette ?>