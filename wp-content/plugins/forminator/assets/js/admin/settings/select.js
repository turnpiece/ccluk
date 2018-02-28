( function ($) {
	define([
		'admin/settings/multi-setting'
	], function( MultiSetting ) {

		return MultiSetting.extend({
			multiple: false,

			className: 'wpmudev-option',

			events: {
				'change .wpmudev-select': 'on_change',
			},

			get_field_html: function(){
				if ( this.options.containerSmall ) {

					var attr = {
						'class': 'forminator-field-singular wpmudev-select wpmudev-select--sm',
						'id': this.get_field_id(),
						'name': this.get_name(),
					};

				} else {

					var attr = {
						'class': 'forminator-field-singular wpmudev-select',
						'id': this.get_field_id(),
						'name': this.get_name(),
					};

				}

				if ( this.options.dataAttr ) {
					_.each(this.options.dataAttr, function (value, key) {
						attr['data-' + key] = value;
					});
				}

				return '<select ' + this.get_field_attr_html( attr ) + '>' + this.get_values_html() + '</select>';
			},

			get_value_html: function ( value, index ) {
				var attr = {
					'value': value.value,
					'id': this.get_field_id(),
				};

				var saved_value = this.get_saved_value();

				if ( value.disabled ) {
					attr.disabled = 'disabled';
				}

				if ( saved_value === value.value ) {
					attr.selected = 'selected';
				}

				return '<option ' + this.get_field_attr_html( attr ) + '>' + value.label + '</option>';
			},

			on_render: function () {
				var self = this;

				Forminator.Utils.init_select2();

				if( this.options.ajax ) {
					$.ajax({
						url: Forminator.Data.ajaxUrl,
						type: "POST",
						data: {
							action: self.options.ajax_action
						},
						success: function( result ) {
							self.get_field().append( result.data );

							// Init select2
							Forminator.Utils.init_select2();
						}
					});
				}
			},

			on_change: function () {
				this.trigger( 'changed', this.get_value() );
			}
		});
	});
})( jQuery );
