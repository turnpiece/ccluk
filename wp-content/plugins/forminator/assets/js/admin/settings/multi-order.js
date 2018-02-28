( function($){
	define([
		'admin/settings/multi-setting',
		'text!tpl/fields.html',
	], function( MultiSetting, fieldsTpl ){

		return MultiSetting.extend({
			multiple: false,

			events: {},

			className: 'wpmudev-multiorder',

			init: function ( options ) {
				this.results = this.model.get( 'results' );

				this.listenTo( Forminator.Events, "forminator:quiz:results:updated", this.render );
			},

			get_values_html: function () {
				var self = this,
					html = ''
				;

				this.results.sort();

				this.results.filter( function( model, index ) {
					html = html + self.get_value_html( model, index );
				});

				return html;
			},

			get_value_html: function ( value, index ) {
				var $rowTpl = Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-order-list-row-tpl' ).html());

				value = value.toJSON();

				return $rowTpl({
					row: value,
					index: index
				});
			},

			get_field_html: function () {
				var childs = this.get_values_html(),
					$mainTpl	= Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-order-list-tpl' ).html() );

				return $mainTpl({
					childs: childs,
				});
			},

			get_index: function ( $row ) {
				return $row.closest( '.wpmudev-multiorder--item' ).data( 'index' );
			},

			get_model: function ( $row ) {
				var index = this.get_index( $row );
				return this.results.get_by_index( index );
			},

			move_option: function ( item, index ) {
				var result = this.get_model( $( item ) ),
					old_index = result.get( 'order' )
				;

				this.results.move_to(old_index,index);
				Forminator.Events.trigger( "forminator:quiz:results:order:updated" );
			},

			on_render: function () {
				var self = this;

				setTimeout( function () {
					$( '.wpmudev-multiorder--wrapper' ).sortable({
						update: function( e, ui ) {
							self.move_option( ui.item, ui.item.index(), ui.items );
						}
					});
				}, 100 );
			}

		});

	});

})(jQuery);
