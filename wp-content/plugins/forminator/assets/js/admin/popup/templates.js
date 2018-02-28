(function ($) {
	define([
		'text!tpl/dashboard.html',
	], function( popupTpl ) {
		return Backbone.View.extend({

			className: 'wpmudev-popup-templates',

			newFormTpl: Forminator.Utils.template( $( popupTpl ).find( '#forminator-new-form-tpl' ).html()),
			templatesTpl: Forminator.Utils.template( $( popupTpl ).find( '#forminator-new-form-template-list-tpl' ).html()),
			events: {
				"click .wpmudev-template--type": "create_form",
			},
			render: function() {
				// Add name field
				this.$el.html( this.newFormTpl());

				this.$el.append( this.templatesTpl({
					templates: Forminator.Data.modules.custom_form.templates
				}));
			},
			create_form: function( e ) {
				e.preventDefault();

				var $container = $( e.target ).closest( '.wpmudev-template--type' ),
					$form_name = $( e.target ).closest( '.wpmudev-box-section' ).find( '.wpmudev-create-form-field' ),
					$template = $container.data( 'template' )
				;

				if( $form_name.val() === "" ) {
					this.$el.find( '#forminator-validate-name' ).show();
				}  else {
					var form_url = Forminator.Data.modules.custom_form.new_form_url;

					this.$el.find( '#forminator-validate-name' ).hide();

					form_url = form_url + '&name=' + $form_name.val() + '&template=' + $template;

					window.location.href = form_url;
				}

			}
		});
	});
})(jQuery);
