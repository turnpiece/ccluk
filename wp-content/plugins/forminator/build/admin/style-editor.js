!function(t){formintorjs.define(["text!admin/templates/style-editor.html"],function(e){return Backbone.View.extend({editor:!1,session:!1,mainTpl:Forminator.Utils.template(t(e).find("#style-editor-main-tpl").html()),selectors:[{selector:".forminator-form input ",label:"Text Input"}],events:{"click .wpmudev-css-stylable":"insert_selector"},initialize:function(t){return this.options=t,this.selectors=t.selectors,this.render()},render:function(){this.$el.html(this.mainTpl({selectors:this.selectors,custom_css:this.model.get(this.options.property)||"",element_id:this.options.element_id})),this.start_editor()},start_editor:function(){var t=this;this.editor=ace.edit(this.$("#"+this.options.element_id)[0]),this.session=this.editor.getSession(),this.session.setUseWorker(!1),this.editor.setShowPrintMargin(!1),this.session.setMode("ace/mode/css"),this.editor.setTheme("ace/theme/forminator"),this.editor.renderer.setShowGutter(!0),this.editor.setHighlightActiveLine(!0),this.editor.on("change",function(e){t.model.set(t.options.property,t.editor.getValue())}),this.editor.focus()},insert_selector:function(e){e.preventDefault();var i=t(e.target),s=i.data("selector")+"{}";this.editor.navigateFileEnd(),this.editor.insert(s),this.editor.navigateLeft(1),this.editor.focus()}})})}(jQuery);