!function(e,t){"use strict";!function(){e(document).ready(function(){"object"==typeof window.Forminator&&"object"==typeof window.Forminator.Utils&&Forminator.Utils.sui_delegate_events(),e("#wpf-cform-check_all").on("click",function(t){var n=this.checked,i=e(this).closest("table");if(e(i).find(".sui-checkbox input").each(function(){this.checked=n}),e('form[name="bulk-action-form"] input[name="ids"]').length){var c=e(i).find(".sui-checkbox input:checked").map(function(){if(parseFloat(this.value))return this.value}).get().join(",");e('form[name="bulk-action-form"] input[name="ids"]').val(c)}}),e(".sui-checkbox input").on("click",function(){if(e('form[name="bulk-action-form"] input[name="ids"]').length){var t=e(".sui-checkbox input:checked").map(function(){if(parseFloat(this.value))return this.value}).get().join(",");e('form[name="bulk-action-form"] input[name="ids"]').val(t)}}),e(".wpmudev-checkbox input#forminator-entries-all").on("click",function(){var t=this.checked;e(".wpmudev-entries--result .wpmudev-checkbox input").each(function(){this.checked=t})}),e(".wpmudev-check-all").on("click",function(t){t.preventDefault(),e(".fui-multicheck .fui-multicheck-item input").each(function(){this.checked=!0})}),e(".wpmudev-uncheck-all").on("click",function(t){t.preventDefault(),e(".fui-multicheck .fui-multicheck-item input").each(function(){this.checked=!1})}),e(".wpmudev-can--hide").ready(function(){e(this).find(".wpmudev-box-header").on("click",function(){e(this).closest(".wpmudev-can--hide").toggleClass("wpmudev-is--hidden")})}),e(document).on("click",".wpmudev-open-entry",function(t){if("checkbox"!==e(t.target).attr("type")&&!e(t.target).hasClass("wpdui-icon-check")){t.preventDefault(),t.stopPropagation();var n=e(this),i=n.data("entry"),c=e("#forminator-"+i),u=!0;c.hasClass("wpmudev-is_open")&&(u=!1),e(".wpmudev-entries--result").removeClass("wpmudev-is_open"),u&&c.toggleClass("wpmudev-is_open")}}),e(".wpmudev-result--menu").ready(function(){e(this).find(".wpmudev-button-action").on("click",function(){var t=e(this).next(".wpmudev-menu");e(".wpmudev-result--menu.wpmudev-active").removeClass("wpmudev-active"),e(".wpmudev-button-action.wpmudev-active").not(e(this)).removeClass("wpmudev-active"),e(".wpmudev-menu").not(t).addClass("wpmudev-hidden"),e(this).toggleClass("wpmudev-active"),t.toggleClass("wpmudev-hidden")})}),e(document).ready(function(){var t=e(".wpmudev-list"),n=t.find(".wpmudev-list-table"),i=n.find(".wpmudev-table-body tr"),c=i.length,u=c;i.each(function(){e(this).find(".wpmudev-body-menu").css("z-index",u),u--})})})}()}(jQuery,document);