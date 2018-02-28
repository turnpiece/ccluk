(function ($) {
	define( [
		'admin/models/wrapper-model'
	], function( WrapperModel ) {
		// Condition Collection
		return Backbone.Collection.extend({
			"model": WrapperModel,

			get_by_id: function ( id ) {
				var found = false;
				this.each( function ( model ) {
					if ( model.cid === id ) found = model;
				});
				return found;
			},

			get_by_index: function ( index ) {
				var model = this.at( index );

				return model;
			},

			model_index: function ( model ) {
				var index = this.indexOf( model );
				return index;
			},

			move_to: function(new_index, original_index) {
			    if (new_index === original_index) return this;
			    // Get the model being moved
			    var temp = this.at(original_index);
			    // Remove it
			    this.remove(temp, { silent: true });
			    // Add it back at the new position
			    this.add(temp, { at: new_index, silent: true });

			    return this;
			},
		});
	});
})(jQuery);
