(function ($) {
	define( [
		'admin/models/condition-model'
	], function( ConditionModel ) {
		// Condition Collection
		return Backbone.Collection.extend({
			"model": ConditionModel,
			get_by_name: function (name) {
				name = name.toLowerCase();
				var found = false;
				this.each( function (model) {
					if ( model.get("name").toLowerCase() == name ) found = model;
				});
				return found;
			},

			model_index: function (model) {
				var index = this.indexOf( model );
				return index;
			},

			get_by_index: function ( index ) {
				var model = this.at( index );

				return model;
			},
		});
	});
})(jQuery);
