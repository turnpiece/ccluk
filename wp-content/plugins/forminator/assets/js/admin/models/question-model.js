(function ($) {
	define( [
		'admin/models/base-model',
		'admin/models/answers-collection'
	], function( BaseModel, AnswersCollection ) {
		// Condition model
		return BaseModel.extend({
			// Model defaults
			defaults: {
				answers: false
			},

			initialize: function () {
				var args = arguments;

				if ( this.get( 'answers' ) === false ) this.set( 'answers', new AnswersCollection() );

				if ( args && args[0] && args[0]["answers"] ) {
					args["answers"] = args[0]["answers"] instanceof AnswersCollection ? args[0]["answers"] : new AnswersCollection( args[0]["answers"] )
					;

					this.set( "answers", args.answers );
				}
			},

			get_id: function () {
				return this.cid;
			},

			/**
			 * Find Answer with no result mapped
			 * Return array
			 * @returns {Array}
			 */
			find_answers_with_no_result: function () {
				var answers = this.get('answers');
				var answers_with_no_result = [];
				if (_.isUndefined(answers)) {
					return answers_with_no_result;
				}
				if (this.answers_count() < 1) {
					return answers_with_no_result;
				}
				answers_with_no_result = answers.filter(function (answer) {
					return !answer.get('result');
				});
				if (_.isUndefined(answers_with_no_result) || answers_with_no_result.length < 1) {
					return [];
				}
				return answers_with_no_result;
			},

			answers_count: function () {
				return this.get( 'answers' ).length;
			}
		});
	});
})(jQuery);
