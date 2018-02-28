( function ($) {
	define([
		'admin/settings/multi-setting',
		'text!tpl/fields.html',
	], function( MultiSetting, fieldsTpl ) {

		return MultiSetting.extend({
			multiple: false,

			events: {
				'click .wpmudev-add-date': 'add_option',
				'click .wpmudev-choice-kill': 'delete_option',
			},

			className: 'wpmudev-option forminator-field-wrap-multidate',

			get_field_html: function () {

				var values = this.get_values_html(),
					$mainTpl =  Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-date-multiple-tpl' ).html())
				;

				return $mainTpl({
					values: values,
				});

			},

			get_values_html: function () {
				var values = this.get_saved_value() || [];
				return _.map( values, this.get_value_html, this ).join('');
			},

			get_value_html: function ( value, index ) {
				var saved_value = this.get_saved_value(),
					$rowTpl =  Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-date-multiple-row-tpl' ).html())
				;

				return $rowTpl({
					value: value,
					index: index
				});
			},

			add_option: function ( e ) {
				var saved_value = this.get_saved_value() || [],
					$field = this.$el.find( '.wpmudev-option--datepicker' ),
					value = $field.val()
				;

				saved_value.push( { value: value } );
				this.save_value( saved_value );

				// Null value
				$field.val( '' );

				this.trigger( 'updated', saved_value );

				this.render();
			},

			delete_option: function ( e ) {
				var index = $( e.target ).closest( '.wpmudev-date-choice' ).data( 'index' ),
					saved_value = this.get_saved_value() || []
				;

				saved_value.splice( index, 1 );
				this.save_value( saved_value );

				this.trigger( 'updated', saved_value );

				this.render();
			},

			on_render: function () {
				var self = this,
					dateFormat = this.options.dateFormat ? this.options.dateFormat : "d MM yy"
				;

				setTimeout( function () {
					self.get_field().datepicker({
						beforeShow: function( input, inst ) {
							$( "#ui-datepicker-div" ).addClass( "wpmudev-option--datepicker-cal" );
						},
						"dateFormat": dateFormat
					});
				}, 50 );
			}
		});
	});
})( jQuery );
