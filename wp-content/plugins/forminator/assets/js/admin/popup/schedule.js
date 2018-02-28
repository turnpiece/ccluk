(function ($) {
	define([
		'text!tpl/dashboard.html',
	], function (popupTpl) {
		return Backbone.View.extend({
			className: 'wpmudev-section--popup',

			popupTpl: Forminator.Utils.template($(popupTpl).find('#forminator-exports-schedule-popup-tpl').html()),

			events: {
				'change select[name="interval"]': "on_change_interval"
			},

			render: function () {

				this.$el.html(this.popupTpl({}));

				// Init select2
				Forminator.Utils.init_select2();
				var data = forminator_l10n.exporter;
				this.$el.find('select[name="interval"]').change();
				if (data.email === null) {
					return;
				}
				this.$el.find('select[name="interval"]').val(data.interval);
				this.$el.find('select[name="day"]').val(data.day);
				this.$el.find('select[name="hour"]').val(data.hour);

			},

			on_change_interval: function(e) {
				//hide column
				this.$el.find('select[name="day"]').parent().hide();
				if(e.target.value === 'weekly' || e.target.value === 'monthly') {
					this.$el.find('select[name="day"]').parent().show();
				}

			}
		});
	});
})(jQuery);
