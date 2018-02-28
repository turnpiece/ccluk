( function ($) {
	define([
		'text!tpl/appearance.html',
	], function( appearanceTpl ) {
		var EmailSettings = Backbone.View.extend({

			mainTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#appearance-section-emails-tpl' ).html()),

			className: 'wpmudev-box-body',

			initialize: function ( options ) {
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html( this.mainTpl() );

				this.render_user_email();
				this.render_admin_email();
			},

			render_user_email: function () {
				var user_email_toggle = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-user-email',
					name: 'use-user-email',
					hide_label: true,
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.send_user_email
						}
					],
				});

				var user_email_title = new Forminator.Settings.Text({
					model: this.model,
					id: 'emails-user-email-title',
					name: 'user-email-title',
					placeholder: Forminator.l10n.appearance.subject,
					inputLarge: "true"
				});

				var user_email_editor = new Forminator.Settings.Editor({
					model: this.model,
					id: 'emails-user-email-editor',
					name: 'user-email-editor',
					placeholder: Forminator.l10n.appearance.body,
					enableFormData: true
				});

				user_email_toggle.$el.find( '.wpmudev-option--switch_content' ).append( [ user_email_title.el, user_email_editor.el ] );

				// Append toggle to template
				this.$el.find( '.appearance-section-form-user-email' ).append( user_email_toggle.el );
			},

			render_admin_email: function () {
				var admin_email_toggle = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'appearance-admin-email',
					name: 'use-admin-email',
					hide_label: true,
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.send_admin_email
						}
					],
				});

				var admin_email_title = new Forminator.Settings.Text({
					model: this.model,
					id: 'emails-admin-email-title',
					name: 'admin-email-title',
					placeholder: Forminator.l10n.appearance.subject,
					inputLarge: "true"
				});

				var admin_email_editor = new Forminator.Settings.Editor({
					model: this.model,
					id: 'emails-admin-email-editor',
					name: 'admin-email-editor',
					placeholder: Forminator.l10n.appearance.body,
					enableFormData: true
				});

				admin_email_toggle.$el.find( '.wpmudev-option--switch_content' ).append( [ admin_email_title.el, admin_email_editor.el ] );

				// Append toggle to template
				this.$el.find( '.appearance-section-form-user-admin-email' ).append( admin_email_toggle.el );
			}
		});

		return EmailSettings;
	});
})( jQuery );
