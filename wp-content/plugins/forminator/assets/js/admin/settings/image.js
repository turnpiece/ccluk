( function($) {
	define([
		'admin/settings/setting'
	], function( Setting ) {

		return Setting.extend({

			className: 'wpmudev-browse',

			events: {
				'click .wpmudev-open_media': 'add_image',
				'click .wpmudev-url--clear': 'clear_image'
			},

			get_field_html: function () {

				if ( this.options.hasPreview ) {

					if ( this.options.disableBrowse ) {

						return '<div class="wpmudev-browse--preview">' +
							'<button class="wpmudev-preview--image wpmudev-open_media">' +
								'<span class="wpmudev-get_image" style="background-image: url(' + this.get_saved_value() + ');"></span>' +
							'</button>' +
							'<div class="wpmudev-preview--url">' +
								'<input class="wpmudev-url--input" type="url" value="' + this.get_saved_value() + '" placeholder="' + Forminator.l10n.options.placeholder_image_alt + '" />' +
								'<button class="wpmudev-url--clear">' + Forminator.l10n.options.clear + '</button>' +
							'</div>' +
						'</div>';

					} else if ( this.options.previewOnly ) {

						return '<div class="wpmudev-browse--preview">' +
							'<button class="wpmudev-preview--image wpmudev-open_media">' +
								'<span class="wpmudev-get_image" style="background-image: url(' + this.get_saved_value() + ');"></span>' +
							'</button>' +
						'</div>';

					} else {

						return '<div class="wpmudev-browse--preview">' +
							'<div class="wpmudev-preview--image wpmudev-open_media">' +
								'<div class="wpmudev-get_image" style="background-image: url(' + this.get_saved_value() + ');"></div>' +
							'</div>' +
							'<div class="wpmudev-preview--url">' +
								'<input class="wpmudev-url--input" type="url" value="' + this.get_saved_value() + '" placeholder="' + Forminator.l10n.options.placeholder_image + '" />' +
								'<button class="wpmudev-url--clear">' + Forminator.l10n.options.clear + '</button>' +
							'</div>' +
						'</div>' +
						'<button class="wpmudev-browse--action wpmudev-open_media">' + Forminator.l10n.options.browse + '</button>';

					}

				} else {

					return '<div class="wpmudev-browse--preview">' +
						'<div class="wpmudev-preview--url">' +
							'<input class="wpmudev-url--input" type="url" value="' + this.get_saved_value() + '" placeholder="' + Forminator.l10n.options.placeholder_image + '" />' +
							'<button class="wpmudev-url--clear">' + Forminator.l10n.options.clear + '</button>' +
						'</div>' +
					'</div>' +
					'<button class="wpmudev-browse--action wpmudev-open_media">' + Forminator.l10n.options.browse + '</button>';
				}
			},

			add_image: function( e ) {
				e.preventDefault();

				var self = this,
					$preview_url = this.$el.find( ".wpmudev-url--input" ),
					media = wp.media({
						title: this.options.popup_label,
						button: {
							text: this.options.popup_button_label
						},
						multiple: false
					}).on( 'select', function() {
						var result = media.state().get('selection').first().toJSON();

						if( result && result.url ){

							if ( self.options.hasPreview ) {
								var $preview = self.$el.find( ".wpmudev-browse--preview" ),
									$preview_image = self.$el.find( ".wpmudev-get_image" )
								;

								$preview.addClass( "wpmudev-has_image" );
								$preview_image.css( "background-image", "url({url})".replace( "{url}", result.url ) );
							}

							$preview_url.val( "{url}".replace( "{url}", result.url ) );

							self.save_value( result.url );

						}

					})
				;

				// Open WP Media popup
				media.open();

			},

			clear_image: function ( e ) {
				e.preventDefault();

				this.save_value( '' );
				this.render();
			}

		});

	});

})( jQuery );
