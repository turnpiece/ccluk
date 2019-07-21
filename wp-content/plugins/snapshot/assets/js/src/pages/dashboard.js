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

	window.SS_PAGES.toplevel_page_snapshot_pro_dashboard = function () {

		var Sfb = window.Sfb || {
			value: function (key) {
				return (window._snp_vars || {})[key];
			},
			l10n: function (key) {
				return (this.value('l10n') || {})[key] || key;
			}
		};

		/**
		 * Deals with listing the backup list
		 *
		 * @type {Object}
		 */
		Sfb.ListDashBackups = {
			loadBackups: function () {
				var data = {
					security: jQuery( '#snapshot-ajax-nonce' ).val()
				};
				$('.wps-hosting-backup-list-loader').show();
				$('.wps-hosting-backup-no-backup').hide();
				$('.wps-backup-list-ajax-error').hide();
				$('.wps-hosting-backup-list').hide();

				var snapshot_href = ajaxurl + '?action=snapshot-hosting_backup-dashboard-list';

				snapshot_ajax_lhb_xhr = jQuery.ajax({
					type: 'POST',
					url: snapshot_href,
					data: data,
					cache: false,
					dataType: 'json',
					error: function (jqXHR, textStatus, errorThrown) {
						$('.wps-hosting-backup-list-loader').hide();
						$('.wps-backup-list-ajax-error').show();
					},
					success: function (reply_data) {
						if (reply_data.success && reply_data.data.backups !== undefined) {
							$('.wps-hosting-backup-list-loader').hide();
							var backupsCount = typeof reply_data.data.backups !== "undefined" && reply_data.data.backups !== null ? reply_data.data.backups.length : 0;
							$('.wps-hosting-backups-count').text(backupsCount).show();
							
							if (backupsCount !== 0) {
								$('.wps-hosting-backup-list').show();
								$('.wps-hosting-backup-no-backup').hide();
								$('#wps-hosting-backups-disclaimer').show();
								$('.wps-count.wps-hosting-backups-count').show();

								$('#my-hosting-backups-table > tbody:last-child').html('');

								$.each(reply_data.data.backups, function(i, item) {
									if(item.tooltip) {
										iconMarkup = '<div class="wps-tooltip sui-tooltip sui-tooltip-constrained sui-tooltip-top-right" data-tooltip="' + item.tooltip + '"><i class="wps-icon '+item.icon+'"></i></div>';
									} else {
										iconMarkup = '<i class="wps-icon '+item.icon+'"></i>';
									}

									$('#my-hosting-backups-table > tbody:last-child').append(
										'<tr><td class="msc-name">'+ iconMarkup + item.link +'</td>'+
										'<td class="msc-type" data-title="Type:">'+item.type+'</td>'+
										'<td class="msc-context" data-title="Frequency:">'+item.context+'</td>'
									);
								});
							} else {
								$('.wps-hosting-backup-list').hide();
								$('.wps-hosting-backup-no-backup').show();
								$('#wps-hosting-backups-disclaimer').hide();
								$('.wps-count.wps-hosting-backups-count').hide();
							}
						} else {
							if (reply_data.data.not_wpmudev_hosting===undefined) {
								$('.wps-hosting-backup-list-loader').hide();
								$('.wps-backup-list-ajax-error').show();
							}
						}
					}
				});
			}
		}

		$(document).on('click', '.wps-reload-backup-listing', function () {
			Sfb.ListDashBackups.loadBackups();
		});

		$(function () {
			Sfb.ListDashBackups.loadBackups();
		});
	}
	
})(jQuery);
