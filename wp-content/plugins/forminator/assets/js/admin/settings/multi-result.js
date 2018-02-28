( function($){
	define([
		'admin/settings/toggle-container',
		'text!tpl/fields.html',
	], function( MultiSetting, fieldsTpl ) {

		return MultiSetting.extend({
			multiple: false,

			events: {
				"click .wpmudev-multiresult--add": "add_result",
				"click .wpmudev-delete-result": "delete_result",
				'click .wpmudev-preview--image': 'add_image',
				"click .wpmudev-open_media": "add_image",
				"click .wpmudev-url--clear": "clear_image",
				"change .wpmudev-result-title": "update_title",
				"change .wpmudev-result-image": "update_image",
				"change .wpmudev-result-descrition": "update_description",
			},

			className: 'wpmudev-option',

			init: function () {
				this.results = this.model.get( 'results' );
				this.listenTo( Forminator.Events, "forminator:quiz:results:order:updated", this.render );
			},

			render: function () {
				this.$el.html('');
				this.$el.append( this.get_label_html() );
				this.$el.append( this.get_field_html() );

				var values = this.get_saved_value() || [];

				if( this.results.length ) {
					this.$el.find( '.wpmudev-multiresult' ).addClass( 'wpmudev-has_results' );
				}

				this.trigger( 'rendered', this.get_value() );

				this.on_render();
			},

			get_field_html: function () {
				var childs = this.get_values_html(),
					$mainTpl = Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-result-list-tpl' ).html() );

				return $mainTpl({
					childs: childs,
				});
			},

			get_values_html: function() {
				var self = this;

				return this.results.map( function ( result, key ) {
					return self.get_value_html( result, key );
				}).join(' ');
			},

			get_value_html: function( value, index ){
				var saved_value = this.get_saved_value(),
					$rowTpl = Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-result-list-row-tpl' ).html() )
				;

				return $rowTpl({
					row: value.toJSON(),
					index: index
				});
			},

			add_result: function ( e ) {
				e.preventDefault();

				// Init new condition
				new_result = new Forminator.Models.Result({});

				new_result.set( 'order', this.results.length );

				// Add condition to the collection
				this.results.add( new_result, { silent: true } );

				this.update();
				this.render();
			},

			delete_result: function ( e ) {
				e.preventDefault();

				var $button = $( e.target ),
					result = this.get_model( $button )
				;

				// Delete condition
				this.results.remove( result, { silent: true });

				this.update();
				this.render();
			},

			get_index: function ( $row ) {
				return $row.closest( '.wpmudev-multiresult--item' ).data( 'index' );
			},

			get_model: function ( $row ) {
				var index = this.get_index( $row );
				return this.results.get_by_index( index );
			},

			update_title: function ( e ) {
				var result = this.get_model( $( e.target ) )
				value = $( e.target ).val()
				;

				result.set( 'title', value );
				result.set( 'slug', Forminator.Utils.get_slug( value ) );

				this.update();
			},

			update_image: function ( e ) {
				var result = this.get_model( $( e.target ) )
				value = $( e.target ).val()
				;

				result.set( 'image', value );

				this.render();
			},

			update_description: function ( e ) {
				var result = this.get_model( $( e.target ) )
				value = $( e.target ).val()
				;

				result.set( 'description', value );
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
						var result = self.get_model( $( e.target ) )
						image = media.state().get('selection').first().toJSON(),
							$row = $( e.target ).closest( '.wpmudev-multiresult--item' ),
							$preview = $row.find( ".wpmudev-browse--preview" ),
							$preview_image = $row.find( ".wpmudev-get_image" ),
							$preview_url = $row.find( ".wpmudev-url--input" );
						;

						if( image && image.url ){
							$preview.addClass( "wpmudev-has_image" );
							$preview_image.css( "background-image", "url({url})".replace( "{url}", image.url ) );
							$preview_url.val( "{url}".replace( "{url}", image.url ) );

							result.set( 'image', image.url );
						}

					});

				media.open();
			},

			clear_image: function ( e ) {
				var result = this.get_model( $( e.target ) );

				result.set( 'image', '' );

				this.render();
			},

			update: function () {
				Forminator.Events.trigger( "forminator:quiz:results:updated" );
			}

		});

	});

})(jQuery);
