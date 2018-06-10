(function($){
	'use strict';

	Optin.Provider = function( options ) {
		var me = this;
	
		_.extend( this, {
			/**
			 * @var (string)
			 * Provider ID.
			 * Must be the same as the value in provider selection options.
			 **/
			id: false,

			/**
			 * @var (object)
			 * An object name:value pattern where `name` is the meta name use and `value` is
			 * the corresponding selector i.e. #optin_api_key.
			 **/
			provider_args: {},

			/**
			 * @var (object)
			 * An error object pattern.
			 * `[META NAME]` {
			 * 		`name`: The field name
			 * 		`message`: The unique error message for the field
			 * 		`iconClass`: An additional icon classes added in error icon.
			 * }
			 **/
			errors: {},

			render_in_previewr: _.noop,
			init: _.noop,

			add_args: function() {
				if ( me.id === Optin.step.services.model.get('optin_provider') ) {
					_.each( me.provider_args, function(selector,key) {
						var value = $(selector);

						if ( value.length ) {
							value = value.val().trim();
							Optin.step.services.provider_args.set( key, value );
						}
					});
				}
			},

			/**
			 * Clear provider_args if previous provider was me.id but then user changes to another provider
			 * @on design:preview:render:start
			 */
			clear_provider_args: function() {
				if ( me.id === Optin.step.services.model.previousAttributes().optin_provider &&
					me.id !== Optin.step.services.model.get('optin_provider') ) {
					Optin.step.services.provider_args.clear( {silent: true} );
				}
			},

			/**
			 * Validates provider args fields.
			 **/
			validate: function() {
				var errors = [];

				_.each( me.provider_args, function( selector, key ) {
					var input = $( selector ),
						$icon = $('<span></span>');

					if ( input.length > 0 && '' === input.val().trim() ){
						errors.push( me.errors[ key ] );
						$icon.attr( 'title', me.errors[ key ].message );
						input.addClass( 'wpoi-error' );
						input.after( $icon );
						_.defer( function(){
							$icon.addClass( 'dashicons dashicons-warning ' + me.errors[ key ].iconClass );
						});
					}
				});

				return _( errors );
			},

			/**
			 * Helper method to check module field per provider.
			 *
			 * @param (object) field		The field object [name,label]
			 * @param (int) optin_id		The current Opt_In ID created/updated.
			 * @param (function) callback	The callback function to execute after successful checking.
			 **/
			check_module_field: function( field, optin_id, callback ) {
				$.getJSON( window.ajaxurl, {
					action: 'add_module_field',
					provider: this.id,
					_wpnonce: optin_vars.get_module_field_nonce,
					optin_id: optin_id,
					field: field
				}, callback ).fail(function() {
					var error = {error:true};
					if ( callback ) {
						callback(error);
					}
				});
			},

			/**
			 * Validate if custom field name/label is valid
			 *
			 * @on optin:update_module_field
			 **/
			validate_custom_field: function( field, module_view, optin_id ) {
				module_view.$('.dashicons-warning').remove();
				module_view.$( '[name]' ).prop( 'disabled', true );

				this.check_module_field( field, optin_id, function( res ) {
					module_view.$( '[name]' ).prop( 'disabled', false );

					if ( res.success ) {
						module_view.options = field;
						module_view._updateOptions();
					} else {
						var $icon = $('<span class="dashicons dashicons-warning">'),
							$title = 'custom' !== res.data.code ? optin_vars.messages.module_fields[ res.data.code ] : res.data.message;

						$icon.attr( 'title', $title );
						module_view.$('[name="label"]').before( $icon );
					}
				});
			},

			/**
			 * Validate if custom field can be created
			 *
			 * @on optin:add_module_field_infusionsoft
			 * @param (object)			The field object [name,label]
			 * @param (object)			Design view object instance.
			 * @param (int)				Current Opt_In ID created/updated.
			 **/
			add_module_field: function( field, design_view, optin_id ) {
				var addbutton = design_view.$( '.wph-add-new-field', '#wpoi-module-field-maker' );

				addbutton.addClass( 'wp-button-save--loading' );
				addbutton.prop('disabled', true );

				this.check_module_field( field, optin_id, function( res ) {
					addbutton.prop( 'disabled', false );

					if ( res.success ) {
						field = res.data.field;
						design_view._add_module_field( field );
					} else {
						var $icon = $('<span class="dashicons dashicons-warning">'),
						$title = 'custom' !== res.data.code ? optin_vars.messages.module_fields[ res.data.code ] : res.data.message;

						$icon.attr( 'title', $title );

						addbutton.before( $icon );
					}
				});
			}
		}, options );

		this.init();
		Optin.Events.on("services:validate:after", $.proxy( this, 'add_args' ) );
		Optin.Events.on("design:preview:render:start", $.proxy( this, 'clear_provider_args' ) );
		Optin.Events.on("optin:add_module_field_" + this.id, $.proxy( this, 'add_module_field' ) );
		Optin.Events.on("optin:update_module_field_" + this.id, $.proxy( this, 'validate_custom_field' ) );

		return this;
	};
}(jQuery,document));
