( function ($) {
	define([
		'admin/settings/toggle'
	], function( Toggle ) {

		return Toggle.extend({
			multiple: false,

			events: {
				'change .wpmudev-switch-container .forminator-field-singular': 'on_change',
				'click .wpmudev-label-container': 'click_label'
			},

			className: 'wpmudev-option',

			click_label: function(e){
				e.preventDefault();

				// Simulate label click
				this.$el.find( '.wpmudev-switch-container .forminator-field-singular' ).click();
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

					return '<div class="wpmudev-toggle--design wpmudev-switch-container">' +
						'<input ' + this.get_field_attr_html( attr ) + ' />' +
						'<label for="' + this.get_field_id() +'" class="wpmudev-label-container"></label>' +
						'</div>';

				} else {

					return '<div class="wpmudev-toggle--design wpmudev-switch-container">' +
						'<input ' + this.get_field_attr_html( attr ) + ' />' +
						'<label for="' + this.get_field_id() +'" class="wpmudev-label-container"></label>' +
						'</div>' +
						'<label for="' + this.get_field_id() +'" class="wpmudev-toggle--label' + labelClass + ' wpmudev-label-container">' + value.label + '</label>';

				}
			},

			get_field_html: function(){
				var has_content = '',
					container_class = '';

				if ( this.options.has_content )
					has_content = ' wpmudev-has_content';

				if ( this.options.containerClass )
					container_class = ' ' + this.options.containerClass;

				return '<div class="wpmudev-toggle' + has_content + '">' +
					this.get_values_html() +
					'<div class="wpmudev-toggle--box' + container_class + ' wpmudev-option--switch_content"></div>' +
					'</div>';
			},

			on_change: function () {
				this.trigger( 'changed', this.get_value() );
			},

			get_value: function () {
				var $field = this.get_field(),
					value = $field.val()
				;

				return $field.is(":checked") && value ? value : '';
			},

			trigger_show: function ( value ) {
				this.toggle_wrapper( value );

				if ( this.options.show )
					this.options.show( value );
			},

			toggle_wrapper: function () {
				var self = this;

				if ( this.get_value() === "true" ) {

					if ( this.$el.find( '.wpmudev-toggle' ).hasClass( 'wpmudev-has_content' ) ){
						this.$el.find( '.wpmudev-has_content' ).addClass( 'wpmudev-is_active' );
					}

					if ( this.$el.find( '.wpmudev-toggle--box' ).hasClass( 'wpmudev-has_cols' ) ) {
						this.$el.find( '.wpmudev-option--switch_content' ).css('display', 'flex');
					} else {
						this.$el.find( '.wpmudev-option--switch_content' ).show();
					}

					if( this.options.hasOpposite ) {
						setTimeout( function () {
							$( self.options.hasOpposite ).hide();
						}, 100 );
					}

				} else {

					if ( this.$el.find( '.wpmudev-toggle' ).hasClass( 'wpmudev-has_content' ) ){
						this.$el.find( '.wpmudev-has_content' ).removeClass( 'wpmudev-is_active' );
					}

					this.$el.find( '.wpmudev-option--switch_content' ).hide();

					if( this.options.hasOpposite ) {
						setTimeout( function () {
							$( self.options.hasOpposite ).show();
						}, 100 );
					}

				}

			}
		});
	});
})( jQuery );
