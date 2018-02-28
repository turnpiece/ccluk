(function($){
	define([
		'admin/settings/multi-setting'
	], function( MultiSetting ){

		return MultiSetting.extend({
			multiple: true,

			className: 'wpmudev-option',

			events: {
				'click .wpmudev-tab label': 'click_label'
			},

			click_label: function ( e ) {
				e.preventDefault();

				// Simulate label click
				$( e.target ).closest( '.wpmudev-tab' ).find( '.forminator-field-singular' ).click();
			},

			render: function () {
				var self = this;

				this.$el.html('');

				if ( this.label ) {
					this.$el.append( this.get_label_html() );
				}

				this.$el.append( this.get_field_html() );

				this.$el.on( 'change', '.wpmudev-tab input', function() {

					self.$el.find( '.wpmudev-tab' ).each( function(){

						if ( $( this ).find( 'input:checked' ).size() > 0 ) {
							$( this ).addClass( 'wpmudev-is_active' );
						} else {
							$( this ).removeClass( 'wpmudev-is_active' );
						}

					});

					self.trigger( 'changed', self.get_value() );

				});

				this.trigger( 'rendered' );

				this.on_render();
			},

			get_field_html: function(){
				var container_size = '',
					items_size = '',
					items_color = '';

				if ( this.options.containerSize )
					container_size = ' wpmudev-tabs--' + this.options.containerSize;

				if ( this.options.sizeLarge )
					items_size = ' wpmudev-tabs--lg';

				if ( this.options.sizeSmall )
					items_size = ' wpmudev-tabs--sm';

				if ( this.options.itemsColor )
					items_color = ' wpmudev-tabs--' + this.options.itemsColor;

				return '<div class="wpmudev-tabs' + container_size + '' + items_size + '' + items_color + '">' + this.get_values_html() + '</div>';
			},

			get_value_html: function ( value, index ) {
				var classes = 'wpmudev-tab',
					saved_value = this.get_saved_value(),
					attr = {
						'type': 'radio',
						'class': 'forminator-field-singular forminator-field-required',
						'id': this.get_field_id(),
						'name': this.get_name(),
						'value': value.value,
						'title': value.label
					}
				;

				if ( saved_value == value.value ) {
					attr.checked = 'checked';
				}

				if ( value.checked ) attr.checked = 'checked';

				if ( attr.checked ){
					classes += ' wpmudev-is_active';
				}

				if ( this.options.hasIcon ) {

					classes += ' wpmudev-has_icon';
					iclasses = 'wpdui-icon';

					if ( this.options.iconTop ) {
						classes += ' wpmudev-icon_top';
					}

					if ( value.iconClass ) {
						iclasses += ' ' + value.iconClass;
					}

					return '<div class="' + classes + '"><input ' + this.get_field_attr_html(attr) + '><label for="' + this.get_field_id() + '" class="' + iclasses + '"><span>' + value.label + '</span></label></div>';

				} else {

					return '<div class="' + classes + '"><input ' + this.get_field_attr_html(attr) + '><label for="' + this.get_field_id() + '">' + value.label + '</label></div>';

				}
			},

			get_value: function(){
				var $field = this.$el.find( ":checked" );
				return $field.val();
			},

			on_change: function(){
				this.trigger( 'changed', this.get_value() );
			}

		});

	});

})(jQuery);
