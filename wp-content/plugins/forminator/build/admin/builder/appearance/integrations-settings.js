!function(i){formintorjs.define(["text!tpl/appearance.html"],function(t){return Backbone.View.extend({mainTpl:Forminator.Utils.template(i(t).find("#appearance-section-integrations-tpl").html()),className:"sui-box",initialize:function(i){return this.listenTo(Forminator.Events,"forminator:addons:reload",this.render),this.render()},render:function(){this.$el.html(this.mainTpl()),this.render_applications()},render_applications:function(){var t=this,n={},a=this.$el.find("#forminator-formwiz-applications");this.$el.find("#forminator-formwiz-applications").html('<div class="sui-notice sui-notice-sm sui-notice-loading"><p>Fetching integration list…</p></div>'),n.action="forminator_addon_get_form_addons",n._ajax_nonce=Forminator.Data.addonNonce,n.data={},n.data.form_id=Forminator.Data.currentForm.formID,i.post({url:Forminator.Data.ajaxUrl,type:"post",data:n}).done(function(i){i&&i.success&&t.$el.find("#forminator-formwiz-applications").html(i.data.data)}).always(function(){a.find(".sui-notice-loading").remove()})}})})}(jQuery);