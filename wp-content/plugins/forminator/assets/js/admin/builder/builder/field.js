( function ($) {
	define([
		'text!admin/templates/fields.html'
	], function( fieldTpl ) {
		var Field = Backbone.View.extend({
			events: {
				"click .wpmudev-form-field": "activate_field",
			},

			mainTpl: Forminator.Utils.template( $( fieldTpl ).find( '#builder-field-main-tpl' ).html() ),

			className: "wpmudev-form-col",

			initialize: function ( options ) {
				this.field = this.model;
				this.layout = options.layout;

				this.field.off('forminator:field:settings:updated');
				this.field.on('forminator:field:settings:updated', this.render, this);

				return this.render();
			},

			render: function () {
				var tplData = {
					field: this.field.toJSON(),
					condition: this.get_condition_markup()
				};

				// Add ID attribute for each field
				this.$el.attr( 'id', 'wpmudev-field-' + this.field.cid );
				this.$el.html('');
				this.$el.append( this.mainTpl( tplData ) );

				this.$el.addClass( 'wpmudev-form-col-' + this.field.get( 'cols' ) );

				this.render_content();
			},

			get_condition_markup: function () {
				if( ! _.isUndefined( this.field.get( 'conditions' ) ) && this.field.get( 'conditions' ).length > 0 ) {
					var action_label = this.field.get( 'condition_action' ) === "show" ? Forminator.l10n.sidebar.shown : Forminator.l10n.sidebar.hidden;

					var fieldsArray = Forminator.Utils.get_fields( this.layout.get( 'wrappers' ) ),
						conditions = this.field.get( 'conditions' )
					;


					if( conditions.length === 0 ) return false;

					// Get the first condition
					var valueLabel,
						moreConditions = '',
						condition = conditions.get_by_index( 0 ),
						label = _.where( fieldsArray, { element_id: condition.get( 'element_id' ) } )[0]
					;

					// Make sure we have all values
					if( typeof label === "undefined" ) {
						//conditions exist but field with element_id not, it could be removed
						//remove condition
						conditions.remove( condition, { silent: true });
						return;
					}

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
							conditions.remove( condition, { silent: true });
							//re trigger
							Forminator.Events.trigger( "sidebar:settings:updated", this.field );
							return;
						}
						valueLabel = labelValue.label;
					} else {
						valueLabel = condition.get( 'value' );
					}

					// If we have conditions, show them
					if( conditions.length > 1 ) {
						var more_template = Forminator.Utils.template( " + {{ total }} {{ more_label }}" );
						var total = conditions.length - 1,
							more_label = total === 1 ? Forminator.l10n.conditions.more_condition : Forminator.l10n.conditions.more_conditions
						;
						moreConditions = more_template({
							total: total,
							more_label: more_label
						});
					}

					if(_.isEmpty(valueLabel)) {
						valueLabel = 'null';
					}

					var template = Forminator.Utils.template( "{{ action }} {{ Forminator.l10n.sidebar.if }} <strong>{{ label }}</strong> {{ rule }} <strong>{{ valueLabel }}</strong> {{ moreConditions }}" );

					return template({
						action: action_label,
						label: label.label,
						rule: Forminator.l10n.conditions[ condition.get( 'rule' ) ],
						valueLabel: valueLabel,
						moreConditions: moreConditions
					});
				}
			},

			render_content: function () {
				var field_settings = _.where( Forminator.Data.fields, { slug: this.field.get( 'type' ) })[0],
					field_markup = ! _.isUndefined( field_settings.markup ) ? field_settings.markup : ''
				;

				this.contentTpl = Forminator.Utils.template( field_markup );

				var fieldAttributes = this.field.toJSON();

				this.$el.find('.wpmudev-form-field--section').append(
					this.contentTpl({
						field: fieldAttributes
					})
				);

				Forminator.Utils.append_select2($(this.$el), this.model);

				return this;
			},

			activate_field: function ( e ) {
				// Open field settings
				if( $( e.target ).closest( '.select2' ).length > 0 ) return;
				
				// Avoid 'double click' due to checkbox and radio behaviour  
				if( $( e.target ).attr('type') === 'checkbox' || $( e.target ).attr('type') === 'radio' ) return;

				var field = this.$el.find( '.wpmudev-form-field' );

				// Deactive all fields
				$( '.wpmudev-form-field' ).not( field ).each( function() {
					$( this ).removeClass( "wpmudev-is_active" );
				});

				field.toggleClass( "wpmudev-is_active" );

				// Toggle field settings
				if( field.hasClass( "wpmudev-is_active" ) ) {
					Forminator.Events.trigger( "forminator:sidebar:open:settings", this.field );
				} else {
					Forminator.Events.trigger( "forminator:sidebar:close:settings" );
				}

			},

		});

		return Field;
	});
})( jQuery );
