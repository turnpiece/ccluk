!function(t){formintorjs.define(["admin/settings/multi-setting","text!tpl/fields.html"],function(e,i){return e.extend({multiple:!1,events:{"click .wpmudev-add-date":"add_option","click .wpmudev-choice-kill":"delete_option"},className:"wpmudev-option forminator-field-wrap-multidate",get_field_html:function(){var e=this.get_values_html();return Forminator.Utils.template(t(i).find("#settings-field-date-multiple-tpl").html())({values:e})},get_values_html:function(){var t=this.get_saved_value()||[];return _.map(t,this.get_value_html,this).join("")},get_value_html:function(e,a){this.get_saved_value();return Forminator.Utils.template(t(i).find("#settings-field-date-multiple-row-tpl").html())({value:e,index:a})},add_option:function(t){var e=this.get_saved_value()||[],i=this.$el.find(".wpmudev-option--datepicker"),a=i.val();e.push({value:a}),this.save_value(e),i.val(""),this.trigger("updated",e),this.render()},delete_option:function(e){var i=t(e.target).closest(".wpmudev-date-choice").data("index"),a=this.get_saved_value()||[];a.splice(i,1),this.save_value(a),this.trigger("updated",a),this.render()},on_render:function(){var e=this,i=this.options.dateFormat?this.options.dateFormat:"d MM yy";setTimeout(function(){e.get_field().datepicker({beforeShow:function(e,i){t("#ui-datepicker-div").addClass("wpmudev-option--datepicker-cal")},dateFormat:i})},50)}})})}(jQuery);