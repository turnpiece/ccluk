(function ($) {
	define([
		'admin/settings/text'
	], function (Text) {

		return Text.extend({
			className: 'wpmudev-option',

			on_render: function () {
				var self = this;
				var timer = null;
				this.get_field().keyup(function () {


				}).trigger('keyup').change(function () {
					self.trigger('changed', self.get_value());
				}).keydown(function(){
					clearTimeout(timer);
					var input = this;
					timer = setTimeout(function () {
						self.check_input_range(input);
					}, 325);
				});
			},

			check_input_range: function (input) {
				var $elRef = null;
				if (!_.isEmpty(this.options.min)) {
					if (_.isNumber(this.options.min)) {
						if (input.value < this.options.min) {
							$(input).val(this.options.min);
						}
					} else {
						//its element reference
						$elRef = $(this.options.min);
						if ($elRef.length > 0) {
							var minValue = parseInt($elRef.first().val());
							if (input.value < minValue) {
								$(input).val(minValue);
							}
						}
					}
				}

				if (!_.isEmpty(this.options.max)) {
					if (_.isNumber(this.options.max)) {
						if (input.value > this.options.max) {
							$(input).val(this.options.max);
						}
					} else {
						//its element reference
						$elRef = $(this.options.max);
						if ($elRef.length > 0) {
							var maxValue = parseInt($elRef.first().val());
							if (input.value > maxValue) {
								$(input).val(maxValue);
							}
						}
					}
				}

			},

			get_field_html: function () {
				var attr = {
					'type': 'number',
					'class': 'forminator-field-singular wpmudev-input',
					'id': this.get_field_id(),
					'name': this.get_name(),
					'value': this.get_saved_value(),
					'placeholder': '10',
					'title': this.label
				};

				if (_.isNumber(this.options.min)) {
					attr.min = this.options.min;
				}

				if (_.isNumber(this.options.max)) {
					attr.max = this.options.max;
				}

				var description = '';

				if (this.options.description)
					description = '<span class="field-description">' + this.options.description + '</span>';

				return '<input ' + this.get_field_attr_html(attr) + ' />' + description;
			}

		});

	});
})(jQuery);
