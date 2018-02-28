(function ($) {
	define([
		'text!tpl/polls.html',
	], function( appearanceTpl ) {

		var AppearanceSettings = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#polls-section-appearance-tpl' ).html() ),
			voteTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#appearance-vote-limit-tpl' ).html() ),
			colorTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#appearance-color-grid-tpl' ).html() ),

			className: 'wpmudev-box-body',

			initialize: function (options) {
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html( this.mainTpl() );

				this.render_results();
				this.render_submission();
				this.render_votes();
				this.render_colors();
			},

			render_results: function() {
				// Poll results behavior
				var results_behav = new Forminator.Settings.Radio({
					model: this.model,
					id: 'forminator-pollwiz--results-behav',
					name: 'results-behav',
					containerSize: '400',
					hide_label: true,
					default_value: 'link_on',
					values: [
						{ value: "link_on", label: Forminator.l10n.polls.link_on },
						{ value: "show_after", label: Forminator.l10n.polls.show_after },
						{ value: "not_show", label: Forminator.l10n.polls.not_show }
					],
				});

				this.$el.find( '#forminator-pollwiz-wrap--results-behav' ).append( [ results_behav.el ] );

				// Poll results style
				var results_style = new Forminator.Settings.Radio({
					model: this.model,
					id: 'forminator-pollwiz--results-style',
					name: 'results-style',
					containerSize: '400',
					sizeLarge: true,
					itemsColor: 'blue',
					hasIcon: true,
					iconTop: true,
					default_value: 'bar',
					hide_label: true,
					values: [
						{ value: "bar", label: Forminator.l10n.polls.chart_bar, iconClass: 'wpdui-icon-graph-bar' },
						{ value: "pie", label: Forminator.l10n.polls.chart_pie, iconClass: 'wpdui-icon-graph-bar_1' }
					],
				});

				this.$el.find( '#forminator-pollwiz-wrap--results-style' ).append( [ results_style.el ] );

			},

			render_submission: function(){
				var enable_ajax = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'behaviours-form-enable-ajax',
					name: 'enable-ajax',
					hide_label: true,
					values: [{
						value: "true",
						label: Forminator.l10n.polls.enable_ajax,
						labelSmall: true
					}]
				});

				this.$el.find( '#forminator-pollwiz-wrap--submission' ).prepend( enable_ajax.el );

			},

			render_votes: function(){
				// Poll votes count
				var votes_count = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'forminator-pollwiz--votes-count',
					name: 'show-votes-count',
					hide_label: true,
					values: [
						{
							value: "true",
							label: Forminator.l10n.polls.show_votes,
							labelSmall: "true"
						}
					],
				});

				this.$el.find( '#forminator-pollwiz-wrap--votes-count' ).prepend( [ votes_count.el ] );

				// Vote number limit
				var votes_limit = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'forminator-pollwiz--votes-limit',
					name: 'enable-votes-limit',
					containerClass: 'wpmudev-is_gray',
					hide_label: true,
					values: [
						{
							value: "true",
							label: Forminator.l10n.polls.enable_limit,
							labelSmall: "true"
						}
					],
				});

				votes_limit.$el.find( '.wpmudev-option--switch_content' ).html( this.voteTpl() );

				var vote_limit_input = new Forminator.Settings.Number({
					model: this.model,
					type: 'number',
					name: 'vote_limit_input',
					hide_label: true,
				});

				var vote_limit_options = new Forminator.Settings.Select({
					model: this.model,
					name: 'vote_limit_options',
					hide_label: true,
					values: [
						{ value: "m", label: Forminator.l10n.appearance.minutes },
						{ value: "h", label: Forminator.l10n.appearance.hours },
						{ value: "d", label: Forminator.l10n.appearance.days },
						{ value: "W", label: Forminator.l10n.appearance.weeks },
						{ value: "M", label: Forminator.l10n.appearance.months },
						{ value: "Y", label: Forminator.l10n.appearance.years }
					],
				});

				votes_limit.$el.find( '.wpmudev-has_cols' ).append( [ vote_limit_input.el, vote_limit_options.el ] );

				this.$el.find( '#forminator-pollwiz-wrap--votes-limit' ).append( [ votes_limit.el ] );

			},

			render_colors: function(){
				this.$el.find( '#forminator-pollwiz-wrap--colors .wpmudev-box-gray' ).html( this.colorTpl() );

				// COLOR box background
				var box_background = new Forminator.Settings.Color({
					model: this.model,
					id: "forminator-pollwiz-color--boxbg",
					name: "box_background",
					hide_label: true,
					default_value: '#FFFFFF'
				});

				this.$el.find( '#forminator-pollwiz-wrap--boxbg' ).append( box_background.el );

				// COLOR box border
				var box_border = new Forminator.Settings.Color({
					model: this.model,
					id: "forminator-pollwiz-color--boxbo",
					name: "box_border",
					hide_label: true,
					default_value: '#E9E9E9'
				});

				this.$el.find( '#forminator-pollwiz-wrap--boxbo' ).append( box_border.el );

				// COLOR description text
				var description = new Forminator.Settings.Color({
					model: this.model,
					id: "forminator-pollwiz-color--description",
					name: "poll_description",
					hide_label: true,
					default_value: '#333333'
				});

				this.$el.find( '#forminator-pollwiz-wrap--descriptionco' ).append( description.el );

				// COLOR question text
				var question = new Forminator.Settings.Color({
					model: this.model,
					id: "forminator-pollwiz-color--question",
					name: "poll_question",
					hide_label: true,
					default_value: '#333333'
				});

				this.$el.find( '#forminator-pollwiz-wrap--questionco' ).append( question.el );

				// COLOR answer text
				var answers = new Forminator.Settings.Color({
					model: this.model,
					id: "forminator-pollwiz-color--answers",
					name: "poll_answers",
					hide_label: true,
					default_value: '#333333'
				});

				this.$el.find( '#forminator-pollwiz-wrap--answersco' ).append( answers.el );

				// COLOR input field bg
				var	inputbg_static = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--inputbg",
						name: "inputbg",
						tooltip: Forminator.l10n.appearance.static,
						hide_label: true,
						default_value: '#FAFAFA'
					}),
					inputbg_hover = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--inputbg_hover",
						name: "inputbg_hover",
						tooltip: Forminator.l10n.appearance.hover,
						hide_label: true,
						default_value: '#FAFAFA'
					}),
					inputbg_active = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--inputbg_active",
						name: "inputbg_active",
						tooltip: Forminator.l10n.appearance.active,
						hide_label: true,
						default_value: '#FAFAFA'
					});

				this.$el.find( '#forminator-pollwiz-wrap--inputbg .wpmudev-pickers' ).append( inputbg_static.el, inputbg_hover.el, inputbg_active.el );

				// COLOR input field border
				var	inputbo_static = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--inputbo",
						name: "inputbo",
						tooltip: Forminator.l10n.appearance.static,
						hide_label: true,
						default_value: '#DDDDDD'
					}),
					inputbo_hover = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--inputbo_hover",
						name: "inputbo_hover",
						tooltip: Forminator.l10n.appearance.hover,
						hide_label: true,
						default_value: '#DDDDDD'
					}),
					inputbo_active = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--inputbo_active",
						name: "inputbo_active",
						tooltip: Forminator.l10n.appearance.active,
						hide_label: true,
						default_value: '#DDDDDD'
					});

				this.$el.find( '#forminator-pollwiz-wrap--inputbo .wpmudev-pickers' ).append( inputbo_static.el, inputbo_hover.el, inputbo_active.el );

				// COLOR input placeholder
				var inputph = new Forminator.Settings.Color({
					model: this.model,
					id: "forminator-pollwiz-color--inputph",
					name: "input_placeholder",
					hide_label: true,
					default_value: '#8F8F8F'
				});

				this.$el.find( '#forminator-pollwiz-wrap--inputph' ).append( inputph.el );

				// COLOR input text
				var inputtxt = new Forminator.Settings.Color({
					model: this.model,
					id: "forminator-pollwiz-color--inputtxt",
					name: "input_text",
					hide_label: true,
					default_value: '#1E1E1E'
				});

				this.$el.find( '#forminator-pollwiz-wrap--inputtxt' ).append( inputtxt.el );

				// COLOR button background
				var	buttonbg_static = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--buttonbg",
						name: "buttonbg",
						tooltip: Forminator.l10n.appearance.static,
						hide_label: true,
						default_value: '#17A8E3'
					}),
					buttonbg_hover = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--buttonbg_hover",
						name: "buttonbg_hover",
						tooltip: Forminator.l10n.appearance.hover,
						hide_label: true,
						default_value: '#008FCA'
					}),
					buttonbg_active = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--buttonbg_active",
						name: "buttonbg_active",
						tooltip: Forminator.l10n.appearance.active,
						hide_label: true,
						default_value: '#008FCA'
					});

				this.$el.find( '#forminator-pollwiz-wrap--buttonbg .wpmudev-pickers' ).append( buttonbg_static.el, buttonbg_hover.el, buttonbg_active.el );

				// COLOR button text
				var	buttontxt_static = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--buttontxt",
						name: "buttontxt",
						tooltip: Forminator.l10n.appearance.static,
						hide_label: true,
						default_value: '#FFFFFF'
					}),
					buttontxt_hover = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--buttontxt_hover",
						name: "buttontxt_hover",
						tooltip: Forminator.l10n.appearance.hover,
						hide_label: true,
						default_value: '#FFFFFF'
					}),
					buttontxt_active = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--buttontxt_active",
						name: "buttontxt_active",
						tooltip: Forminator.l10n.appearance.active,
						hide_label: true,
						default_value: '#FFFFFF'
					});

				this.$el.find( '#forminator-pollwiz-wrap--buttontxt .wpmudev-pickers' ).append( buttontxt_static.el, buttontxt_hover.el, buttontxt_active.el );

				// COLOR results link
				var	link_static = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--link",
						name: "color_link",
						tooltip: Forminator.l10n.appearance.static,
						hide_label: true,
						default_value: '#17A8E3'
					}),
					link_hover = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--link_hover",
						name: "color_link_hover",
						tooltip: Forminator.l10n.appearance.hover,
						hide_label: true,
						default_value: '#008FCA'
					}),
					link_active = new Forminator.Settings.Color({
						model: this.model,
						id: "forminator-pollwiz-color--link_active",
						name: "color_link_active",
						tooltip: Forminator.l10n.appearance.active,
						hide_label: true,
						default_value: '#008FCA'
					});

				this.$el.find( '#forminator-pollwiz-wrap--link .wpmudev-pickers' ).append( link_static.el, link_hover.el, link_active.el );
			},

		});

		return AppearanceSettings;
	});

})(jQuery);
