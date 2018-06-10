Hustle.define("Media_Holder", function(){
	"use strict";
	return Backbone.View.extend({
		template: Optin.template("hustle-media-holder-tpl"),
		media_frame: false,
		options: {
			attribute: "image",
			title: optin_vars.messages.media_uploader.select_or_upload,
			button_text: optin_vars.messages.media_uploader.use_this_image,
			multiple: false
		},
		events: {
			"click .wph-media--add": "open",
			"click .wph-button--dots": "toggle_options",
			"click .i-close": "toggle_options",
			"click .wpoi-swap-image-button": "swap_image",
			"click .wpoi-delete-image-button": "delete_image"
		},
		initialize: function( options ){
			this.options = _.extend( {}, this.options, options );
			if( !this.model || !this.options.attribute )
				throw new Error("Undefined model or attribute");
			
			jQuery(document).on('click', this.close_media_options);
			
			this.render();
		},
		render: function(){
			var html = this.template({
				image: this.model.get( this.options.attribute )
			});
			this.setElement( html );
			this.define_media_frame();
			return this;
		},
		define_media_frame: function(){
			var self = this,
				$preview = this.$(".wph-media--preview"),
				$holder = this.$(".wph-media--holder"),
				$options = this.$(".wph-media--options");

			this.media_frame = wp.media({
				title: self.options.title,
				button: {
					text: self.options.button_text
				},
				multiple: self.options.multiple
			}).on( 'select', function() {

				var media = self.media_frame.state().get('selection').first().toJSON();

				if( media && media.url ){
					$preview.css( "background-image", "url({url})".replace("{url}", media.url ));
					$holder.addClass("has-image");
					self.model.set( self.options.attribute , media.url);
					$options.removeClass("hidden");
					self.$(".wph-media--add").addClass("hidden");
				}

			});
		},
		open: function(e){
			e.preventDefault();
			var $holder = this.$(".wph-media--holder");
			$holder.removeClass("has-image");
			this.media_frame.open();
		},
		toggle_options: function(){
			this.$("wph-media--list").toggleClass("wph-open");
			this.$(".wph-media--items").toggleClass("hidden");
			this.$(".svg-triangle").toggleClass("hidden");
		},
		close_media_options: function(e){
			var $target = jQuery(e.target),
				$media = $target.closest('.wph-media--list');
			
			if ( $media.length === 0 && !$target.hasClass("wph-button wph-button--dots") ) {
				var $list = jQuery('.wph-media--list'),
					$items = $list.find(".wph-media--items"),
					$svg = $list.find(".svg-triangle")
				;
				if ( !$items.hasClass("hidden") ) $items.addClass("hidden");
				if ( !$svg.hasClass("hidden") ) $svg.addClass("hidden");
			}
		},
		swap_image: function(e){
			e.preventDefault();
			if( !this.media_frame ) return;

			this.media_frame.open();
			this.toggle_options();

		},
		delete_image: function(e){
			e.preventDefault();
			var $preview = this.$(".wph-media--preview");
			this.model.set( this.options.attribute, "" );
			$preview.css( "background-image", "url('')");
			this.toggle_options();
			this.$(".wph-media--add").removeClass("hidden");
			this.$(".wph-media--options").addClass("hidden");
		}
   });
});
