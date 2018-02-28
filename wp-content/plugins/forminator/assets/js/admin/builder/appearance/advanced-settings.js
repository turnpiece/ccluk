( function ($) {
	define([
		'text!tpl/appearance.html',
	], function( appearanceTpl ) {
		var AdvancedSettings = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#appearance-section-advanced-tpl' ).html()),

			className: 'wpmudev-box-body',

			initialize: function ( options ) {
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html( this.mainTpl() );

				this.render_fields();
			},

			render_fields: function () {
				var generate_pdf = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'advanced-generate-pdf',
					name: 'generate-pdf',
					hide_label: true,
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.generate_pdf
						}
					],
				});

				var integrations = new Forminator.Settings.Toggle({
					model: this.model,
					id: 'advanced-integrations',
					name: 'integrations',
					hide_label: true,
					values: [
						{
							value: "true",
							label: Forminator.l10n.appearance.integrations
						}
					],
				});

				this.$el.find( '.appearance-section-form-advanced' ).append( [ generate_pdf.el, integrations.el ] );
			}

		});

		return AdvancedSettings;
	});
})( jQuery );
