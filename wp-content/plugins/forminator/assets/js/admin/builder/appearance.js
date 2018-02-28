( function ($) {
	define([
		'admin/builder/appearance/appearance-settings',
		'admin/builder/appearance/behaviour-settings',
		'admin/builder/appearance/email-settings',
		'admin/builder/appearance/advanced-settings',
		'admin/builder/appearance/pagination-settings',
		'admin/popup/ajax',
		'text!admin/templates/appearance.html'
	], function( AppearanceSettings, BehaviourSettings, EmailSettings, AdvancedSettings, PaginationSettings, AjaxPopup, appearanceTpl ) {
		var Appearance = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#appearance-main-tpl' ).html() ),
			buttonsTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#appearance-buttons-tpl' ).html() ),

			events: {
				"click .forminator-save-changes": "save_changes",
				"click .forminator-save-layout": "save_layout",
				"click .wpmudev-button-back": "back_to_builder",
				"click .wpmudev-preview": "open_preview",
				"change .wpmudev-input,.forminator-field-singular": "set_dirty"
			},

			has_pagination: false,

			dirty: false,

			initialize: function ( options ) {
				return this.render();
			},

			render: function () {
				var tplData = {};

				// Check if we have pagination field
				var wrappers = this.model.get('wrappers');
				this.has_pagination = Forminator.Utils.has_pagination( wrappers );

				// Make sure we close sidebar settings every time we go to settings
				Forminator.Events.trigger( "forminator:sidebar:close:settings" );

				this.$el.html( this.mainTpl({
					has_pagination: this.has_pagination
				}));
				this.$el.append( this.buttonsTpl() );

				// Initialize tabs
				this.$el.find( ".wpmudev-wizard--content" ).tabs();

				// Navigate through tabs with Select on Mobile
				this.$el.find( ".wpmudev-tabs-mobile" ).on( 'change', function ( e ) {
					$( '.' + $(this).val() + ' a' ).click();
				});

				// Update Title
				$( '#wpmudev-header h1' ).html( Forminator.l10n.appearance.settings_title );

				var settings_tabs = {
					'appearance': AppearanceSettings,
					'behaviour': BehaviourSettings,
					'emails': EmailSettings,
					//'advanced': AdvancedSettings
				};

				if( this.has_pagination ) {
					settings_tabs['pagination'] = PaginationSettings;
				}

				// Append settigns to their wrapper
				this.append_settings( settings_tabs );

				//leave confirmation
				var self = this;
				$( window ).off('beforeunload.forminator-leave-wizard-confirm');
				$( window ).on('beforeunload.forminator-leave-wizard-confirm', function(e){
					if(self.dirty) {
						return Forminator.l10n.popup.save_alert;
					}
				});

				// Initialize Select 2
				this.init_select2();

			},

			back_to_builder: function ( e ) {
				// Return to Builder, no saving
				Forminator.navigate( 'builder', { trigger: true } );
			},

			cancel_builder: function ( e ) {
				e.preventDefault();

				// Go to listings page
				window.location.href = Forminator.Data.modules.custom_form.form_list_url;
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

				this.open_preview_popup( $module, nonce, id, Forminator.l10n.appearance.preview_form, this.model.toJSON() );
			},

			open_preview_popup: function( action, nonce, id, title, data ) {
				if( _.isUndefined( title ) ) {
					title = Forminator.l10n.appearance.preview_form;
				}

				var view = new AjaxPopup({
					action: action,
					nonce: nonce,
					data: data,
					id: id
				});

				Forminator.Popup.open( function () {
					$( this ).append( view.el );

					$( '.forminator-design--material' ).each( function() {
						var form = $(this),
							$input = form.find('.forminator-input'),
							$textarea = form.find('.forminator-textarea'),
							$product = form.find('.forminator-product'),
							$date = form.find('.forminator-date')
						;

						var $navigation = form.find('.forminator-pagination--nav'),
							$navitem = $navigation.find('li');

						$('<span class="forminator-nav-border"></span>').insertAfter($navitem);

						$input.prev('.forminator-field--label').addClass('forminator-floating--input');
						$textarea.prev('.forminator-field--label').addClass('forminator-floating--textarea');

						if ($date.hasClass('forminator-has_icon')) {
							$date.prev('.forminator-field--label').addClass('forminator-floating--date');
						} else {
							$date.prev('.forminator-field--label').addClass('forminator-floating--input');
						}

						$product.closest('.forminator-field').addClass('forminator-product--material');

						$input.wrap('<div class="forminator-input--wrap"></div>');

					});
				}, {
					title: title
				});
			},

			save_changes: function ( e ) {
				e.preventDefault();

				if( this.validate() ) {
					this.save(false, true);
					$( '.wpmudev-preview' ).show();
				} else {
					$( '.wpmudev-wizard--content' ).tabs("option", "active", 0 );
					$( 'html, body' ).animate( {scrollTop : 0}, 500 );
				}
			},


			save_layout: function ( e ) {
				e.preventDefault();

				if( this.validate() ) {
					this.save(true, true);
				} else {
					$( '.wpmudev-wizard--content' ).tabs("option", "active", 0 );
					$( 'html, body' ).animate( {scrollTop : 0}, 500 );
				}
			},

			save: function ( redirect, preloader ) {
				var isNew = false;

				if( preloader ) {
					this.$el.find( '.forminator-save' ).addClass( 'wpmudev-button-onload' );
				}

				if( redirect ) {
					var hasFlag = this.model.get( 'is_new' ) || false;

					if( ! hasFlag ) {
						this.model.set( 'is_new', true );
						isNew = true;
					}
				}

				// AJAX save
				var self = this,
					data = Forminator.Utils.model_to_json( this.model ),
					formName = Forminator.Data.currentForm.formName || this.model.get( 'formName' ) || '',
					formID = Forminator.Data.currentForm.formID || -1
				;

				$.post({
					"url": Forminator.Data.ajaxUrl,
					"data": {
						"action": "forminator_save_builder_settings",
						"formName": formName,
						"formID": formID,
						"data": data,
					}
				})
				.success( function ( response ) {
					//mark non dirty on save
					self.dirty = false;

					if ( formID === -1 ) {
						Forminator.Data.currentForm.formID = response.data;
					}

					if( preloader ) {
						setTimeout(function () {
							self.$el.find( '.forminator-save' ).removeClass( 'wpmudev-button-onload' );
						}, 500);

						if( ! redirect ) {
							Forminator.Notification.open('success', formName + ' ' + Forminator.l10n.options.been_saved, 4000);
						}
					}

					if( redirect ) {
						var form_list;

						// If new form redirect to form listing
						if (isNew) {
							form_list = Forminator.Data.modules.custom_form.form_list_url;
							window.location.href = form_list + '&new=true&title=' + formName.replace(/ /g, '-');
						} else {
							form_list = Forminator.Data.modules.custom_form.form_list_url;
							window.location.href = form_list + '&notification=true&title=' + formName.replace(/ /g, '-');
						}
					}
				})
				.error(function () {
					Forminator.Notification.open( 'error', Forminator.l10n.options.error_saving, 5000 );
				});
			},

			validate: function () {
				var has_error = false;
				if( _.isEmpty( this.model.get( 'formName' ) ) ) {
					this.$el.find( '#forminator-validate-name' ).show();
					has_error = true;
				} else {
					this.$el.find( '#forminator-validate-name' ).hide();
				}

				if( has_error ) {
					return false;
				} else {
					return true;
				}
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

					self.$el.find( "#forminator-wizard-" + slug ).append( setting.el );
				});
			},
			set_dirty: function () {
				this.dirty = true;
			}

		});

		return Appearance;
	});
})( jQuery );
