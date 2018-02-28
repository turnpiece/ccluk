(function ($) {
    define([
        'text!tpl/dashboard.html',
    ], function (popupTpl) {
        return Backbone.View.extend({
            className: 'wpmudev-section--popup',

            popupTpl: Forminator.Utils.template($(popupTpl).find('#forminator-delete-popup-tpl').html()),
			
			initialize: function( options ) {
				this.nonce = options.nonce;
				this.id = options.id;
				this.referrer = options.referrer;
			},
			
            render: function () {
				
                this.$el.html(this.popupTpl({
					nonce: this.nonce,
					id: this.id,
					referrer: this.referrer
				}));
            },  
        });
    });
})(jQuery);
