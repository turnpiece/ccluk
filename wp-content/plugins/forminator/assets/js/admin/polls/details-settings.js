(function ($) {
	define([
		'text!tpl/polls.html',
	], function( appearanceTpl ) {

		var DetailsSettings = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#polls-section-details-tpl' ).html()),

			className: 'wpmudev-box-body',

			initialize: function (options) {
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html( this.mainTpl());

				this.render_fields();
			},

			render_fields: function () {
				// POLL title
				var poll_title = new Forminator.Settings.Text({
					model: this.model,
					name: 'formName',
					hide_label: true,
					placeholder: Forminator.l10n.polls.poll_title_placeholder,
				});

				this.$el.find( '.details-section-title' ).append( poll_title.el );

				// POLL description
				var poll_description = new Forminator.Settings.Text({
					model: this.model,
					name: 'poll-description',
					hide_label: true,
					placeholder: Forminator.l10n.polls.poll_desc_placeholder,
				});

				this.$el.find( '.details-section-description' ).append( poll_description.el );

				// POLL question
				var poll_question = new Forminator.Settings.Text({
					model: this.model,
					name: 'poll-question',
					hide_label: true,
					placeholder: Forminator.l10n.polls.poll_question_placeholder,
				});

				this.$el.find( '.details-section-question' ).append( poll_question.el );

				// POLL answers
				var poll_answers = new Forminator.Settings.MultiAnswer({
					model: this.model,
					name: 'poll-answers',
					hide_label: true
				});

				this.$el.find( '.details-section-answers' ).append( poll_answers.el );

				// POLL button label
				var poll_button = new Forminator.Settings.Text({
					model: this.model,
					name: 'poll-button-label',
					hide_label: true,
					placeholder: Forminator.l10n.polls.poll_button_placeholder,
				});

				this.$el.find( '.details-section-button-label' ).append( poll_button.el );

			}

		});

		return DetailsSettings;
	});
})(jQuery);
