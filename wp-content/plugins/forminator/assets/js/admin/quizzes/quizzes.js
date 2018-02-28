(function ($) {
	define([
		'admin/quizzes/knowledge/details-settings',
		'admin/quizzes/knowledge/questions-settings',
		'admin/quizzes/knowledge/settings-settings',
		'admin/quizzes/no-wrong/details-settings',
		'admin/quizzes/no-wrong/questions-settings',
		'admin/quizzes/no-wrong/results-settings',
		'admin/popup/ajax',
		'text!admin/templates/quizzes.html'
	], function( KnowledgeDSettings, KnowledgeQSettings, KnowledgeRSettings, NowrongDSettings, NowrongQSettings, NowrongRSettings, AjaxPopup, quizzesTpl ) {
		var Quizzes = Backbone.View.extend({
			currentTab: 0,
			mainTpl: Forminator.Utils.template( $( quizzesTpl ).find( '#quiz-main-tpl' ).html() ),
			buttonsTpl: Forminator.Utils.template( $( quizzesTpl ).find( '#quiz-buttons-tpl' ).html() ),

			events: {
				"click .wpmudev-tab--item a": "disable_link",
				"click .forminator-save": "save_changes",
				"click #forminator-polls-finish": "save_layout",
				"click #forminator-polls-cancel": "cancel",
				"click #forminator-polls-back": "prev_tab",
				"click #forminator-polls-next": "next_tab",
				"click .wpmudev-preview": "open_preview",
				"change input": "set_dirty"
			},

			initialize: function ( options ) {
				this.app = Forminator.Data.application || false;
				return this.render();
			},

			render: function () {
				var tplData = {},
					settings_tabs = {}
				;

				this.$el.html( this.mainTpl({
					type: this.app
				}) );

				this.$el.append( this.buttonsTpl() );

				if( this.app === "knowledge" ) {
					settings_tabs = {
						'details': KnowledgeDSettings,
						'questions': KnowledgeQSettings,
						'settings': KnowledgeRSettings,
					};
				} else {
					settings_tabs = {
						'details': NowrongDSettings,
						'questions': NowrongQSettings,
						'results': NowrongRSettings,
					};
				}

				var self = this;
				$(window).off('beforeunload.forminator-leave-wizard-confirm');
				$(window).on('beforeunload.forminator-leave-wizard-confirm', function (e) {
					if (self.dirty) {
						return Forminator.l10n.popup.save_alert;
					}
				});

				this.init_tabs();
				this.append_settings( settings_tabs );
				this.update_buttons();

				this.init_select2();

			},

			cancel: function ( e ) {
				e.preventDefault();

				// Go to listings page
				window.location.href = Forminator.Data.modules.quizzes.form_list_url;
			},

			save_layout: function ( e ) {
				// AJAX save
				var action,
					data = Forminator.Utils.model_to_json( this.model ),
					formName = this.model.get( 'quiz_title' ) || '',
					formID = Forminator.Data.currentForm.formID || -1
				;

				if( this.app === "knowledge" ) {
					action = 'forminator_save_quiz_knowledge';
				} else {
					action = 'forminator_save_quiz_nowrong';
				}

				$.post({
					"url": Forminator.Data.ajaxUrl,
					"data": {
						"action": action,
						"formName": formName,
						"formID": formID,
						"data": data,
					}
				})
					.success( function ( response ) {
						// If new form redirect to form listing
						if( formID === -1 ) {
							var form_list = Forminator.Data.modules.quizzes.form_list_url;
							window.location.href = form_list + '#' + formName.replace( ' ', '-' );
						} else {
							window.location.href = Forminator.Data.modules.quizzes.form_list_url;
						}
					})
					.error(function () {

					});
			},

			save_changes: function ( e ) {
				if( this.validate() ) {
					this.save(false, true);
					$( '.wpmudev-preview' ).show();
				} else {
					$( 'html, body' ).animate( {scrollTop : 0}, 500 );
				}
			},

			save_layout: function ( e ) {
				if( this.validate() ) {
					this.save(true, true);
				} else {
					$( 'html, body' ).animate( {scrollTop : 0}, 500 );
				}
			},

			save: function ( redirect, preloader ) {
				// AJAX save
				var action,
					self = this,
					data = Forminator.Utils.model_to_json( this.model ),
					formName = this.model.get( 'quiz_title' ) || '',
					formID = Forminator.Data.currentForm.formID || -1
				;

				if( preloader ) {
					this.$el.find('.forminator-loading').addClass('wpmudev-button-onload');
				}

				if( this.app === "knowledge" ) {
					action = 'forminator_save_quiz_knowledge';
				} else {
					action = 'forminator_save_quiz_nowrong';
				}

				$.post({
					"url": Forminator.Data.ajaxUrl,
					"data": {
						"action": action,
						"formName": formName,
						"formID": formID,
						"data": data,
					}
				})
					.success( function ( response ) {
						self.dirty = false;
						if (formID === -1) {
							Forminator.Data.currentForm.formID = response.data;
						}

						var form_list;

						if( redirect ) {
							// If new form redirect to form listing
							if( formID === -1 ) {
								form_list = Forminator.Data.modules.quizzes.form_list_url;
								window.location.href = form_list + '&new=true&title=' + formName.replace(/ /g, '-');
							} else {
								form_list = Forminator.Data.modules.quizzes.form_list_url;
								window.location.href = form_list + '&notification=true&title=' + formName.replace(/ /g, '-');
							}
						}

						if( preloader ) {
							setTimeout(function () {
								self.$el.find('.forminator-loading').removeClass('wpmudev-button-onload');
							}, 500);

							if( ! redirect ) {
								var markup = _.template( '<strong>{{ formName }}</strong> {{ Forminator.l10n.options.been_saved }}' );

								Forminator.Notification.open( 'success', markup({
									formName: formName
								}), 4000 );
							}
						}
					})
					.error(function () {
						Forminator.Notification.open( 'error', Forminator.l10n.options.error_saving, 5000 );
					});
			},

			init_select2: function () {
				Forminator.Utils.init_select2();
			},

			append_settings: function ( settings ) {
				var self = this;

				_.each( settings, function ( view, slug ) {
					var setting = new view({
						model: self.model
					});

					self.$el.find( "#forminator-quiz-" + slug ).append( setting.el );
				});
			},

			update_buttons: function () {
				if( this.is_first_tab() ) {
					this.$el.find( '#forminator-polls-cancel' ).show();
					this.$el.find( '#forminator-polls-back' ).hide();
					this.$el.find( '#forminator-polls-next' ).show();
					this.$el.find( '#forminator-polls-finish' ).hide();
				} else if( this.is_last_tab() ) {
					this.$el.find( '#forminator-polls-cancel' ).hide();
					this.$el.find( '#forminator-polls-back' ).show();
					this.$el.find( '#forminator-polls-next' ).hide();
					this.$el.find( '#forminator-polls-finish' ).show();
				} else {
					this.$el.find( '#forminator-polls-cancel' ).hide();
					this.$el.find( '#forminator-polls-back' ).show();
					this.$el.find( '#forminator-polls-next' ).show();
					this.$el.find( '#forminator-polls-finish' ).hide();
				}
			},

			init_tabs: function () {
				this.update_tab();
			},

			update_tab: function () {
				this.clear_tabs();

				this.$el.find( '[data-tab-id=' + this.currentTab + ']' ).addClass( 'wpmudev-is--active' );
				this.$el.find( '.wpmudev-tab-content-' + this.currentTab ).show();
			},

			clear_tabs: function () {
				this.$el.find( '.wpmudev-tab--item ').removeClass( 'wpmudev-is--active' );
				this.$el.find( '.wpmudev-settings--box' ).hide();
			},

			is_first_tab: function () {
				if( this.currentTab === 0 ) return true;

				return false;
			},

			is_last_tab: function () {
				if( this.currentTab === ( this.$el.find( '.wpmudev-tab--item' ).length - 1 ) ) return true;

				return false;
			},

			mark_tab: function () {
				// Mark tab with icon
				this.$el.find( '[data-tab-id=' + this.currentTab + ']' ).addClass( 'wpmudev-is--done' );
			},

			/**
			 * Validate Quiz Title
			 * show name element on empty, and hide it when not empty
			 * @returns {boolean}
			 */
			validate_quiz_title: function(){
				if( _.isEmpty( this.model.get( 'quiz_title' ) ) ) {
					//go to details settings
					if (this.currentTab !== 0) {
						this.currentTab = 0;
						this.update_tab();
						this.update_buttons();
					}
					this.$el.find( '#forminator-validate-name' ).show();
					return false;
				} else {
					this.$el.find( '#forminator-validate-name' ).hide();
				}

				return true;
			},

			/**
			 * Validate Questions Answers mapped with Result
			 * Check for answers that does not have result
			 * Only for `nowrong` Quiz
			 *
			 * @returns {boolean}
			 */
			validate_quiz_question_answers: function(){
				var self = this;

				if(this.app === 'knowledge') {
					return true;
				}

				this.$el.find('.forminator-validate-answer-result').hide();
				var questions = this.model.get('questions');

				//question could be not added yet / deleted
				if(_.isUndefined(questions) || questions.length < 1) {
					return true;
				}

				var questions_with_no_result_answer = questions.filter(function (question) {
					return question.find_answers_with_no_result().length > 0;

				});


				if (questions_with_no_result_answer.length > 0) {
					//go to question settings
					if (this.currentTab !== 2) {
						this.currentTab = 2;
						this.update_tab();
						this.update_buttons();
					}

					_.each(questions_with_no_result_answer, function (question_with_no_result_answer) {
						var question_index = questions.model_index(question_with_no_result_answer);
						var question_element = self.$el.find('.wpmudev-multiqs--questions .wpmudev-multiqs--item[data-index=' + question_index + ']');

						var no_result_answers = question_with_no_result_answer.find_answers_with_no_result();
						_.each(no_result_answers, function (no_result_answer){
							var answer_index = question_with_no_result_answer.get('answers').model_index(no_result_answer);
							var answer_element = question_element.find('.wpmudev-answer[data-index=' + answer_index + ']');
							if(!answer_element.hasClass('wpmudev-is_open')) {
								answer_element.addClass('wpmudev-is_open');
							}
							answer_element.find('.forminator-validate-answer-result[data-index=' + answer_index + ']').show();
						});

					});

					return false;
				}
				return true;
			},

			validate: function () {
				// var has_error = false;
				if(!this.validate_quiz_title()) {
					return false;
				}

				if(!this.validate_quiz_question_answers()) {
					return false;
				}

				return true;
			},

			prev_tab: function () {
				this.currentTab = this.currentTab - 1;
				this.update_tab();
				this.update_buttons();
			},

			next_tab: function () {
				if( this.validate() ) {
					this.mark_tab();
					this.currentTab = this.currentTab + 1;
					this.update_tab();
					this.update_buttons();
				} else {
					// Show errors
					this.$el.find( '[data-tab-id=' + this.currentTab + ']' ).removeClass( 'wpmudev-is--done' );
				}
			},

			disable_link: function ( e ) {
				e.preventDefault();
				e.stopPropagation();
			},

			open_preview: function ( e ) {
				e.preventDefault();

				var $target = $( e.target );

				if( ! $target.hasClass( 'wpmudev-preview' ) ) {
					$target = $target.closest( '.wpmudev-preview' );
				}

				var $module = $target.data( 'modal' ),
					nonce = $target.data( 'nonce' ),
					id = $target.data( 'form-id' )
				;

				this.open_preview_popup( $module, nonce, id, Forminator.l10n.quizzes.preview_quiz, this.model.toJSON() );
			},

			open_preview_popup: function( action, nonce, id, title, data ) {
				if( _.isUndefined( title ) ) {
					title = Forminator.l10n.quizzes.preview_quiz;
				}

				var view = new AjaxPopup({
					action: action,
					nonce: nonce,
					data: data,
					id: id
				});

				Forminator.Popup.open( function () {
					$( this ).append( view.el );
				}, {
					title: title
				});
			},

			set_dirty: function () {
				this.dirty = true;
			}

		});

		return Quizzes;
	});
})(jQuery);
