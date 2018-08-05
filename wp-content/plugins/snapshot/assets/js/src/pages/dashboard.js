;(function ($) {
	// page ID or "slug"
	window.SS_PAGES.snapshot_page_snapshot_pro_dashboard = function () {

		var view_snapshot_key_button = function () {

			var modal_content = $("#ss-show-apikey").html();
			var after_render_modal = function () {
				$("#reset-api-key").click(function () {
					//SS_UTILS.openModal("NOPE", "Can't do that yet" );
					var reset_api_key_url = $(this).data("url");
					$("<a>").attr("href", reset_api_key_url).attr("target", "_blank")[0].click();
				});
			};

			SS_UTILS.openModal(snapshot_messages.snapshot_key, modal_content, after_render_modal);
		};

		$("#view-snapshot-key,#view-snapshot-key-2").click(view_snapshot_key_button);

	};
})(jQuery);
