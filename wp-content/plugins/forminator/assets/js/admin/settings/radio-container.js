(function ($) {
	define([
		'admin/settings/multi-setting',
		'text!tpl/fields.html',
	], function( MultiSetting, fieldsTpl ) {

		 return MultiSetting.extend({
			multiple: true,

			events: {
				'click .wpmudev-tab > label': 'click_label'
			},

			className: 'wpmudev-option',

			click_label: function ( e ) {
				e.preventDefault();

				// Simulate label click
				$( e.target ).closest( '.wpmudev-tab' ).find( '.forminator-field-singular' ).click();
			},

			render: function () {
				var self = this;
				this.$el.html('');

				if( this.label ) {
					this.$el.append( this.get_label_html() );
				}

				this.$el.append( this.get_field_html() );

				this.$el.on( 'change', '.wpmudev-tab input', function () {
                 self.$el.find( '.wpmudev-tab' ).each( function () {
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

			get_field_html: function () {
				var attr = {
					'class': 'wpmudev-tabs',
				};

				return '<div ' + this.get_field_attr_html( attr ) + '>' + this.get_values_html() + '</div><div class="wpmudev-option--tabs_content">' + this.get_values_container_html() + '</div>';
			},

			get_values_container_html: function () {
				return _.map( this.options.values, this.get_value_container_html, this ).join('');
			},

			get_value_container_html: function ( value, index ) {
				var attr = {
					'class': 'wpmudev-option--tab_content wpmudev-radio-tab-' + value.value,
				}

				return '<div ' + this.get_field_attr_html( attr ) + '></div>';
			},

			get_value_html: function ( value, index ) {
				var classes = 'wpmudev-tab',
					attr = {
						'type': 'radio',
						'class': 'forminator-field-singular forminator-field-required',
						'id': this.get_field_id(),
						'name': this.get_name(),
						'value': value.value,
						'title': value.label
					};

				var saved_value = this.get_saved_value();

				if ( saved_value == value.value ) {
				  attr.checked = 'checked';
				}
				if ( value.checked ) attr.checked = 'checked';
				if ( attr.checked ) {
				  classes += ' wpmudev-is_active';
				}

				return '<div class="' + classes + '"><input ' + this.get_field_attr_html( attr ) + '><label for="' + this.get_field_id() + '">' + value.label + '</label></div>';
			},

			get_value: function () {
				var $field = this.$el.find( ":checked" );

				return $field.val();
			},

			on_change: function () {
				this.trigger( 'changed', this.get_value() );
			},

			trigger_show: function ( value ) {
				this.toggle_wrapper( value );

				if ( this.options.show )
					this.options.show( value );
			},

			toggle_wrapper: function ( value ) {
				this.$el.find( '.wpmudev-option--tab_content' ).hide();
				this.$el.find( '.wpmudev-radio-tab-' + this.get_value() ).show();
			}
		});
	});
})(jQuery);
