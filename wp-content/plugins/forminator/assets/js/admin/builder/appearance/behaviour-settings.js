( function ($) {
	define([
		'admin/style-editor',
		'text!tpl/appearance.html',
	], function( styleEditor, appearanceTpl ) {
		var BehaviourSettings = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#appearance-section-behaviour-tpl' ).html()),

			className: 'wpmudev-box-body',

			initialize: function ( options ) {
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html( this.mainTpl() );

				this.render_form_lifespan();
				this.render_form_submission();
				this.render_form_validation();
				this.render_form_submit();
				this.render_form_security();
			},

			render_form_lifespan: function () {
				var self = this,
					form_expires = new Forminator.Settings.Radio({
						model: this.model,
						id: 'behaviours-form-expire',
						name: 'form-expire',
						show: function ( value ) {
							setTimeout( function () {
								var date = self.$el.find( '#behaviours-form-expire-date' ),
									submits = self.$el.find( '#behaviours-form-expire-submits' )
								;

								// Hide all fields
								date.hide();
								submits.hide();

								// Show required field
								if( value === "date" ) date.show();
								if( value === "submits" ) submits.show();
							}, 10);
						},
						default_value: 'no_expire',
						values: [
							{ value: "no_expire", label: Forminator.l10n.appearance.form_doesnt_expire },
							{ value: "date", label: Forminator.l10n.appearance.on_certain_date },
							{ value: "submits", label: Forminator.l10n.appearance.expires_submits }
						]
					}),
					expire_date = new Forminator.Settings.Date({
						model: this.model,
						id: 'behaviours-form-expire-date',
						name: 'expire_date',
						placeholder: "Select date",
						hide_label: true
					}),
					expire_submits = new Forminator.Settings.Number({
						model: this.model,
						id: 'behaviours-form-expire-submits',
						name: 'expire_submits',
						hide_label: true
					})
				;

				this.$el.find( '.appearance-section-form-lifespan' ).append([
					form_expires.el,
					expire_date.el,
					expire_submits.el
				]);
			},

			render_form_submission: function(){
				var enable_ajax = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'behaviours-form-enable-ajax',
					name: 'enable-ajax',
					hide_label: true,
					values: [{
						value: "true",
						label: Forminator.l10n.appearance.enable_ajax,
						labelSmall: true
					}]
				});

				this.$el.find( '.appearance-section-form-submission' ).append( enable_ajax.el );
				
			},

			render_form_validation: function(){
				var validation = new Forminator.Settings.Radio({
					model: this.model,
					id: 'behaviours-form-validation',
					containerSize: '400',
					name: 'validation',
					default_value: 'on_submit',
					hide_label: true,
					values: [
						{ value: "server", label: Forminator.l10n.appearance.server_only },
						{ value: "on_submit", label: Forminator.l10n.appearance.form_submit }
					]
				});

				var inline_val = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'behaviours-form-validation-inline',
					name: 'validation-inline',
					hide_label: true,
					values: [{
						value: "true",
						label: Forminator.l10n.appearance.inline,
						labelSmall: true
					}]
				});

				this.$el.find( '.appearance-section-form-validation' ).append( validation.el, inline_val.el );
				
			},

			render_form_submit: function () {
				var self = this,
					thankyou_message = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'behaviour-thankyou',
					name: 'thankyou',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.show_thank_you_message,
							labelSmall: "true"
						}
					],
					on_change: function ( value ) {
						var is_enabled = this.model.get( 'redirect' );
						if( ( value === true || value === "true" ) &&  ( is_enabled === true || is_enabled === "true" ) ) {
							this.model.set( 'redirect', false );
							self.render();
						}
					}
				});

				// Custom font field
				var message = new Forminator.Settings.Editor({
					model: this.model,
					id: 'behaviour-thankyou-message',
					name: 'thankyou-message',
					hide_label: true,
					enableFormData: true
				});

				// Append to toggle
				thankyou_message.$el.find( '.wpmudev-option--switch_content' ).append( message.$el );

				var redirect_url = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'behaviour-redirect',
					name: 'redirect',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.redirect_to_url,
							labelSmall: "true"
						}
					],
					on_change: function ( value ) {
						var is_enabled = this.model.get( 'thankyou' );
						if( ( value === true || value === "true" ) &&  ( is_enabled === true || is_enabled === "true" ) ) {
							this.model.set( 'thankyou', false );
							self.render();
						}
					}
				});

				// Custom font field
				var url = new Forminator.Settings.Text({
					model: this.model,
					id: 'behaviour-redirect-url',
					name: 'redirect-url',
					hide_label: true,
					placeholder: "E.g. /thank-you",
				});

				// Append to toggle
				redirect_url.$el.find( '.wpmudev-option--switch_content' ).append( url.$el );

				this.$el.find( '.appearance-section-form-after-submit' ).append( [ thankyou_message.el, redirect_url.el ] );
			},

			render_form_security: function () {
				var honeypot = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'behaviours-form-honeypot',
					name: 'honeypot',
					hide_label: true,
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.enable_honeypot,
							labelSmall: "true"
						}
					],
				});

				var logged_users = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'behaviours-form-logged-users',
					name: 'logged-users',
					hide_label: true,
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.only_logged,
							labelSmall: "true"
						}
					],
				});

				this.$el.find( '.appearance-section-form-security' ).append( [ honeypot.el, logged_users.el ] );
			}
 		});

		return BehaviourSettings;
	});
})( jQuery );
