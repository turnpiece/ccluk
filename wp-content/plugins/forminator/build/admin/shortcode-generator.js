!function(o,e){"use strict";!function(){o(document).ready(function(){t.init()})}();var t={init:function(){o("body").addClass("wpmudev-scgen-ui"),o(".wpmudev-tabs").tabs(),this.init_select2(),o(document).on("click","#forminator-generate-shortcode",this.open_modal),o(document).on("click","#forminator-popup-close",this.close_modal),o(document).on("click",".wpmudev-insert-cform",this.insert_form),o(document).on("click",".wpmudev-insert-poll",this.insert_poll),o(document).on("click",".wpmudev-insert-quiz",this.insert_quiz)},init_select2:function(){setTimeout(function(){o(".wpmudev-select").wpmuiSelect({allowClear:!1,minimumResultsForSearch:1/0,containerCssClass:"wpmudev-select2",dropdownCssClass:"wpmudev-select-dropdown"})},10)},open_modal:function(e){e.preventDefault(),e.stopPropagation();var t=(o(this),o("#forminator-popup")),n=t.find(".wpmudev-box-modal");t.addClass("wpmudev-modal-active"),o("body").addClass("wpmudev-modal-is_active"),setTimeout(function(){n.addClass("wpmudev-show")})},close_modal:function(o){o.preventDefault(),o.stopPropagation(),t.close()},close:function(){var e=o(".wpmudev-modal"),t=e.find(".wpmudev-box-section");e.removeClass("wpmudev-modal-active"),o("body").removeClass("wpmudev-modal-is_active"),t.removeClass("wpmudev-hide")},insert_form:function(e){e.preventDefault(),e.stopPropagation();var n=o(".forminator-custom-form-list").val();t.insert_shortcode("forminator_form",n)},insert_poll:function(e){e.preventDefault(),e.stopPropagation();var n=o(".forminator-insert-poll").val();t.insert_shortcode("forminator_poll",n)},insert_quiz:function(e){e.preventDefault(),e.stopPropagation();var n=o(".forminator-quiz-list").val();t.insert_shortcode("forminator_quiz",n)},insert_shortcode:function(o,e){var n="["+o+' id="'+e+'"]';window.parent.send_to_editor(n),t.close()}}}(jQuery,document);