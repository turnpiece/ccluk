( function($) {
	define([
		'admin/settings/toggle-container',
		'text!tpl/fields.html',
	], function( MultiSetting, fieldsTpl ) {

		return MultiSetting.extend({
			multiple: false,

			events: {
				'click .wpmudev-open_media': 'add_image',
				'click .wpmudev-add-variation': 'add_option',
				'click .wpmudev-action--kill': 'delete_option',
				'click .wpmudev-label-list-variations': 'click_variation_label',
				'change .wpmudev-add-name': 'update_label',
				'change .wpmudev-add-price': 'update_value',
				'change #list-all-variations': 'update_list'

			},

			className: 'wpmudev-option',

			render: function () {
				this.$el.html('');
				this.$el.append( this.get_label_html() );
				this.$el.append( this.get_field_html() );

				var values = this.get_saved_value() || [];

				if( !_.isEmpty( values ) ) {
					this.$el.find( '.wpmudev-multiproduct' ).addClass( 'wpmudev-has_products' );
				}

				this.trigger( 'rendered', this.get_value() );

				this.on_render();
			},

			get_field_html: function () {
				var childs = this.get_values_html(),
					$mainTpl	= Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-product-multiple-tpl' ).html() ),
					list_variations = this.model.get( 'list_variations' ) || false
				;

				return $mainTpl({
					childs: childs,
					list_variations: list_variations
				});
			},

			get_values_html: function () {
				var values = this.get_saved_value() || [];

				return _.map( values, this.get_value_html, this ).join('');
			},

			get_value_html: function ( value, index ) {
				var saved_value = this.get_saved_value(),
					$rowTpl = Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-product-multiple-row-tpl' ).html())
				;

				return $rowTpl({
					row: value,
					index: index
				});
			},

			update_label: function(e) {
				var index = $( e.target ).closest( '.wpmudev-list--item' ).data( 'index' ),
					value = $( e.target ).val(),
					saved_value = this.get_saved_value() || [];

				saved_value[ index ].label = value;

				this.save_value( saved_value );
				this.trigger( 'updated', saved_value );
			},

			update_value: function(e) {
				var index = $( e.target ).closest( '.wpmudev-list--item' ).data( 'index' ),
					value = $( e.target ).val(),
					saved_value = this.get_saved_value() || [];

				saved_value[ index ].value = value;

				this.save_value( saved_value );
				this.trigger( 'updated', saved_value );
			},

			add_option: function(e) {
				var saved_value = this.get_saved_value() || [];

				saved_value.push( { label: '', value: '', image: '' } );

				this.save_value( saved_value );
				this.trigger( 'updated', saved_value );
				this.render();
			},

			delete_option: function(e) {
				var index = $( e.target ).closest( '.wpmudev-list--item' ).data( 'index' ),
					saved_value = this.get_saved_value() || []
				;

				saved_value.splice( index, 1 );

				this.save_value( saved_value );
				this.trigger( 'updated', saved_value );
				this.render();
			},

			update_list: function (e) {
				e.preventDefault();
				if( $( e.target ).is( ':checked' ) ) {
					this.model.set( 'list_variations', true );
				} else {
					this.model.set( 'list_variations', false );
				}
			},

			click_variation_label: function( e ) {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				this.$el.find( '#list-all-variations' ).click();
			},

			add_image: function ( e ) {
				e.preventDefault();

				var self = this,
					media = wp.media({
						title: this.options.popup_label,
						button: {
							text: this.options.popup_button_label
						},
						multiple: false
					}).on( 'select', function() {
						var image = media.state().get('selection').first().toJSON(),
							$row = $( e.target ).closest( '.wpmudev-list--item' ),
							$preview = $row.find( ".wpmudev-browse--preview" ),
							$preview_image = $row.find( ".wpmudev-get_image" ),
							$preview_url = $row.find( ".wpmudev-url--input" );
						;

						if( image && image.url ){
							$preview.addClass( "wpmudev-has_image" );
							$preview_image.css( "background-image", "url({url})".replace( "{url}", image.url ) );
							$preview_url.val( "{url}".replace( "{url}", image.url ) );

							var index = $( e.target ).closest( '.wpmudev-list--item' ).data( 'index' ),
								value = $( e.target ).val(),
								saved_value = self.get_saved_value() || [];

							saved_value[ index ].image = image.url;

							self.save_value( saved_value );
							self.trigger( 'updated', saved_value );
						}

					});

				media.open();
			}
		});
	});

})( jQuery );
