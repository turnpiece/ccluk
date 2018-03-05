!function(t,e,o,r){"use strict";function a(e,o){this.element=e,this.$el=t(this.element),this.forminatorFront=null,this.settings=t.extend({},n,o),this._defaults=n,this._name=s,this.init()}var s="forminatorFrontSubmit",n={form_type:"custom-form",forminatorFront:!1,forminator_selector:""};t.extend(a.prototype,{init:function(){switch(this.settings.form_type){case"custom-form":this.settings.forminator_selector&&t(this.settings.forminator_selector).length||(this.settings.forminator_selector=".forminator-custom-form"),this.handle_submit_custom_form();break;case"quiz":this.settings.forminator_selector&&t(this.settings.forminator_selector).length||(this.settings.forminator_selector=".forminator-quiz"),this.handle_submit_quiz();break;case"poll":this.settings.forminator_selector&&t(this.settings.forminator_selector).length||(this.settings.forminator_selector=".forminator-poll"),this.handle_submit_poll()}},handle_submit_custom_form:function(){var o=this;t(this.element);o.$el.find(".forminator-cform-response-message").find(".forminator-label--success").not(":hidden").length&&o.focus_to_element(o.$el.find(".forminator-cform-response-message"),!0),t("body").on("submit.frontSubmit",this.settings.forminator_selector,function(r){var a=t(this),s=new FormData(this),n=a.find(".forminator-cform-response-message"),i=a.find(".forminator-g-recaptcha");if(i.length){i=t(i.get(0));var l=i.data("forminator-recapchta-widget"),f=i.data("size"),m=e.grecaptcha.getResponse(l);if("invisible"===f&&0===m.length)return e.grecaptcha.execute(l),!1;if(e.grecaptcha.reset(l),n.html(""),i.hasClass("error")&&i.removeClass("error"),0===m.length)return i.hasClass("error")||i.addClass("error"),n.html('<label class="forminator-label--error"><span>'+e.ForminatorFront.cform.captcha_error+"</span></label>"),o.focus_to_element(n),!1}return!o.$el.hasClass("forminator_ajax")||(n.html(""),n.html('<label class="forminator-label--info"><span>'+e.ForminatorFront.cform.processing+"</span></label>"),o.focus_to_element(n),r.preventDefault(),t.ajax({type:"POST",url:e.ForminatorFront.ajaxUrl,data:s,cache:!1,contentType:!1,processData:!1,beforeSend:function(){a.find("button").attr("disabled",!0)},success:function(r){a.find(".forminator-label--validation").remove(),a.find(".forminator-field").removeClass("forminator-has_error"),a.find("button").removeAttr("disabled"),n.html("");var s=r.success?"success":"error";void 0!==r.message?(n.html('<label class="forminator-label--'+s+'"><span>'+r.message+"</span></label>"),o.focus_to_element(n,"success"===s)):void 0!==r.data&&(s=r.data.success?"success":"error",n.html('<label class="forminator-label--'+s+'"><span>'+r.data.message+"</span></label>"),o.focus_to_element(n,"success"===s)),!r.data.success&&r.data.errors.length&&o.show_messages(r.data.errors),!0===r.success&&(a[0]&&(a[0].reset(),a.find(".forminator-upload--remove").hide(),a.find(".forminator-upload .forminator-input").val(""),a.find(".forminator-upload .forminator-label").html("No file chosen"),a.find(".forminator-select").each(function(){t(this).val(null).trigger("change")})),void 0!==r.data.url&&(e.location.href=r.data.url))},error:function(){a.find("button").removeAttr("disabled"),n.html(""),n.html('<label class="forminator-label--notice"><span>'+e.ForminatorFront.cform.error+"</span></label>"),o.focus_to_element(n)}}),!1)})},handle_submit_quiz:function(){var o=this;t("body").on("submit.frontSubmit",this.settings.forminator_selector,function(r){var a=t(this),s=[];return r.preventDefault(),o.$el.find(".forminator-has-been-disabled").removeAttr("disabled"),s=a.serialize(),o.$el.find(".forminator-has-been-disabled").attr("disabled","disabled"),t.ajax({type:"POST",url:e.ForminatorFront.ajaxUrl,data:s,beforeSend:function(){o.$el.find("button").attr("disabled","disabled")},success:function(t){t.success?"nowrong"===t.data.type?o.$el.find(".quiz-form-button-holder").html(t.data.result):"knowledge"===t.data.type&&(o.$el.find(".quiz-form-button-holder").size()>0&&o.$el.find(".quiz-form-button-holder").html(t.data.finalText),Object.keys(t.data.result).forEach(function(e){var r=o.$el.find("#"+e);r.find(".forminator-question--result").text(t.data.result[e].message),r.find(".forminator-submit-rightaway").attr("disabled","disabled");var a,s=o.$el.find('[id|="'+t.data.result[e].answer+'"]'),n=s.closest(".forminator-answer");a=t.data.result[e].isCorrect?"forminator-is_correct":"forminator-is_incorrect",n.addClass(a)})):o.$el.find("button").removeAttr("disabled")}}),!1}),t("body").on("click",".forminator-result--header button",function(){location.reload()})},handle_submit_poll:function(){var o=this;o.$el.find(".forminator-poll-response-message").find(".forminator-label--success").not(":hidden").length&&o.focus_to_element(o.$el.find(".forminator-poll-response-message"),!0),t("body").on("submit.frontSubmit",this.settings.forminator_selector,function(r){var a=(t(this),o.$el.find(".forminator-poll-response-message"));return!o.$el.hasClass("forminator_ajax")||(a.html(""),a.html('<label class="forminator-label--info"><span>'+e.ForminatorFront.poll.processing+"</span></label>"),o.focus_to_element(a),r.preventDefault(),t.ajax({type:"POST",url:e.ForminatorFront.ajaxUrl,data:o.$el.serialize(),beforeSend:function(){o.$el.find("button").attr("disabled",!0)},success:function(t){o.$el.find("button").removeAttr("disabled"),a.html("");var r=t.success?"success":"error";!1===t.success?(a.html('<label class="forminator-label--'+r+'"><span>'+t.data.message+"</span></label>"),o.focus_to_element(a)):void 0!==t.data&&(r=t.data.success?"success":"error",a.html('<label class="forminator-label--'+r+'"><span>'+t.data.message+"</span></label>"),o.focus_to_element(a,"success"===r)),!0===t.success&&void 0!==t.data.url&&(e.location.href=t.data.url)},error:function(){o.$el.find("button").removeAttr("disabled"),a.html(""),a.html('<label class="forminator-label--notice"><span>'+e.ForminatorFront.poll.error+"</span></label>"),o.focus_to_element(a)}}),!1)})},focus_to_element:function(o,r){r=r||!1,o.show(),t("html,body").animate({scrollTop:o.offset().top-(t(e).height()-o.outerHeight(!0))/2},500,function(){o.attr("tabindex")||o.attr("tabindex",-1),o.focus(),r&&o.show().delay(5e3).fadeOut("slow")})},show_messages:function(e){var o=this,r=o.$el.data("forminatorFrontCondition");return void 0!==r&&(this.$el.find(".forminator-label--validation").remove(),e.forEach(function(e){var o=Object.keys(e),a=Object.values(e),s=r.get_form_field(o);if(s.length){var n=t(s).closest(".forminator-field--inner");0===n.length&&(n=t(s).closest(".forminator-field"),0===n.length&&(n=t(s).find(".forminator-field"),n.length>1&&(n=n.first())));var i=n.find(".forminator-label--validation");0===i.length&&(n.append('<label class="forminator-label--validation"></label>'),i=n.find(".forminator-label--validation")),t(s).attr("aria-invalid","true"),i.text(a),n.addClass("forminator-has_error")}})),this}}),t.fn[s]=function(e){return this.each(function(){t.data(this,s)||t.data(this,s,new a(this,e))})}}(jQuery,window,document);