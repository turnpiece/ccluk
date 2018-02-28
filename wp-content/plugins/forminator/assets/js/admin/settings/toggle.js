(function($){
	define([
		'admin/settings/multi-setting'
	], function( MultiSetting ) {

		return MultiSetting.extend({
			multiple: false,

			events: {
				'change .wpmudev-switch-normal .forminator-field-singular': 'on_change',
				'click .wpmudev-toggle .wpmudev-label-normal': 'click_label'
			},

			className: 'wpmudev-option',

			click_label: function(e){
				e.preventDefault();

				// Simulate label click
				this.$el.find( '.wpmudev-switch-normal .forminator-field-singular' ).click();
			},

			get_value_html: function( value, index ){
				var attr = {
					'type': 'checkbox',
					'class': 'forminator-field-singular',
					'id': this.get_field_id(),
					'name': this.get_name(),
					'value': value.value,
					'title': value.label
				};

				if ( this.get_saved_value() === value.value )
					attr.checked = 'checked';

				labelClass = '';

				if ( value.labelSmall ){
					labelClass = ' wpmudev-label--sm';
				}

				if ( value.labelBig ){
					labelClass = ' wpmudev-label--big';
				}

				if ( value.hideTL ){

					return '<div class="wpmudev-toggle--design wpmudev-switch-normal">' +
						'<input ' + this.get_field_attr_html( attr ) + ' />' +
						'<label for="' + this.get_field_id() +'" class="wpmudev-label-normal"></label>' +
					'</div>';

				} else {

					return '<div class="wpmudev-toggle--design wpmudev-switch-normal">' +
						'<input ' + this.get_field_attr_html( attr ) + ' />' +
						'<label for="' + this.get_field_id() +'" class="wpmudev-label-normal"></label>' +
					'</div>' +
					'<label for="' + this.get_field_id() +'" class="wpmudev-toggle--label' + labelClass + ' wpmudev-label-normal">' + value.label + '</label>';

				}
			},

			get_value: function(){
				var $field = this.$el.find( ":checkbox" ),
					value = $field.val();

				return $field.is( ":checked" ) && value ? value : '';
			},

			on_change: function(){
				this.trigger( 'changed', this.get_value() );
			}
		});

	});

})( jQuery );
