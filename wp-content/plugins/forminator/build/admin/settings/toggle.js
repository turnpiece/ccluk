!function(e){formintorjs.define(["admin/settings/multi-setting"],function(e){return e.extend({multiple:!1,events:{"change .wpmudev-switch-normal .forminator-field-singular":"on_change","click .wpmudev-toggle .wpmudev-label-normal":"click_label"},className:"wpmudev-option",click_label:function(e){e.preventDefault(),this.$el.find(".wpmudev-switch-normal .forminator-field-singular").click()},get_value_html:function(e,l){var i={type:"checkbox",class:"forminator-field-singular",id:this.get_field_id(),name:this.get_name(),value:e.value,title:e.label};return!_.isUndefined(e.disabled)&&e.disabled&&(i.disabled="disabled"),this.get_saved_value()===e.value&&(i.checked="checked"),labelClass="",e.labelSmall&&(labelClass=" wpmudev-label--sm"),e.labelBig&&(labelClass=" wpmudev-label--big"),e.hideTL?'<div class="wpmudev-toggle--design wpmudev-switch-normal"><input '+this.get_field_attr_html(i)+' /><label for="'+this.get_field_id()+'" class="wpmudev-label-normal"></label></div>':'<div class="wpmudev-toggle--design wpmudev-switch-normal"><input '+this.get_field_attr_html(i)+' /><label for="'+this.get_field_id()+'" class="wpmudev-label-normal"></label></div><label for="'+this.get_field_id()+'" class="wpmudev-toggle--label'+labelClass+' wpmudev-label-normal">'+e.label+"</label>"},get_value:function(){var e=this.$el.find(":checkbox"),l=e.val();return e.is(":checked")&&l?l:""},on_change:function(){this.trigger("changed",this.get_value())}})})}(jQuery);