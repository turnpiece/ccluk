(function ($) {
	define([
		'text!tpl/quizzes.html',
	], function( quizzesTpl ) {

		var ResultsSettings = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( quizzesTpl ).find( '#quiz-section-results-tpl' ).html() ),

			className: 'wpmudev-box-body',

			initialize: function (options){
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html( this.mainTpl({
					type: 'knowledge'
				}) );

				this.render_separator();
				this.render_results();
				this.render_social();
			},

			render_separator: function(){

				var separator = new Forminator.Settings.Separator({
					model: this.model,
					name: 'separator',
					hide_label: true
				});

				this.$el.find( '.forminator-quizwiz-wrap--separator' ).append( [ separator.el ] );

			},

			render_results: function(){

				// OPTION results behaviour
				var results_behav = new Forminator.Settings.Radio({
					model: this.model,
					id: 'forminator-quizwiz--results-behav',
					name: 'results_behav',
					containerSize: '400',
					itemsColor: 'blue',
					label: Forminator.l10n.quizzes.reveal,
					default_value: 'end',
					values: [
						{ value: 'after', label: Forminator.l10n.quizzes.after },
						{ value: 'end', label: Forminator.l10n.quizzes.before }
					]
				});

				this.$el.find( '#forminator-quizwiz-wrap--results-behav' ).append( [ results_behav.el ] );

				// OPTION correct answer message
				var msg_correct = new Forminator.Settings.Text({
					model: this.model,
					id: 'forminator-quizwiz--msg-correct',
					name: 'msg_correct',
					label: Forminator.l10n.quizzes.msg_correct,
				});

				this.$el.find( '#forminator-quizwiz-wrap--msg-correct' ).append( [ msg_correct.el ] );

				// OPTION incorrect answer message
				var msg_incorrect = new Forminator.Settings.Text({
					model: this.model,
					id: 'forminator-quizwiz--msg-incorrect',
					name: 'msg_incorrect',
					label: Forminator.l10n.quizzes.msg_incorrect,
				});

				this.$el.find( '#forminator-quizwiz-wrap--msg-incorrect' ).append( [ msg_incorrect.el ] );

				// OPTION final count message
				var msg_count = new Forminator.Settings.Textarea({
					model: this.model,
					id: 'forminator-quizwiz--msg-count',
					name: 'msg_count',
					label: Forminator.l10n.quizzes.msg_count,
				});

				this.$el.find( '#forminator-quizwiz-wrap--msg-count' ).append( [ msg_count.el ] );

			},

			render_social: function(){

				// SOCIAL facebook
				var facebook = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'forminator-quizwiz--facebook',
					name: 'facebook',
					hide_label: true,
					values: [{
						value: 'true',
						hideTL: true
					}]
				});

				this.$el.find( '#wpmudev-quizwiz-wrap--facebook' ).append( [ facebook.el ] );

				// SOCIAL twitter
				var twitter = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'forminator-quizwiz--twitter',
					name: 'twitter',
					hide_label: true,
					values: [{
						value: 'true',
						hideTL: true
					}]
				});

				this.$el.find( '#wpmudev-quizwiz-wrap--twitter' ).append( [ twitter.el ] );

				// SOCIAL google
				var google = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'forminator-quizwiz--google',
					name: 'google',
					hide_label: true,
					values: [{
						value: 'true',
						hideTL: true
					}]
				});

				this.$el.find( '#wpmudev-quizwiz-wrap--google' ).append( [ google.el ] );

				// SOCIAL linkedin
				var linkedin = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'forminator-quizwiz--linkedin',
					name: 'linkedin',
					hide_label: true,
					values: [{
						value: 'true',
						hideTL: true
					}]
				});

				this.$el.find( '#wpmudev-quizwiz-wrap--linkedin' ).append( [ linkedin.el ] );
			}

		});

		return ResultsSettings;

	});

})(jQuery);
