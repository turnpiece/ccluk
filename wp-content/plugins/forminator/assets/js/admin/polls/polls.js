(function ($) {
	define([
		'admin/polls/appearance-settings',
		'admin/polls/details-settings',
		'admin/popup/ajax',
		'text!admin/templates/polls.html'
	], function( AppearanceSettings, DetailsSettings, AjaxPopup, appearanceTpl ) {
		var Polls = Backbone.View.extend({

			currentTab: 0,
			mainTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#polls-main-tpl' ).html() ),
			buttonsTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#polls-buttons-tpl' ).html() ),

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
				return this.render();
			},

			render: function () {
				var tplData = {};

				this.$el.html( this.mainTpl() );
				this.$el.append( this.buttonsTpl() );

				var settings_tabs = {
					'details': DetailsSettings,
					'appearance': AppearanceSettings
				};

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
				window.location.href = Forminator.Data.modules.polls.form_list_url;
			},

			save_changes: function ( e ) {
				if( this.validate() ) {
					this.save(false, true);
					$( '.wpmudev-preview' ).show();
				} else {
					$( 'html' ).animate( {scrollTop : 0}, 800 );
				}
			},

			save_layout: function ( e ) {
				if( this.validate() ) {
					this.save(true, true);
				} else {
					$( 'html' ).animate( {scrollTop : 0}, 800 );
				}
			},

			save: function ( redirect, preloader ) {
				var self = this,
					data = Forminator.Utils.model_to_json( this.model ),
					formName = Forminator.Data.currentForm.formName || this.model.get( 'formName' ) || '',
					formID = Forminator.Data.currentForm.formID || -1
				;

				if( preloader ) {
					this.$el.find('.forminator-loading').addClass('wpmudev-button-onload');
				}

				$.post({
					"url": Forminator.Data.ajaxUrl,
					"data": {
						"action": "forminator_save_poll",
						"formName": formName,
						"formID": formID,
						"data": data
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
							if (formID === -1) {
								form_list = Forminator.Data.modules.polls.form_list_url;
								window.location.href = form_list + '&new=true&title=' + formName.replace(/ /g, '-');
							} else {
								form_list = Forminator.Data.modules.polls.form_list_url;
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

					self.$el.find( "#forminator-poll-" + slug ).append( setting.el );
				});
			},

			update_buttons: function () {
				if( this.is_first_tab() ) {
					this.$el.find( '#forminator-polls-cancel' ).show();
					this.$el.find( '#forminator-polls-back' ).hide();
					this.$el.find( '#forminator-polls-next' ).show();
					this.$el.find( '#forminator-polls-finish' ).hide();
				} else {
					this.$el.find( '#forminator-polls-cancel' ).hide();
					this.$el.find( '#forminator-polls-back' ).show();
					this.$el.find( '#forminator-polls-next' ).hide();
					this.$el.find( '#forminator-polls-finish' ).show();
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
				if( this.currentTab === this.$el.find( '.wpmudev-tab--item' ).length ) return true;

				return false;
			},

			mark_tab: function () {
				// Mark tab with icon
				this.$el.find( '[data-tab-id=' + this.currentTab + ']' ).addClass( 'wpmudev-is--done' );
			},

			validate: function () {
				var has_error = false;
				if( _.isEmpty( this.model.get( 'formName' ) ) ) {
					this.$el.find( '#forminator-validate-name' ).show();
					has_error = true;
				} else {
					this.$el.find( '#forminator-validate-name' ).hide();
				}

				if( _.isEmpty( this.model.get( 'poll-question' ) ) ) {
					this.$el.find( '#forminator-validate-question' ).show();
					has_error = true;
				} else {
					this.$el.find( '#forminator-validate-question' ).hide();
				}

				if( this.model.get( 'answers' ).length === 0 ) {
					this.$el.find( '#forminator-validate-answers' ).show();
					has_error = true;
				} else {
					this.$el.find( '#forminator-validate-answers' ).hide();
				}

				if( has_error ) {
					return false;
				} else {
					return true;
				}
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

				this.open_preview_popup( $module, nonce, id, Forminator.l10n.popup.preview_polls, this.model.toJSON() );
			},

			open_preview_popup: function( action, nonce, id, title, data ) {
				if( _.isUndefined( title ) ) {
					title = Forminator.l10n.polls.popup_label;
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

		return Polls;
	});
})(jQuery);
