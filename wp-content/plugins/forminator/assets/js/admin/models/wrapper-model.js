(function ($) {
	define( [
		'admin/models/base-model',
		'admin/models/fields-collection',
		'admin/models/conditions-collection'
	], function( BaseModel, FieldCollection, ConditionsCollection ) {
		// Condition model
		return BaseModel.extend({
			// Model defaults
			defaults: {
				fields: false
			},

			initialize: function () {
				var args = arguments;

				if ( this.get( 'fields' ) === false ) this.set( 'fields', new FieldCollection() );

				if ( args && args[0] && args[0]["fields"] ) {
					args["fields"] = args[0]["fields"] instanceof FieldCollection ? args[0]["fields"] : new FieldCollection( args[0]["fields"] )
					;

					this.set( "fields", args.fields );
				}
			},

			get_id: function () {
				return this.cid;
			},

			fields_count: function () {
				return this.get( 'fields' ).length;
			}
		});
	});
})(jQuery);
