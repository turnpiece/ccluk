!function(e){formintorjs.define(["admin/settings/setting"],function(e){return e.extend({className:"sui-form-field",on_render:function(){var e=this;this.get_field().keyup(function(){}).trigger("keyup").change(function(){e.trigger("changed",e.get_value())})},get_field_html:function(){var e={id:this.get_field_id(),name:this.get_name(),placeholder:this.options.placeholder?this.options.placeholder:""},t="";this.options.description&&(t='<span class="sui-description">'+this.options.description+"</span>");var i="";return this.options.error&&(i='<span class="sui-error-message">'+this.options.error+"</span>"),'<textarea class="sui-form-control" '+this.get_field_attr_html(e)+">"+this.get_saved_value()+"</textarea>"+i+t}})})}(jQuery);