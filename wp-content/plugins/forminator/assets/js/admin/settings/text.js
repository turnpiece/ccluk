( function ($) {
	define([
		'admin/settings/setting'
	], function( Setting ) {

		 return Setting.extend({
			className: 'wpmudev-option',

			on_render: function () {
				var self = this;
				this.get_field().keyup( function (){

				}).trigger( 'keyup' ).change( function (){
					self.trigger( 'changed', self.get_value() );
				});
			},

			get_field_html: function () {
				var attr = {
					'type': 'text',
					'id': this.get_field_id(),
					'name': this.get_name(),
					'value': this.get_saved_value(),
					'placeholder': this.options.placeholder ? this.options.placeholder : '',
					'title': this.label
				};

				inputClass = '';

				if( this.options.inputLarge )
					inputClass = ' wpmudev-input--lg';

				if( this.options.inputSmall )
					inputClass = ' wpmudev-input--sm';

				var description = '';

				if( this.options.description )
					description = '<label class="wpmudev-label--description">' + this.options.description + '</label>';

				return '<input class="forminator-field-singular wpmudev-input' + inputClass +'" ' + this.get_field_attr_html( attr ) + ' />' + description;
			}
		});
	});
})( jQuery );
