!function(t){formintorjs.define(["admin/settings/toggle-container","text!tpl/fields.html"],function(e,a){return e.extend({multiple:!1,events:{"click .wpmudev-add-variation":"add_option","click .wpmudev-action--kill":"delete_option","change .wpmudev-add-name":"update_label","change .wpmudev-add-price":"update_value"},className:"wpmudev-option forminator-field-wrap-productvar",get_field_html:function(){var e=this.get_values_html();return Forminator.Utils.template(t(a).find("#settings-field-product-tpl").html())({childs:e})},get_values_html:function(){var t=this.get_saved_value()||[];return _.map(t,this.get_value_html,this).join("")},get_value_html:function(e,i){this.get_saved_value();return Forminator.Utils.template(t(a).find("#settings-field-product-row-tpl").html())({row:e,index:i})},update_label:function(e){var a=t(e.target).closest(".wpmudev-list--item").data("index"),i=t(e.target).val(),l=this.get_saved_value()||[];l[a].label=i,this.save_value(l),this.trigger("updated",l)},update_value:function(e){var a=t(e.target).closest(".wpmudev-list--item").data("index"),i=t(e.target).val(),l=this.get_saved_value()||[];l[a].value=i,this.save_value(l),this.trigger("updated",l)},add_option:function(t){var e=this.get_saved_value()||[];e.push({label:"",value:""}),this.save_value(e),this.trigger("updated",e),this.render()},delete_option:function(e){var a=t(e.target).closest(".wpmudev-list--item").data("index"),i=this.get_saved_value()||[];i.splice(a,1),this.save_value(i),this.trigger("updated",i),this.render()}})})}(jQuery);