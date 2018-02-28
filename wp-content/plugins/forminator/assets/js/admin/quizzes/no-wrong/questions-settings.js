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
					type: 'nowrong'
				}) );

				this.render_questions();
			},

			render_questions: function() {
				// OPTION questions
				var nowrong_questions = new Forminator.Settings.MultiQuestion({
					model: this.model,
					id: 'forminator-quizwiz-wrap--questions',
					name: 'nowrong_questions',
					noWrong: true,
					hide_label: true
				});

				this.$el.find( '#forminator-quizwiz-wrap--multiquestion' ).prepend( [ nowrong_questions.el ] );

				// OPTION priority order
				var priority_order = new Forminator.Settings.MultiOrder({
					model: this.model,
					id: 'forminator-quizwiz-wrap--order-fields',
					name: 'priority_order',
					noWrong: true,
					hide_label: true
				});

				this.$el.find( '#forminator-quizwiz-wrap--order' ).append( [ priority_order.el ] );

			},

		});

		return QuestionsSettings;

	});

})(jQuery);
