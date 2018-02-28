( function ($) {
	define([
		'admin/settings/text'
	], function( Text ) {

		return Text.extend({
			className: 'wpmudev-option forminator-field-wrap-date-picker wpmudev-wrap-option--datepicker',

			get_field_html: function () {
				var attr = {
					'type': 'text',
					'class': 'forminator-field-singular wpmudev-option--datepicker',
					'name': this.get_name(),
					'value': this.get_saved_value(),
					'placeholder': this.options.placeholder ? this.options.placeholder : this.label,
					'title': this.label,
				};

				return '<input ' + this.get_field_attr_html(attr) + ' />';
			},

			on_render: function () {
				var self = this,
					dateFormat = this.options.dateFormat ? this.options.dateFormat : "d MM yy";
				this.get_field().datepicker({
					beforeShow: function( input, inst ) {
						$( "#ui-datepicker-div" ).addClass( "wpmudev-option--datepicker-cal" );
					},
					"dateFormat": dateFormat,
					onSelect: function() {
						self.trigger( 'changed', self.get_value() );
					}
				});
			},
		});
	});
})( jQuery );
