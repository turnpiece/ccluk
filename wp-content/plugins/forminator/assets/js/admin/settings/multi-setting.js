(function ($) {
	define([
		'admin/settings/setting'
	], function( Setting ) {

		return Setting.extend( _.extend( {}, {
			get_field_html: function () {
				var attr = {
					'class': 'wpmudev-toggle',
				};
				return '<div ' + this.get_field_attr_html(attr) + '>' + this.get_values_html() + '</div>';
			},
			get_values_html: function () {
				return _.map( this.options.values, this.get_value_html, this ).join('');
			},
			set_value: function ( value ) {
				this.$el.find( '[value="'+value+'"]' ).trigger('click');
			}
		}));

	});
})( jQuery );
