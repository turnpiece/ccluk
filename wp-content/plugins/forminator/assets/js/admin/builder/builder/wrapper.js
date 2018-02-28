( function ($) {
	define([
		'text!admin/templates/fields.html'
	], function() {
		var Wrapper = Backbone.View.extend({
			className: "wpmudev-form-row",

			initialize: function ( options ) {
				return this.render();
			},

			render: function () {
			}
		});

		return Wrapper;
	});
})( jQuery );
