!function(e){formintorjs.define(["text!tpl/appearance.html"],function(a){return Backbone.View.extend({mainTpl:Forminator.Utils.template(e(a).find("#appearance-section-emails-tpl").html()),className:"wpmudev-box-body",initialize:function(e){return this.render()},render:function(){this.$el.html(this.mainTpl()),this.render_user_email(),this.render_admin_email()},render_user_email:function(){var e=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-user-email",name:"use-user-email",hide_label:!0,values:[{value:"true",label:Forminator.l10n.appearance.send_user_email}]}),a=new Forminator.Settings.Text({model:this.model,id:"emails-user-email-title",name:"user-email-title",placeholder:Forminator.l10n.appearance.subject,inputLarge:"true"}),i=new Forminator.Settings.Editor({model:this.model,id:"emails-user-email-editor",name:"user-email-editor",placeholder:Forminator.l10n.appearance.body,enableFormData:!0});e.$el.find(".wpmudev-option--switch_content").append([a.el,i.el]),this.$el.find(".appearance-section-form-user-email").append(e.el)},render_admin_email:function(){var e=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-admin-email",name:"use-admin-email",hide_label:!0,values:[{value:"true",label:Forminator.l10n.appearance.send_admin_email}]}),a=new Forminator.Settings.Text({model:this.model,id:"emails-admin-email-title",name:"admin-email-title",placeholder:Forminator.l10n.appearance.subject,inputLarge:"true"}),i=new Forminator.Settings.Editor({model:this.model,id:"emails-admin-email-editor",name:"admin-email-editor",placeholder:Forminator.l10n.appearance.body,enableFormData:!0});e.$el.find(".wpmudev-option--switch_content").append([a.el,i.el]),this.$el.find(".appearance-section-form-user-admin-email").append(e.el)}})})}(jQuery);