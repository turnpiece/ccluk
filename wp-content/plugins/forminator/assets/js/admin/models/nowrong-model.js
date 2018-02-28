(function ($) {
	define( [
		'admin/models/base-model',
		'admin/models/questions-collection',
		'admin/models/results-collection'
	], function( BaseModel, QuestionsCollection, ResultsCollection ) {
		// Condition model
		return BaseModel.extend({
			// Model defaults
			defaults: {
				questions: false,
				results: false
			},
			initialize: function () {
				var args = arguments;

				if ( this.get( 'questions' ) === false ) this.set( 'questions', new QuestionsCollection() );
				if ( this.get( 'results' ) === false ) this.set( 'results', new ResultsCollection() );

				if ( args && args[0] && args[0][ "questions" ] ) {
					args[ "questions" ] = args[0][ "questions" ] instanceof QuestionsCollection ? args[0][ "questions" ] : new QuestionsCollection(args[0][ "questions" ])
					;
					this.set( "questions", args.questions );
				}

				if ( args && args[0] && args[0][ "results" ] ) {
					args[ "results" ] = args[0][ "results" ] instanceof ResultsCollection ? args[0][ "results" ] : new ResultsCollection(args[0][ "results" ])
					;
					this.set( "results", args.results );
				}
			}
		});
	});
})(jQuery);
