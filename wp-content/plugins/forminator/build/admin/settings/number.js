!function(i){formintorjs.define(["admin/settings/text"],function(t){return t.extend({className:"wpmudev-option",on_render:function(){var i=this,t=null;this.get_field().keyup(function(){}).trigger("keyup").change(function(){i.trigger("changed",i.get_value()),clearTimeout(t);var n=this;t=setTimeout(function(){i.check_input_range(n)},325)}).keydown(function(){clearTimeout(t);var n=this;t=setTimeout(function(){i.check_input_range(n)},325)})},check_input_range:function(t){var n=null;if(!_.isEmpty(this.options.min))if(_.isNumber(this.options.min))t.value<this.options.min&&i(t).val(this.options.min);else if(n=i(this.options.min),n.length>0){var e=parseInt(n.first().val());t.value<e&&i(t).val(e)}if(!_.isEmpty(this.options.max))if(_.isNumber(this.options.max))t.value>this.options.max&&i(t).val(this.options.max);else if(n=i(this.options.max),n.length>0){var s=parseInt(n.first().val());t.value>s&&i(t).val(s)}},get_field_html:function(){var i={type:"number",class:"forminator-field-singular wpmudev-input",id:this.get_field_id(),name:this.get_name(),value:this.get_saved_value(),placeholder:"10",title:this.label};_.isNumber(this.options.min)&&(i.min=this.options.min),_.isNumber(this.options.max)&&(i.max=this.options.max);var t="";return this.options.description&&(t='<span class="field-description">'+this.options.description+"</span>"),"<input "+this.get_field_attr_html(i)+" />"+t}})})}(jQuery);