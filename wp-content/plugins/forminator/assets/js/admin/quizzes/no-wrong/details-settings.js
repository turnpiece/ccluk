(function ($) {
	define([
		'text!tpl/quizzes.html',
	], function( quizzesTpl ) {

		var QuestionsSettings = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( quizzesTpl ).find( '#quiz-section-details-tpl' ).html() ),

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
				// OPTION quiz title
				var quiz_title = new Forminator.Settings.Text({
					model: this.model,
					id: 'forminator-quizwiz--title',
					name: 'quiz_title',
					hide_label: true
				});

				this.$el.find('#forminator-quizwiz-wrap--title').prepend( [ quiz_title.el ] );

				// OPTION main image
				var quiz_feat_image = new Forminator.Settings.Image({
					model: this.model,
					id: 'forminator-quizwiz--feat-image',
					name: 'quiz_feat_image',
					hasPreview: true,
					hide_label: true,
				});

				this.$el.find('#forminator-quizwiz-wrap--feat-image').append([quiz_feat_image.el]);

				// OPTION description or intro
				var description = new Forminator.Settings.Textarea({
					model: this.model,
					id: 'forminator-quizwiz--description',
					name: 'quiz_description',
					hide_label: true,
					size: '120'
				});

				this.$el.find('#forminator-quizwiz-wrap--description').append([description.el]);

				// OPTION visual style
				var visual_style = new Forminator.Settings.Radio({
					model: this.model,
					id: 'forminator-quizwiz--visual-style',
					name: 'visual_style',
					containerSize: '400',
					itemsColor: 'blue',
					hasIcon: true,
					hide_label: true,
					default_value: 'list',
					values: [
						{value: 'list', label: Forminator.l10n.quizzes.list, iconClass: 'wpdui-icon-align-justify',},
						{value: 'grid', label: Forminator.l10n.quizzes.grid, iconClass: 'wpdui-icon-thumbnails',}
					]
				});

				this.$el.find('#forminator-quizwiz-wrap--visual-style').append([visual_style.el]);
			}
		});

		return QuestionsSettings;
	});

})(jQuery);
