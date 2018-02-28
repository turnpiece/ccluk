(function($){
	define([
		'admin/settings/text'
	], function( Text ) {
		
		return Text.extend({
			multiple: false,
			
			className: 'wpmudev-option--border',
			
			get_field_html: function(){
				return '<div class="wpmudev-border">';
			},

			get_label_html: function () {
				return '';
			}
		});
	});
})( jQuery );