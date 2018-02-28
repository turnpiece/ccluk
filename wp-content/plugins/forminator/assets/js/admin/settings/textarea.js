( function ($) {
	define([
		'admin/settings/setting'
	], function( Setting ) {

		return Setting.extend({
			className: 'wpmudev-option forminator-field-wrap-textarea',

			on_render: function () {
				var self = this;

				this.get_field().keyup( function () {
				}).trigger( 'keyup' ).change( function () {
					self.trigger( 'changed', self.get_value() );
				});
			},

			get_field_html: function (){
				var classes = 'wpmudev-textarea',
					attr = {
						'id': this.get_field_id(),
						'name': this.get_name(),
						'placeholder': this.options.placeholder ? this.options.placeholder : this.label,
					}
				;

				if ( this.options.size ){
					classes += ' wpmudev-textarea--' + this.options.size;
				}

				if ( this.options.resize ){
					classes += ' wpmudev-can_resize';
				}

				return '<textarea class="' + classes + '" ' + this.get_field_attr_html( attr ) + '>' + this.get_saved_value() + '</textarea>';
			}
		});
	});

})( jQuery );
