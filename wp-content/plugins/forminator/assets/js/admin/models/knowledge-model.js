(function ($) {
	define( [
		'admin/models/base-model',
		'admin/models/questions-collection'
	], function( BaseModel, QuestionsCollection ) {
		// Condition model
		return BaseModel.extend({
			// Model defaults
			defaults: {
				questions: false
			},
			initialize: function () {
				var args = arguments;

				if ( this.get( 'questions' ) === false ) this.set( 'questions', new QuestionsCollection() );

				if ( args && args[0] && args[0][ "questions" ] ) {
					args[ "questions" ] = args[0][ "questions" ] instanceof QuestionsCollection ? args[0][ "questions" ] : new QuestionsCollection(args[0][ "questions" ])
					;
					this.set( "questions", args.questions );
				}
			}
		});
	});
})(jQuery);
