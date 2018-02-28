( function ($) {
	define([
		'admin/settings/text'
	], function( Text ) {

		 return Text.extend({
			className: 'wpmudev-option forminator-field-wrap-vars',

			get_field_html: function () {
				var attr = {
					'cols': '40',
					'rows': '5',
					'class': 'wpmudev-textarea',
					'id': this.get_field_id(),
					'name': this.get_name()
				};

				if ( this.options.placeholder ) {
					attr.placeholder = this.options.placeholder;
				}

				return '<div class="wpmudev-option--vars">' +
					'<textarea ' + this.get_field_attr_html( attr ) + '>' + this.get_saved_value() + '</textarea>' +
					'<div class="wpmudev-vars--actions">' +
						'<div class="wpmudev-action--insert">' +
							'<button class="wpmudev-button wpmudev-button-sm">Insert Var</button>' +
						'</div>' +
					'</div>' +
				'</div>';
			},

		});
	});
})( jQuery );
