(function ($) {
	define( [
		'admin/models/answer-model'
	], function( AnswerModel ) {
		// Condition Collection
		return Backbone.Collection.extend({
			"model": AnswerModel,

			get_by_name: function ( name ) {
				name = name.toLowerCase();
				var found = false;
				this.each( function ( model ) {
					if ( model.get( "name" ).toLowerCase() == name ) found = model;
				});
				return found;
			},

			model_index: function ( model ) {
				var index = this.indexOf( model );
				return index;
			},

			get_by_index: function ( index ) {
				var model = this.at( index );

				return model;
			},

			move_to: function( new_index, original_index ) {
			    if ( new_index === original_index ) return this;
			    // Get the model being moved
			    var temp = this.at( original_index );
			    // Remove it
			    this.remove( temp, { silent: true });
			    // Add it back at the new position
			    this.add( temp, { at: new_index, silent: true });

			    return this;
			},
		});
	});
})(jQuery);
