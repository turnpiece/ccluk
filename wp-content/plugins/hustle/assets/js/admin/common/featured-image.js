Hustle.define("Featured_Image_Holder", function($){
	"use strict";

	return Backbone.View.extend({
		template: Optin.template('wph-popup-choose_image'),
		target_div: '',
		media_frame: false,
		options: {
			attribute: 'feature_image',
			title: optin_vars.messages.media_uploader.select_or_upload,
			button_text: optin_vars.messages.media_uploader.use_this_image,
			multiple: false
		},
		initialize: function( options ){
			this.options = _.extend( {}, this.options, options );
			if( !this.model || !this.options.attribute )
				throw new Error('Undefined model or attribute');
			
			if ( options.module_type ) {
				this.template = Optin.template( 'wph-'+ options.module_type +'-choose_image' );
			}
			
			this.target_div = options.target_div;
			
			$(document).on( 'click', 'button#wpmudev-feature-image-browse', $.proxy(this.open, this) );
			$(document).on( 'click', '.wpmudev-feature-image-src-input_text', $.proxy(this.open, this) );
			$(document).on( 'click', 'button#wpmudev-feature-image-clear', $.proxy(this.clear, this) );
			
			this.render();
		},
		render: function(){
			var html = this.template({
				image: this.model.get( this.options.attribute )
			});
			this.setElement( html );
			this.define_media_frame();
			this.show_or_hide_clear_button();
			
			return this;
		},
		// If no content, hide clear button and enable browsing on input click.
		show_or_hide_clear_button: function() {
			var feature_image = this.model.get('feature_image');
			if (feature_image === '' || typeof feature_image === 'undefined') {
				// Hide clear button.
				this.$el.find('.wpmudev-clear_image .wpmudev-button').hide();
				// Activate input click for browsing.
				this.$el.find('.wpmudev-feature-image-src-input_text').css('pointer-events', 'auto');
			} else {
				// Show clear button.
				this.$el.find('.wpmudev-clear_image .wpmudev-button').show();
				// Disable input click for browsing.
				this.$el.find('.wpmudev-feature-image-src-input_text').css('pointer-events', 'none');
			}
		},
		define_media_frame: function(){
			var self = this;
			this.media_frame = wp.media({
				title: self.options.title,
				button: {
					text: self.options.button_text
				},
				multiple: self.options.multiple
			}).on( 'select', function() {

				var media = self.media_frame.state().get('selection').first().toJSON();

				if( media && media.url ){
					var feature_image_src = media.url,
						feature_image_thumbnail = '';
					
					if ( media.sizes && media.sizes.thumbnail && media.sizes.thumbnail.url ) {
						feature_image_thumbnail = media.sizes.thumbnail.url;
					}
					
					self.target_div.find('.wpmudev-feature-image-src-input_text').val(feature_image_src);
					self.target_div.find('.wpmudev-inserted_image').css( 'background-image', 'url('+ feature_image_thumbnail +')' );
					self.model.set( 'feature_image', feature_image_src, {silent: true} );
					self.show_or_hide_clear_button();
				}

			});
		},
		open: function(e){
			e.preventDefault();
			this.media_frame.open();
		},
		clear: function(e) {
			e.preventDefault();
			this.target_div.find('.wpmudev-feature-image-src-input_text').val('');
			this.target_div.find('.wpmudev-inserted_image').css( 'background-image', 'url()' );
			this.model.set( 'feature_image', '', {silent: true} );
			this.show_or_hide_clear_button();
		}
	});
	
});
