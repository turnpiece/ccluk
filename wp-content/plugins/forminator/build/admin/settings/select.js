!function(t){formintorjs.define(["admin/settings/multi-setting"],function(e){return e.extend({multiple:!1,className:"wpmudev-option",events:{"change .wpmudev-select":"on_change"},get_field_html:function(){if(this.options.containerSmall)var t={class:"forminator-field-singular wpmudev-select wpmudev-select--sm",id:this.get_field_id(),name:this.get_name()};else var t={class:"forminator-field-singular wpmudev-select",id:this.get_field_id(),name:this.get_name()};return this.options.dataAttr&&_.each(this.options.dataAttr,function(e,i){t["data-"+i]=e}),"<select "+this.get_field_attr_html(t)+">"+this.get_values_html()+"</select>"},get_value_html:function(t,e){var i={value:t.value,id:this.get_field_id()},n=this.get_saved_value();return t.disabled&&(i.disabled="disabled"),n===t.value&&(i.selected="selected"),"<option "+this.get_field_attr_html(i)+">"+t.label+"</option>"},on_render:function(){var e=this;Forminator.Utils.init_select2(),this.options.ajax&&t.ajax({url:Forminator.Data.ajaxUrl,type:"POST",data:{action:e.options.ajax_action},success:function(t){e.get_field().append(t.data),Forminator.Utils.init_select2()}})},on_change:function(){this.trigger("changed",this.get_value())},trigger_show:function(t){this.toggle_field(t),this.options.show&&this.options.show(t)},toggle_field:function(t){var e=this.$el.closest(".wpmudev-toggle--box"),i=e.find(".forminator-dependant").closest(".wpmudev-option");if(""===t)return!1;var n=e.find("."+t).closest(".wpmudev-option");i.length>0&&i.hide(),n.length>0&&n.show()}})})}(jQuery);