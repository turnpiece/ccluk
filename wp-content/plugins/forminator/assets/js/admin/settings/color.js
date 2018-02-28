( function ($) {
	define([
		'admin/settings/text'
	], function( Text ) {
		return Text.extend({
			className: 'wpmudev-option forminator-field-wrap-color',

			get_field_html: function () {
				var pickerClass,
					tooltipData,
					attr = {
						'type': 'text',
						//'class': 'forminator-field-singular wpmudev-input_color',
						'id': this.get_field_id(),
						'name': this.get_name(),
						'value': this.get_saved_value(),
						'data-default-color': this.options.default_color ? this.options.default_color : '',
						'data-alpha': true
					};

				if( this.options.tooltip ) {
					pickerClass = 'wpmudev-tip';
					tooltipData = 'data-tip="' + this.options.tooltip + '"';
				}

				return '<div class="wpmudev-picker ' + pickerClass + '" ' + tooltipData + '><input ' + this.get_field_attr_html(attr) + ' /></div>';
			},

			on_render: function () {
				var self = this;
				this.get_field().wpColorPicker({
					change: function( e, ui ) {
						self.trigger( 'changed', ui.color.toCSS() );
					}
				});
			}
		});
	});
})( jQuery );
