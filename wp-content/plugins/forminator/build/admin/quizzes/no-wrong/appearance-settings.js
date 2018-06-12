!function(o){formintorjs.define(["text!tpl/quizzes.html"],function(e){return Backbone.View.extend({mainTpl:Forminator.Utils.template(o(e).find("#quiz-section-appearance-nowrong-tpl").html()),className:"sui-box",google_fonts_options_list:[{value:"",label:Forminator.l10n.appearance.select_font},{value:"custom",label:Forminator.l10n.appearance.custom_font}],initialize:function(o){return this.render()},render:function(){var o=this;this.$el.html(this.mainTpl({type:"knowledge"})),Forminator.Utils.load_google_fonts(function(e){var n=_.map(e,function(o){return{value:o,label:o}});o.google_fonts_options_list=_.union(o.google_fonts_options_list,n),o.render_title(),o.render_description(),o.render_question(),o.render_answer(),o.render_submit(),o.render_result_quiz(),o.render_result_retake(),o.render_result_title(),o.render_result_description()}),this.render_image(),this.render_result_background(),this.render_result_border()},render_title:function(){var o=this,e=new Forminator.Settings.ToggleContainer({model:this.model,id:"forminator-nowrong-title-settings",name:"nowrong-title-settings",alternative:!0,label:Forminator.l10n.quizzes.title,hide_label:!0,description:Forminator.l10n.quizzes.title_desc,default_value:"false",values:[{value:"true",label:Forminator.l10n.quizzes.enable}]}),n=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-title-color",name:"nowrong-title-color",label:Forminator.l10n.commons.font_color,default_value:"#333333"}),r=new Forminator.Settings.Select({model:this.model,id:"forminator-nowrong-title-font-family",name:"nowrong-title-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:this.google_fonts_options_list,show:function(e){setTimeout(function(){"custom"===e?o.$el.find("#forminator-nowrong-title-custom-family").show():o.$el.find("#forminator-nowrong-title-custom-family").hide()},100)}}),t=new Forminator.Settings.Text({model:this.model,id:"forminator-nowrong-title-custom-family",name:"nowrong-title-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});e.$el.find(".sui-box-body").append([n.el,r.el,t.el,'<div class="sui-row sui-form-field"><div id="forminator-nowrong-title-wrap--font-size" class="sui-col-md-6"></div><div id="forminator-nowrong-title-wrap--font-weight" class="sui-col-md-6"></div></div>']);var l=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-title-font-size",name:"nowrong-title-font-size",label:"Font size",default_value:"42",values:[{value:"24",label:"Regular"},{value:"36",label:"Medium"},{value:"42",label:"Large"}]});e.$el.find("#forminator-nowrong-title-wrap--font-size").append(l.el);var a=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-title-font-weight",name:"nowrong-title-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"500",values:[{value:"300",label:"Light"},{value:"500",label:"Normal"},{value:"700",label:"Bold"}]});e.$el.find("#forminator-nowrong-title-wrap--font-weight").append(a.el),this.$el.find("#forminator-nowrong-wrap--title-settings").append(e.el)},render_description:function(){var o=this,e=new Forminator.Settings.ToggleContainer({model:this.model,id:"forminator-nowrong-description-settings",name:"nowrong-description-settings",alternative:!0,label:Forminator.l10n.quizzes.description,hide_label:!0,description:Forminator.l10n.quizzes.desc_desc,default_value:"false",values:[{value:"true",label:Forminator.l10n.quizzes.enable}]}),n=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-description-color",name:"nowrong-description-color",label:Forminator.l10n.commons.font_color,default_value:"#8C8C8C"}),r=new Forminator.Settings.Select({model:this.model,id:"forminator-nowrong-description-font-family",name:"nowrong-description-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:this.google_fonts_options_list,show:function(e){setTimeout(function(){"custom"===e?o.$el.find("#forminator-nowrong-description-custom-family").show():o.$el.find("#forminator-nowrong-description-custom-family").hide()},100)}}),t=new Forminator.Settings.Text({model:this.model,id:"forminator-nowrong-description-custom-family",name:"nowrong-description-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});e.$el.find(".sui-box-body").append([n.el,r.el,t.el,'<div class="sui-row sui-form-field"><div id="forminator-nowrong-description-wrap--font-size" class="sui-col-md-6"></div><div id="forminator-nowrong-description-wrap--font-weight" class="sui-col-md-6"></div></div>']);var l=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-description-font-size",name:"nowrong-description-font-size",label:"Font size",default_value:"20",values:[{value:"16",label:"Regular"},{value:"20",label:"Medium"},{value:"24",label:"Large"}]});e.$el.find("#forminator-nowrong-description-wrap--font-size").append(l.el);var a=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-description-font-weight",name:"nowrong-description-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"400",values:[{value:"300",label:"Light"},{value:"400",label:"Normal"},{value:"600",label:"Bold"}]});e.$el.find("#forminator-nowrong-description-wrap--font-weight").append(a.el),this.$el.find("#forminator-nowrong-wrap--description-settings").append(e.el)},render_image:function(){var o=new Forminator.Settings.ToggleContainer({model:this.model,id:"forminator-nowrong-image-settings",name:"nowrong-image-settings",alternative:!0,label:Forminator.l10n.quizzes.feat_image,hide_label:!0,description:Forminator.l10n.quizzes.image_desc,default_value:"false",values:[{value:"true",label:Forminator.l10n.quizzes.enable}]}),e=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-image-border-color",name:"nowrong-image-border-color",label:Forminator.l10n.commons.border_color,default_value:"#000000"}),n=new Forminator.Settings.Number({model:this.model,id:"forminator-nowrong-image-border-width",name:"nowrong-image-border-width",label:Forminator.l10n.commons.border_width,default_value:"0"}),r=new Forminator.Settings.Select({model:this.model,id:"forminator-nowrong-image-border-style",name:"nowrong-image-border-style",label:Forminator.l10n.commons.border_style,default_value:"none",values:[{value:"none",label:"None"},{value:"solid",label:"Solid"},{value:"dashed",label:"Dashed"},{value:"dotted",label:"Dotted"},{value:"double",label:"Double"}]});o.$el.find(".sui-box-body").append([e.el,n.el,r.el]),this.$el.find("#forminator-nowrong-wrap--image-settings").append(o.el)},render_question:function(){var o=this,e=new Forminator.Settings.ToggleContainer({model:this.model,id:"forminator-nowrong-question-settings",name:"nowrong-question-settings",alternative:!0,label:Forminator.l10n.quizzes.questions_title,hide_label:!0,description:Forminator.l10n.quizzes.question_desc,default_value:"false",values:[{value:"true",label:Forminator.l10n.quizzes.enable}]}),n=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-question-color",name:"nowrong-question-color",label:Forminator.l10n.commons.font_color,default_value:"#333333"}),r=new Forminator.Settings.Select({model:this.model,id:"forminator-nowrong-question-font-family",name:"nowrong-question-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:this.google_fonts_options_list,show:function(e){setTimeout(function(){"custom"===e?o.$el.find("#forminator-nowrong-question-custom-family").show():o.$el.find("#forminator-nowrong-question-custom-family").hide()},100)}}),t=new Forminator.Settings.Text({model:this.model,id:"forminator-nowrong-question-custom-family",name:"nowrong-question-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});e.$el.find(".sui-box-body").append([n.el,r.el,t.el,'<div class="sui-row sui-form-field"><div id="forminator-nowrong-question-wrap--font-size" class="sui-col-md-6"></div><div id="forminator-nowrong-question-wrap--font-weight" class="sui-col-md-6"></div></div>']);var l=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-question-font-size",name:"nowrong-question-font-size",label:"Font size",default_value:"24",values:[{value:"16",label:"Regular"},{value:"20",label:"Medium"},{value:"24",label:"Large"}]});e.$el.find("#forminator-nowrong-question-wrap--font-size").append(l.el);var a=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-question-font-weight",name:"nowrong-question-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"500",values:[{value:"300",label:"Light"},{value:"500",label:"Normal"},{value:"700",label:"Bold"}]});e.$el.find("#forminator-nowrong-question-wrap--font-weight").append(a.el),this.$el.find("#forminator-nowrong-wrap--question-settings").append(e.el)},render_answer:function(){var o=this,e=this.$el.find("#forminator-nowrong-wrap--answer-settings"),n=new Forminator.Settings.ToggleContainer({model:this.model,id:"forminator-nowrong-answer-settings",name:"nowrong-answer-settings",alternative:!0,label:Forminator.l10n.quizzes.answer,hide_label:!0,description:Forminator.l10n.quizzes.answer_desc,default_value:"false",values:[{value:"true",label:Forminator.l10n.quizzes.enable}]}),r=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-color-static",name:"nowrong-answer-color-static",label:Forminator.l10n.commons.font_color,default_value:"#888888"}),t=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-color-hover",name:"nowrong-answer-color-hover",label:Forminator.l10n.commons.font_color_hover,default_value:"#888888"}),l=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-color-active",name:"nowrong-answer-color-active",label:Forminator.l10n.commons.font_color_active,default_value:"#333333"}),a=new Forminator.Settings.Select({model:this.model,id:"forminator-nowrong-answer-font-family",name:"nowrong-answer-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:this.google_fonts_options_list,show:function(e){setTimeout(function(){"custom"===e?o.$el.find("#forminator-nowrong-answer-custom-family").show():o.$el.find("#forminator-nowrong-answer-custom-family").hide()},100)}}),i=new Forminator.Settings.Text({model:this.model,id:"forminator-nowrong-answer-custom-family",name:"nowrong-answer-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family}),s=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-border-static",name:"nowrong-answer-border-static",label:Forminator.l10n.commons.border_color,default_value:"#EBEDEB"}),m=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-border-hover",name:"nowrong-answer-border-hover",label:Forminator.l10n.commons.border_color_hover,default_value:"#EBEDEB"}),d=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-border-active",name:"nowrong-answer-border-active",label:Forminator.l10n.commons.border_color_active,default_value:"#17A8E3"}),u=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-background-static",name:"nowrong-answer-background-static",label:Forminator.l10n.commons.background,default_value:"#FAFAFA"}),f=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-background-hover",name:"nowrong-answer-background-hover",label:Forminator.l10n.commons.background_hover,default_value:"#FAFAFA"}),c=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-background-active",name:"nowrong-answer-background-active",label:Forminator.l10n.commons.background_active,default_value:"#F3FBFE"}),g="<h4>"+Forminator.l10n.quizzes.checkbox_styles+"</h4>",w=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-chkbo-static",name:"nowrong-answer-chkbo-static",label:Forminator.l10n.commons.border_color,default_value:"#BFBFBF"}),p=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-chkbo-hover",name:"nowrong-answer-chkbo-hover",label:Forminator.l10n.commons.border_color_hover,default_value:"#BFBFBF"}),v=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-chkbo-active",name:"nowrong-answer-chkbo-active",label:Forminator.l10n.commons.border_color_active,default_value:"#17A8E3"}),b=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-chkbg-static",name:"nowrong-answer-chkbg-static",label:Forminator.l10n.commons.background,default_value:"#FFFFFF"}),h=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-chkbg-hover",name:"nowrong-answer-chkbg-hover",label:Forminator.l10n.commons.background_hover,default_value:"#FFFFFF"}),_=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-answer-chkbg-active",name:"nowrong-answer-chkbg-active",label:Forminator.l10n.commons.background_active,default_value:"#17A8E3"});n.$el.find(".sui-box-body").append([r.el,t.el,l.el,a.el,i.el,'<div class="sui-row sui-form-field"><div id="forminator-nowrong-answer-wrap--font-size" class="sui-col-md-6"></div><div id="forminator-nowrong-answer-wrap--font-weight" class="sui-col-md-6"></div></div>',s.el,m.el,d.el,u.el,f.el,c.el,g,w.el,p.el,v.el,b.el,h.el,_.el]);var F=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-answer-font-size",name:"nowrong-answer-font-size",label:Forminator.l10n.appearance.font_size,default_value:"14",values:[{value:"12",label:"Regular"},{value:"14",label:"Medium"},{value:"16",label:"Large"}]});n.$el.find("#forminator-nowrong-answer-wrap--font-size").append(F.el);var z=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-answer-font-weight",name:"nowrong-answer-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"500",values:[{value:"300",label:"Light"},{value:"500",label:"Normal"},{value:"700",label:"Bold"}]});n.$el.find("#forminator-nowrong-answer-wrap--font-weight").append(z.el),e.append(n.el)},render_submit:function(){var o=this,e=new Forminator.Settings.ToggleContainer({model:this.model,id:"forminator-nowrong-submit-settings",name:"nowrong-submit-settings",alternative:!0,label:Forminator.l10n.quizzes.submit,hide_label:!0,description:Forminator.l10n.quizzes.submit_desc,default_value:"false",values:[{value:"true",label:Forminator.l10n.commons.enable}]}),n=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-submit-color-static",name:"nowrong-submit-color-static",label:Forminator.l10n.commons.font_color,default_value:"#FFFFFF"}),r=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-submit-color-hover",name:"nowrong-submit-color-hover",label:Forminator.l10n.commons.font_color_hover,default_value:"#FFFFFF"}),t=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-submit-color-active",name:"nowrong-submit-color-active",label:Forminator.l10n.commons.font_color_active,default_value:"#FFFFFF"}),l=new Forminator.Settings.Select({model:this.model,id:"forminator-nowrong-submit-font-family",name:"nowrong-submit-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:this.google_fonts_options_list,show:function(e){setTimeout(function(){"custom"===e?o.$el.find("#forminator-nowrong-submit-custom-family").show():o.$el.find("#forminator-nowrong-submit-custom-family").hide()},100)}}),a=new Forminator.Settings.Text({model:this.model,id:"forminator-nowrong-submit-custom-family",name:"nowrong-submit-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family}),i=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-submit-background-static",name:"nowrong-submit-background-static",label:Forminator.l10n.commons.background,default_value:"#17A8E3"}),s=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-submit-background-hover",name:"nowrong-submit-background-hover",label:Forminator.l10n.commons.background_hover,default_value:"#008FCA"}),m=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-submit-background-active",name:"nowrong-submit-background-active",label:Forminator.l10n.commons.background_active,default_value:"#008FCA"});e.$el.find(".sui-box-body").append([n.el,r.el,t.el,l.el,a.el,'<div class="sui-row sui-form-field"><div id="forminator-nowrong-submit-wrap--font-size" class="sui-col-md-6"></div><div id="forminator-nowrong-submit-wrap--font-weight" class="sui-col-md-6"></div></div>',i.el,s.el,m.el]);var d=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-submit-font-size",name:"nowrong-submit-font-size",label:"Font size",default_value:"14",values:[{value:"12",label:"Regular"},{value:"14",label:"Medium"},{value:"17",label:"Large"}]});e.$el.find("#forminator-nowrong-submit-wrap--font-size").append(d.el);var u=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-submit-font-weight",name:"nowrong-submit-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"500",values:[{value:"300",label:"Light"},{value:"500",label:"Normal"},{value:"700",label:"Bold"}]});e.$el.find("#forminator-nowrong-submit-wrap--font-weight").append(u.el),this.$el.find("#forminator-nowrong-wrap--submit-settings").append(e.el)},render_result_background:function(){var o="<h4>"+Forminator.l10n.quizzes.background+"</h4>",e="<p><small>"+Forminator.l10n.quizzes.background_desc+"</small></p>",n=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-background-main",name:"nowrong-result-background-main",label:Forminator.l10n.quizzes.main,default_value:"#17A8E3"}),r=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-background-head",name:"nowrong-result-background-head",label:Forminator.l10n.quizzes.header,default_value:"#17A8E3"}),t=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-background-body",name:"nowrong-result-background-body",label:Forminator.l10n.quizzes.content,default_value:"#FFFFFF"});this.$el.find("#forminator-nowrong-result-box-options").append([o,e,n.el,r.el,t.el])},render_result_border:function(){var o="<h4>"+Forminator.l10n.quizzes.border+"</h4>",e="<p><small>"+Forminator.l10n.quizzes.border_desc+"</small></p>",n=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-border-color",name:"nowrong-result-border-color",label:Forminator.l10n.commons.border_color,default_value:"#17A8E3"}),r=new Forminator.Settings.Number({model:this.model,id:"forminator-nowrong-result-border-width",name:"nowrong-result-border-width",label:Forminator.l10n.commons.border_width,default_value:"10"}),t=new Forminator.Settings.Select({model:this.model,id:"forminator-nowrong-result-border-style",name:"nowrong-result-border-style",label:Forminator.l10n.commons.border_style,default_value:"solid",values:[{value:"none",label:"None"},{value:"solid",label:"Solid"},{value:"dashed",label:"Dashed"},{value:"dotted",label:"Dotted"},{value:"double",label:"Double"}]});this.$el.find("#forminator-nowrong-result-box-options").append([o,e,n.el,r.el,t.el])},render_result_quiz:function(){var o=this,e="<h4>"+Forminator.l10n.quizzes.quiz_title+"</h4>",n="<p><small>"+Forminator.l10n.quizzes.quiz_title_desc+"</small></p>",r=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-quiz-color",name:"nowrong-result-quiz-color",label:Forminator.l10n.commons.font_color,default_value:"#FFFFFF"}),t=new Forminator.Settings.Select({model:this.model,id:"forminator-nowrong-result-quiz-font-family",name:"nowrong-result-quiz-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.commons.font_family,values:this.google_fonts_options_list,show:function(e){setTimeout(function(){"custom"===e?o.$el.find("#forminator-nowrong-result-quiz-custom-family").show():o.$el.find("#forminator-nowrong-result-quiz-custom-family").hide()},100)}}),l=new Forminator.Settings.Text({model:this.model,id:"forminator-nowrong-result-quiz-custom-family",name:"nowrong-result-quiz-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});this.$el.find("#forminator-nowrong-result-box-options").append([e,n,r.el,t.el,l.el,'<div class="sui-row sui-form-field"><div id="forminator-nowrong-result-wrap--quiz-font-size" class="sui-col-md-6"></div><div id="" class="sui-col-md-6"></div></div>']);var a=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-result-quiz-font-size",name:"nowrong-result-quiz-font-size",label:Forminator.l10n.appearance.font_size,default_value:"15",values:[{value:"13",label:"Regular"},{value:"15",label:"Medium"},{value:"19",label:"Large"}]});this.$el.find("#forminator-nowrong-result-wrap--quiz-font-size").append(a.el);var i=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-result-quiz-font-weight",name:"nowrong-result-quiz-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"500",values:[{value:"300",label:"Light"},{value:"500",label:"Normal"},{value:"700",label:"Bold"}]});this.$el.find("#forminator-nowrong-result-wrap--quiz-font-weight").append(i.el)},render_result_retake:function(){var o=this,e="<h4>"+Forminator.l10n.quizzes.retake_button+"</h4>",n="<p><small>"+Forminator.l10n.quizzes.retake_button_desc+"</small></p>",r=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-retake-color-static",name:"nowrong-result-retake-color-static",label:Forminator.l10n.commons.font_color,default_value:"#FFFFFF"}),t=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-retake-color-hover",name:"nowrong-result-retake-color-hover",label:Forminator.l10n.commons.font_color_hover,default_value:"#FFFFFF"}),l=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-retake-color-active",name:"nowrong-result-retake-color-active",label:Forminator.l10n.commons.font_color_active,default_value:"#FFFFFF"}),a=new Forminator.Settings.Select({model:this.model,id:"forminator-nowrong-result-retake-font-family",name:"nowrong-result-retake-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:this.google_fonts_options_list,show:function(e){setTimeout(function(){"custom"===e?o.$el.find("#forminator-nowrong-result-retake-custom-family").show():o.$el.find("#forminator-nowrong-result-retake-custom-family").hide()},100)}}),i=new Forminator.Settings.Text({model:this.model,id:"forminator-nowrong-result-retake-custom-family",name:"nowrong-result-retake-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family}),s=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-retake-background-static",name:"nowrong-result-retake-background-static",label:Forminator.l10n.commons.background,default_value:"#17A8E3"}),m=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-retake-background-hover",name:"nowrong-result-retake-background-hover",label:Forminator.l10n.commons.background_hover,default_value:"#17A8E3"}),d=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-retake-background-active",name:"nowrong-result-retake-background-active",label:Forminator.l10n.commons.background_active,default_value:"#17A8E3"});this.$el.find("#forminator-nowrong-result-box-options").append([e,n,r.el,l.el,t.el,a.el,i.el,'<div class="sui-row sui-form-field"><div id="forminator-nowrong-result-wrap--retake-font-size" class="sui-col-md-6"></div><div id="forminator-nowrong-result-wrap--retake-font-weight" class="sui-col-md-6"></div></div>',s.el,m.el,d.el]);var u=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-result-retake-font-size",name:"nowrong-result-retake-font-size",label:"Font size",default_value:"13",values:[{value:"13",label:"Regular"},{value:"15",label:"Normal"},{value:"19",label:"Large"}]});this.$el.find("#forminator-nowrong-result-wrap--retake-font-size").append(u.el);var f=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-result-retake-font-weight",name:"nowrong-result-retake-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"500",values:[{value:"300",label:"Light"},{value:"500",label:"Normal"},{value:"700",label:"Bold"}]});this.$el.find("#forminator-nowrong-result-wrap--retake-font-weight").append(f.el)},render_result_title:function(){var o=this,e="<h4>"+Forminator.l10n.quizzes.result_title+"</h4>",n="<p><small>"+Forminator.l10n.quizzes.result_title_desc+"</small></p>",r=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-title-color",name:"nowrong-result-title-color",label:Forminator.l10n.commons.font_color,default_value:"#333333"}),t=new Forminator.Settings.Select({model:this.model,id:"forminator-nowrong-result-title-font-family",name:"nowrong-result-title-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:this.google_fonts_options_list,show:function(e){setTimeout(function(){"custom"===e?o.$el.find("#forminator-nowrong-result-title-custom-family").show():o.$el.find("#forminator-nowrong-result-title-custom-family").hide()},100)}}),l=new Forminator.Settings.Text({model:this.model,id:"forminator-nowrong-result-title-custom-family",name:"nowrong-result-title-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});this.$el.find("#forminator-nowrong-result-box-options").append([e,n,r.el,t.el,l.el,'<div class="sui-row sui-form-field"><div id="forminator-nowrong-result-wrap--title-font-size" class="sui-col-md-6"></div><div id="forminator-nowrong-result-wrap--title-font-weight" class="sui-col-md-6"></div></div>']);var a=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-result-title-font-size",name:"nowrong-result-title-font-size",label:Forminator.l10n.appearance.font_size,default_value:"15",values:[{value:"15",label:"Regular"},{value:"20",label:"Medium"},{value:"25",label:"Large"}]});this.$el.find("#forminator-nowrong-result-wrap--title-font-size").append(a.el);var i=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-result-title-font-weight",name:"nowrong-result-title-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"500",values:[{value:"300",label:"Light"},{value:"500",label:"Normal"},{value:"700",label:"Bold"}]});this.$el.find("#forminator-nowrong-result-wrap--title-font-weight").append(i.el)},render_result_description:function(){var o=this,e="<h4>"+Forminator.l10n.quizzes.result_description+"</h4>",n="<p><small>"+Forminator.l10n.quizzes.result_description_desc+"</small></p>",r=new Forminator.Settings.Color({model:this.model,id:"forminator-nowrong-result-description-color",name:"nowrong-result-description-color",label:Forminator.l10n.commons.font_color,default_value:"#4D4D4D"}),t=new Forminator.Settings.Select({model:this.model,id:"forminator-nowrong-result-description-font-family",name:"nowrong-result-description-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:this.google_fonts_options_list,show:function(e){setTimeout(function(){"custom"===e?o.$el.find("#forminator-nowrong-result-description-custom-family").show():o.$el.find("#forminator-nowrong-result-description-custom-family").hide()},100)}}),l=new Forminator.Settings.Text({model:this.model,id:"forminator-nowrong-result-description-custom-family",name:"nowrong-result-description-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});this.$el.find("#forminator-nowrong-result-box-options").append([e,n,r.el,t.el,l.el,'<div class="sui-row sui-form-field"><div id="forminator-nowrong-result-wrap--description-font-size" class="sui-col-md-6"></div><div id="forminator-nowrong-result-wrap--description-font-weight" class="sui-col-md-6"></div></div>']);var a=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-result-description-font-size",name:"nowrong-result-description-font-size",label:Forminator.l10n.appearance.font_size,default_value:"13",values:[{value:"13",label:"Regular"},{value:"15",label:"Medium"},{value:"20",label:"Large"}]});this.$el.find("#forminator-nowrong-result-wrap--description-font-size").append(a.el);var i=new Forminator.Settings.Radio({model:this.model,id:"forminator-nowrong-result-description-font-weight",name:"nowrong-result-description-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"400",values:[{value:"300",label:"Light"},{value:"400",label:"Normal"},{value:"600",label:"Bold"}]});this.$el.find("#forminator-nowrong-result-wrap--description-font-weight").append(i.el)}})})}(jQuery);