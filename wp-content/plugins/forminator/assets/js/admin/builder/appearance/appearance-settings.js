( function ($) {
	define([
		'admin/style-editor',
		'text!tpl/appearance.html',
	], function( styleEditor, appearanceTpl ) {
		var AppearanceSettings = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#appearance-section-appearance-tpl' ).html()),
			colorTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#appearance-color-grid-tpl' ).html()),

			className: 'wpmudev-box-body',

			initialize: function ( options ) {
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html( this.mainTpl() );

				this.render_form_name();
				this.render_form_style();
				this.render_fields_style();
				this.render_title_settings();
				this.render_subtitle_settings();
				this.render_label_settings();
				this.render_input_settings();
				this.render_button_settings();
				this.render_notification_settings();
				this.render_color_settings();
				this.render_custom_submit();
				this.render_custom_invalid_form();
				this.render_custom_css();
			},

			render_form_name: function () {
				var form_name = new Forminator.Settings.Text({
					model: this.model,
					id: 'appearance-form-name',
					name: 'formName'
				});

				this.$el.find( '.appearance-section-form-name' ).append( form_name.el );
			},

			render_form_style: function () {
				var form_style = new Forminator.Settings.Select({
					model: this.model,
					id: 'appearance-form-style',
					name: 'form-style',
					label: 'Select a style to use',
					default_value: "default",
					values: [
						{ value: "default", label: Forminator.l10n.appearance.default },
						{ value: "flat", label: Forminator.l10n.appearance.flat },
						{ value: "bold", label: Forminator.l10n.appearance.bold },
						{ value: "material", label: Forminator.l10n.appearance.material }
					]
				});

				this.$el.find( '.appearance-section-form-style' ).append( form_style.el );
			},

			render_fields_style: function () {
				var fields_style = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-fields-style',
					name: 'fields-style',
					containerSize: '400',
					label: Forminator.l10n.appearance.fields_style,
					hide_label: true,
					default_value: 'open',
					values: [
						{ value: "open", label: Forminator.l10n.appearance.open_fields },
						{ value: "enclosed", label: Forminator.l10n.appearance.enclosed_fields },
					]
				});

				this.$el.find( '.appearance-section-fields-style' ).append( fields_style.el );
			},

			render_title_settings: function () {
				var self = this;
				
				var fonts_toggle = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-cform-title-font-settings',
					name: 'cform-title-font-settings',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					default_value: "false",
					values: [{
						value: "true",
						label: 'Title typo'
					}],
				});

				// Font family
				var font_family = new Forminator.Settings.Select({
					model: this.model,
					id: 'appearance-cform-title-font-family',
					name: 'cform-title-font-family',
					dataAttr:{
						'allow-search' : 1
					},
					label: Forminator.l10n.appearance.font_family,
					values: [
						{ value: '', label: Forminator.l10n.appearance.select_font },
						{ value: 'custom', label: Forminator.l10n.appearance.custom_font }
					],
					show: function ( value ) {
						setTimeout( function() {
							if( value === "custom" ) {
								self.$el.find( '#appearance-cform-title-custom-family' ).show();
							} else {
								self.$el.find( '#appearance-cform-title-custom-family' ).hide();
							}
						}, 100);
					},
					rendered: function () {
						var self = this;

						$.ajax({
							url: Forminator.Data.ajaxUrl,
							type: "POST",
							data: {
								action: "forminator_load_google_fonts",
								active: self.get_saved_value()
							},
							success: function( result ) {
								self.get_field().append( result.data );

								// Init select2
								Forminator.Utils.init_select2();
							}
						});
					},
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( font_family.$el );

				// Custom Font
				var custom_font = new Forminator.Settings.Text({
					model: this.model,
					id: 'appearance-cform-title-custom-family',
					name: 'cform-title-custom-family',
					placeholder: Forminator.l10n.appearance.custom_font_placeholder,
					description: Forminator.l10n.appearance.custom_font_description,
					label: Forminator.l10n.appearance.custom_font_family,
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( custom_font.$el );

				// Font size & weight markup
				var size_weight_markup = '<div class="wpmudev-option--half"></div>';
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( size_weight_markup );

				// Font size field
				var font_size = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-title-font-size',
					name: 'cform-title-font-size',
					label: Forminator.l10n.appearance.font_size,
					default_value: '45',
					values: [
						{ value: "25", label: "Aa", },
						{ value: "35", label: "Aa" },
						{ value: "45", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_size.el );

				// Font weight field
				var font_weight = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-title-font-weight',
					name: 'cform-title-font-weight',
					label: Forminator.l10n.appearance.font_weight,
					default_value: 'normal',
					values: [
						{ value: "lighter", label: "Aa", },
						{ value: "normal", label: "Aa" },
						{ value: "bold", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_weight.el );

				// Text align field
				var text_align = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-title-text-align',
					name: 'cform-title-text-align',
					label: 'Text align',
					containerSize: '300',
					default_value: 'left',
					values: [
						{ value: "left", label: "Left", },
						{ value: "center", label: "Center" },
						{ value: "right", label: "Right" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( text_align.$el );


				// Append toggle to template
				this.$el.find( '.appearance-section-customize-fonts' ).append( fonts_toggle.el );
			},

			render_subtitle_settings: function () {
				var self = this;
				
				var fonts_toggle = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-cform-subtitle-font-settings',
					name: 'cform-subtitle-font-settings',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [{
						value: "true",
						label: 'Subtitle typo'
					}],
				});

				// Font family
				var font_family = new Forminator.Settings.Select({
					model: this.model,
					id: 'appearance-cform-subtitle-font-family',
					name: 'cform-subtitle-font-family',
					dataAttr:{
						'allow-search' : 1
					},
					label: Forminator.l10n.appearance.font_family,
					values: [
						{ value: '', label: Forminator.l10n.appearance.select_font },
						{ value: 'custom', label: Forminator.l10n.appearance.custom_font }
					],
					show: function ( value ) {
						setTimeout( function() {
							if( value === "custom" ) {
								self.$el.find( '#appearance-cform-subtitle-custom-family' ).show();
							} else {
								self.$el.find( '#appearance-cform-subtitle-custom-family' ).hide();
							}
						}, 100);
					},
					rendered: function () {
						var self = this;

						$.ajax({
							url: Forminator.Data.ajaxUrl,
							type: "POST",
							data: {
								action: "forminator_load_google_fonts",
								active: self.get_saved_value()
							},
							success: function( result ) {
								self.get_field().append( result.data );

								// Init select2
								Forminator.Utils.init_select2();
							}
						});
					},
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( font_family.$el );

				// Custom Font
				var custom_font = new Forminator.Settings.Text({
					model: this.model,
					id: 'appearance-cform-subtitle-custom-family',
					name: 'cform-subtitle-custom-family',
					placeholder: Forminator.l10n.appearance.custom_font_placeholder,
					description: Forminator.l10n.appearance.custom_font_description,
					label: Forminator.l10n.appearance.custom_font_family,
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( custom_font.$el );

				// Font size & weight markup
				var size_weight_markup = '<div class="wpmudev-option--half"></div>';
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( size_weight_markup );

				// Font size field
				var font_size = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-subtitle-font-size',
					name: 'cform-subtitle-font-size',
					label: Forminator.l10n.appearance.font_size,
					default_value: '18',
					values: [
						{ value: "14", label: "Aa", },
						{ value: "18", label: "Aa" },
						{ value: "22", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_size.el );

				// Font weight field
				var font_weight = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-subtitle-font-weight',
					name: 'cform-subtitle-font-weight',
					label: Forminator.l10n.appearance.font_weight,
					default_value: 'normal',
					values: [
						{ value: "lighter", label: "Aa", },
						{ value: "normal", label: "Aa" },
						{ value: "bold", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_weight.el );

				// Text align field
				var text_align = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-subtitle-text-align',
					name: 'cform-subtitle-text-align',
					label: 'Text align',
					containerSize: '300',
					default_value: 'left',
					values: [
						{ value: "left", label: "Left", },
						{ value: "center", label: "Center" },
						{ value: "right", label: "Right" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( text_align.$el );

				// Append toggle to template
				this.$el.find( '.appearance-section-customize-fonts' ).append( fonts_toggle.el );
			},

			render_label_settings: function () {
				var self = this;
				
				var fonts_toggle = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-cform-label-font-settings',
					name: 'cform-label-font-settings',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [{
						value: "true",
						label: 'Label typo'
					}],
				});

				// Font family
				var font_family = new Forminator.Settings.Select({
					model: this.model,
					id: 'appearance-cform-label-font-family',
					name: 'cform-label-font-family',
					dataAttr:{
						'allow-search' : 1
					},
					label: Forminator.l10n.appearance.font_family,
					values: [
						{ value: '', label: Forminator.l10n.appearance.select_font },
						{ value: 'custom', label: Forminator.l10n.appearance.custom_font }
					],
					show: function ( value ) {
						setTimeout( function() {
							if( value === "custom" ) {
								self.$el.find( '#appearance-cform-label-custom-family' ).show();
							} else {
								self.$el.find( '#appearance-cform-label-custom-family' ).hide();
							}
						}, 100);
					},
					rendered: function () {
						var self = this;

						$.ajax({
							url: Forminator.Data.ajaxUrl,
							type: "POST",
							data: {
								action: "forminator_load_google_fonts",
								active: self.get_saved_value()
							},
							success: function( result ) {
								self.get_field().append( result.data );

								// Init select2
								Forminator.Utils.init_select2();
							}
						});
					},
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( font_family.$el );

				// Custom Font
				var custom_font = new Forminator.Settings.Text({
					model: this.model,
					id: 'appearance-cform-label-custom-family',
					name: 'cform-label-custom-family',
					placeholder: Forminator.l10n.appearance.custom_font_placeholder,
					description: Forminator.l10n.appearance.custom_font_description,
					label: Forminator.l10n.appearance.custom_font_family,
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( custom_font.$el );

				// Font size & weight markup
				var size_weight_markup = '<div class="wpmudev-option--half"></div>';
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( size_weight_markup );

				// Font size field
				var font_size = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-label-font-size',
					name: 'cform-label-font-size',
					label: Forminator.l10n.appearance.font_size,
					default_value: '16',
					values: [
						{ value: "12", label: "Aa", },
						{ value: "16", label: "Aa" },
						{ value: "18", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_size.el );

				// Font weight field
				var font_weight = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-label-font-weight',
					name: 'cform-label-font-weight',
					label: Forminator.l10n.appearance.font_weight,
					default_value: 'normal',
					values: [
						{ value: "lighter", label: "Aa", },
						{ value: "normal", label: "Aa" },
						{ value: "bold", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_weight.el );


				// Append toggle to template
				this.$el.find( '.appearance-section-customize-fonts' ).append( fonts_toggle.el );
			},

			render_input_settings: function () {
				
				var self = this;
				
				var fonts_toggle = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-cform-input-font-settings',
					name: 'cform-input-font-settings',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [{
						value: "true",
						label: 'Input typo'
					}],
				});

				// Font family
				var font_family = new Forminator.Settings.Select({
					model: this.model,
					id: 'appearance-cform-input-font-family',
					name: 'cform-input-font-family',
					dataAttr:{
						'allow-search' : 1
					},
					label: Forminator.l10n.appearance.font_family,
					values: [
						{ value: '', label: Forminator.l10n.appearance.select_font },
						{ value: 'custom', label: Forminator.l10n.appearance.custom_font }
					],
					show: function ( value ) {
						setTimeout( function() {
							if( value === "custom" ) {
								self.$el.find( '#appearance-cform-input-custom-family' ).show();
							} else {
								self.$el.find( '#appearance-cform-input-custom-family' ).hide();
							}
						}, 100);
					},
					rendered: function () {
						var self = this;

						$.ajax({
							url: Forminator.Data.ajaxUrl,
							type: "POST",
							data: {
								action: "forminator_load_google_fonts",
								active: self.get_saved_value()
							},
							success: function( result ) {
								self.get_field().append( result.data );

								// Init select2
								Forminator.Utils.init_select2();
							}
						});
					},
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( font_family.$el );

				// Custom Font
				var custom_font = new Forminator.Settings.Text({
					model: this.model,
					id: 'appearance-cform-input-custom-family',
					name: 'cform-input-custom-family',
					placeholder: Forminator.l10n.appearance.custom_font_placeholder,
					description: Forminator.l10n.appearance.custom_font_description,
					label: Forminator.l10n.appearance.custom_font_family,
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( custom_font.$el );

				// Font size & weight markup
				var size_weight_markup = '<div class="wpmudev-option--half"></div>';
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( size_weight_markup );

				// Font size field
				var font_size = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-input-font-size',
					name: 'cform-input-font-size',
					label: Forminator.l10n.appearance.font_size,
					default_value: '16',
					values: [
						{ value: "12", label: "Aa", },
						{ value: "16", label: "Aa" },
						{ value: "18", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_size.el );

				// Font weight field
				var font_weight = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-input-font-weight',
					name: 'cform-input-font-weight',
					label: Forminator.l10n.appearance.font_weight,
					default_value: 'normal',
					values: [
						{ value: "lighter", label: "Aa", },
						{ value: "normal", label: "Aa" },
						{ value: "bold", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_weight.el );


				// Append toggle to template
				this.$el.find( '.appearance-section-customize-fonts' ).append( fonts_toggle.el );
			},

			render_button_settings: function () {
				var self = this;
				
				var fonts_toggle = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-cform-button-font-settings',
					name: 'cform-button-font-settings',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [{
						value: "true",
						label: 'Button typo'
					}],
				});

				// Font family
				var font_family = new Forminator.Settings.Select({
					model: this.model,
					id: 'appearance-cform-button-font-family',
					name: 'cform-button-font-family',
					dataAttr:{
						'allow-search' : 1
					},
					label: Forminator.l10n.appearance.font_family,
					values: [
						{ value: '', label: Forminator.l10n.appearance.select_font },
						{ value: 'custom', label: Forminator.l10n.appearance.custom_font }
					],
					show: function ( value ) {
						setTimeout( function() {
							if( value === "custom" ) {
								self.$el.find( '#appearance-cform-button-custom-family' ).show();
							} else {
								self.$el.find( '#appearance-cform-button-custom-family' ).hide();
							}
						}, 100);
					},
					rendered: function () {
						var self = this;

						$.ajax({
							url: Forminator.Data.ajaxUrl,
							type: "POST",
							data: {
								action: "forminator_load_google_fonts",
								active: self.get_saved_value()
							},
							success: function( result ) {
								self.get_field().append( result.data );

								// Init select2
								Forminator.Utils.init_select2();
							}
						});
					},
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( font_family.$el );

				// Custom Font
				var custom_font = new Forminator.Settings.Text({
					model: this.model,
					id: 'appearance-cform-button-custom-family',
					name: 'cform-button-custom-family',
					placeholder: Forminator.l10n.appearance.custom_font_placeholder,
					description: Forminator.l10n.appearance.custom_font_description,
					label: Forminator.l10n.appearance.custom_font_family,
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( custom_font.$el );

				// Font size & weight markup
				var size_weight_markup = '<div class="wpmudev-option--half"></div>';
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( size_weight_markup );

				// Font size field
				var font_size = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-button-font-size',
					name: 'cform-button-font-size',
					label: Forminator.l10n.appearance.font_size,
					default_value: '14',
					values: [
						{ value: "12", label: "Aa", },
						{ value: "14", label: "Aa" },
						{ value: "16", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_size.el );

				// Font weight field
				var font_weight = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-button-font-weight',
					name: 'cform-button-font-weight',
					label: Forminator.l10n.appearance.font_weight,
					default_value: '500',
					values: [
						{ value: "300", label: "Aa", },
						{ value: "500", label: "Aa" },
						{ value: "700", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_weight.el );


				// Append toggle to template
				this.$el.find( '.appearance-section-customize-fonts' ).append( fonts_toggle.el );
			},

			render_notification_settings: function () {
				var self = this;
				
				var fonts_toggle = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-cform-notice-font-settings',
					name: 'cform-notice-font-settings',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [{
						value: "true",
						label: 'Notification typo'
					}],
				});

				// Font family
				var font_family = new Forminator.Settings.Select({
					model: this.model,
					id: 'appearance-cform-notice-font-family',
					name: 'cform-notice-font-family',
					dataAttr:{
						'allow-search' : 1
					},
					label: Forminator.l10n.appearance.font_family,
					values: [
						{ value: '', label: Forminator.l10n.appearance.select_font },
						{ value: 'custom', label: Forminator.l10n.appearance.custom_font }
					],
					show: function ( value ) {
						setTimeout( function() {
							if( value === "custom" ) {
								self.$el.find( '#appearance-cform-notice-custom-family' ).show();
							} else {
								self.$el.find( '#appearance-cform-notice-custom-family' ).hide();
							}
						}, 100);
					},
					rendered: function () {
						var self = this;

						$.ajax({
							url: Forminator.Data.ajaxUrl,
							type: "POST",
							data: {
								action: "forminator_load_google_fonts",
								active: self.get_saved_value()
							},
							success: function( result ) {
								self.get_field().append( result.data );

								// Init select2
								Forminator.Utils.init_select2();
							}
						});
					},
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( font_family.$el );

				// Custom Font
				var custom_font = new Forminator.Settings.Text({
					model: this.model,
					id: 'appearance-cform-notice-custom-family',
					name: 'cform-notice-custom-family',
					placeholder: Forminator.l10n.appearance.custom_font_placeholder,
					description: Forminator.l10n.appearance.custom_font_description,
					label: Forminator.l10n.appearance.custom_font_family,
				});

				// Append to toggle
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( custom_font.$el );

				// Font size & weight markup
				var size_weight_markup = '<div class="wpmudev-option--half"></div>';
				fonts_toggle.$el.find( '.wpmudev-option--switch_content' ).append( size_weight_markup );

				// Font size field
				var font_size = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-notice-font-size',
					name: 'cform-notice-font-size',
					label: Forminator.l10n.appearance.font_size,
					default_value: '13',
					values: [
						{ value: "13", label: "Aa", },
						{ value: "15", label: "Aa" },
						{ value: "17", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_size.el );

				// Font weight field
				var font_weight = new Forminator.Settings.Radio({
					model: this.model,
					id: 'appearance-cform-notice-font-weight',
					name: 'cform-notice-font-weight',
					label: Forminator.l10n.appearance.font_weight,
					default_value: 'normal',
					values: [
						{ value: "lighter", label: "Aa", },
						{ value: "normal", label: "Aa" },
						{ value: "bold", label: "Aa" },
					]
				});
				fonts_toggle.$el.find( '.wpmudev-option--half' ).append( font_weight.el );


				// Append toggle to template
				this.$el.find( '.appearance-section-customize-fonts' ).append( fonts_toggle.el );
			},

			render_color_settings: function () {
				var color_toggle = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-cform-color-settings',
					name: 'cform-color-settings',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.customize_colors
						}
					],
				});

				color_toggle.$el.find( '.wpmudev-option--switch_content' ).html( this.colorTpl() );

				var form_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-form-background',
						name: 'cform-form-background',
						hide_label: true,
						default_value: '#EEEEEE',
						label: '',
					});

				color_toggle.$el.find( '.color-grid-form-background .wpmudev-picker' ).append( [ form_color.$el ] );

				var form_border = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-form-border',
						name: 'cform-form-border',
						hide_label: true,
						default_value: '#EEEEEE',
						label: '',
					});

				color_toggle.$el.find( '.color-grid-form-border .wpmudev-picker' ).append( [ form_border.$el ] );

				var title_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-title-color',
						name: 'cform-title-color',
						hide_label: true,
						default_value: '#333333',
						label: '',
					});

				color_toggle.$el.find( '.color-grid-title .wpmudev-picker' ).append( [ title_color.$el ] );

				var subtitle_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-subtitle-color',
						name: 'cform-subtitle-color',
						hide_label: true,
						default_value: '#333333',
						label: '',
					});

				color_toggle.$el.find( '.color-grid-subtitle .wpmudev-picker' ).append( [ subtitle_color.$el ] );

				var label_main_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-label-color',
						name: 'cform-label-color',
						hide_label: true,
						default_value: '#777771',
						label: '',
					});

				color_toggle.$el.find( '.color-grid-main-label .wpmudev-picker' ).append( [ label_main_color.$el ] );

				var label_asterisk_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-label-asterisk-color',
						name: 'cform-asterisk-color',
						hide_label: true,
						default_value: '#777771',
						label: '',
				});

				color_toggle.$el.find( '.color-grid-asterisk-label .wpmudev-picker' ).append( label_asterisk_color.$el );

				var label_helper_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-label-helper-color',
						name: 'label-helper-color',
						hide_label: true,
						default_value: '#777771',
						label: '',
					});

				color_toggle.$el.find( '.color-grid-helper-label .wpmudev-picker' ).append( [ label_helper_color.$el ] );

				var input_bg_static = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-input-bg',
						name: 'input-bg',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.static,
						label: '',
						default_value: '#FFFFFF'
					}),
					input_bg_hover = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-input-hover-bg',
						name: 'input-hover-bg',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.hover,
						label: '',
						default_value: '#FFFFFF'
					}),
					input_bg_active = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-input-active-bg',
						name: 'input-active-bg',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.active,
						label: '',
						default_value: '#FFFFFF'
					})
				;

				color_toggle.$el.find( '.color-grid-input-bg .wpmudev-pickers' ).append( [ input_bg_static.$el, input_bg_hover.$el, input_bg_active.$el ] );

				var border_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-border-color',
						name: 'input-border',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.static,
						label: '',
						default_value: '#BBBBBB'
					}),
					border_hover_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-border-hover-color',
						name: 'input-border-hover',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.hover,
						label: '',
						default_value: '#17A8E3'
					}),
					border_active_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-border-active-color',
						name: 'input-border-active',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.active,
						label: '',
						default_value: '#17A8E3'
					})
				;

				color_toggle.$el.find( '.color-grid-input-border .wpmudev-pickers' ).append( [ border_color.$el, border_hover_color.$el, border_active_color.$el ] );

				var input_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-input-color',
						name: 'input-color',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.static,
						label: '',
						default_value: '#000000'
					}),
					input_hover_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-input-color-hover',
						name: 'input-color-hover',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.hover,
						label: '',
						default_value: '#000000'
					}),
					input_active_color = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-input-color-active',
						name: 'input-color-active',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.active,
						label: '',
						default_value: '#000000'
					})
				;

				color_toggle.$el.find( '.color-grid-input-color .wpmudev-pickers' ).append( [ input_color.$el, input_hover_color.$el, input_active_color.$el ] );

				var submitbg_static = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-submit-background-static',
						name: 'submit-background-static',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.static,
						label: '',
						default_value: '#17A8E3'
					}),
					submitbg_hover = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-submit-background-hover',
						name: 'submit-background-hover',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.hover,
						label: '',
						default_value: '#008FCA'
					}),
					submitbg_active = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-submit-background-active',
						name: 'submit-background-active',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.active,
						label: '',
						default_value: '#008FCA'
					})
				;

				color_toggle.$el.find( '#appearance-submit-background .wpmudev-pickers' ).append( [ submitbg_static.$el, submitbg_hover.$el, submitbg_active.$el ] );

				var submitco_static = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-submit-color-static',
						name: 'submit-color-static',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.static,
						label: '',
						default_value: '#FFFFFF'
					}),
					submitco_hover = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-submit-color-hover',
						name: 'submit-color-hover',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.hover,
						label: '',
						default_value: '#FFFFFF'
					}),
					submitco_active = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-submit-color-active',
						name: 'submit-color-active',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.active,
						label: '',
						default_value: '#FFFFFF'
					})
				;

				color_toggle.$el.find( '#appearance-submit-color .wpmudev-pickers' ).append( [ submitco_static.$el, submitco_hover.$el, submitco_active.$el ] );

				var paginationbg_static = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-pagination-background-static',
						name: 'pagination-background-static',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.static,
						label: '',
						default_value: '#333333'
					}),
					paginationbg_hover = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-pagination-background-hover',
						name: 'pagination-background-hover',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.hover,
						label: '',
						default_value: '#000000'
					}),
					paginationbg_active = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-pagination-background-active',
						name: 'pagination-background-active',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.active,
						label: '',
						default_value: '#000000'
					})
				;

				color_toggle.$el.find( '#appearance-pagination-background .wpmudev-pickers' ).append( [ paginationbg_static.$el, paginationbg_hover.$el, paginationbg_active.$el ] );

				var paginationco_static = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-pagination-color-static',
						name: 'pagination-color-static',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.static,
						label: '',
						default_value: '#FFFFFF'
					}),
					paginationco_hover = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-pagination-color-hover',
						name: 'pagination-color-hover',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.hover,
						label: '',
						default_value: '#FFFFFF'
					}),
					paginationco_active = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-pagination-color-active',
						name: 'pagination-color-active',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.active,
						label: '',
						default_value: '#FFFFFF'
					})
				;

				color_toggle.$el.find( '#appearance-pagination-color .wpmudev-pickers' ).append( [ paginationco_static.$el, paginationco_hover.$el, paginationco_active.$el ] );

				var uploadbg_static = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-upload-background-static',
						name: 'upload-background-static',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.static,
						label: '',
						default_value: '#333333'
					}),
					uploadbg_hover = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-upload-background-hover',
						name: 'upload-background-hover',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.hover,
						label: '',
						default_value: '#000000'
					}),
					uploadbg_active = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-upload-background-active',
						name: 'upload-background-active',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.active,
						label: '',
						default_value: '#000000'
					})
				;

				color_toggle.$el.find( '#appearance-upload-background .wpmudev-pickers' ).append( [ uploadbg_static.$el, uploadbg_hover.$el, uploadbg_active.$el ] );

				var uploadco_static = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-upload-color-static',
						name: 'upload-color-static',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.static,
						label: '',
						default_value: '#FFFFFF'
					}),
					uploadco_hover = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-upload-color-hover',
						name: 'upload-color-hover',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.hover,
						label: '',
						default_value: '#FFFFFF'
					}),
					uploadco_active = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-upload-color-active',
						name: 'upload-color-active',
						hide_label: true,
						tooltip: Forminator.l10n.appearance.active,
						label: '',
						default_value: '#FFFFFF'
					})
				;

				color_toggle.$el.find( '#appearance-upload-color .wpmudev-pickers' ).append( [ uploadco_static.$el, uploadco_hover.$el, uploadco_active.$el ] );

				var error_elements = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-cform-error',
						name: 'cform-error',
						hide_label: true,
						default_value: '#AA1111'
					});

				color_toggle.$el.find( '#appearance-error-elements .wpmudev-picker' ).append( [ error_elements.$el ] );

				var filled_elements = new Forminator.Settings.Color({
						model: this.model,
						id: 'appearance-cform-filled',
						name: 'cform-filled',
						hide_label: true,
						default_value: '#777771',
					});

				color_toggle.$el.find( '#appearance-filled-elements .wpmudev-picker' ).append( [ filled_elements.$el ] );

				// Append toggle to template
				this.$el.find( '.appearance-section-customize-colors' ).append( color_toggle.el );
			},

			render_custom_submit: function () {
				var custom_submit = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-custom-submit',
					name: 'use-custom-submit',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.use_custom_submit
						}
					],
				});

				var custom_text = new Forminator.Settings.Text({
					model: this.model,
					id: 'appearance-custom-submit-text',
					name: 'custom-submit-text',
					placeholder: Forminator.l10n.appearance.custom_submit_text,
					label: Forminator.l10n.appearance.custom_text,
					hide_label: true
				});

				// Append to toggle
				custom_submit.$el.find( '.wpmudev-option--switch_content' ).append( custom_text.$el );

				// Append toggle to template
				this.$el.find( '.appearance-section-custom-submit' ).append( custom_submit.el );
			},

			render_custom_invalid_form: function () {
				var custom_invalid_form = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-custom-invalid-form',
					name: 'use-custom-invalid-form',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.use_custom_invalid_form
						}
					],
				});

				var custom_invalid_form_message = new Forminator.Settings.Text({
					model: this.model,
					id: 'appearance-custom-invalid-form-message',
					name: 'custom-invalid-form-message',
					placeholder: Forminator.l10n.appearance.custom_invalid_form_message,
					label: Forminator.l10n.appearance.custom_text,
					hide_label: true
				});

				// Append to toggle
				custom_invalid_form.$el.find( '.wpmudev-option--switch_content' ).append( custom_invalid_form_message.$el );

				// Append toggle to template
				this.$el.find( '.appearance-section-custom-submit' ).append( custom_invalid_form.el );
			},

			render_custom_css: function () {

				var custom_css = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-custom-css',
					name: 'use-custom-css',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.use_custom_css
						}
					],
				});

				var ace = new styleEditor({
					model: this.model,
					property: 'custom_css',
					element_id: 'forminator_custom_css',
					selectors: [
						{ selector: ".forminator-custom-form ", label: "Form" },
						{ selector: ".forminator-custom-form .forminator-label--main ", label: "Main Label" },
						{ selector: ".forminator-custom-form .forminator-label--helper ", label: "Helper Label" },
						{ selector: ".forminator-custom-form .forminator-input ", label: "Text Input" },
						{ selector: ".forminator-custom-form .forminator-textarea ", label: "Textarea" },
						{ selector: ".forminator-custom-form .forminator-select + .select2 ", label: "Select" },
					],
				});

				// Append to toggle
				custom_css.$el.find( '.wpmudev-option--switch_content' ).append( ace.$el );

				// Append toggle to template
				this.$el.find( '.appearance-section-custom-style' ).append( custom_css.el );
			}

		});

		return AppearanceSettings;
	});
})( jQuery );
