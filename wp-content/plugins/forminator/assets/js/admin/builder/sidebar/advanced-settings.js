(function ($) {
	define([
		'admin/builder/sidebar/conditions'
	], function( ConditionsPopup ) {
		var AdvancedSettings = Backbone.View.extend({
			className: 'wpmudev-advanced-settings--options',

			events: {
				"click .wpmudev-manage-conditions": "conditions_popup"
			},

			settings: [],

			initialize: function ( options ) {
				this.field = options.field;
				this.l10n = options.l10n;
				this.field_settings = options.field_settings;
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html('');

				this.render_custom_class();
				this.render_conditions();
			},

			render_custom_class: function () {
				var self = this;

				var custom_class = new Forminator.Settings.ToggleContainer({
					model: self.field,
					id: 'advanced-custom-class',
					name: 'use-custom-class',
					hide_label: true,
					has_content: true,
					values: [{
						value: "true",
						label: Forminator.l10n.builder.use_custom_class,
						labelSmall: "true"
					}],
					on_change: function( value ) {
						self.render_fields();
					}
				});

				var custom_class_field = new Forminator.Settings.Text({
					model: self.field,
					id: 'advanced-custom-class-field',
					name: 'custom-class',
					label: Forminator.l10n.builder.custom_class,
					on_change: function( value ) {
						self.render_fields();
					}
				});

				custom_class.$el.find( '.wpmudev-option--switch_content' ).append( custom_class_field.$el );
				self.$el.append( custom_class.$el );

				var separator = new Forminator.Settings.Separator({
					model: self.field,
				});

				self.$el.append( separator.$el );
			},

			render_conditions: function () {
				var self = this;

				var conditions = new Forminator.Settings.ToggleContainer({
					model: self.field,
					id: 'advanced-conditions',
					name: 'use_conditions',
					hide_label: true,
					values: [
						{
							value: "true",
							label: Forminator.l10n.conditions.conditional_logic,
							labelSmall: "true"
						}
					],
					on_change: function( value ) {
						self.render_fields();
					}
				});

				if( ! _.isUndefined( this.field.get( 'conditions' ) ) && this.field.get( 'conditions' ).length > 0 ) {
					var action_label = this.field.get( 'condition_action' ) === "show" ? Forminator.l10n.sidebar.shown : Forminator.l10n.sidebar.hidden;

					conditions.$el.find( '.wpmudev-option--switch_content' ).addClass( 'wpmudev-is_gray' ).append(
						'<div class="wpmudev-conditions--rule"><label class="wpmudev-rule--base"> ' + Forminator.l10n.sidebar.field_will_be + ' <strong>' + action_label.toLowerCase() +'</strong> ' + Forminator.l10n.sidebar.if + ':</label>' +
						'<ul class="wpmudev-rule--match"></ul></div>' +
						'<div class="wpmudev-conditions--manage"><button class="wpmudev-button wpmudev-manage-conditions wpmudev-button-sm">' + Forminator.l10n.conditions.edit_conditions + '</button></div>' );

					var fieldsArray = Forminator.Utils.get_fields( this.model.get( 'wrappers' ) );

					var fieldConditions = this.field.get( 'conditions' );

					fieldConditions.each( function ( condition ) {
						var valueLabel,
							label = _.where( fieldsArray, { element_id: condition.get( 'element_id' ) } )[0]
						;

						// Make sure we have all values
						if( typeof label === "undefined" ) return;

						// If option, get option label
						if( label.hasOptions && label.values.length > 0 ) {
							var labelValue = _.where( label.values, { value: condition.get( 'value' ) } )[0];

							//find on label when not found on 'value'
							if(!labelValue) {
								labelValue = _.where( label.values, { label: condition.get( 'value' ) } )[0];
							}

							//if options not found, it could be deleted
							if(!labelValue) {
								//remove condition
								fieldConditions.remove( condition, { silent: true });
								//retrigger
								Forminator.Events.trigger( "sidebar:settings:updated", self.field );
								return false;
							}

							valueLabel = labelValue.label;
						} else {
							valueLabel = condition.get( 'value' );
						}

						if(_.isEmpty(valueLabel)) {
							valueLabel = 'null';
						}

						// Append condition to list
						conditions.$el.find( ".wpmudev-rule--match" ).append(
							'<li>' + label.label + ' <strong>' + Forminator.l10n.conditions[ condition.get( 'rule' ) ] + '</strong> ' + valueLabel + '</li>'
						);
					});

				} else {
					conditions.$el.find( '.wpmudev-option--switch_content' ).addClass( 'wpmudev-is_gray' ).append( '<div class="wpmudev-conditions--manage"><button class="wpmudev-button wpmudev-manage-conditions wpmudev-button-sm">' + Forminator.l10n.conditions.setup_conditions + '</button></div>' );
				}

				self.$el.append( conditions.$el );

				var separator = new Forminator.Settings.Separator({
					model: self.field,
				});

				self.$el.append( separator.$el );
			},

			render_fields: function () {
				Forminator.Events.trigger( "sidebar:settings:updated", this.field );
			},

			conditions_popup: function ( e ) {
				e.preventDefault();

				var self = this,
					conditions = new ConditionsPopup({
						model: this.field,
						wrappers: this.model.get( 'wrappers' )
					})
				;

				// Handle close
				this.listenTo( conditions, 'conditions:modal:close', function () {
					Forminator.Popup.close();
					self.render();
				});

				Forminator.Popup.open( function () {
					$( this ).append( conditions.el );
				}, {
					title: Forminator.l10n.conditions.setup_conditions
				});

			}

		});

		return AdvancedSettings;
	});
})( jQuery );
