( function ($) {
	define([
		'admin/settings/text'
	], function( Input ) {

		 return Input.extend({
			className: 'wpmudev-option forminator-field-wrap-password',

			get_field_html: function () {
				var attr = {
					'type': 'password',
					'class': 'forminator-field-singular wpmudev-input_text',
					'id': this.get_field_id(),
					'name': this.get_name(),
					'value': this.get_saved_value(),
					'placeholder': this.options.placeholder ? this.options.placeholder : this.label,
					'title': this.label
				};

				var description = '';

				if( this.options.description )
					description = '<span class="field-description">' + this.options.description + '</span>';

				return '<input ' + this.get_field_attr_html( attr ) + ' />' + description;
			}
		});
	});
})( jQuery );
