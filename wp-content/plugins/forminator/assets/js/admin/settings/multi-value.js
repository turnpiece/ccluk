( function ($) {
	define([
		'admin/settings/multi-setting',
		'text!tpl/fields.html',
	], function( MultiSetting, fieldsTpl ) {

		 return MultiSetting.extend({
			multiple: false,

			events: {
				'click .wpmudev-add-option': 'add_option',
				'click .wpmudev-action--kill': 'delete_option',
				'keyup .wpmudev-item--label .wpmudev-input': 'update_label',
				'keyup .wpmudev-item--value .wpmudev-input': 'update_value'
			},

			className: 'wpmudev-option',

			render: function () {
				this.$el.html('');
				this.$el.append( this.get_label_html() );
				this.$el.append( this.get_field_html() );

				var values = this.get_saved_value() || [];

				if( !_.isEmpty( values ) ) {
					this.$el.find( '.wpmudev-multivalue' ).addClass( 'wpmudev-has_options' );
				}

				this.trigger( 'rendered', this.get_value() );

				this.on_render();
			},

			get_field_html: function () {
				var childs = this.get_values_html(),
					$mainTpl = Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-multiple-tpl' ).html())
				;

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
					$rowTpl = Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-multiple-row-tpl' ).html())
				;

				return $rowTpl({
					row: value,
					index: index
				});
			},

			update_label: function ( e ) {
				var index = $( e.target ).closest( '.wpmudev-multivalue--item' ).data( 'index'),
					value = $( e.target ).val(),
					saved_value = this.get_saved_value() || []
				;

				saved_value[ index ].label = value;
				this.save_value( saved_value );

				this.trigger( 'updated', saved_value );
			},

			update_value: function ( e ) {
				var index = $( e.target ).closest( '.wpmudev-multivalue--item' ).data( 'index'),
					value = $( e.target ).val(),
					saved_value = this.get_saved_value() || []
				;

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
				var index = $( e.target ).closest( '.wpmudev-multivalue--item' ).data( 'index'),
					saved_value = this.get_saved_value() || []
				;

				saved_value.splice( index, 1 );
				this.save_value( saved_value );

				this.trigger( 'updated', saved_value );

				this.render();
			},

			move_option: function ( item, index ) {
				var saved_value = this.get_saved_value() || [],
					old_index = item.data( 'index')
				;

				saved_value.splice( index, 0, saved_value.splice( old_index, 1 )[0] );
				this.save_value( saved_value );

				this.trigger( 'updated', saved_value );

				this.render();
			},

			on_render: function () {
				var self = this;

				setTimeout( function () {
					$( '.wpmudev-multivalue--items' ).sortable({
						update: function( e, ui ) {
							self.move_option( ui.item, ui.item.index() );
						}
					});
				}, 100 );
			}
		});
	});
})( jQuery );
