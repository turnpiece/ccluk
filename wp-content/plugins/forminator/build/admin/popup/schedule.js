!function(e){formintorjs.define(["text!tpl/dashboard.html"],function(n){return Backbone.View.extend({className:"wpmudev-section--popup",popupTpl:Forminator.Utils.template(e(n).find("#forminator-exports-schedule-popup-tpl").html()),events:{'change select[name="interval"]':"on_change_interval","change .wpmudev-switch-normal .forminator-field-singular":"on_change","click .wpmudev-toggle .wpmudev-label-normal":"click_label"},render:function(){this.$el.html(this.popupTpl({})),Forminator.Utils.init_select2();var e=forminator_l10n.exporter;this.$el.find('input[name="enabled"]').prop("checked",e.enabled),e.enabled?this.$el.find('input[name="email"]').prop("required",!0):this.$el.find(".wpmudev-box-gray").hide(),this.$el.find('select[name="interval"]').change(),null!==e.email&&(this.$el.find('select[name="interval"]').val(e.interval),this.$el.find('select[name="day"]').val(e.day),this.$el.find('select[name="hour"]').val(e.hour),"weekly"!==e.interval&&"monthly"!==e.interval||this.$el.find('select[name="day"]').parent().show())},on_change_interval:function(e){this.$el.find('select[name="day"]').parent().hide(),"weekly"!==e.target.value&&"monthly"!==e.target.value||this.$el.find('select[name="day"]').parent().show()},click_label:function(e){e.preventDefault(),this.$el.find(".wpmudev-switch-normal .forminator-field-singular").click()},get_value:function(){var e=this.$el.find(":checkbox"),n=e.val();return e.is(":checked")&&n?n:""},on_change:function(){this.trigger("changed",this.get_value()),this.$el.find(".wpmudev-box-gray").toggle(),this.$el.find('input[name="email"]').prop("required","true"===this.get_value())}})})}(jQuery);