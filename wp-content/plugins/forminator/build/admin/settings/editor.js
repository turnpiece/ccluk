!function(t){formintorjs.define(["admin/settings/text"],function(e){return e.extend({events:{"click .wpmudev-insert-content":"insert_content","click .wpmudev-vars--button":"toggle_menu"},className:"wpmudev-option forminator-field-wrap-editor",get_field_html:function(){var t={cols:"40",rows:"5",class:"forminator-field-singular wpmudev-textarea",id:this.get_field_id(),name:this.get_name()};this.options.placeholder&&(t.placeholder=this.options.placeholder);var e=!_.isUndefined(this.options.enableFormData)&&this.options.enableFormData?this.get_form_data():"";return'<div class="wpmudev-vars"><textarea '+this.get_field_attr_html(t)+">"+this.get_saved_value()+'</textarea><div class="wpmudev-vars--mask"><div class="wpmudev-vars--innermask"><button class="wpmudev-button wpmudev-vars--button">'+Forminator.l10n.options.form_based_data+'</button><ul class="wpmudev-vars--dropdown">'+e+this.get_utilities()+"</ul></div></div></div>"},toggle_dropdown:function(){this.$el.find(".wpmudev-vars--dropdown").toggleClass("wpmudev-is_active")},toggle_menu:function(t){t.preventDefault(),this.toggle_dropdown()},get_form_data:function(){var t=["captcha","product","hidden","pagination","postdata","total","upload"];!_.isUndefined(this.options.enablePostData)&&this.options.enablePostData&&!_.isUndefined(this.options.enableUpload)&&this.options.enableUpload&&(t=["captcha","product","hidden","pagination","total"]);var e=Forminator.Utils.get_fields(this.model.get("wrappers"),t),n="",i="",o="";return _.each(e,function(t,e){"true"===t.required?i+='<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{'+t.element_id+'}">'+t.label+"</a></li>":o+='<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{'+t.element_id+'}">'+t.label+"</a></li>"}),!_.isUndefined(this.options.enableAllFormFields)&&this.options.enableAllFormFields&&(n+='<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{all_fields}">'+Forminator.l10n.options.all_fields+"</a></li>"),n+='<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{form_name}">'+Forminator.l10n.options.form_name+"</a></li>",""!==i&&(n+='<li class="wpmudev-dropdown--option"><strong>'+Forminator.l10n.options.required_form_fields+"</strong></li>",n+=i),""!==o&&(n+='<li class="wpmudev-dropdown--option"><strong>'+Forminator.l10n.options.optional_form_fields+"</strong></li>",n+=o),n},get_utilities:function(){return'<li class="wpmudev-dropdown--option"><strong>'+Forminator.l10n.options.misc_data+'</strong></li><li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{user_ip}">User IP Address</a></li><li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{date_mdy}">Date (mm/dd/yyyy)</a></li><li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{date_dmy}">Date (dd/mm/yyyy)</a></li><li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{embed_id}">Embed Post/Page ID</a></li><li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{embed_title}">Embed Post/Page Title</a></li><li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{embed_url}">Embed URL</a></li><li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{user_agent}">HTTP User Agent</a></li><li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{http_refer}">HTTP Refer URL</a></li><li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{user_name}">User Display Name</a></li><li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{user_email}">User Email</a></li><li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{user_login}">User Login</a></li>'},insert_content:function(e){if(e.preventDefault(),!_.isUndefined(tinymce)){var n=t(e.target).data("content");tinymce.get(this.get_field_id()).insertContent(n),this.toggle_dropdown()}},on_render:function(){this.$el.attr("id","wrapper-"+this.get_field_id()),this.initialize_editor()},initialize_editor:function(){var e=this;_.isUndefined(window.wp.editor)||_.isUndefined(tinymce)?setTimeout(function(){e.initialize_editor()},100):setTimeout(function(){window.wp.editor.remove(e.get_field_id()),window.wp.editor.initialize(e.get_field_id(),{tinymce:!0,quicktags:!0});var n=tinymce.get(e.get_field_id());n.on("change",function(t){e.save_value(n.getContent()),e.trigger("changed",n.getContent())}),t("#"+e.get_field_id()).on("change",function(i){n.setContent(t(this).val()),e.trigger("changed",n.getContent())})},100)}})})}(jQuery);