(function ($) {
	define([
		'text!tpl/quizzes.html',
	], function( quizzesTpl ) {

		var QuestionsSettings = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( quizzesTpl ).find( '#quiz-section-questions-tpl' ).html() ),

			className: 'wpmudev-box-body',

			initialize: function (options) {
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html( this.mainTpl({
					type: 'knowledge'
				}) );

				this.render_questions();
			},

			render_questions: function() {
				// OPTION questions
				var knowledge_questions = new Forminator.Settings.MultiQuestion({
					model: this.model,
					id: 'forminator-quizwiz-wrap--questions',
					name: 'knowledge_questions',
					hide_label: true
				});

				this.$el.find( '#forminator-quizwiz-wrap--multiquestion' ).prepend( [ knowledge_questions.el ] );

			},

		});

		return QuestionsSettings;

	});

})(jQuery);
