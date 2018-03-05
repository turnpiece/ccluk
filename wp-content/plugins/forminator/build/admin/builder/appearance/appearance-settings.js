!function(e){formintorjs.define(["admin/style-editor","text!tpl/appearance.html"],function(a,o){return Backbone.View.extend({mainTpl:Forminator.Utils.template(e(o).find("#appearance-section-appearance-tpl").html()),colorTpl:Forminator.Utils.template(e(o).find("#appearance-color-grid-tpl").html()),className:"wpmudev-box-body",initialize:function(e){return this.render()},render:function(){this.$el.html(this.mainTpl()),this.render_form_name(),this.render_form_style(),this.render_fields_style(),this.render_title_settings(),this.render_subtitle_settings(),this.render_label_settings(),this.render_input_settings(),this.render_button_settings(),this.render_notification_settings(),this.render_color_settings(),this.render_custom_submit(),this.render_custom_invalid_form(),this.render_custom_css()},render_form_name:function(){var e=new Forminator.Settings.Text({model:this.model,id:"appearance-form-name",name:"formName"});this.$el.find(".appearance-section-form-name").append(e.el)},render_form_style:function(){var e=new Forminator.Settings.Select({model:this.model,id:"appearance-form-style",name:"form-style",label:"Select a style to use",default_value:"default",values:[{value:"default",label:Forminator.l10n.appearance.default},{value:"flat",label:Forminator.l10n.appearance.flat},{value:"bold",label:Forminator.l10n.appearance.bold},{value:"material",label:Forminator.l10n.appearance.material}]});this.$el.find(".appearance-section-form-style").append(e.el)},render_fields_style:function(){var e=new Forminator.Settings.Radio({model:this.model,id:"appearance-fields-style",name:"fields-style",containerSize:"400",label:Forminator.l10n.appearance.fields_style,hide_label:!0,default_value:"open",values:[{value:"open",label:Forminator.l10n.appearance.open_fields},{value:"enclosed",label:Forminator.l10n.appearance.enclosed_fields}]});this.$el.find(".appearance-section-fields-style").append(e.el)},render_title_settings:function(){var a=this,o=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-cform-title-font-settings",name:"cform-title-font-settings",hide_label:!0,containerClass:"wpmudev-is_gray",default_value:"false",values:[{value:"true",label:"Section title typography"}]}),t=new Forminator.Settings.Select({model:this.model,id:"appearance-cform-title-font-family",name:"cform-title-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:[{value:"",label:Forminator.l10n.appearance.select_font},{value:"custom",label:Forminator.l10n.appearance.custom_font}],show:function(e){setTimeout(function(){"custom"===e?a.$el.find("#appearance-cform-title-custom-family").show():a.$el.find("#appearance-cform-title-custom-family").hide()},100)},rendered:function(){var a=this;e.ajax({url:Forminator.Data.ajaxUrl,type:"POST",data:{action:"forminator_load_google_fonts",active:a.get_saved_value()},success:function(e){a.get_field().append(e.data),Forminator.Utils.init_select2()}})}});o.$el.find(".wpmudev-option--switch_content").append(t.$el);var l=new Forminator.Settings.Text({model:this.model,id:"appearance-cform-title-custom-family",name:"cform-title-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});o.$el.find(".wpmudev-option--switch_content").append(l.$el);o.$el.find(".wpmudev-option--switch_content").append('<div class="wpmudev-option--half"></div>');var n=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-title-font-size",name:"cform-title-font-size",label:Forminator.l10n.appearance.font_size,default_value:"45",values:[{value:"25",label:"Aa"},{value:"35",label:"Aa"},{value:"45",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(n.el);var i=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-title-font-weight",name:"cform-title-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"normal",values:[{value:"lighter",label:"Aa"},{value:"normal",label:"Aa"},{value:"bold",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(i.el);var r=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-title-text-align",name:"cform-title-text-align",label:"Text align",containerSize:"300",default_value:"left",values:[{value:"left",label:"Left"},{value:"center",label:"Center"},{value:"right",label:"Right"}]});o.$el.find(".wpmudev-option--switch_content").append(r.$el),this.$el.find(".appearance-section-customize-fonts").append(o.el)},render_subtitle_settings:function(){var a=this,o=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-cform-subtitle-font-settings",name:"cform-subtitle-font-settings",hide_label:!0,containerClass:"wpmudev-is_gray",values:[{value:"true",label:"Section subtitle typography"}]}),t=new Forminator.Settings.Select({model:this.model,id:"appearance-cform-subtitle-font-family",name:"cform-subtitle-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:[{value:"",label:Forminator.l10n.appearance.select_font},{value:"custom",label:Forminator.l10n.appearance.custom_font}],show:function(e){setTimeout(function(){"custom"===e?a.$el.find("#appearance-cform-subtitle-custom-family").show():a.$el.find("#appearance-cform-subtitle-custom-family").hide()},100)},rendered:function(){var a=this;e.ajax({url:Forminator.Data.ajaxUrl,type:"POST",data:{action:"forminator_load_google_fonts",active:a.get_saved_value()},success:function(e){a.get_field().append(e.data),Forminator.Utils.init_select2()}})}});o.$el.find(".wpmudev-option--switch_content").append(t.$el);var l=new Forminator.Settings.Text({model:this.model,id:"appearance-cform-subtitle-custom-family",name:"cform-subtitle-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});o.$el.find(".wpmudev-option--switch_content").append(l.$el);o.$el.find(".wpmudev-option--switch_content").append('<div class="wpmudev-option--half"></div>');var n=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-subtitle-font-size",name:"cform-subtitle-font-size",label:Forminator.l10n.appearance.font_size,default_value:"18",values:[{value:"14",label:"Aa"},{value:"18",label:"Aa"},{value:"22",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(n.el);var i=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-subtitle-font-weight",name:"cform-subtitle-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"normal",values:[{value:"lighter",label:"Aa"},{value:"normal",label:"Aa"},{value:"bold",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(i.el);var r=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-subtitle-text-align",name:"cform-subtitle-text-align",label:"Text align",containerSize:"300",default_value:"left",values:[{value:"left",label:"Left"},{value:"center",label:"Center"},{value:"right",label:"Right"}]});o.$el.find(".wpmudev-option--switch_content").append(r.$el),this.$el.find(".appearance-section-customize-fonts").append(o.el)},render_label_settings:function(){var a=this,o=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-cform-label-font-settings",name:"cform-label-font-settings",hide_label:!0,containerClass:"wpmudev-is_gray",values:[{value:"true",label:"Label typography"}]}),t=new Forminator.Settings.Select({model:this.model,id:"appearance-cform-label-font-family",name:"cform-label-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:[{value:"",label:Forminator.l10n.appearance.select_font},{value:"custom",label:Forminator.l10n.appearance.custom_font}],show:function(e){setTimeout(function(){"custom"===e?a.$el.find("#appearance-cform-label-custom-family").show():a.$el.find("#appearance-cform-label-custom-family").hide()},100)},rendered:function(){var a=this;e.ajax({url:Forminator.Data.ajaxUrl,type:"POST",data:{action:"forminator_load_google_fonts",active:a.get_saved_value()},success:function(e){a.get_field().append(e.data),Forminator.Utils.init_select2()}})}});o.$el.find(".wpmudev-option--switch_content").append(t.$el);var l=new Forminator.Settings.Text({model:this.model,id:"appearance-cform-label-custom-family",name:"cform-label-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});o.$el.find(".wpmudev-option--switch_content").append(l.$el);o.$el.find(".wpmudev-option--switch_content").append('<div class="wpmudev-option--half"></div>');var n=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-label-font-size",name:"cform-label-font-size",label:Forminator.l10n.appearance.font_size,default_value:"16",values:[{value:"12",label:"Aa"},{value:"16",label:"Aa"},{value:"18",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(n.el);var i=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-label-font-weight",name:"cform-label-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"normal",values:[{value:"lighter",label:"Aa"},{value:"normal",label:"Aa"},{value:"bold",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(i.el),this.$el.find(".appearance-section-customize-fonts").append(o.el)},render_input_settings:function(){var a=this,o=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-cform-input-font-settings",name:"cform-input-font-settings",hide_label:!0,containerClass:"wpmudev-is_gray",values:[{value:"true",label:"Input typography"}]}),t=new Forminator.Settings.Select({model:this.model,id:"appearance-cform-input-font-family",name:"cform-input-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:[{value:"",label:Forminator.l10n.appearance.select_font},{value:"custom",label:Forminator.l10n.appearance.custom_font}],show:function(e){setTimeout(function(){"custom"===e?a.$el.find("#appearance-cform-input-custom-family").show():a.$el.find("#appearance-cform-input-custom-family").hide()},100)},rendered:function(){var a=this;e.ajax({url:Forminator.Data.ajaxUrl,type:"POST",data:{action:"forminator_load_google_fonts",active:a.get_saved_value()},success:function(e){a.get_field().append(e.data),Forminator.Utils.init_select2()}})}});o.$el.find(".wpmudev-option--switch_content").append(t.$el);var l=new Forminator.Settings.Text({model:this.model,id:"appearance-cform-input-custom-family",name:"cform-input-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});o.$el.find(".wpmudev-option--switch_content").append(l.$el);o.$el.find(".wpmudev-option--switch_content").append('<div class="wpmudev-option--half"></div>');var n=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-input-font-size",name:"cform-input-font-size",label:Forminator.l10n.appearance.font_size,default_value:"16",values:[{value:"12",label:"Aa"},{value:"16",label:"Aa"},{value:"18",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(n.el);var i=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-input-font-weight",name:"cform-input-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"normal",values:[{value:"lighter",label:"Aa"},{value:"normal",label:"Aa"},{value:"bold",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(i.el),this.$el.find(".appearance-section-customize-fonts").append(o.el)},render_button_settings:function(){var a=this,o=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-cform-button-font-settings",name:"cform-button-font-settings",hide_label:!0,containerClass:"wpmudev-is_gray",values:[{value:"true",label:"Button typography"}]}),t=new Forminator.Settings.Select({model:this.model,id:"appearance-cform-button-font-family",name:"cform-button-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:[{value:"",label:Forminator.l10n.appearance.select_font},{value:"custom",label:Forminator.l10n.appearance.custom_font}],show:function(e){setTimeout(function(){"custom"===e?a.$el.find("#appearance-cform-button-custom-family").show():a.$el.find("#appearance-cform-button-custom-family").hide()},100)},rendered:function(){var a=this;e.ajax({url:Forminator.Data.ajaxUrl,type:"POST",data:{action:"forminator_load_google_fonts",active:a.get_saved_value()},success:function(e){a.get_field().append(e.data),Forminator.Utils.init_select2()}})}});o.$el.find(".wpmudev-option--switch_content").append(t.$el);var l=new Forminator.Settings.Text({model:this.model,id:"appearance-cform-button-custom-family",name:"cform-button-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});o.$el.find(".wpmudev-option--switch_content").append(l.$el);o.$el.find(".wpmudev-option--switch_content").append('<div class="wpmudev-option--half"></div>');var n=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-button-font-size",name:"cform-button-font-size",label:Forminator.l10n.appearance.font_size,default_value:"14",values:[{value:"12",label:"Aa"},{value:"14",label:"Aa"},{value:"16",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(n.el);var i=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-button-font-weight",name:"cform-button-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"500",values:[{value:"300",label:"Aa"},{value:"500",label:"Aa"},{value:"700",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(i.el),this.$el.find(".appearance-section-customize-fonts").append(o.el)},render_notification_settings:function(){var a=this,o=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-cform-notice-font-settings",name:"cform-notice-font-settings",hide_label:!0,containerClass:"wpmudev-is_gray",values:[{value:"true",label:"Notification typography"}]}),t=new Forminator.Settings.Select({model:this.model,id:"appearance-cform-notice-font-family",name:"cform-notice-font-family",dataAttr:{"allow-search":1},label:Forminator.l10n.appearance.font_family,values:[{value:"",label:Forminator.l10n.appearance.select_font},{value:"custom",label:Forminator.l10n.appearance.custom_font}],show:function(e){setTimeout(function(){"custom"===e?a.$el.find("#appearance-cform-notice-custom-family").show():a.$el.find("#appearance-cform-notice-custom-family").hide()},100)},rendered:function(){var a=this;e.ajax({url:Forminator.Data.ajaxUrl,type:"POST",data:{action:"forminator_load_google_fonts",active:a.get_saved_value()},success:function(e){a.get_field().append(e.data),Forminator.Utils.init_select2()}})}});o.$el.find(".wpmudev-option--switch_content").append(t.$el);var l=new Forminator.Settings.Text({model:this.model,id:"appearance-cform-notice-custom-family",name:"cform-notice-custom-family",placeholder:Forminator.l10n.appearance.custom_font_placeholder,description:Forminator.l10n.appearance.custom_font_description,label:Forminator.l10n.appearance.custom_font_family});o.$el.find(".wpmudev-option--switch_content").append(l.$el);o.$el.find(".wpmudev-option--switch_content").append('<div class="wpmudev-option--half"></div>');var n=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-notice-font-size",name:"cform-notice-font-size",label:Forminator.l10n.appearance.font_size,default_value:"13",values:[{value:"13",label:"Aa"},{value:"15",label:"Aa"},{value:"17",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(n.el);var i=new Forminator.Settings.Radio({model:this.model,id:"appearance-cform-notice-font-weight",name:"cform-notice-font-weight",label:Forminator.l10n.appearance.font_weight,default_value:"normal",values:[{value:"lighter",label:"Aa"},{value:"normal",label:"Aa"},{value:"bold",label:"Aa"}]});o.$el.find(".wpmudev-option--half").append(i.el),this.$el.find(".appearance-section-customize-fonts").append(o.el)},render_color_settings:function(){var e=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-cform-color-settings",name:"cform-color-settings",hide_label:!0,containerClass:"wpmudev-is_gray",values:[{value:"true",label:Forminator.l10n.appearance.customize_colors}]});e.$el.find(".wpmudev-option--switch_content").html(this.colorTpl());var a=new Forminator.Settings.Color({model:this.model,id:"appearance-form-background",name:"cform-form-background",hide_label:!0,default_value:"#EEEEEE",label:""});e.$el.find(".color-grid-form-background .wpmudev-picker").append([a.$el]);var o=new Forminator.Settings.Color({model:this.model,id:"appearance-form-border",name:"cform-form-border",hide_label:!0,default_value:"#EEEEEE",label:""});e.$el.find(".color-grid-form-border .wpmudev-picker").append([o.$el]);var t=new Forminator.Settings.Color({model:this.model,id:"appearance-title-color",name:"cform-title-color",hide_label:!0,default_value:"#333333",label:""});e.$el.find(".color-grid-title .wpmudev-picker").append([t.$el]);var l=new Forminator.Settings.Color({model:this.model,id:"appearance-subtitle-color",name:"cform-subtitle-color",hide_label:!0,default_value:"#333333",label:""});e.$el.find(".color-grid-subtitle .wpmudev-picker").append([l.$el]);var n=new Forminator.Settings.Color({model:this.model,id:"appearance-label-color",name:"cform-label-color",hide_label:!0,default_value:"#777771",label:""});e.$el.find(".color-grid-main-label .wpmudev-picker").append([n.$el]);var i=new Forminator.Settings.Color({model:this.model,id:"appearance-label-asterisk-color",name:"cform-asterisk-color",hide_label:!0,default_value:"#777771",label:""});e.$el.find(".color-grid-asterisk-label .wpmudev-picker").append(i.$el);var r=new Forminator.Settings.Color({model:this.model,id:"appearance-label-helper-color",name:"label-helper-color",hide_label:!0,default_value:"#777771",label:""});e.$el.find(".color-grid-helper-label .wpmudev-picker").append([r.$el]);var m=new Forminator.Settings.Color({model:this.model,id:"appearance-input-bg",name:"input-bg",hide_label:!0,tooltip:Forminator.l10n.appearance.static,label:"",default_value:"#FFFFFF"}),p=new Forminator.Settings.Color({model:this.model,id:"appearance-input-hover-bg",name:"input-hover-bg",hide_label:!0,tooltip:Forminator.l10n.appearance.hover,label:"",default_value:"#FFFFFF"}),d=new Forminator.Settings.Color({model:this.model,id:"appearance-input-active-bg",name:"input-active-bg",hide_label:!0,tooltip:Forminator.l10n.appearance.active,label:"",default_value:"#FFFFFF"});e.$el.find(".color-grid-input-bg .wpmudev-pickers").append([m.$el,p.$el,d.$el]);var c=new Forminator.Settings.Color({model:this.model,id:"appearance-border-color",name:"input-border",hide_label:!0,tooltip:Forminator.l10n.appearance.static,label:"",default_value:"#BBBBBB"}),s=new Forminator.Settings.Color({model:this.model,id:"appearance-border-hover-color",name:"input-border-hover",hide_label:!0,tooltip:Forminator.l10n.appearance.hover,label:"",default_value:"#17A8E3"}),u=new Forminator.Settings.Color({model:this.model,id:"appearance-border-active-color",name:"input-border-active",hide_label:!0,tooltip:Forminator.l10n.appearance.active,label:"",default_value:"#17A8E3"});e.$el.find(".color-grid-input-border .wpmudev-pickers").append([c.$el,s.$el,u.$el]);var f=new Forminator.Settings.Color({model:this.model,id:"appearance-input-color",name:"input-color",hide_label:!0,tooltip:Forminator.l10n.appearance.static,label:"",default_value:"#000000"}),v=new Forminator.Settings.Color({model:this.model,id:"appearance-input-color-hover",name:"input-color-hover",hide_label:!0,tooltip:Forminator.l10n.appearance.hover,label:"",default_value:"#000000"}),b=new Forminator.Settings.Color({model:this.model,id:"appearance-input-color-active",name:"input-color-active",hide_label:!0,tooltip:Forminator.l10n.appearance.active,label:"",default_value:"#000000"});e.$el.find(".color-grid-input-color .wpmudev-pickers").append([f.$el,v.$el,b.$el]);var _=new Forminator.Settings.Color({model:this.model,id:"appearance-submit-background-static",name:"submit-background-static",hide_label:!0,tooltip:Forminator.l10n.appearance.static,label:"",default_value:"#17A8E3"}),h=new Forminator.Settings.Color({model:this.model,id:"appearance-submit-background-hover",name:"submit-background-hover",hide_label:!0,tooltip:Forminator.l10n.appearance.hover,label:"",default_value:"#008FCA"}),g=new Forminator.Settings.Color({model:this.model,id:"appearance-submit-background-active",name:"submit-background-active",hide_label:!0,tooltip:Forminator.l10n.appearance.active,label:"",default_value:"#008FCA"});e.$el.find("#appearance-submit-background .wpmudev-pickers").append([_.$el,h.$el,g.$el]);var F=new Forminator.Settings.Color({model:this.model,id:"appearance-submit-color-static",name:"submit-color-static",hide_label:!0,tooltip:Forminator.l10n.appearance.static,label:"",default_value:"#FFFFFF"}),w=new Forminator.Settings.Color({model:this.model,id:"appearance-submit-color-hover",name:"submit-color-hover",hide_label:!0,tooltip:Forminator.l10n.appearance.hover,label:"",default_value:"#FFFFFF"}),$=new Forminator.Settings.Color({model:this.model,id:"appearance-submit-color-active",name:"submit-color-active",hide_label:!0,tooltip:Forminator.l10n.appearance.active,label:"",default_value:"#FFFFFF"});e.$el.find("#appearance-submit-color .wpmudev-pickers").append([F.$el,w.$el,$.$el]);var S=new Forminator.Settings.Color({model:this.model,id:"appearance-pagination-background-static",name:"pagination-background-static",hide_label:!0,tooltip:Forminator.l10n.appearance.static,label:"",default_value:"#333333"}),y=new Forminator.Settings.Color({model:this.model,id:"appearance-pagination-background-hover",name:"pagination-background-hover",hide_label:!0,tooltip:Forminator.l10n.appearance.hover,label:"",default_value:"#000000"}),C=new Forminator.Settings.Color({model:this.model,id:"appearance-pagination-background-active",name:"pagination-background-active",hide_label:!0,tooltip:Forminator.l10n.appearance.active,label:"",default_value:"#000000"});e.$el.find("#appearance-pagination-background .wpmudev-pickers").append([S.$el,y.$el,C.$el]);var A=new Forminator.Settings.Color({model:this.model,id:"appearance-pagination-color-static",name:"pagination-color-static",hide_label:!0,tooltip:Forminator.l10n.appearance.static,label:"",default_value:"#FFFFFF"}),k=new Forminator.Settings.Color({model:this.model,id:"appearance-pagination-color-hover",name:"pagination-color-hover",hide_label:!0,tooltip:Forminator.l10n.appearance.hover,label:"",default_value:"#FFFFFF"}),T=new Forminator.Settings.Color({model:this.model,id:"appearance-pagination-color-active",name:"pagination-color-active",hide_label:!0,tooltip:Forminator.l10n.appearance.active,label:"",default_value:"#FFFFFF"});e.$el.find("#appearance-pagination-color .wpmudev-pickers").append([A.$el,k.$el,T.$el]);var x=new Forminator.Settings.Color({model:this.model,id:"appearance-upload-background-static",name:"upload-background-static",hide_label:!0,tooltip:Forminator.l10n.appearance.static,label:"",default_value:"#333333"}),z=new Forminator.Settings.Color({model:this.model,id:"appearance-upload-background-hover",name:"upload-background-hover",hide_label:!0,tooltip:Forminator.l10n.appearance.hover,label:"",default_value:"#000000"}),R=new Forminator.Settings.Color({model:this.model,id:"appearance-upload-background-active",name:"upload-background-active",hide_label:!0,tooltip:Forminator.l10n.appearance.active,label:"",default_value:"#000000"});e.$el.find("#appearance-upload-background .wpmudev-pickers").append([x.$el,z.$el,R.$el]);var E=new Forminator.Settings.Color({model:this.model,id:"appearance-upload-color-static",name:"upload-color-static",hide_label:!0,tooltip:Forminator.l10n.appearance.static,label:"",default_value:"#FFFFFF"}),j=new Forminator.Settings.Color({model:this.model,id:"appearance-upload-color-hover",name:"upload-color-hover",hide_label:!0,tooltip:Forminator.l10n.appearance.hover,label:"",default_value:"#FFFFFF"}),U=new Forminator.Settings.Color({model:this.model,id:"appearance-upload-color-active",name:"upload-color-active",hide_label:!0,tooltip:Forminator.l10n.appearance.active,label:"",default_value:"#FFFFFF"});e.$el.find("#appearance-upload-color .wpmudev-pickers").append([E.$el,j.$el,U.$el]);var B=new Forminator.Settings.Color({model:this.model,id:"appearance-cform-error",name:"cform-error",hide_label:!0,default_value:"#AA1111"});e.$el.find("#appearance-error-elements .wpmudev-picker").append([B.$el]);var D=new Forminator.Settings.Color({model:this.model,id:"appearance-cform-filled",name:"cform-filled",hide_label:!0,default_value:"#777771"});e.$el.find("#appearance-filled-elements .wpmudev-picker").append([D.$el]),this.$el.find(".appearance-section-customize-colors").append(e.el)},render_custom_submit:function(){var e=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-custom-submit",name:"use-custom-submit",hide_label:!0,containerClass:"wpmudev-is_gray",values:[{value:"true",label:Forminator.l10n.appearance.use_custom_submit}]}),a=new Forminator.Settings.Text({model:this.model,id:"appearance-custom-submit-text",name:"custom-submit-text",placeholder:Forminator.l10n.appearance.custom_submit_text,label:Forminator.l10n.appearance.custom_text,hide_label:!0});e.$el.find(".wpmudev-option--switch_content").append(a.$el),this.$el.find(".appearance-section-custom-submit").append(e.el)},render_custom_invalid_form:function(){var e=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-custom-invalid-form",name:"use-custom-invalid-form",hide_label:!0,containerClass:"wpmudev-is_gray",values:[{value:"true",label:Forminator.l10n.appearance.use_custom_invalid_form}]}),a=new Forminator.Settings.Text({model:this.model,id:"appearance-custom-invalid-form-message",name:"custom-invalid-form-message",placeholder:Forminator.l10n.appearance.custom_invalid_form_message,label:Forminator.l10n.appearance.custom_text,hide_label:!0});e.$el.find(".wpmudev-option--switch_content").append(a.$el),this.$el.find(".appearance-section-custom-submit").append(e.el)},render_custom_css:function(){var e=new Forminator.Settings.ToggleContainer({model:this.model,id:"appearance-custom-css",name:"use-custom-css",hide_label:!0,containerClass:"wpmudev-is_gray",values:[{value:"true",label:Forminator.l10n.appearance.use_custom_css}]}),o=new a({model:this.model,property:"custom_css",element_id:"forminator_custom_css",selectors:[{selector:".forminator-custom-form ",label:"Form"},{selector:".forminator-custom-form .forminator-label--main ",label:"Main Label"},{selector:".forminator-custom-form .forminator-label--helper ",label:"Helper Label"},{selector:".forminator-custom-form .forminator-input ",label:"Text Input"},{selector:".forminator-custom-form .forminator-textarea ",label:"Textarea"},{selector:".forminator-custom-form .forminator-select + .select2 ",label:"Select"}]});e.$el.find(".wpmudev-option--switch_content").append(o.$el),this.$el.find(".appearance-section-custom-style").append(e.el)}})})}(jQuery);