( function ($) {
	define([
		'text!tpl/appearance.html',
	], function( appearanceTpl ) {
		var PaginationSettings = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( appearanceTpl ).find( '#appearance-section-pagination-tpl' ).html()),

			className: 'wpmudev-box-body',

			initialize: function ( options ) {
				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html( this.mainTpl() );

				// PAGINATION SETTING
				this.render_pagination_header();
				this.render_pagination_footer();
			},

			render_pagination_header: function () {
				var self = this,
					pagination_header = new Forminator.Settings.RadioContainer({
					model: this.model,
					id: 'pagination-header-design',
					name: 'pagination-header-design',
					default_value: 'off',
					containerClass: 'wpmudev-is_gray',
					values: [
						{ value: "off", label: Forminator.l10n.appearance.pagination_none },
						// { value: "bar", label: Forminator.l10n.appearance.pagination_bar },
						{ value: "nav", label: Forminator.l10n.appearance.pagination_nav }
					]
				});

				var pagination_off = '<label class="wpmudev-label--info"><span>' + Forminator.l10n.appearance.pagination_off_label + '</span></label>';

				var pagination_bar = '<label class="wpmudev-label--info"><span>' + Forminator.l10n.appearance.pagination_bar_label + '</span></label>';

				var pagination_last_step_label = new Forminator.Settings.Text({
					model: this.model,
					id: 'pagination-step-label',
					name: 'pagination-step-label',
					hide_label: false,
					label: Forminator.l10n.appearance.last_step_label,
					placeholder: Forminator.l10n.appearance.last_step_placeholder
				});

				this.$el.find( '.pagination-settings-header' ).append( pagination_header.el );

				this.$el.find( '.pagination-settings-header .wpmudev-radio-tab-off' ).append( pagination_off );
				this.$el.find( '.pagination-settings-header .wpmudev-radio-tab-bar' ).append( pagination_bar );
				this.$el.find( '.pagination-settings-header .wpmudev-radio-tab-nav' ).append( pagination_last_step_label.el );

			},

			render_pagination_footer: function () {
				var self = this;
				var pagination_back = new Forminator.Settings.ToggleContainer({
					model: this.model,
					id: 'pagination-footer-button',
					name: 'pagination-footer-button',
					hide_label: true,
					containerClass: 'wpmudev-is_gray',
					values: [{
						value: 'true',
						label: Forminator.l10n.appearance.last_page_button
					}]
				});

				var pagination_back_label = new Forminator.Settings.Text({
					model: this.model,
					id: 'pagination-footer-button-text',
					name: 'pagination-footer-button-text',
					hide_label: true,
					placeholder: Forminator.l10n.appearance.last_page_placeholder
				});

				this.$el.find( '#pagination-settings-footer' ).append( pagination_back.el );
				this.$el.find( '#pagination-footer-button .wpmudev-toggle--box' ).append( pagination_back_label.el );

			},

		});

		return PaginationSettings;
	});
})( jQuery );
