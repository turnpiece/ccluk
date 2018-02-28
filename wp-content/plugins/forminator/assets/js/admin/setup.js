define( 'jquery', [], function () {
	return jQuery;
});

define( 'forminator_global_data', function() {
   var data = forminator_data;
	return data;
});

define( 'forminator_language', function() {
   var l10n = forminator_l10n;
	return l10n;
});

var Forminator = window.Forminator || {};
Forminator.Events = {};
Forminator.Data = {};
Forminator.l10n = {};

require.config({
	baseUrl: ".",
	paths: {
		"js": ".",
		"admin": "admin",
	},
	shim: {
		'backbone': {
			//These script dependencies should be loaded before loading
			//backbone.js
			deps: [ 'underscore', 'jquery', 'forminator_global_data', 'forminator_language' ],
			//Once loaded, use the global 'Backbone' as the
			//module value.
			exports: 'Backbone'
		},
		'underscore': {
			exports: '_'
		}
	},
	"waitSeconds": 60,
});

require([  'admin/utils' ], function ( Utils ) {
	// Fix Underscore templating to Mustache style
	_.templateSettings = {
		evaluate : /\{\[([\s\S]+?)\]\}/g,
		interpolate : /\{\{([\s\S]+?)\}\}/g
	};

	_.extend( Forminator.Data, forminator_data );
	_.extend( Forminator.l10n, forminator_l10n );
	_.extend( Forminator, Utils );

	require([ 'admin/application' ], function ( Application ) {
		jQuery( document ).ready( function() {
			_.extend(Forminator, Application);
			_.extend(Forminator.Events, Backbone.Events);

			Forminator.Events.trigger("application:booted");
			Backbone.history.start();
		});
	});
});
