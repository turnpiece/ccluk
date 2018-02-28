( function ($) {
	define([
		'admin/settings/text'
	], function( Text ) {
		return Text.extend({

			multiple: false,

			className: 'wpmudev-option',

			get_field_html: function () {
				return '<div class="wpmudev-option--half"></div>';
			}

		});
	});

})( jQuery );
