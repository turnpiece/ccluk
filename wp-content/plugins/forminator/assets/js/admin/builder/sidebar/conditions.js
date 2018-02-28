( function ($) {
	define([
		'text!tpl/builder-sidebar.html',
	], function( sidebarTpl ) {
		return Backbone.View.extend({
			conditionsTpl: Forminator.Utils.template( $( sidebarTpl ).find( '#builder-sidebar-conditions-popup-tpl' ).html()),
			ruleTpl: Forminator.Utils.template( $( sidebarTpl ).find( '#builder-sidebar-conditions-rule-tpl' ).html()),
			wrappers: false,
			conditions: false,
			fields: [],

			className: 'wpmudev-section--conditions',

			events: {
				"click .wpmudev-action-done": "close",
				"click .wpmudev-list--add .wpmudev-button": "new",
				"click .wpmudev-delete-action": "delete",
				"change .wpmudev-conditions--field": "update_field",
				"change .wpmudev-conditions--action": "update_rule",
				"change .wpmudev-conditions--values": "update_value",
				"change .wpmudev-conditions--input": "update_value",
				"change .wpmudev-condition--actions": "update_actions",
				"change .wpmudev-condition--rules": "update_rules"
			},

			initialize: function ( options ) {
				this.wrappers = options.wrappers;
				this.fields = Forminator.Utils.get_fields( this.wrappers );
				this.conditions = this.model.get( 'conditions' );

				this.render();
			},

			render: function () {
				this.$el.html( this.conditionsTpl({
					data: this.model.toJSON()
				}));

				// Render existing conditions
				this.render_conditions();

				// Initialize Select 2
				this.init_select2();
			},

			render_conditions: function () {
				var self = this;

				if( _.isUndefined( this.conditions ) ) return;

				this.conditions.each( function ( condition, key ) {
					var index = self.conditions.model_index( condition ),
						fieldData = _.where( self.fields, { element_id: condition.get( 'element_id' ) } )[0] || {},
						condition_el = self.ruleTpl({
							index: index,
							fields: _.filter( self.fields, function( field ) { return field.element_id !== self.model.get( 'element_id' ) } ),
							field: fieldData,
							condition: condition.toJSON()
						}),
						$condition_el = $( condition_el ),
						$parent = self.$el.find( '.wpmudev-list--rules' )
					;

					$condition_el.appendTo( $parent );

					if( ! condition.get( 'element_id' ) ) {
						$condition_el.find( '.wpmudev-conditions--action' ).prop( "disabled", true );
						$condition_el.find( '.wpmudev-conditions--values-wrapper' ).hide();
						$condition_el.find( '.wpmudev-conditions--input-wrapper' ).hide();
					} else {
						if( fieldData.hasOptions ) {
							$condition_el.find( '.wpmudev-conditions--input-wrapper' ).hide();
							self.update_field_values( $condition_el, fieldData, condition );
						} else {
							$condition_el.find( '.wpmudev-conditions--values-wrapper' ).hide();
							self.update_field_input( $condition_el, fieldData, condition );
						}
					}
				});
			},

			update_field: function ( e ) {
				// Update condition field
				var $target = $( e.target ),
					value = $target.val(),
					$parent = $target.closest( '.wpmudev-rule--new' ),
					condition = this.get_model( $target )
				;

				if( value !== "" ) {
					// Update model
					condition.set( 'element_id', value );
				} else {
					// Update model with id & default values
					condition.set( 'element_id', value );
					condition.set( 'rule', 'is' );
					condition.set( 'value', false );
				}

				this.render();
			},

			update_field_values: function ( $row, fieldData, condition ) {
				var $options = $row.find( '.wpmudev-conditions--values' ),
					field = fieldData,
					condition_value = condition.get( 'value' )
				;

				// Empty the list
				$options.empty();

				if( field && field.values.length ) {
					// Append the options to select
					$options.append( new Option( Forminator.l10n.conditions.select_option, "" ) );

					_.each( field.values, function ( value, key ) {
						//if option doesnt have value, use its label as value
						var option_value = value.value;
						if(!option_value) {
							option_value = value.label;
						}
						$options.append( new Option( value.label, option_value, false, condition_value === option_value ? true : false ) );
					});
				}
			},

			update_field_input: function ( $row, fieldData, condition ) {
				var $input = $row.find( '.wpmudev-conditions--input' ),
					field = fieldData,
					condition_value = condition.get( 'value' )
				;

				$input.val( condition_value );
			},

			update_rule: function ( e ) {
				var $target = $( e.target ),
					value = $target.val(),
					condition = this.get_model( $target )
				;

				condition.set( 'rule', value );
			},

			update_value: function ( e ) {
				var $target = $( e.target ),
					value = $target.val(),
					condition = this.get_model( $target )
				;

				condition.set( 'value', value );
			},

			update_actions: function ( e ) {
				var $target = $( e.target ),
					value = $target.val()
				;

				this.model.set( 'condition_action', value );
			},

			update_rules: function ( e ) {
				var $target = $( e.target ),
					value = $target.val()
				;

				this.model.set( 'condition_rule', value );
			},

			new: function ( e ) {
				e.preventDefault();

				// Init new condition
				new_condition = new Forminator.Models.Condition({
					'element_id': '',
					'rule': 'is',
					'value': ''
				});

				// Add condition to the collection
				this.conditions.add( new_condition, { silent: true } );

				this.render();
			},

			delete: function ( e ) {
				e.preventDefault();

				var $button = $( e.target ),
					condition = this.get_model( $button )
				;

				// Delete condition
				this.conditions.remove( condition, { silent: true });

				this.render();
			},

			get_model: function ( $field ) {
				var index = $field.closest( '.wpmudev-rule--new' ).data( 'index' );

				return this.conditions.get_by_index( index );
			},

			close: function ( e ) {
				e.preventDefault();

				this.trigger( "conditions:modal:close" );

				Forminator.Events.trigger( "sidebar:settings:updated", this.model );
			},

			init_select2: function () {
				Forminator.Utils.init_select2();
			}
		});
	});
})( jQuery );
