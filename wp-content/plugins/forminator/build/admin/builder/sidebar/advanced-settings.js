!function(e){formintorjs.define(["admin/builder/sidebar/conditions"],function(n){return Backbone.View.extend({className:"wpmudev-advanced-settings--options",events:{"click .wpmudev-manage-conditions":"conditions_popup"},settings:[],initialize:function(e){return this.field=e.field,this.l10n=e.l10n,this.field_settings=e.field_settings,this.render()},render:function(){this.$el.html(""),"hidden"!==this.field.get("type")&&this.render_custom_class(),this.render_conditions()},render_custom_class:function(){var e=this,n=new Forminator.Settings.ToggleContainer({model:e.field,id:"advanced-custom-class",name:"use-custom-class",hide_label:!0,has_content:!0,values:[{value:"true",label:Forminator.l10n.builder.use_custom_class,labelSmall:"true"}],on_change:function(n){e.render_fields()}}),i=new Forminator.Settings.Text({model:e.field,id:"advanced-custom-class-field",name:"custom-class",label:Forminator.l10n.builder.custom_class,on_change:function(n){e.render_fields()}});n.$el.find(".wpmudev-option--switch_content").append(i.$el),e.$el.append(n.$el);var t=new Forminator.Settings.Separator({model:e.field});e.$el.append(t.$el)},render_conditions:function(){var e=this,n=new Forminator.Settings.ToggleContainer({model:e.field,id:"advanced-conditions",name:"use_conditions",hide_label:!0,values:[{value:"true",label:Forminator.l10n.conditions.conditional_logic,labelSmall:"true"}],on_change:function(n){e.render_fields()}});if(!_.isUndefined(this.field.get("conditions"))&&this.field.get("conditions").length>0){var i="show"===this.field.get("condition_action")?Forminator.l10n.sidebar.shown:Forminator.l10n.sidebar.hidden;n.$el.find(".wpmudev-option--switch_content").addClass("wpmudev-is_gray").append('<div class="wpmudev-conditions--rule"><label class="wpmudev-rule--base"> '+Forminator.l10n.sidebar.field_will_be+" <strong>"+i.toLowerCase()+"</strong> "+Forminator.l10n.sidebar.if+':</label><ul class="wpmudev-rule--match"></ul></div><div class="wpmudev-conditions--manage"><button class="wpmudev-button wpmudev-manage-conditions wpmudev-button-sm">'+Forminator.l10n.conditions.edit_conditions+"</button></div>");var t=Forminator.Utils.get_fields(this.model.get("wrappers")),o=this.field.get("conditions");o.each(function(i){var s,l=_.where(t,{element_id:i.get("element_id")})[0];if(void 0!==l){if(l.hasOptions&&l.values.length>0){var d=_.where(l.values,{value:i.get("value")})[0];if(d||(d=_.where(l.values,{label:i.get("value")})[0]),!d)return o.remove(i,{silent:!0}),Forminator.Events.trigger("sidebar:settings:updated",e.field),!1;s=d.label}else s=i.get("value");_.isEmpty(s)&&(s="null"),n.$el.find(".wpmudev-rule--match").append("<li>"+l.label+" <strong>"+Forminator.l10n.conditions[i.get("rule")]+"</strong> "+s+"</li>")}})}else n.$el.find(".wpmudev-option--switch_content").addClass("wpmudev-is_gray").append('<div class="wpmudev-conditions--manage"><button class="wpmudev-button wpmudev-manage-conditions wpmudev-button-sm">'+Forminator.l10n.conditions.setup_conditions+"</button></div>");e.$el.append(n.$el);var s=new Forminator.Settings.Separator({model:e.field});e.$el.append(s.$el)},render_fields:function(){Forminator.Events.trigger("sidebar:settings:updated",this.field)},conditions_popup:function(i){i.preventDefault();var t=this,o=new n({model:this.field,wrappers:this.model.get("wrappers")});this.listenTo(o,"conditions:modal:close",function(){Forminator.Popup.close(),t.render()}),Forminator.Popup.open(function(){e(this).append(o.el)},{title:Forminator.l10n.conditions.setup_conditions})}})})}(jQuery);