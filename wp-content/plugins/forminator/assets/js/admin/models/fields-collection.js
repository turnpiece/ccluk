(function ($) {
	define( [
		'admin/models/field-model'
	], function( FieldModel ) {
		// Condition Collection
		return Backbone.Collection.extend({
			"model": FieldModel,

			get_by_id: function ( id ) {
				var found = false;
				this.each( function ( model ) {
					if ( model.cid === id ) found = model;
				});
				return found;
			},

			model_index: function (model) {
				var index = this.indexOf( model );
				return index;
			},

			update_cols: function ( cols ) {
				this.each( function( each, i ) {
					each.set( 'cols', cols, { silent: true });
				});
			},

			/**
			 * Get fields that its conditions use {field}
			 * @param field
			 * @returns {Array}
			 */
			get_fields_related_to: function(field) {
				var fields_related = [];

				this.each( function ( model ) {
					var related_element_ids = model.get_conditions_element_ids();
					var index = _.indexOf(related_element_ids,field.get('element_id'));
					if(index > -1) {
						fields_related.push(model);
					}

					//multiname fields
					if(field.get('type') === 'name' && field.get('multiple_name') === "true") {
						var field_element_ids = [
							field.get('element_id') + '-prefix',
							field.get('element_id') + '-first-name',
							field.get('element_id') + '-middle-name',
							field.get('element_id') + '-last-name'
						];

						_.each(field_element_ids, function(field_element_id){
							index = _.indexOf(related_element_ids,field_element_id);
							if(index > -1) {
								fields_related.push(model);
							}
						});
					}
				});

				return fields_related;
			}
		});
	});
})(jQuery);
