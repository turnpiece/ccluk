Hustle.define("Delete_Confirmation", function($){
	"use strict";
	return Backbone.View.extend({
		el: "#wph-delete-module-confirmation",
		opts:{
			id: "",
			multiple: 0,
			ids: "",
			nonce: "",
			action: "",
			url: ajaxurl
		},
		events: {
			"click .hustle-delete-module-confirm": "confirm",
			"click .hustle-delete-module-cancel": "cancel"
		},
		initialize: function( options ){
			this.opts = _.extend({}, this.opts, options);
		},
		confirm: function(e){
			e.preventDefault();
			e.stopPropagation();

			var self = this,
				$this = this.$( e.target );
			
			$this.addClass('wpmudev-button-onload');

			$.ajax({
				url: this.opts.url,
				type: "POST",
				data: {
					action: this.opts.action,
					_ajax_nonce: this.opts.nonce,
					id: this.opts.id,
					multiple: this.opts.multiple,
					ids: this.opts.ids,
				},
				success: function(res){
					if( self.opts.onSuccess && _.isFunction( self.opts.onSuccess ) )
						self.opts.onSuccess.call(this, res, self);
				}
			});
		},
		cancel: function(e){
			e.preventDefault();
			e.stopPropagation();
			this.$el.removeClass('wpmudev-modal-active');
		}
	});
});
