!function(e){formintorjs.define(["text!tpl/quizzes.html"],function(i){return Backbone.View.extend({mainTpl:Forminator.Utils.template(e(i).find("#quiz-section-questions-tpl").html()),className:"wpmudev-box-body",initialize:function(e){return this.render()},render:function(){this.$el.html(this.mainTpl({type:"knowledge"})),this.render_questions()},render_questions:function(){var e=new Forminator.Settings.MultiQuestion({model:this.model,id:"forminator-quizwiz-wrap--questions",name:"knowledge_questions",hide_label:!0});this.$el.find("#forminator-quizwiz-wrap--multiquestion").prepend([e.el])}})})}(jQuery);