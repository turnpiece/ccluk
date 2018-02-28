( function($) {
	define([
		'admin/settings/toggle-container',
		'text!tpl/fields.html',
	], function( MultiSetting, fieldsTpl ) {

		return MultiSetting.extend({
			multiple: false,

			events: {
				'click .wpmudev-add-variation': 'add_option',
				'click .wpmudev-action--kill': 'delete_option',
				'change .wpmudev-add-name': 'update_label',
				'change .wpmudev-add-price': 'update_value',
			},

			className: 'wpmudev-option forminator-field-wrap-productvar',

			get_field_html: function () {
				var childs = this.get_values_html(),
					$mainTpl	= Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-product-tpl' ).html() );

				return $mainTpl({
					childs: childs,
				});
			},

			get_values_html: function () {
				var values = this.get_saved_value() || [];

				return _.map( values, this.get_value_html, this ).join('');
			},

			get_value_html: function ( value, index ) {
				var saved_value = this.get_saved_value(),
					$rowTpl = Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-product-row-tpl' ).html())
				;

				return $rowTpl({
					row: value,
					index: index
				});
			},

			update_label: function ( e ) {
				var index = $( e.target ).closest( '.wpmudev-list--item' ).data( 'index' ),
					value = $( e.target ).val(),
					saved_value = this.get_saved_value() || [];

				saved_value[ index ].label = value;

				this.save_value( saved_value );
				this.trigger( 'updated', saved_value );
			},

			update_value: function ( e ) {
				var index = $( e.target ).closest( '.wpmudev-list--item' ).data( 'index' ),
					value = $( e.target ).val(),
					saved_value = this.get_saved_value() || [];

				saved_value[ index ].value = value;

				this.save_value( saved_value );
				this.trigger( 'updated', saved_value );
			},

			add_option: function ( e ) {
				var saved_value = this.get_saved_value() || [];

				saved_value.push( { label: '', value: '' } );

				this.save_value( saved_value );
				this.trigger( 'updated', saved_value );
				this.render();
			},

			delete_option: function ( e ) {
				var index = $( e.target ).closest( '.wpmudev-list--item' ).data( 'index' ),
					saved_value = this.get_saved_value() || [];

				saved_value.splice( index, 1 );

				this.save_value( saved_value );
				this.trigger( 'updated', saved_value );
				this.render();
			}
		});
	});
})( jQuery );
