( function ($) {
	define([
		'admin/settings/toggle-container',
		'text!tpl/fields.html',
	], function( ToggleContainer, fieldsTpl ) {

		return ToggleContainer.extend({
			multiple: false,

			events: {
				'change .wpmudev-toggle--design .forminator-field-singular': 'on_change',
				'click .wpmudev-label-name': 'click_label',
				'click .wpmudev-toggle-field': 'toggle_field'
			},

			className: 'wpmudev-option',

			click_label: function(e) {
				e.preventDefault();

				// Simulate label click
				this.$el.find( '.wpmudev-toggle--design .forminator-field-singular' ).click();
			},

			get_value_html: function ( value, index ) {
				var attr = {
					'type': 'checkbox',
					'class': 'forminator-field-singular forminator-field-required',
					'id': this.get_field_id(),
					'name': this.get_name(),
					'value': value.value,
					'title': value.label
				};

				if ( this.get_saved_value() === value.value )
					attr.checked = 'checked';

				return '<div class="wpmudev-toggle--design">' +
					'<input ' + this.get_field_attr_html( attr ) + ' />' +
					'<label for="' + this.get_field_id() +'" class="wpmudev-label-name"></label>' +
					'</div>' +
					'<label for="' + this.get_field_id() +'" class="wpmudev-toggle--label wpmudev-label--sm wpmudev-label-name">' + value.label + '</label>';
			},

			get_field_html: function () {
				var attr = {
					'class': 'wpmudev-toggle',
				};

				return '<div class="wpmudev-multiname">' +
					'<div class="wpmudev-multiname--action">' +
					'<div class="wpmudev-action--text">' +
					'<div ' + this.get_field_attr_html( attr ) + '>' + this.get_values_html() + '</div>' +
					'</div>' +
					'<button class="wpmudev-action--button wpmudev-toggle-field">' +
					'<span class="wpmudev-icon--plus" aria-hidden="true"></span>' +
					'<span class="wpmudev-text">Open field</span>' +
					'</button>' +
					'</div>' +
					'<div class="wpmudev-multiname--content"></div>' +
					'</div>';
			},

			on_change: function () {
				this.trigger( 'changed', this.get_value() );
			},

			trigger_show: function ( value ) {
				if ( this.options.show )
					this.options.show( value );
			},

			toggle_field: function ( e ) {
				e.preventDefault();

				var $container = this.$el.find( '.wpmudev-multiname' );
				$container.toggleClass( 'wpmudev-is_active' );
			}
		});
	});
})(jQuery);
