( function($) {
	define([
		'admin/settings/toggle-container',
	], function( ToggleContainer ) {

		 return ToggleContainer.extend({
			multiple: false,

			className: 'wpmudev-option',

			get_field_html: function () {
				return '<div class="wpmudev-product"></div>';
			}

		});
	});

})( jQuery );
