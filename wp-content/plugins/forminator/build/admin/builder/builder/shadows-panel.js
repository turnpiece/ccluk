!function(e){formintorjs.define(["admin/builder/drag-drop","admin/builder/builder/field","admin/builder/builder/wrapper"],function(e,n,i){return Backbone.View.extend({initialize:function(e){return this.render()},render:function(){this.$el.html("");var i=this,t=Forminator.Data.fields;t.length&&_.each(t,function(t,d){var o=_.extend({type:t.type,options:t.options,cols:12,conditions:new Forminator.Collections.Conditions},t.defaults),r=new Forminator.Models.Fields(o),l=new n({model:r});l.dnd=new e(l,r,!1,"field",i.model),l.$el.attr("data-shadow",t.slug),i.$el.append(l.$el)})}})})}(jQuery);