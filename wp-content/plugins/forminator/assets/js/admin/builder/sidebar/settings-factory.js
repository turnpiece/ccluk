(function ($) {
	define([
		'admin/builder/sidebar/advanced-settings'
	], function( AdvancedSettings ) {
		var SettingsFactory = Backbone.View.extend({
			className: 'wpmudev-options--wrap',
			tab: false,
			field: false,
			field_settings: false,
			nested_settings: [
				"ToggleContainer",
				"RadioContainer",
				"Accordion",
				"MultiName",
				"Product",
				"ProductVar",
				"ColDouble"
			],

			initialize: function (options) {
				this.tab = options.tab;
				this.field = options.field;
				this.field_settings = options.field_settings;
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html('');

				// If Advanced settings tab, render advanced settings
				if( this.tab === "Advanced" ) {
					var advanced_setting = new AdvancedSettings({
						model: this.model,
						field: this.field,
						field_settings: this.field_settings,
					});

					// Append advanced setting $el to sidebar
					self.$el.append( advanced_setting.$el );
				}

				// Check for settings
				if( typeof this.field_settings.settings === "undefined" ) return;

				var tab_settings = this.tab === "Advanced" ?
					this.field_settings.advanced_settings : this.field_settings.settings;

				if( _.size( tab_settings ) === 0 ) return;

				_.each( tab_settings, function ( setting ) {

					// Check if setting exist
					if( ! _.isFunction( Forminator.Settings[ setting.type ] ) ) {
						console.log( 'Field setting doesnt exist' );
						return;
					}

					// Get setting level zero
					var zero_level_nested = self.add_setting( setting, self.field );

					// Check if we have a nested settings
					if( _.contains( self.nested_settings, setting.type ) ) {
						// We have nested settings, list them
						_.each( setting.fields, function ( first_level_setting, first_key ) {

							// Get setting
							var first_level_nested = self.add_setting( first_level_setting, self.field );

							// Check if we have a nested settings
							if( _.contains( self.nested_settings, first_level_setting.type ) ) {
								// We have nested settings, list them
								_.each( first_level_setting.fields, function ( second_level_setting, second_key ) {

									// Get setting
									var second_level_nested = self.add_setting( second_level_setting, self.field );

									// Append second level child to parent
									first_level_nested.$el.find( self.get_setting_class( first_level_setting.type, second_level_setting.tab ? second_level_setting.tab : '' ) ).append( second_level_nested.$el );
								});
							}

							// Append first level child to parent
							zero_level_nested.$el.find( self.get_setting_class( setting.type, first_level_setting.tab ? first_level_setting.tab : '' ) ).append( first_level_nested.$el );
						});
					}
					// Append setting $el to sidebar
					self.$el.append( zero_level_nested.$el );
				});
			},

			add_setting: function ( setting, model ) {
				var self = this;

				// Return setting object
				return new Forminator.Settings[ setting.type ]({
					model: model,
					layout: self.model,
					id: setting.id,
					name: setting.name,
					label: setting.label || '',
					show_title: setting.show_title || false,
					hide_label: setting.hide_label || false,
					dateFormat: setting.dateFormat || '',
					containerClass: setting.containerClass || '',
					has_content: setting.has_content || false,
					default_value: setting.default_value || '',
					previewOnly: setting.previewOnly || false,
					hasPreview: setting.hasPreview || false,
					hasOpposite: setting.hasOpposite || false,
					ajax: setting.ajax || false,
					min: setting.min || '',
					max: setting.max || '',
					ajax_action: setting.ajax_action || false,
					values: setting.values,
					on_change: function( value ) {
						self.render_fields();
					}
				});
			},

			get_setting_class: function ( setting, key ) {
				var className;

				// Get setting container class
				switch ( setting ){
					
					case "Product":
						className = '.wpmudev-product';
						break;
						
					case "ProductVar":
						className = '.wpmudev-product--var';
						break;
						
					case "ColDouble":
						className = '.wpmudev-option--half';
						break;
						
					case "MultiName":
						className = '.wpmudev-multiname--content';
						break;
						
					case "RadioContainer":
						className = '.wpmudev-radio-tab-' + key;
						break;

					case "Accordion":
						className = '.wpmudev-accordion--section';
						break;
						
					case "MultiName":
						className = '.wpmudev-field--content';
						break;
						
					default:
						className = '.wpmudev-option--switch_content';
				}

				return className;
			},

			render_fields: function () {
				Forminator.Events.trigger( "sidebar:settings:updated", this.field );
			},

		});

		return SettingsFactory;
	});
})( jQuery );
