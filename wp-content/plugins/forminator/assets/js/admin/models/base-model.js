(function ($) {
	define( [], function() {
		// Base Model
		return Backbone.Model.extend({
			toJSON: function () {
				var attributes = _.clone(this.attributes);

				for( var attr in attributes ) {
					if( ( attributes[attr] instanceof Backbone.Model ) || ( attributes[attr] instanceof Backbone.Collection ) ) {
						attributes[attr] = attributes[attr].toJSON();
					}
				}

				return attributes;
			},
		});
	});
})(jQuery);
