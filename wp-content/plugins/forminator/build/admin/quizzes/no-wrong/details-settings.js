!function(i){formintorjs.define(["text!tpl/quizzes.html"],function(e){return Backbone.View.extend({mainTpl:Forminator.Utils.template(i(e).find("#quiz-section-details-tpl").html()),className:"wpmudev-box-body",initialize:function(i){return this.render()},render:function(){this.$el.html(this.mainTpl({type:"nowrong"})),this.render_questions()},render_questions:function(){var i=new Forminator.Settings.Text({model:this.model,id:"forminator-quizwiz--title",name:"quiz_title",hide_label:!0});this.$el.find("#forminator-quizwiz-wrap--title").prepend([i.el]);var e=new Forminator.Settings.Image({model:this.model,id:"forminator-quizwiz--feat-image",name:"quiz_feat_image",hasPreview:!0,imageSize:"large",hide_label:!0});this.$el.find("#forminator-quizwiz-wrap--feat-image").append([e.el]);var t=new Forminator.Settings.Textarea({model:this.model,id:"forminator-quizwiz--description",name:"quiz_description",hide_label:!0,size:"120"});this.$el.find("#forminator-quizwiz-wrap--description").append([t.el]);var n=new Forminator.Settings.Radio({model:this.model,id:"forminator-quizwiz--visual-style",name:"visual_style",containerSize:"400",itemsColor:"blue",hasIcon:!0,hide_label:!0,default_value:"list",values:[{value:"list",label:Forminator.l10n.quizzes.list,iconClass:"wpdui-icon-align-justify"},{value:"grid",label:Forminator.l10n.quizzes.grid,iconClass:"wpdui-icon-thumbnails"}]});this.$el.find("#forminator-quizwiz-wrap--visual-style").append([n.el])}})})}(jQuery);