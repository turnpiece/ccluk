(function ($) {
	define([
		'text!tpl/quizzes.html',
	], function( quizzesTpl ) {

		var ResultsSettings = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( quizzesTpl ).find( '#quiz-section-results-tpl' ).html() ),

			className: 'wpmudev-box-body',

			initialize: function (options) {
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html( this.mainTpl({
					type: 'nowrong'
				}) );

				this.render_results();
			},

			render_results: function() {

				var results = new Forminator.Settings.MultiResult({
					model: this.model,
					hide_label: true
				});

				this.$el.find( '#forminator-quizwiz-wrap--results' ).prepend( [ results.el ] );

			},

		});

		return ResultsSettings;

	});

})(jQuery);
