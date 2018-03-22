!function(e){formintorjs.define(["text!tpl/builder-sidebar.html"],function(i){return Backbone.View.extend({conditionsTpl:Forminator.Utils.template(e(i).find("#builder-sidebar-conditions-popup-tpl").html()),ruleTpl:Forminator.Utils.template(e(i).find("#builder-sidebar-conditions-rule-tpl").html()),wrappers:!1,conditions:!1,fields:[],className:"wpmudev-section--conditions",events:{"click .wpmudev-action-done":"close","click .wpmudev-list--add .wpmudev-button":"new","click .wpmudev-delete-action":"delete","change .wpmudev-conditions--field":"update_field","change .wpmudev-conditions--action":"update_rule","change .wpmudev-conditions--values":"update_value","change .wpmudev-conditions--input":"update_value","change .wpmudev-condition--actions":"update_actions","change .wpmudev-condition--rules":"update_rules"},initialize:function(e){this.wrappers=e.wrappers,this.fields=Forminator.Utils.get_fields(this.wrappers),this.conditions=this.model.get("conditions"),this.render()},render:function(){this.$el.html(this.conditionsTpl({data:this.model.toJSON()})),this.render_conditions(),this.init_select2()},render_conditions:function(){var i=this;_.isUndefined(this.conditions)||this.conditions.each(function(t,n){var d=i.conditions.model_index(t),o=_.where(i.fields,{element_id:t.get("element_id")})[0]||{},l=i.ruleTpl({index:d,fields:_.filter(i.fields,function(e){return e.element_id!==i.model.get("element_id")}),field:o,condition:t.toJSON()}),s=e(l),a=i.$el.find(".wpmudev-list--rules");s.appendTo(a),t.get("element_id")?o.hasOptions?(s.find(".wpmudev-conditions--input-wrapper").hide(),i.update_field_values(s,o,t)):(s.find(".wpmudev-conditions--values-wrapper").hide(),i.update_field_input(s,o,t)):(s.find(".wpmudev-conditions--action").prop("disabled",!0),s.find(".wpmudev-conditions--values-wrapper").hide(),s.find(".wpmudev-conditions--input-wrapper").hide())})},update_field:function(i){var t=e(i.target),n=t.val(),d=(t.closest(".wpmudev-rule--new"),this.get_model(t));""!==n?d.set("element_id",n):(d.set("element_id",n),d.set("rule","is"),d.set("value",!1)),this.render()},update_field_values:function(e,i,t){var n=e.find(".wpmudev-conditions--values"),d=i,o=t.get("value");n.empty(),d&&d.values.length&&(n.append(new Option(Forminator.l10n.conditions.select_option,"")),_.each(d.values,function(e,i){var t=e.value;t||(t=e.label),n.append(new Option(e.label,t,!1,o===t))}))},update_field_input:function(e,i,t){var n=e.find(".wpmudev-conditions--input"),d=t.get("value");n.val(d)},update_rule:function(i){var t=e(i.target),n=t.val();this.get_model(t).set("rule",n)},update_value:function(i){var t=e(i.target),n=t.val();this.get_model(t).set("value",n)},update_actions:function(i){var t=e(i.target),n=t.val();this.model.set("condition_action",n)},update_rules:function(i){var t=e(i.target),n=t.val();this.model.set("condition_rule",n)},new:function(e){e.preventDefault(),new_condition=new Forminator.Models.Condition({element_id:"",rule:"is",value:""}),this.conditions.add(new_condition,{silent:!0}),this.render()},delete:function(i){i.preventDefault();var t=e(i.target),n=this.get_model(t);this.conditions.remove(n,{silent:!0}),this.render()},get_model:function(e){var i=e.closest(".wpmudev-rule--new").data("index");return this.conditions.get_by_index(i)},close:function(e){e.preventDefault(),this.trigger("conditions:modal:close"),Forminator.Events.trigger("sidebar:settings:updated",this.model)},init_select2:function(){Forminator.Utils.init_select2()}})})}(jQuery);