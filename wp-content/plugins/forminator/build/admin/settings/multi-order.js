!function(t){formintorjs.define(["admin/settings/multi-setting","text!tpl/fields.html"],function(e,i){return e.extend({multiple:!1,events:{},className:"wpmudev-multiorder",init:function(t){this.results=this.model.get("results"),this.listenTo(Forminator.Events,"forminator:quiz:results:updated",this.render)},get_values_html:function(){var t=this,e="";return this.results.sort(),this.results.filter(function(i,r){e+=t.get_value_html(i,r)}),e},get_value_html:function(e,r){var n=Forminator.Utils.template(t(i).find("#settings-field-order-list-row-tpl").html());return e=e.toJSON(),n({row:e,index:r})},get_field_html:function(){var e=this.get_values_html();return Forminator.Utils.template(t(i).find("#settings-field-order-list-tpl").html())({childs:e})},get_index:function(t){return t.closest(".wpmudev-multiorder--item").data("index")},get_model:function(t){var e=this.get_index(t);return this.results.get_by_index(e)},move_option:function(e,i){var r=this.get_model(t(e)),n=r.get("order");this.results.move_to(n,i),Forminator.Events.trigger("forminator:quiz:results:order:updated")},on_render:function(){var e=this;setTimeout(function(){t(".wpmudev-multiorder--wrapper").sortable({update:function(t,i){e.move_option(i.item,i.item.index(),i.items)}})},100)}})})}(jQuery);