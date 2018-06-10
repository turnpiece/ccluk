Hustle.define("Embedded.View", function($, doc, win){
	"use strict";

	return Hustle.View.extend({
		el: '.wpmudev-hustle-embedded-wizard-view',
		preview: false,
		preview_model: false,
		events: {
			'click .wpmudev-button-save': 'save_changes',
			'click .wpmudev-button-continue': 'save_continue',
			'click .wpmudev-button-finish': 'save_finish',
			'click .wpmudev-button-cancel': 'cancel',
			'click .wpmudev-button-back': 'back',
			'change .wpmudev-menu .wpmudev-select': 'mobile_navigate',
		},
		init: function( opts ){
			this.content_view = opts.content_view;
			this.design_view = opts.design_view;
			this.settings_view = opts.settings_view;

			// unset listeners
			this.stopListening( this.content_view.model, 'change', this.update_base_model );
			this.stopListening( this.content_view.model, 'change', this.content_view_changed );
			//this.stopListening( this.content_view.model, 'change', this.handle_preview );
			//this.stopListening( this.design_view.model, 'change', this.handle_preview );
			this.stopListening( this.design_view.model, 'change', this.design_view_changed );
			this.stopListening( this.settings_view.model, 'change', this.settings_view_changed );
			$(document).off( 'click', 'ul.wpmudev-cta-target-options li', $.proxy( this.toggle_cta_options, this ) );
			$(document).off( 'click', 'ul.wpmudev-after-submit-options li', $.proxy( this.toggle_submit_options, this ) );
			$(document).off( 'click', 'ul.wpmudev-feature-image-position-options li', $.proxy( this.toggle_feature_image_position_options, this ) );
			$(document).off( 'click', 'ul.wpmudev-feature-image-fit-options li', $.proxy( this.toggle_feature_image_fit_options, this ) );
			$(document).off( 'click', 'ul.wpmudev-feature-image-horizontal-options li', $.proxy( this.toggle_feature_image_horizontal_options, this ) );
			$(document).off( 'click', 'ul.wpmudev-feature-image-vertical-options li', $.proxy( this.toggle_feature_image_vertical_options, this ) );
			$(document).off( 'click', 'ul.wpmudev-form-fields-icon-options li', $.proxy( this.toggle_form_fields_icon_options, this ) );
			$(document).off( 'click', 'ul.wpmudev-form-fields-proximity-options li', $.proxy( this.toggle_form_fields_proximity_options, this ) );
			$(document).off( 'click', 'ul.wpmudev-display-triggers li', $.proxy( this.toggle_display_triggers, this ) );
			$(document).off( 'click', '.wpmudev-preview', $.proxy( this.open_preview, this ) );
			$(document).off( 'click', '.hustle-modal-close .hustle-icon', $.proxy( this.close_preview, this ) );
			$(document).off( 'click', '.wpmudev-modal-mask', $.proxy( this.close_preview, this ) );
			$(document).off( 'click', '.wph-reset-color-palette', $.proxy( this.reset_color_palette, this ) );
			// Get rid of escape key listener.
			$(document).off( 'keydown', $.proxy( this.escape_key, this ) );
			//Hustle.Events.off( 'embedded.preview.prepare', $.proxy( this.handle_preview, this ) );

			// set listeners
			this.listenTo( this.content_view.model, 'change', this.update_base_model );
			this.listenTo( this.content_view.model, 'change', this.content_view_changed );
			//this.listenTo( this.content_view.model, 'change', this.handle_preview );
			//this.listenTo( this.design_view.model, 'change', this.handle_preview );
			this.listenTo( this.design_view.model, 'change', this.design_view_changed );
			this.listenTo( this.settings_view.model, 'change', this.settings_view_changed );
			$(document).on( 'click', 'ul.wpmudev-cta-target-options li', $.proxy( this.toggle_cta_options, this ) );
			$(document).on( 'click', 'ul.wpmudev-after-submit-options li', $.proxy( this.toggle_submit_options, this ) );
			$(document).on( 'click', 'ul.wpmudev-feature-image-position-options li', $.proxy( this.toggle_feature_image_position_options, this ) );
			$(document).on( 'click', 'ul.wpmudev-feature-image-fit-options li', $.proxy( this.toggle_feature_image_fit_options, this ) );
			$(document).on( 'click', 'ul.wpmudev-feature-image-horizontal-options li', $.proxy( this.toggle_feature_image_horizontal_options, this ) );
			$(document).on( 'click', 'ul.wpmudev-feature-image-vertical-options li', $.proxy( this.toggle_feature_image_vertical_options, this ) );
			$(document).on( 'click', 'ul.wpmudev-form-fields-icon-options li', $.proxy( this.toggle_form_fields_icon_options, this ) );
			$(document).on( 'click', 'ul.wpmudev-form-fields-proximity-options li', $.proxy( this.toggle_form_fields_proximity_options, this ) );
			$(document).on( 'click', 'ul.wpmudev-display-triggers li', $.proxy( this.toggle_display_triggers, this ) );
			$(document).on( 'click', '.wpmudev-preview', $.proxy( this.open_preview, this ) );
			$(document).on( 'click', '.hustle-modal-close .hustle-icon', $.proxy( this.close_preview, this ) );
			$(document).on( 'click', '.wpmudev-modal-mask', $.proxy( this.close_preview, this ) );
			$(document).on( 'click', '.wph-reset-color-palette', $.proxy( this.reset_color_palette, this ) );
			$(document).on( 'change keyup keypress', 'input[name=module_name]', $.proxy( this.validate_modal_name, this ) );
			// Add escape key listener.
			$(document).on( 'keydown', $.proxy( this.escape_key, this ) );
			Hustle.Events.on( 'modules.view.preview.success', $.proxy( this.preview_success_message_delay, this ) );
			//Hustle.Events.on( 'embedded.preview.prepare', $.proxy( this.handle_preview, this ) );

			return this.render();
		},
		render: function(){
			// content view
			this.content_view.target_container.html('');
			this.content_view.render();
			this.content_view.delegateEvents();
			this.content_view.target_container.append( this.content_view.$el );
			this.content_view.after_render();
			// manually trigger this change to reflect what's been saved
			var use_email_collection = parseInt(this.content_view.model.get('use_email_collection'));
			this.use_email_collection_changed(use_email_collection);
			if ( use_email_collection ) {
				this.after_successful_submission_changed(this.content_view.model.get('after_successful_submission'));
			}

			// design view
			this.design_view.target_container.html('');
			this.design_view.delegateEvents();
			this.design_view.target_container.append( this.design_view.$el );
			this.design_view.after_render();

			// settings view
			this.settings_view.target_container.html('');
			this.settings_view.delegateEvents();
			this.settings_view.target_container.append( this.settings_view.$el );
			this.settings_view.after_render();

			Hustle.Events.trigger("modules.view.rendered", this);


		},
		set_content_from_tinymce: function(keep_silent) {

			keep_silent = keep_silent || false;

			if ( typeof tinyMCE !== 'undefined' ) {
				// main_content editor
				var main_content_editor = tinyMCE.get('main_content'),
					$main_content_textarea = this.$('textarea#main_content'),
					main_content = ( $main_content_textarea.attr('aria-hidden') === 'true' ) ? main_content_editor.getContent() : $main_content_textarea.val();

				this.content_view.model.set( 'main_content', main_content, {silent: keep_silent} );

				// success_message editor
				var success_message_editor = tinyMCE.get('success_message'),
					$success_message_textarea = this.$('textarea#success_message'),
					success_message = ( $success_message_textarea.attr('aria-hidden') === 'true' ) ? success_message_editor.getContent() : $success_message_textarea.val();

				this.content_view.model.set( 'success_message', success_message, {silent: keep_silent} );

				// gdpr_message editor
				var gdpr_message_editor = tinyMCE.get('gdpr_message'),
					$gdpr_message_textarea = this.$('textarea#gdpr_message'),
					gdpr_message = ( $gdpr_message_textarea.attr('aria-hidden') === 'true' ) ? gdpr_message_editor.getContent() : $gdpr_message_textarea.val();

				this.content_view.model.set( 'gdpr_message', gdpr_message, {silent: keep_silent} );

			}
		},
		open_preview: function(e) {
			e.preventDefault();
			e.stopPropagation();

			this.handle_preview();
		},
		handle_preview: function() {
			this.set_content_from_tinymce(true);
			this.sanitize_data();

			var $preview_content = this.$('#wph-preview-modal .wpmudev-modal-mask').siblings('.hustle-modal');

			if ( $preview_content.length ) {
					$preview_content.remove();
			}

					var me = this,
					main_content = me.content_view.model.get('main_content'),
					nonce = $(".wpmudev-preview").data("nonce")
			;
			// If no shortcodes are used, bypass ajax for speed.
			if (main_content.search(/\[/g) === -1) {
				me.render_preview(me.content_view.model.toJSON());
			// If shortcodes are used, trigger ajax.
			} else {
				// Render shortcodes in main content.
				$.ajax({
					type: "POST",
					url: ajaxurl,
					dataType: "json",
					data: {
						action: 'hustle_shortcode_render',
						content: main_content,
						_ajax_nonce: nonce
					},
					success: function(res) {
						if (res && res.data && res.data.content) {
							// Update content model with rendered shortcode content.
							var content_model = _.extend(me.content_view.model.toJSON(), {
								main_content: res.data.content
							});
							me.render_preview(content_model);
						}
					},
					error: function() {
					}
				});
			}
		},
		render_preview: function(content_model) {
			var me = this,
				is_optin_active = this.content_view.model.get('use_email_collection'),
				template = ( _.isTrue( is_optin_active ) )
						? Optin.template("wpmudev-hustle-modal-with-optin-tpl")
						: Optin.template("wpmudev-hustle-modal-without-optin-tpl"),

				data = _.extend(
					me.model.toJSON(),
					{
							content: content_model,
							design: me.design_view.model.toJSON(),
							settings: me.settings_view.model.toJSON()
					}
				)
			;
			// Append to preview after content updated.
			me.$('#wph-preview-modal').append(template(data));
			// Apply custom CSS and preview styles after content is appended.
			me.apply_custom_css();
			me.apply_preview_styles();
			me.after_preview_render();
			Hustle.Events.trigger("modules.view.rendered", me);
		},
		after_preview_render: function() {
			var me = this,
			$preview = this.$('#wph-preview-modal').addClass('wpmudev-modal-active'),
			$modal = $preview.find('.hustle-modal'),
			animation_in = this.settings_view.model.get('animation_in');

			$('body').addClass('wpmudev-modal-is_active');

			if ($modal.hasClass('hustle-animated')) {
					setTimeout(function(){
							$modal.addClass('hustle-animate-' + animation_in ); // hustle-animate-{animate_in}
							me.apply_custom_size();
					}, 100);
			} else {
					this.apply_custom_size();
			}
		},
		apply_preview_styles: function() {
			var me = this,
				content_data = this.content_view.model.toJSON(),
				design_data = this.design_view.model.toJSON(),
				style = design_data.style;

			if ( _.isTrue(content_data.use_email_collection) ) {
				// skip and proceed to previewing with optin
				this.apply_preview_optin_styles();
				return;
			}

			// modal parts
			var $preview_modal = this.$('#wph-preview-modal'),
				$modal = $preview_modal.find('.hustle-modal'),
				$modal_body = $modal.find('.hustle-modal-body'),
				$modal_body_cabriolet = $modal.find('.hustle-modal-body section'),
				$modal_title = $modal.find('.hustle-modal-title'),
				$modal_subtitle_color = $modal.find('.hustle-modal-subtitle'),
				$img_container = $modal.find('.hustle-modal-image'),
				$content = $modal.find('article, .hustle-modal-message'),
				$content_bq = $modal.find('article blockquote, .hustle-modal-message blockquote'),
				$content_link = $modal.find('article a, .hustle-modal-message a'),
				$cta_button = $modal.find('.hustle-modal-cta'),
				$close_container = $modal.find('.hustle-modal-close'),
				$close_button = $modal.find('.hustle-modal-close svg path'),
				$overlay = $preview_modal.find('.wpmudev-modal-mask');

			// main_bg_color
			if ( style === 'cabriolet' ) {
				$modal_body_cabriolet.css( 'background-color', design_data.main_bg_color );
			} else {
				$modal_body.css( 'background-color', design_data.main_bg_color );
			}

			// title_color
			$modal_title.css( 'color', design_data.title_color );

			// subtitle_color
			$modal_subtitle_color.css( 'color', design_data.subtitle_color );

			// image_container_bg
			$img_container.css( 'background-color', design_data.image_container_bg );

			// content_color
			$content.css( 'color', design_data.content_color );
			$content_bq.css( 'border-left-color', design_data.link_static_color );
			$content_bq.mouseover(function(){
				$(this).css( 'border-left-color', design_data.link_hover_color );
			}).mouseout(function(){
				$(this).css( 'border-left-color', design_data.link_static_color );
			});

			// link color
			$content_link.css( 'color', design_data.link_static_color );
			$content_link.mouseover(function(){
				$(this).css( 'color', design_data.link_hover_color );
			}).mouseout(function(){
				$(this).css( 'color', design_data.link_static_color );
			});

			// cta button
			$cta_button.css({
				'background-color': design_data.cta_button_static_bg,
				'color': design_data.cta_button_static_color,
			});
			$cta_button.mouseover(function(){
				$(this).css({
					'background-color': design_data.cta_button_hover_bg,
					'color': design_data.cta_button_hover_color,
				});
			}).mouseout(function(){
				$(this).css({
					'background-color': design_data.cta_button_static_bg,
					'color': design_data.cta_button_static_color,
				});
			});

			// close button
			$close_button.css( 'fill', design_data.close_button_static_color );
			$close_container.mouseover(function(){
				$close_button.css( 'fill', design_data.close_button_hover_color );
			}).mouseout(function(){
				$close_button.css( 'fill', design_data.close_button_static_color );
			});

			// overlay_bg
			$overlay.css( 'background-color', design_data.overlay_bg );

			// feature image
			var $feature_image = $preview_modal.find('.hustle-modal-image img'),
				horizontal_fit = '',
				vertical_fit = '';

			if ( design_data.feature_image_fit === 'contain' || design_data.feature_image_fit === 'cover' ) {
				if ( design_data.feature_image_horizontal === 'custom' ) {
					horizontal_fit = design_data.feature_image_horizontal_px + 'px';
				} else {
					horizontal_fit = design_data.feature_image_horizontal;
				}
				if ( design_data.feature_image_vertical === 'custom' ) {
					vertical_fit = design_data.feature_image_vertical_px + 'px';
				} else {
					vertical_fit = design_data.feature_image_vertical;
				}
				$feature_image.css({
					'background-position': horizontal_fit + ' ' + vertical_fit,
					'object-position': horizontal_fit + ' ' + vertical_fit
				});
			}

			// border, drop shadow
			if ( _.isTrue( design_data.border ) ) {
				var border_style = design_data.border_weight + 'px '
					+ design_data.border_type + ' '
					+ design_data.border_color;

				if ( style === 'cabriolet' ) {

					$modal.find("section").css({
						'border': border_style,
						'border-radius': design_data.border_radius + 'px'
					});

				} else {

					$modal_body.css({
						'border': border_style,
						'border-radius': design_data.border_radius + 'px'
					});

				}
			}
			if ( _.isTrue( design_data.drop_shadow ) ) {
				var box_shadow = design_data.drop_shadow_x + 'px '
					+ design_data.drop_shadow_y + 'px '
					+ design_data.drop_shadow_blur + 'px '
					+ design_data.drop_shadow_spread + 'px '
					+ design_data.drop_shadow_color;

				if ( style === 'cabriolet' ) {

					$modal.find("section").css({
						'box-shadow': box_shadow
					});

				} else {

					$modal_body.css({
						'box-shadow': box_shadow
					});

				}
			}
		},
		apply_preview_optin_styles: function() {
			// do the preview styles here with optin enabled
			var me = this,
				content_data = this.content_view.model.toJSON(),
				design_data = this.design_view.model.toJSON(),
				layout = design_data.form_layout;

			// modal parts
			var $preview_modal = this.$('#wph-preview-modal'),
				$modal = $preview_modal.find('.hustle-modal'),
				$modal_body = $modal.find('.hustle-modal-body'),
				$modal_success = $modal.find('.hustle-modal-success'),
				$modal_title = $modal.find('.hustle-modal-title'),
				$modal_subtitle_color = $modal.find('.hustle-modal-subtitle'),
				$content = $modal.find('article'),
				$content_bq = $modal.find('article blockquote'),
				$content_link = $modal.find('article a:not(.hustle-modal-cta)'),
				$input = $modal.find('.hustle-modal-optin_field'),
				$input_icon = $input.find('label .hustle-modal-optin_icon .hustle-icon path'),
				$placeholder = $input.find('label .hustle-modal-optin_placeholder'),
				$button = $modal.find('.hustle-modal-optin_button button'),
				$checkbox = $modal.find('.hustle-modal-mc_checkbox input+label, .hustle-modal-mc_checkbox input:checked+label'),
				$radio = $modal.find('.hustle-modal-mc_radio input+label, .hustle-modal-mc_radio input:checked+label'),
				$close_container = $modal.find('.hustle-modal-close'),
				$close_button = $modal.find('.hustle-modal-close svg path');

			// main_bg_color
			$modal_body.css( 'background-color', design_data.main_bg_color );
			// success bg
			$modal_success.css( 'background-color', design_data.main_bg_color );

			// image_container_bg
			$modal_body.find('.hustle-modal-image').css( 'background-color', design_data.image_container_bg );

			// form_area_bg
			if ( layout === 'one' || layout === 'two' ) {
				$modal.find('footer').css( 'background-color', design_data.form_area_bg );
			} else {
				$modal.find('.hustle-modal-optin_wrap').css( 'background-color', design_data.form_area_bg );
			}

			// title_color
			$modal_title.css( 'color', design_data.title_color );

			// subtitle_color
			$modal_subtitle_color.css( 'color', design_data.subtitle_color );

			// content_color
			$content.css( 'color', design_data.content_color );

			// content special: blockquote left border
			$content_bq.css( 'border-left-color', design_data.link_static_color );

			// link color
			$content_link.css( 'color', design_data.link_static_color );
			$content_link.mouseover(function(){
				$(this).css( 'color', design_data.link_hover_color );
			}).mouseout(function(){
				$(this).css( 'color', design_data.link_static_color );
			});

			// cta button
			$content.find('.hustle-modal-cta').css({
				'background': design_data.cta_button_static_bg,
				'color': design_data.cta_button_static_color
			});
			$content.find('.hustle-modal-cta').mouseover(function(){
				$(this).css({
					'background': design_data.cta_button_hover_bg,
					'color': design_data.cta_button_hover_color
				});
			}).mouseout(function(){
				$(this).css({
					'background': design_data.cta_button_static_bg,
					'color': design_data.cta_button_static_color
				});
			});

			// optin inputs
			$input.find('input').css( 'color', design_data.optin_form_field_text_static_color );
			$input.css( 'background-color', design_data.optin_input_static_bg );
			$input.mouseover(function(){
				$(this).find('input').css( 'color', design_data.optin_form_field_text_hover_color );
				$(this).css( 'background-color', design_data.optin_input_hover_bg );
			}).mouseout(function(){
				$(this).find('input').css( 'color', design_data.optin_form_field_text_static_color );
				$(this).css( 'background-color', design_data.optin_input_static_bg );
			});

			// optin_input_icon
			$input_icon.css( 'fill', design_data.optin_input_icon );

			// optin submit button
			$button.css( 'color', design_data.optin_submit_button_static_color );
			$button.css( 'background-color', design_data.optin_submit_button_static_bg );
			$button.mouseover(function(){
				$(this).css( 'color', design_data.optin_submit_button_hover_color );
				$(this).css( 'background-color', design_data.optin_submit_button_hover_bg );
			}).mouseout(function(){
				$(this).css( 'color', design_data.optin_submit_button_static_color );
				$(this).css( 'background-color', design_data.optin_submit_button_static_bg );
			});

			// optin_placeholder_color
			$placeholder.css( 'color', design_data.optin_placeholder_color );

			// mailchimp stuffs and modal overlay
			$checkbox.css( 'background-color', design_data.optin_check_radio_static_bg );
			$radio.css( 'background-color', design_data.optin_check_radio_static_bg );
			var $styles_el = $('#hustle-module-checkbox-radio-custom-styles'),
				checkbox_selector = '.hustle-modal .hustle-modal-optin_form .hustle-modal-mc_groups .hustle-modal-mc_option .hustle-modal-mc_checkbox input+label:before',
				checkbox_checked_selector = '.hustle-modal .hustle-modal-optin_form .hustle-modal-mc_groups .hustle-modal-mc_option .hustle-modal-mc_checkbox input:checked+label:before',
				radio_selector = '.hustle-modal .hustle-modal-optin_form .hustle-modal-mc_groups .hustle-modal-mc_option .hustle-modal-mc_radio input+label:before',
				radio_checked_selector = '.hustle-modal .hustle-modal-optin_form .hustle-modal-mc_groups .hustle-modal-mc_option .hustle-modal-mc_radio input:checked+label:before',
				mc_group_title = '.hustle-modal .hustle-modal-optin_form .hustle-modal-mc_title label',
				mc_group_labels = '.hustle-modal .hustle-modal-optin_form .hustle-modal-mc_groups .hustle-modal-mc_option .hustle-modal-mc_label label',
				overlay_bg = '.wpmudev-ui .wpmudev-modal .wpmudev-modal-mask',
				checkbox_styles = checkbox_selector + ' { color: '+ design_data.optin_check_radio_static_bg +'; }'
					+ checkbox_checked_selector + ' { color: '+ design_data.optin_check_radio_tick_color +'; }'
					+ radio_selector + ' { color: '+ design_data.optin_check_radio_static_bg +'; }'
					+ radio_checked_selector + ' { color: '+ design_data.optin_check_radio_tick_color +'; }'
					+ mc_group_title + ' { color: '+ design_data.optin_mailchimp_title_color +'; }'
					+ mc_group_labels + ' { color: '+ design_data.optin_mailchimp_labels_color +'; }'
					+ overlay_bg + ' { background-color: '+ design_data.overlay_bg +'; }';

			if ( $styles_el.length ) {
				$styles_el.remove();
			}
			$('<style id="hustle-module-mailchimp-custom-styles">' + checkbox_styles + '</style>').appendTo('body');

			// close button
			$close_button.css( 'fill', design_data.close_button_static_color );
			$close_container.mouseover(function(){
				$close_button.css( 'fill', design_data.close_button_hover_color );
			}).mouseout(function(){
				$close_button.css( 'fill', design_data.close_button_static_color );
			});

			// feature image
			var $feature_image = $preview_modal.find('.hustle-modal-image img'),
				horizontal_fit = '',
				vertical_fit = '';

			if ( design_data.feature_image_fit === 'contain' || design_data.feature_image_fit === 'cover' ) {
				if ( design_data.feature_image_horizontal === 'custom' ) {
					horizontal_fit = design_data.feature_image_horizontal_px + 'px';
				} else {
					horizontal_fit = design_data.feature_image_horizontal;
				}
				if ( design_data.feature_image_vertical === 'custom' ) {
					vertical_fit = design_data.feature_image_vertical_px + 'px';
				} else {
					vertical_fit = design_data.feature_image_vertical;
				}
				$feature_image.css({
					'background-position': horizontal_fit + ' ' + vertical_fit,
					'object-position': horizontal_fit + ' ' + vertical_fit
				});
			}


			// modal border
			if ( _.isTrue( design_data.border ) ) {
				var border_style = design_data.border_weight + 'px '
					+ design_data.border_type + ' '
					+ design_data.border_color;

				$modal_body.css({
					'border': border_style,
					'border-radius': design_data.border_radius + 'px'
				});
			}

			// drop shadow
			if ( _.isTrue( design_data.drop_shadow ) ) {
				var box_shadow = design_data.drop_shadow_x + 'px '
					+ design_data.drop_shadow_y + 'px '
					+ design_data.drop_shadow_blur + 'px '
					+ design_data.drop_shadow_spread + 'px '
					+ design_data.drop_shadow_color;

				$modal_body.css({
					'box-shadow': box_shadow
				});
			}

			// form fields border
			if ( _.isTrue( design_data.form_fields_border ) ) {
				var field_border_style = design_data.form_fields_border_weight + 'px '
					+ design_data.form_fields_border_type + ' '
					+ design_data.form_fields_border_color;

				$input.css({
					'border': field_border_style,
					'border-radius': design_data.form_fields_border_radius + 'px'
				});
			}

			// button border
			if ( _.isTrue( design_data.button_border ) ) {
				var button_border_style = design_data.button_border_weight + 'px '
					+ design_data.button_border_type + ' '
					+ design_data.button_border_color;

				$button.css({
					'border': button_border_style,
					'border-radius': design_data.button_border_radius + 'px'
				});
			}

		},
		apply_custom_size: function() {
			var me = this,
				content_data = this.content_view.model.toJSON(),
				design_data = this.design_view.model.toJSON(),
				style = design_data.style,
				layout = design_data.form_layout;

			// modal parts
			var $preview_modal = this.$('#wph-preview-modal'),
				$modal = $preview_modal.find('.hustle-modal'),
				$modal_body = $modal.find('.hustle-modal-body');

			// custom size
			if ( _.isTrue( design_data.customize_size ) ) {
				$modal.css({
					'width': design_data.custom_width + 'px',
					'max-width': 'none'
				});

				// adjust
				if ( style === 'simple' && _.isFalse( content_data.use_email_collection ) ) {
					var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
						modal_content = $modal.find('.hustle-modal-content');

					$modal_body.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)'
					});

					modal_content.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)',
						'overflow-y': 'auto'
					});
				}
				if ( style === 'minimal' && _.isFalse( content_data.use_email_collection ) ) {
					var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
						modal_section = $modal.find('section'),
						modal_message = $modal.find('.hustle-modal-message');

					if ( _.isTrue( content_data.has_title ) && ( content_data.title !== '' || content_data.sub_title !== '' ) ) {
						var calc_header = $modal.find('header').outerHeight();
					} else {
						var calc_header = 0;
					}

					if ( _.isTrue( content_data.show_cta ) && ( content_data.cta_label !== '' && content_data.cta_url !== '' ) ) {
						var calc_footer = $modal.find('footer').innerHeight();
					} else {
						var calc_footer = 0;
					}

					modal_section.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_header + 'px - ' + calc_footer + 'px)'
					});

					modal_message.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_header + 'px - ' + calc_footer + 'px)',
						'overflow-y': 'auto'
					});
				}
				if ( style === 'cabriolet' && _.isFalse( content_data.use_email_collection ) ) {
					var calc_header = $modal.find('header').height() + 20, // add "20" for header margin.
						modal_section = $modal.find('section'),
						modal_message = $modal.find('.hustle-modal-message');

					modal_section.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_header + 'px)'
					});

					modal_message.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_header + 'px)',
						'overflow-y': 'auto'
					});
				}
				if ( layout === 'one' && _.isTrue( content_data.use_email_collection ) ) {
					var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
						calc_footer = $modal.find('footer').height(),
						modal_image = $modal.find('.hustle-modal-image'),
						modal_section = $modal.find('section'),
						modal_article = $modal.find('article');

					modal_section.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_footer + 'px)'
					});

					if (
						modal_section.hasClass('hustle-modal-image_above') ||
						modal_section.hasClass('hustle-modal-image_below')
					) {
						var avg_height = design_data.custom_height + calc_close + calc_footer;

						if (modal_section.height() < 250 ) {
							modal_section.css({
								'overflow-y': 'auto'
							});
						} else {
							modal_article.css({
								'height': 'calc(' + modal_section.height() + 'px - ' + modal_image.height() + 'px)',
								'overflow-y': 'auto'
							});
						}
					} else {
						modal_article.css({
							'max-height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_footer + 'px)',
							'overflow-y': 'auto'
						});

						modal_image.css({
							'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_footer + 'px)'
						});
					}
				}
				if ( layout === 'two' && _.isTrue( content_data.use_email_collection ) ) {
					var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
						calc_footer = $modal.find('footer').height(),
						modal_body = $modal.find('.hustle-modal-body'),
						modal_section = $modal.find('section'),
						modal_article = $modal.find('article');

					modal_body.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)'
					});

					modal_section.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_footer + 'px)'
					});

					modal_article.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_footer + 'px)',
						'overflow-y': 'auto'
					});
				}
				if ( layout === 'three' && _.isTrue( content_data.use_email_collection ) ) {
					var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
						calc_image = $modal.find('.hustle-modal-image').height(),
						modal_article = $modal.find('article');

					$modal_body.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)'
					});

					modal_article.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_image + 'px)',
						'overflow-y': 'auto'
					});
				}
				if ( layout === 'four' && _.isTrue( content_data.use_email_collection ) ) {
					var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
						calc_image = $modal.find('.hustle-modal-image').height(),
						calc_wrap = design_data.custom_height - calc_close - calc_image,
						optin_wrap = $modal.find('.hustle-modal-optin_wrap'),
						modal_article = $modal.find('article');

					$modal_body.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)'
					});

					modal_article.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)',
						'overflow-y': 'auto'
					});

					optin_wrap.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_image + 'px)',
						'overflow-y': 'auto'
					});

					if ( $modal.find('.hustle-modal-optin_form').innerHeight() > calc_wrap ) {
						optin_wrap.css({
							'align-items': 'flex-start'
						});
					}
				}
			}
		},
		apply_custom_css: function() {
			// Get rid of old styles.
			var $styles_el = $('#hustle-module-custom-styles');
			$styles_el.remove();

			var customize_css = this.design_view.model.toJSON().customize_css;
			// If custom CSS is enabled, add styles.
			if (customize_css === 1 || customize_css === '1') {
				// custom css
				var custom_css = this.design_view.model.get('custom_css'),
					nonce = $("#hustle_custom_css").data("nonce");

				if ( _.isEmpty(custom_css) || typeof nonce === 'undefined' ) {
					return;
				}

				$.ajax({
					type: "POST",
					url: ajaxurl,
					dataType: "json",
					data: {
						action: 'hustle_embedded_prepare_custom_css',
						css: custom_css,
						_ajax_nonce: nonce
					},
					success: function(res){
						if( res && res.success ){
							var $styles_el = $('#hustle-module-custom-styles');
							if ( $styles_el.length ) {
								$styles_el.remove();
							}
							$('<style id="hustle-module-custom-styles">' + res.data + '</style>').appendTo('body');
						}
					},
					error: function() {

					}
				});
			}
		},
		close_preview: function(e) {
			e.stopPropagation();

			var $preview = this.$('#wph-preview-modal'),
				$modal = $preview.find('.hustle-modal'),
				animation_in = this.settings_view.model.get('animation_in'),
				animation_in_class = 'hustle-animate-' + animation_in,
				animation_out = this.settings_view.model.get('animation_out'),
				animation_out_class = 'hustle-animate-' + animation_out,
				time_out = 1000;

			$modal.removeClass(animation_in_class).addClass(animation_out_class);

			if ( $modal.hasClass('hustle-animated') ) {

				if ( animation_out === 'fadeOut' ) {
					time_out = 305;
				}
				if ( animation_out === 'newspaperOut' ) {
					time_out = 505;
				}
				if ( animation_out === 'bounceOut' ) {
					time_out = 755;
				}

				setTimeout(function(){
					$preview.removeClass('wpmudev-modal-active');
					$('body').removeClass('wpmudev-modal-is_active');
					$modal.removeClass(animation_out_class);
				}, time_out);
			}

			if ($modal.hasClass('hustle-modal-static')) {
				$modal.removeClass('hustle-modal-static');
				$preview.removeClass('wpmudev-modal-active');
				$('body').removeClass('wpmudev-modal-is_active');
			}
		},
		sanitize_data: function() {
			// cta_url
			var cta_url = this.content_view.model.get('cta_url');
			if (!/^(f|ht)tps?:\/\//i.test(cta_url)) {
				cta_url = "http://" + cta_url;
				this.content_view.model.set( 'cta_url', cta_url, {silent:true} );
			}

			// custom css
			this.design_view.update_custom_css();
		},
		save: function($btn) {
			if ( !Module.Validate.validate_module_name() ) return false;

			this.set_content_from_tinymce(true);
			this.sanitize_data();

			// preparing the data
			var me = this,
				module = this.model.toJSON(),
				content = this.content_view.model.toJSON(),
				design = this.design_view.model.toJSON(),
				settings = this.settings_view.model.toJSON();

			// ajax save here
			return $.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'hustle_save_embedded_module',
					_ajax_nonce: $btn.data('nonce'),
					id: ( !$btn.data('id') ) ? '-1' : $btn.data('id'),
					module: module,
					content: content,
					design: design,
					settings: settings,
					shortcode_id: me._get_shortcode_id()
				},
				complete: function(resp) {
					var response = resp.responseJSON;
				}
			});
		},
		save_changes: function(e) {
			e.preventDefault();
			var me = this,
				$btn = $(e.target);
				
			me.$('.wpmudev-button-save, .wpmudev-button-continue').addClass('wpmudev-button-onload').prop('disabled', true);
			var save = this.save($btn);

			if ( save ) {
				save.done( function(resp) {
					if (typeof resp === 'string') {
						resp = JSON.parse(resp);
					}
					if ( resp.success ) {
						var current_url = window.location.pathname + window.location.search;
						if ( current_url.indexOf('&id=') === -1 ) {
							current_url = current_url + '&id=' + resp.data;
							window.history.replaceState( {} , '', current_url );
							me.$('.wpmudev-menu-content-link a, .wpmudev-menu-design-link a, .wpmudev-menu-settings-link a').each(function(){
								$(this).attr( 'href', $(this).data('link') + '&id=' + resp.data );
							});
						}
						$btn.data( 'id', resp.data );
						$btn.siblings().data( 'id', resp.data );
						Module.hasChanges = false;
					}
				} ).always( function() {
					me.$('.wpmudev-button-save, .wpmudev-button-continue').removeClass('wpmudev-button-onload').prop('disabled', false);
				});
			} else {
				// If saving did not work, remove loading icon.
				me.$('.wpmudev-button-save, .wpmudev-button-continue').removeClass('wpmudev-button-onload').prop('disabled', false);
			}
		},
		save_continue: function(e) {
			e.preventDefault();
			var me = this;
			// Disable buttons during save.
			me.$('.wpmudev-button-save, .wpmudev-button-continue').addClass('wpmudev-button-onload').prop('disabled', true);
							
			var save = this.save($(e.target));
			
			if ( save ) {
				save.done( function(resp) {
					if (typeof resp === 'string') {
						resp = JSON.parse(resp);
					}
					if ( resp.success ) {
						var module_id = resp.data;
						// redirect
						var current = optin_vars.current.section || false,
							target_link = '';

						window.onbeforeunload = null;

						if ( !current || current === 'content' ) {
							target_link =  me.$('.wpmudev-menu-design-link a').data('link');
						} else if ( current === 'design' ) {
							target_link = me.$('.wpmudev-menu-settings-link a').data('link');
						}

						if ( target_link.indexOf('&id') === -1 ) {
							target_link += '&id=' + module_id;
						}
						return window.location.replace(target_link);
					}
				} );
			} else {
				// If saving did not work, remove loading icon.
				me.$('.wpmudev-button-save, .wpmudev-button-continue').removeClass('wpmudev-button-onload').prop('disabled', false);
			}

		},
		save_finish: function(e) {
			e.preventDefault();
			var me = this;
			// Disable buttons during save.
			me.$('.wpmudev-button-save, .wpmudev-button-continue').addClass('wpmudev-button-onload').prop('disabled', true);

	
			var save = this.save($(e.target));

			if ( save ) {
				save.done( function(resp) {
					if ( resp.success ) {
						var module_id = resp.data;
						window.onbeforeunload = null;
						return window.location.replace( '?page=' + optin_vars.current.listing_page + '&module=' + module_id );
					}
				} );
			} else {
				me.$('.wpmudev-button-save, .wpmudev-button-continue').removeClass('wpmudev-button-onload').prop('disabed', false);
			}

		},
		cancel: function(e) {
			e.preventDefault();
			window.onbeforeunload = null;
			window.location.replace( '?page=' + optin_vars.current.listing_page );
			return;
		},
		back: function(e) {
			e.preventDefault();
			var me = this;
			me.$('.wpmudev-button-back').addClass('wpmudev-button-onload');
			// redirect
			var current = optin_vars.current.section;
			window.onbeforeunload = null;
			if ( current === 'design' ) {
				window.location.replace( this.$('.wpmudev-menu-content-link a').attr('href') );
			} else if ( current === 'settings' ) {
				window.location.replace( this.$('.wpmudev-menu-design-link a').attr('href') );
			}
			return;
		},
		mobile_navigate: function(e) {
			e.preventDefault();
			var value = e.target.value;

			if (value === 'content') {
				window.location.replace( this.$('.wpmudev-menu-content-link a').attr('href') );
			} else if (value === 'design') {
				window.location.replace( this.$('.wpmudev-menu-design-link a').attr('href') );
			} else {
				window.location.replace( this.$('.wpmudev-menu-settings-link a').attr('href') );
			}
		},

		//on type or paste
		validate_modal_name : function(e) {
			Module.Validate.on_change_validate_module_name(e);
		},
		update_base_model: function(e) {
			var changed = e.changed;

			// for module_name
			if ( 'module_name' in changed ) {
				this.model.set( 'module_name', changed['module_name'], { silent:true } )
			}

		},
		content_view_changed: function(model) {
			var changed = model.changed,
				key = Object.keys(changed);

			// has_title
			if ( 'has_title' in changed ) {
				var $target_div = this.$('#wph-wizard-content-title-textboxes');
				if ( $target_div.length ) {
					if ( changed['has_title'] ) {
						$target_div.removeClass('wpmudev-hidden');
					} else if ( !$target_div.hasClass('wpmudev-hidden') ) {
						$target_div.addClass('wpmudev-hidden');
					}
				}
			}

			// use_feature_image
			if ( 'use_feature_image' in changed ) {
				var $target_div = this.$('#wph-wizard-content-image-options');
				if ( $target_div.length ) {
					if ( changed['use_feature_image'] ) {
						$target_div.removeClass('wpmudev-hidden');
					} else if ( !$target_div.hasClass('wpmudev-hidden') ) {
						$target_div.addClass('wpmudev-hidden');
					}
				}
			}

			// show_cta
			if ( 'show_cta' in changed ) {
				var $target_div = this.$('#wph-wizard-content-cta-options');
				if ( $target_div.length ) {
					if ( changed['show_cta'] ) {
						$target_div.removeClass('wpmudev-hidden');
					} else if ( !$target_div.hasClass('wpmudev-hidden') ) {
						$target_div.addClass('wpmudev-hidden');
					}
				}
			}

			// show_gdpr
			if ( 'show_gdpr' in changed ) {
				var $target_div = this.$('#wph-wizard-content-gdpr-message');
				if ( $target_div.length ) {
					if ( changed['show_gdpr'] ) {
						$target_div.removeClass('wpmudev-hidden');
					} else if ( !$target_div.hasClass('wpmudev-hidden') ) {
						$target_div.addClass('wpmudev-hidden');
					}
				}
			}

			// use_email_collection
			if ( 'use_email_collection' in changed ) {
				this.use_email_collection_changed(changed['use_email_collection']);
			}

			// after_successful_submission
			if ( 'after_successful_submission' in changed ) {
				this.after_successful_submission_changed(changed['after_successful_submission']);
			}

			// auto_close_success_message
			if ( 'auto_close_success_message' in changed ) {
				var $target_div = this.$('#wph-wizard-content-form_success_options');
				if ( $target_div.length ) {
					if ( changed['auto_close_success_message'] ) {
						$target_div.addClass('wpmudev-show');
						$target_div.removeClass('wpmudev-hidden');
					} else {
						$target_div.addClass('wpmudev-hidden');
						$target_div.removeClass('wpmudev-show');
					}
				}
			}

			// email service was toggled
			if ( key[0].indexOf('_service_provider') !== -1 ) {
				var service = key[0].replace( '_service_provider', '' );
				this.handle_email_service( service, changed[ service + '_service_provider' ] );
			}

		},
		design_view_changed: function(model) {
			var changed = model.changed;

			// form_layout
			if ( 'form_layout' in changed ) {
				var $box_layouts = this.$('.wpmudev-box-layouts'),
					$target = $box_layouts.find('.wpmudev-box-layout_' + changed['form_layout']);
				if ( $box_layouts.length && $target.length ) {
					$target.siblings().removeClass('active');
					if ( !$target.hasClass('active') ) {
						$target.addClass('active');
					}
					this.design_view.hide_unwanted_options();
				}
			}

			// styles
			if ( 'style' in changed ) {
				// only if with email
				this.update_color_palette(changed['style']);
			}

			// customize_colors
			if ( 'customize_colors' in changed ) {
				var $target_without_optin = this.$('#wph-modal-styles-palette'),
					$target_with_optin = this.$('#wph-modal-palette');

				if ( $target_without_optin.length ) {
					if ( changed['customize_colors'] ) {
						$target_without_optin.removeClass('wpmudev-hidden');
						$target_without_optin.addClass('wpmudev-show');
					} else {
						$target_without_optin.addClass('wpmudev-hidden');
						$target_without_optin.removeClass('wpmudev-show');
					}
				}
				if ( $target_with_optin.length ) {
					if ( changed['customize_colors'] ) {
						$target_with_optin.removeClass('wpmudev-hidden');
						$target_with_optin.addClass('wpmudev-show');
					} else {
						$target_with_optin.addClass('wpmudev-hidden');
						$target_with_optin.removeClass('wpmudev-show');
					}
				}
			}

			// feature_image_fit
			if ( 'feature_image_fit' in changed ) {
				var $target = this.$('#wph-wizard-content-image_fit_horizontal_vertical_options');
				if ( $target.length ) {
					if ( changed['feature_image_fit'] === 'contain' || changed['feature_image_fit'] === 'cover' ) {
						if ( !$target.hasClass('wpmudev-show') ) {
							$target.addClass('wpmudev-show');
						}
						$target.removeClass('wpmudev-hidden');
					} else {
						$target.removeClass('wpmudev-show');
						$target.addClass('wpmudev-hidden');
					}
				}
			}

			// feature_image_horizontal
			if ( 'feature_image_horizontal' in changed ) {
				var $target = this.$('#wph-wizard-design-horizontal-position');
				if ( $target.length ) {
					if ( changed['feature_image_horizontal'] === 'custom' ) {
						if ( !$target.hasClass('wpmudev-show') ) {
							$target.addClass('wpmudev-show');
						}
						$target.removeClass('wpmudev-hidden');
					} else {
						$target.removeClass('wpmudev-show');
						$target.addClass('wpmudev-hidden');
					}
				}
			}

			// feature_image_vertical
			if ( 'feature_image_vertical' in changed ) {
				var $target = this.$('#wph-wizard-design-vertical-position');
				if ( $target.length ) {
					if ( changed['feature_image_vertical'] === 'custom' ) {
						if ( !$target.hasClass('wpmudev-show') ) {
							$target.addClass('wpmudev-show');
						}
						$target.removeClass('wpmudev-hidden');
					} else {
						$target.removeClass('wpmudev-show');
						$target.addClass('wpmudev-hidden');
					}
				}
			}

			// border
			if ( 'border' in changed ) {
				var $target = this.$('#wph-wizard-design-border-options');
				if ( $target.length ) {
					if ( changed['border'] ) {
						if ( !$target.hasClass('wpmudev-show') ) {
							$target.addClass('wpmudev-show');
						}
						$target.removeClass('wpmudev-hidden');
					} else {
						$target.removeClass('wpmudev-show');
						$target.addClass('wpmudev-hidden');
					}
				}
			}

			// form_fields_border
			if ( 'form_fields_border' in changed ) {
				var $target = this.$('#wph-wizard-design-form-fields-border-options');
				if ( $target.length ) {
					if ( changed['form_fields_border'] ) {
						if ( !$target.hasClass('wpmudev-show') ) {
							$target.addClass('wpmudev-show');
						}
						$target.removeClass('wpmudev-hidden');
					} else {
						$target.removeClass('wpmudev-show');
						$target.addClass('wpmudev-hidden');
					}
				}
			}

			// button_border
			if ( 'button_border' in changed ) {
				var $target = this.$('#wph-wizard-design-button-border-options');
				if ( $target.length ) {
					if ( changed['button_border'] ) {
						if ( !$target.hasClass('wpmudev-show') ) {
							$target.addClass('wpmudev-show');
						}
						$target.removeClass('wpmudev-hidden');
					} else {
						$target.removeClass('wpmudev-show');
						$target.addClass('wpmudev-hidden');
					}
				}
			}

			// drop_shadow
			if ( 'drop_shadow' in changed ) {
				var $target = this.$('#wph-wizard-design-shadow-options');
				if ( $target.length ) {
					if ( changed['drop_shadow'] ) {
						if ( !$target.hasClass('wpmudev-show') ) {
							$target.addClass('wpmudev-show');
						}
						$target.removeClass('wpmudev-hidden');
					} else {
						$target.removeClass('wpmudev-show');
						$target.addClass('wpmudev-hidden');
					}
				}
			}

			// customize_size
			if ( 'customize_size' in changed ) {
				var $target = this.$('#wph-wizard-design-size-options');
				if ( $target.length ) {
					if ( changed['customize_size'] ) {
						if ( !$target.hasClass('wpmudev-show') ) {
							$target.addClass('wpmudev-show');
						}
						$target.removeClass('wpmudev-hidden');
					} else {
						$target.removeClass('wpmudev-show');
						$target.addClass('wpmudev-hidden');
					}
				}
			}

			// customize_css
			if ( 'customize_css' in changed ) {
				var $target = this.$('#wph-wizard-design-css_holder');
				if ( $target.length ) {
					if ( changed['customize_css'] ) {
						if ( !$target.hasClass('wpmudev-show') ) {
							$target.addClass('wpmudev-show');
						}
						$target.removeClass('wpmudev-hidden');
					} else {
						$target.removeClass('wpmudev-show');
						$target.addClass('wpmudev-hidden');
					}
				}
			}

		},
		settings_view_changed: function(model) {
			var changed = model.changed;

			// on_time
			if ( 'on_time' in changed ) {
				var $target = this.$('#wpmudev-display-trigger-time-options');
				if ( $target.length ) {
					if ( changed['on_time'] ) {
						$target.addClass('wpmudev-show');
						$target.removeClass('wpmudev-hidden');
					} else {
						$target.removeClass('wpmudev-show');
						$target.addClass('wpmudev-hidden');
					}
				}
				this.settings_view.model.set( 'triggers.on_time', changed['on_time'], {silent:true} );
			}

			// on_time_delay
			if ( 'on_time_delay' in changed ) {
				this.settings_view.model.set( 'triggers.on_time_delay', changed['on_time_delay'], {silent:true} );
			}

			// on_time_unit
			if ( 'on_time_unit' in changed ) {
				this.settings_view.model.set( 'triggers.on_time_unit', changed['on_time_unit'], {silent:true} );
			}

			// on_exit_intent
			if ( 'on_exit_intent' in changed ) {
				this.settings_view.model.set( 'triggers.on_exit_intent', changed['on_exit_intent'], {silent:true} );
			}

			// on_exit_intent_per_session
			if ( 'on_exit_intent_per_session' in changed ) {
				this.settings_view.model.set( 'triggers.on_exit_intent_per_session', changed['on_exit_intent_per_session'], {silent:true} );
			}

			// on_adblock
			if ( 'on_adblock' in changed ) {
				this.settings_view.model.set( 'triggers.on_adblock', changed['on_adblock'], {silent:true} );
			}

		},
		display_triggers_changed: function(model) {
			var changed = model.changed;

			// trigger
			if ( 'trigger' in changed ) {
				var $target = this.$('#wpmudev-display-trigger-' + changed['trigger'] );
				if ( $target.length ) {
					if ( !$target.hasClass('current') ) {
						$target.addClass('current');
					}
					$target.siblings().removeClass('current');
				}
			}

		},
		update_color_palette: function(style) {
			var me = this,
				use_email_collection = parseInt(this.content_view.model.get('use_email_collection'));

			if ( use_email_collection ) {
				var $target_option = this.$('option[value="'+ style +'"]'),
					selected_style = $target_option.text();

				if ( typeof optin_vars.palettes[selected_style] !== 'undefined' ) {
					var colors = optin_vars.palettes[selected_style];

					// disable customize color
					this.design_view.model.set( 'customize_colors', 0 );
					this.$('input[data-attribute="customize_colors"]').removeAttr('checked');;

					// update color palettes
					_.each( colors, function( color, key ){
						me.$('input[data-attribute="'+ key +'"]').val(color).trigger('change');
					} );
				}
			}
		},
		reset_color_palette: function(){
			var me = this,
				style = this.$('#wph-wizard-design-palette .select2-selection__rendered').attr('title').toLowerCase().replace(/\s/g, '_');

			var use_email_collection = parseInt(this.content_view.model.get('use_email_collection'), 10);

			if ( use_email_collection ) {
				var $target_option = this.$('option[value="'+ style +'"]'),
					selected_style = $target_option.text();

				if ( typeof optin_vars.palettes[selected_style] !== 'undefined' ) {
					var colors = optin_vars.palettes[selected_style];

					// update color palettes
					_.each( colors, function( color, key ){
						me.$('input[data-attribute="'+ key +'"]').val(color).trigger('change');
					} );
				}
			}

		},
		use_email_collection_changed: function(value) {
			var $target_email = this.$('#wph-wizard-content-email'),
				$target_email_options = this.$('#wph-wizard-content-email-options'),
				$target_form_elements = this.$('#wph-wizard-content-form_elements'),
				$target_form_submission = this.$('#wph-wizard-content-form_submission'),
				$target_message = this.$('#wph-wizard-content-form_message'),
				$target_message_options = this.$('#wph-wizard-content-form_success');

			if ( parseInt(value) ) {
				$target_email.removeClass('last');
				$target_email_options.removeClass('wpmudev-hidden_table');
				$target_email_options.addClass('wpmudev-show_table');
				$target_form_elements.show();
				$target_form_submission.show();
				this.after_successful_submission_changed(this.content_view.model.get('after_successful_submission'));

				// set default style for embedded with optin
				this.design_view.model.set( 'style', 'gray_slate' );

			} else {
				if ( !$target_email.hasClass('last') ) $target_email.addClass('last');
				$target_email_options.removeClass('wpmudev-show_table');
				$target_email_options.addClass('wpmudev-hidden_table');
				$target_form_elements.hide();
				$target_form_submission.hide();
				$target_message.hide();
				$target_message_options.hide();

				// set default style for embedded with no optin
				this.design_view.model.set( 'style', 'cabriolet' );
			}
		},
		after_successful_submission_changed: function(value) {
			var $target_redirect_url = this.$('#wph-wizard-content-form_submission_redirect_url'),
				$target_message = this.$('#wph-wizard-content-form_message'),
				$target_message_options = this.$('#wph-wizard-content-form_success');

			if ( value === 'redirect' ) {
				if ( $target_redirect_url.length ) {
					$target_redirect_url.removeClass('wpmudev-hidden');
				}
				if ( $target_message.length && $target_message_options.length ) {
					$target_message.hide();
					$target_message_options.hide();
				}
			} else {
				if ( $target_redirect_url.length && !$target_redirect_url.hasClass('wpmudev-hidden') ) {
					$target_redirect_url.addClass('wpmudev-hidden');
				}
				if ( $target_message.length && $target_message_options.length ) {
					$target_message.show();
					$target_message_options.show();
				}
			}
		},
		toggle_cta_options: function(e) {
			var $li = $(e.target).closest('li'),
				$input = $li.find('input');

			if ( !$li.hasClass('current') ) {
				$li.addClass('current');
			}
			$li.siblings().removeClass('current');
			this.content_view.model.set( 'cta_target', $input.val(), {silent:true} );
		},
		toggle_submit_options: function(e) {
			var $li = $(e.target).closest('li'),
				$input = $li.find('input');

			if ( !$li.hasClass('current') ) {
				$li.addClass('current');
			}
			$li.siblings().removeClass('current');
			this.content_view.model.set( 'after_successful_submission', $input.val() );
		},
		toggle_feature_image_position_options: function(e) {
			var $li = $(e.target).closest('li'),
				$input = $li.find('input');

			if ( !$li.hasClass('current') ) {
				$li.addClass('current');
			}
			$li.siblings().removeClass('current');
			this.design_view.model.set( 'feature_image_position', $input.val(), {silent:true} );
		},
		toggle_feature_image_fit_options: function(e) {
			var $li = $(e.target).closest('li'),
				$input = $li.find('input');

			if ( !$li.hasClass('current') ) {
				$li.addClass('current');
			}
			$li.siblings().removeClass('current');
			this.design_view.model.set( 'feature_image_fit', $input.val(), {silent:false} );
		},
		toggle_feature_image_horizontal_options: function(e) {
			var $li = $(e.target).closest('li'),
				$input = $li.find('input');

			if ( !$li.hasClass('current') ) {
				$li.addClass('current');
			}
			$li.siblings().removeClass('current');
			this.design_view.model.set( 'feature_image_horizontal', $input.val(), {silent:false} );
		},
		toggle_feature_image_vertical_options: function(e) {
			var $li = $(e.target).closest('li'),
				$input = $li.find('input');

			if ( !$li.hasClass('current') ) {
				$li.addClass('current');
			}
			$li.siblings().removeClass('current');
			this.design_view.model.set( 'feature_image_vertical', $input.val(), {silent:false} );
		},
		toggle_form_fields_icon_options: function(e) {
			var $li = $(e.target).closest('li'),
				$input = $li.find('input');

			if ( !$li.hasClass('current') ) {
				$li.addClass('current');
			}
			$li.siblings().removeClass('current');
			this.design_view.model.set( 'form_fields_icon', $input.val(), {silent:false} );
		},
		toggle_form_fields_proximity_options: function(e) {
			var $li = $(e.target).closest('li'),
				$input = $li.find('input');

			if ( !$li.hasClass('current') ) {
				$li.addClass('current');
			}
			$li.siblings().removeClass('current');
			this.design_view.model.set( 'form_fields_proximity', $input.val(), {silent:false} );
		},
		toggle_display_triggers: function(e) {
			var $li = $(e.target).closest('li'),
				$input = $li.find('input');

			if ( !$li.hasClass('current') ) {
				$li.addClass('current');
			}
			$li.siblings().removeClass('current');
			this.settings_view.model.set( 'triggers.trigger', $input.val(), {silent:true}  );
			this.display_triggers_changed( this.settings_view.model.get('triggers') );
		},
		handle_email_service: function(service, enable) {
			var email_services = this.content_view.model.get('email_services');

			if ( _.isEmpty( email_services ) ) {
				email_services = {};
			}

			if ( enable ) {
				this.content_view.model.set( 'active_email_service', service );

				if ( _.isEmpty( email_services ) ) {
					email_services[service] = {
						enabled: enable
					};
				} else {
					// set the rest to disable
					_.each( email_services, function(email_service, key){
						if ( key === service ) {
							email_services[key] = _.extend( email_services[key], {
								enabled: 1
							} );
						} else {
							email_services[key] = _.extend( email_services[key], {
								enabled: 0
							} );
							$( 'input[data-attribute="'+ key +'_service_provider"]' ).removeAttr('checked');
						}
					} );
				}

			} else {
				this.content_view.model.set( 'active_email_service', '' );
				email_services[service] = _.extend( email_services[service], {
					enabled: enable
				} );
			}

			this.content_view.model.set( 'email_services', email_services );
		},
		_get_shortcode_id: function(){
			return this.content_view.model.get('module_name').trim().toLowerCase().replace(/\s+/g, '-');
		},
		escape_key: function(e) {
			// If escape key, close.
			if (e.keyCode === 27) {
				this.close_preview(e);
			}
		},
		preview_success_message_delay: function(view) {
			var content_model = this.content_view.model,
				auto_close = content_model.get('auto_close_success_message'),
				time = this.content_view.model.get('auto_close_time'),
				unit = this.content_view.model.get('auto_close_unit'),
				$preview = view.$('#wph-preview-modal').addClass('wpmudev-modal-active')
			;
			// Is auto close success message enabled?
			if ( _.isTrue( auto_close ) ) {
				// The dealy before closing.
				var on_success_time = parseInt( time ),
					// The unit for the delay time.
					on_success_unit = unit
				;

				if ( 'minutes' === on_success_unit ) {
					on_success_time *= 60;
				}

				on_success_time *= 1000;
				_.delay(function(){
					var $modal = $preview.find('.hustle-modal'),
						modal_close = $modal.find('.hustle-modal-close .hustle-icon')
					;

					if ( modal_close.length > 0 ) {
						// Close the modal.
						modal_close.trigger("click");
					} else {
						$success_msg.removeAttr( 'style' );
					}
				}, on_success_time );
			}

		}

	});

});
