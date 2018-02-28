(function ($) {
	define( [
		'admin/models/base-model',
		'admin/models/answers-collection'
	], function( BaseModel, AnswersCollection ) {
		// Condition model
		return BaseModel.extend({
			// Model defaults
			defaults: {
				answers: false,
				'results-behav': 'link_on',
				'results-style': 'bar',
				'enable-ajax': 'true'
			},
			initialize: function () {
				var args = arguments;

				if ( this.get( 'answers' ) === false ) this.set( 'answers', new AnswersCollection() );

				if ( args && args[0] && args[0][ "answers" ] ) {
					args[ "answers" ] = args[0][ "answers" ] instanceof AnswersCollection ? args[0][ "answers" ] : new AnswersCollection(args[0][ "answers" ])
					;
					this.set( "answers", args.answers );
				}
			}
		});
	});
})(jQuery);
