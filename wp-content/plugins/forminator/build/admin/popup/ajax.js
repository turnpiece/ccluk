!function(t){formintorjs.define(["text!tpl/dashboard.html"],function(a){return Backbone.View.extend({className:"wpmudev-section--popup",events:{"click .wpmudev-action-done":"save","click .wpmudev-button-clear-exports":"clear_exports"},initialize:function(t){return this.action=t.action,this.nonce=t.nonce,this.data=t.data,this.id=t.id,this.render()},render:function(){var a=this,n={};n.action="forminator_load_"+this.action+"_popup",n._ajax_nonce=this.nonce,n.data=this.data,this.id&&(n.id=this.id),a.$el.html('<div class="preloader"><div class="wpmudev-loading"></div></div>'),a.$el.find(".preloader .wpmudev-loading").css({width:"32px",height:"32px"}),t.post(Forminator.Data.ajaxUrl,n).done(function(t){if(t&&t.success){a.$el.html(t.data),a.$el.find(".wpmudev-hidden-popup").show(400),Forminator.Utils.init_select2();var n=a.$el.find(".forminator-custom-form");n.length>0&&Forminator.Pagination.init(n),a.delegateEvents()}}).always(function(){a.$el.find(".preloader").remove()})},save:function(a){a.preventDefault();var n={},e=t(a.target).data("nonce");n.action="forminator_save_"+this.action+"_popup",n._ajax_nonce=e,t(".wpmudev-popup-form input, .wpmudev-popup-form select").each(function(){var a=t(this);n[a.attr("name")]=a.val()}),t.ajax({url:Forminator.Data.ajaxUrl,type:"POST",data:n,success:function(t){Forminator.Popup.close(!1,function(){window.location.reload()})}})},clear_exports:function(a){a.preventDefault();var n={},e=this,o=t(a.target).data("nonce"),i=t(a.target).data("form-id");n.action="forminator_clear_"+this.action+"_popup",n._ajax_nonce=o,n.id=i,t.ajax({url:Forminator.Data.ajaxUrl,type:"POST",data:n,success:function(){e.render()}})}})})}(jQuery);