(function ($) {
	define( [
		'admin/models/base-model',
		'admin/models/wrappers-collection'
	], function( BaseModel, WrappersCollection ) {
		// Condition model
		return BaseModel.extend({
			// Model defaults
			defaults: {
				wrappers: false,
				'validation-inline': "true",
				'enable-ajax': "true",
				'form-style': "flat",
				'fields-style': "open",
				"form-expire": "no_expire"
			},
			initialize: function () {
				var args = arguments;

				if ( this.get( 'wrappers' ) === false ) this.set( 'wrappers', new WrappersCollection() );

				if ( args && args[0] && args[0]["wrappers"] ) {
					args[ "wrappers" ] = args[0][ "wrappers" ] instanceof WrappersCollection ? args[0][ "wrappers" ] : new WrappersCollection(args[0][ "wrappers" ])
					;
					this.set("wrappers", args.wrappers);
				}

				// Remove fields object
				delete this.attributes.fields;
			}
		});
	});
})(jQuery);
