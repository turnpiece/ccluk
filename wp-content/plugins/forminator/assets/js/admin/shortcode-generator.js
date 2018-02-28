(function ($, doc) {
	"use strict";

	(function () {
		$(document).ready(function () {
			Forminator_Shortcode_Generator.init();
		});

	}());

	var Forminator_Shortcode_Generator = {
		init: function () {
			// Add proper class to page body
			$('body').addClass('wpmudev-ui');

			// Init modal tabs
			$(".wpmudev-tabs").tabs();

			// Init select2 library
			this.init_select2();

			// Handle modal open click
			$(document).on("click", "#forminator-generate-shortcode", this.open_modal );

			// Handle modal close click
			$(document).on("click", "#forminator-popup-close", this.close_modal );

			// Handle modal custom form insert
			$(document).on("click", ".wpmudev-insert-cform", this.insert_form );

			// Handle modal poll insert
			$(document).on("click", ".wpmudev-insert-poll", this.insert_poll );

			// Handle modal quiz insert
			$(document).on("click", ".wpmudev-insert-quiz", this.insert_quiz );
		},

		init_select2: function () {
			setTimeout( function(){
				$( ".wpmudev-select" ).wpmuiSelect({
					allowClear: false,
					minimumResultsForSearch: Infinity,
					containerCssClass: "wpmudev-select2",
					dropdownCssClass: "wpmudev-select-dropdown"
				});
			}, 10 );
		},

		open_modal: function (e) {
			e.preventDefault();
			e.stopPropagation();

			var $this = $(this),
				$modal = $("#forminator-popup"),
				$content = $modal.find(".wpmudev-box-modal")
			;

			$modal.addClass("wpmudev-modal-active");
			$("body").addClass("wpmudev-modal-is_active");

			setTimeout(function(){
				$content.addClass("wpmudev-show");
			});
		},

		close_modal: function (e) {
			e.preventDefault();
			e.stopPropagation();

			Forminator_Shortcode_Generator.close();
		},

		close: function () {
			var $modal = $('.wpmudev-modal'),
				$content = $modal.find(".wpmudev-box-section");

			$modal.removeClass('wpmudev-modal-active');
			$('body').removeClass('wpmudev-modal-is_active');
			$content.removeClass('wpmudev-hide');
		},

		insert_form: function (e) {
			e.preventDefault();
			e.stopPropagation();

			var module_id = $('.forminator-custom-form-list').val();
			Forminator_Shortcode_Generator.insert_shortcode('forminator_form', module_id);
		},

		insert_poll: function (e) {
			e.preventDefault();
			e.stopPropagation();

			var module_id = $('.forminator-insert-poll').val();
			Forminator_Shortcode_Generator.insert_shortcode('forminator_poll', module_id);
		},

		insert_quiz: function (e) {
			e.preventDefault();
			e.stopPropagation();

			var module_id = $('.forminator-quiz-list').val();
			Forminator_Shortcode_Generator.insert_shortcode('forminator_quiz', module_id);
		},

		insert_shortcode: function (module, id) {
			var shortcode = '[' + module + ' id="' + id + '"]';
			window.parent.send_to_editor( shortcode );

			Forminator_Shortcode_Generator.close();
		}
	};
}(jQuery, document));
