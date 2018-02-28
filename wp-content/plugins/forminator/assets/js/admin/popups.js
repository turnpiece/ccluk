(function ($) {
	define([
		'admin/popup/templates',
		'admin/popup/login',
		'admin/popup/quizzes',
		'admin/popup/schedule',
		'admin/popup/new-form',
		'admin/popup/ajax',
		'admin/popup/delete'
	], function(
			TemplatesPopup,
			LoginPopup,
			QuizzesPopup,
			SchedulePopup,
			NewFormPopup,
			AjaxPopup,
			DeletePopup
		) {
		var Popups = Backbone.View.extend({
			el: '#wpmudev-main',

			events: {
				"click .wpmudev-open-modal": "open_modal",
				"click .wpmudev-button-open-modal": "open_modal"
			},

			initialize: function () {
				var new_form = Forminator.Utils.get_url_param( 'new' ),
					form_title = Forminator.Utils.get_url_param( 'title' )
				;

				if( new_form ) {
					var newForm = new NewFormPopup({
						title: form_title
					});
					newForm.render();

					this.open_popup( newForm, Forminator.l10n.popup.congratulations );
				}

				return this.render();
			},

			render: function() {
				return this;
			},

			open_modal: function( e ) {
				e.preventDefault();

				var $target = $( e.target ),
					$container = $( e.target ).closest( '.wpmudev-split--item' );

				if( ! $target.hasClass( 'wpmudev-open-modal' ) && ! $target.hasClass( 'wpmudev-button-open-modal' ) ) {
					$target = $target.closest( '.wpmudev-open-modal' );
				}

				var $module = $target.data( 'modal' ),
					nonce = $target.data( 'nonce' ),
					id = $target.data( 'form-id' )
				;

				// Open appropriate popup
				switch ( $module ) {
					case 'custom_forms':
						this.open_cform_popup();
						break;
					case 'login_registration_forms':
						this.open_login_popup();
						break;
					case 'polls':
						this.open_polls_popup();
						break;
					case 'quizzes':
						this.open_quizzes_popup();
						break;
					case 'exports':
						this.open_settings_modal( $module, nonce, id, Forminator.l10n.popup.your_exports );
						break;
					case 'exports-schedule':
						this.open_exports_schedule_popup();
						break;
					case 'delete-module':
						this.open_delete_popup( id, nonce );
						break;
					case 'paypal':
						this.open_settings_modal( $module, nonce, id, Forminator.l10n.popup.paypal_settings );
						break;
					case 'preview_cforms':
						this.open_settings_modal( $module, nonce, id, Forminator.l10n.popup.preview_cforms );
						break;
					case 'preview_polls':
						this.open_settings_modal( $module, nonce, id, Forminator.l10n.popup.preview_polls );
						break;
					case 'preview_quizzes':
						this.open_settings_modal( $module, nonce, id, Forminator.l10n.popup.preview_quizzes );
						break;
					case 'captcha':
						this.open_settings_modal( $module, nonce, id, Forminator.l10n.popup.captcha_settings );
						break;
					case 'currency':
						this.open_settings_modal( $module, nonce, id, Forminator.l10n.popup.currency_settings );
						break;
				}
			},
			
			open_popup: function( view, title ) {
				if( _.isUndefined( title ) ) {
					title = Forminator.l10n.custom_form.popup_label;
				}

				Forminator.Popup.open( function () {
					// If not a view append directly
					if( ! _.isUndefined( view.el ) ) {
						$( this ).append( view.el );
					} else {
						$( this ).append( view );
					}
				}, {
					title: title
				});
			},

			open_ajax_popup: function( action, nonce, id, title ) {
				if( _.isUndefined( title ) ) {
					title = Forminator.l10n.custom_form.popup_label;
				}

				var view = new AjaxPopup({
					action: action,
					nonce: nonce,
					id: id
				});
				
				Forminator.Popup.open( function () {
					$( this ).append( view.el );
				}, {
					title: title
				});
			},

			open_cform_popup: function () {
				/* Disable template selection */
				/*
				var newForm = new TemplatesPopup();
				newForm.render();

				this.open_popup( newForm );
				*/
				var form_url = Forminator.Data.modules.custom_form.new_form_url;

				window.location.href = form_url;
			},

			open_delete_popup: function (id, nonce) {
				var newForm = new DeletePopup({
					id: id,
					nonce: nonce,
					referrer: window.location.pathname + window.location.search
				});
				newForm.render();

				this.open_popup( newForm, Forminator.l10n.popup.are_you_sure );
			},

			open_login_popup: function () {
				var newForm = new LoginPopup();
				newForm.render();

				this.open_popup( newForm, Forminator.l10n.popup.edit_login_form );
			},

			open_polls_popup: function () {
				var form_url = Forminator.Data.modules.polls.new_form_url;

				window.location.href = form_url;
			},

			open_quizzes_popup: function () {
				var newForm = new QuizzesPopup();
				newForm.render();

				this.open_popup( newForm, Forminator.l10n.popup.quiz_type );
			},

			open_exports_schedule_popup: function () {
				var newForm = new SchedulePopup();
				newForm.render();

				this.open_popup( newForm, Forminator.l10n.popup.edit_scheduled_export );
			},

			open_settings_modal: function ( type, nonce, id, label ) {
				this.open_ajax_popup( type, nonce, id, label );
			}
		});

		//init after jquery ready
		jQuery( document ).ready( function() {
			new Popups();
		});
	});
})(jQuery);
