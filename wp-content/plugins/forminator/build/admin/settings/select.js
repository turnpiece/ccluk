!function(t){formintorjs.define(["admin/settings/multi-setting"],function(e){return e.extend({multiple:!1,className:"wpmudev-option",events:{"change .wpmudev-select":"on_change"},get_field_html:function(){if(this.options.containerSmall)var t={class:"forminator-field-singular wpmudev-select wpmudev-select--sm",id:this.get_field_id(),name:this.get_name()};else var t={class:"forminator-field-singular wpmudev-select",id:this.get_field_id(),name:this.get_name()};return this.options.dataAttr&&_.each(this.options.dataAttr,function(e,i){t["data-"+i]=e}),"<select "+this.get_field_attr_html(t)+">"+this.get_values_html()+"</select>"},get_value_html:function(t,e){var i={value:t.value,id:this.get_field_id()},a=this.get_saved_value();return t.disabled&&(i.disabled="disabled"),a===t.value&&(i.selected="selected"),"<option "+this.get_field_attr_html(i)+">"+t.label+"</option>"},on_render:function(){var e=this;Forminator.Utils.init_select2(),this.options.ajax&&t.ajax({url:Forminator.Data.ajaxUrl,type:"POST",data:{action:e.options.ajax_action},success:function(t){e.get_field().append(t.data),Forminator.Utils.init_select2()}})},on_change:function(){this.trigger("changed",this.get_value())}})})}(jQuery);