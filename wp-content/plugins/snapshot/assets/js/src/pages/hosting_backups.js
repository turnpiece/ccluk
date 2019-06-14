(function ($) {

	// page ID or "slug"
	window.SS_PAGES.snapshot_page_snapshot_pro_hosting_backups = function () {

		var Sfb = window.Sfb || {
			value: function (key) {
				return (window._snp_vars || {})[key];
			},
			l10n: function (key) {
				return (this.value('l10n') || {})[key] || key;
			}
		};

		var ElementSelector = {
			/**
			 * Used to get all applicable element anchors
			 *
			 * @type {String}
			 */
			element_selector: '',

			/**
			 * Gets applicable elements for targeting
			 *
			 * @uses this.element_selector
			 *
			 * @return {Object} jQuery nodes object
			 */
			get_elements: function () {
				return $(this.element_selector);
			},

			/**
			 * Initializes event listeners
			 *
			 * @return {Boolean}
			 */
			initialize_listeners: function () {
				var me = this;
				this.get_elements().each(function () {
					me.initialize_listener($(this));
				});
				return true;
			},

			/**
			 * Initializes individual event listener for a node
			 *
			 * @param {object} $el jQuery node
			 */
			initialize_listener: function ($el) {
			}
		};

		/**
		 * Shared utilities
		 *
		 * @type {Object}
		 */
		Sfb.Util = {
			/**
			 * Stops event propagation
			 *
			 * @param {Object} e Event
			 *
			 * @return {Boolean} False
			 */
			stop_prop: function (e) {
				if (e && e.preventDefault) e.preventDefault();
				if (e && e.stopPropagation) e.stopPropagation();
				return false;
			}
		};

		/**
		 * Deals with any currently running backups on pageload
		 *
		 * @type {Object}
		 */
		Sfb.DealWithCurrentBackup = {
			show_current_backup_state: function () {
				var data = {
					security: jQuery( '#snapshot-ajax-nonce' ).val()
				};
	
				var snapshot_href = ajaxurl + '?action=snapshot-deal_with_current_backup';
	
				snapshot_ajax_lhb_xhr = jQuery.ajax({
					type: 'POST',
					url: snapshot_href,
					data: data,
					cache: false,
					dataType: 'json',
					error: function (jqXHR, textStatus, errorThrown) {
					},
					success: function (reply_data) {

						if (reply_data.is_done !== undefined) {
							// This means that there's currently a backup being created.
							$('.wps-new-hosting-backup-state').show();

							if (reply_data.is_done===true) {
								Sfb.StartHostingBackups.Viewer.show_backup_result('completed', false);
							} else if (reply_data.error===true) {
								Sfb.StartHostingBackups.Viewer.show_backup_result('errored', false);
							} else {
								Sfb.StartHostingBackups.Viewer.show_backup_result('in progress', false);

								$('.wps-page-hosting-backups input[name="start_hosting_backup"]').addClass('button-disabled');
								$('.wps-page-hosting-backups input[name="start_hosting_backup"]').attr('disabled','disabled');
								$('.wps-page-hosting-backups input[name="start_first_hosting_backup"]').addClass('button-disabled');
								$('.wps-page-hosting-backups input[name="start_first_hosting_backup"]').attr('disabled','disabled');
							}
						} else {
							$('.wps-new-hosting-backup-state').hide();
						}
					}
				});
			}
		}

		/**
		 * Deals with listing the backup list
		 *
		 * @type {Object}
		 */
		Sfb.ListHostingBackups = {
			snapshot_list_hosted_backups: function () {
				var data = {
					security: jQuery( '#snapshot-ajax-nonce' ).val()
				};
				$('.wps-hosting-backup-list-loader').show();
				$('.wps-my-hosting-backups').hide();
				$('.wps-no-hosting-managed-backups').hide();
				$('.wps-backup-list-ajax-error').hide();

				var snapshot_href = ajaxurl + '?action=snapshot-hosting_backup-list';
	
				snapshot_ajax_lhb_xhr = jQuery.ajax({
					type: 'POST',
					url: snapshot_href,
					data: data,
					cache: false,
					dataType: 'json',
					error: function (jqXHR, textStatus, errorThrown) {
						$('.wps-backup-list-ajax-error').show();
						$('.wps-hosting-backup-list-loader').hide();
					},
					success: function (reply_data) {
	
						if (reply_data.success && reply_data.data.backups !== undefined) {
							$('.wps-hosting-backup-list-loader').hide();
							$('.wps-my-hosting-backups').show();

							if ( reply_data.data.backups && reply_data.data.backups.length !== 0) {
								var no_backups = true;

								// Deal with the Last Backup section.
								lastBackup = reply_data.data.backups[0];
								var lastBackupEl = $('.wps-hosting-backups-last-backup');
								lastBackupEl.find('.wps-hosting-spinner').remove();
								lastBackupEl.html(lastBackup.date + '<span>' + lastBackup.time + '</span>' );

								// Deal with the backup listing and loader.
								$('.wps-page-hosting-backups .wps-my-hosting-backups').show();
								$('.wps-page-hosting-backups input[name="start_hosting_backup"]').show();
								$('.wps-page-hosting-backups .wps-available-backups-header').show();
								$('.wps-page-hosting-backups .wps-no-available-backups-header').hide();
								$('.wps-page-hosting-backups .wps-no-hosting-managed-backups').hide();
								$('#wps-hosting-backups-disclaimer').show();

								$('#my-hosting-backups-table > tbody:last-child').html('');
								$.each(reply_data.data.backups, function(i, item) {
									var iconMarkup;

									if(item.tooltip) {
										iconMarkup = '<div class="wps-tooltip sui-tooltip sui-tooltip-constrained sui-tooltip-top-right" data-tooltip="' + item.tooltip + '"><i class="wps-icon '+item.icon+'"></i></div>';
									} else {
										iconMarkup = '<i class="wps-icon '+item.icon+'"></i>';
									}
									$('#my-hosting-backups-table > tbody:last-child').append(
										'<tr><td class="msc-name">'+ iconMarkup + item.link +'</td>'+
										'<td class="msc-type" data-title="Type:">'+item.type+'</td>'+
										'<td class="msc-context" data-title="Frequency:">'+item.context+'</td>'+
										'<td class="msc-info">'+item.menu+'</td></tr>'
									);
								});
							} else {
								var no_backups = false;

								// Deal with the Last Backup section.
								lastBackup = "Never";
								var lastBackupEl = $('.wps-hosting-backups-last-backup');
								lastBackupEl.find('.wps-hosting-spinner').remove();
								lastBackupEl.html(lastBackup);

								// Deal with the backup listing and loader.
								$('.wps-page-hosting-backups .wps-my-hosting-backups').hide();
								$('.wps-page-hosting-backups input[name="start_hosting_backup"]').hide();
								$('.wps-page-hosting-backups .wps-available-backups-header').hide();
								$('.wps-page-hosting-backups .wps-no-available-backups-header').show();
								$('.wps-page-hosting-backups .wps-no-hosting-managed-backups').css( 'display', 'flex');
								$('#wps-hosting-backups-disclaimer').hide();
							}

							if (reply_data.data.is_done !== undefined) {
								// This means that there's currently a backup being created.
								if (reply_data.data.is_done===true) {
									Sfb.StartHostingBackups.Viewer.show_backup_result('completed', no_backups);
									Sfb.StartHostingBackups.Viewer.toggle_new_backup('enable');
								} else if (reply_data.data.error===true) {
									Sfb.StartHostingBackups.Viewer.show_backup_result('errored', no_backups);
									Sfb.StartHostingBackups.Viewer.toggle_new_backup('enable');
								} else {
									Sfb.StartHostingBackups.Viewer.show_backup_result('in progress', no_backups);
									Sfb.StartHostingBackups.Viewer.toggle_new_backup('disable');
									Sfb.StartHostingBackups.Viewer.handle_view(null, no_backups);
								}
							}
							
							// Initialize events after AJAX complete
							Sfb.ListHostingBackups.initialize_ajax_actions();

							// Run backup restore events
							Sfb.RestoreBackup.run();
							// Run backup export events
							Sfb.ExportBackup.run();
						} else {
							$('.wps-backup-list-ajax-error').show();
							$('.wps-hosting-backup-list-loader').hide();
						}
					}
				});
			},

			/**
			 * Backup actions events
			 */
			initialize_ajax_actions: function () {
				// Menu options
				$('.wps-menu-dots').each(function () {
					var $dots = $(this),
						$menu = $dots.parent('.wps-menu'),
						$all_menu = $('.wps-menu').not($menu);

					$dots.on('click', function () {
						$all_menu.removeClass('open');
						$menu[$menu.hasClass('open') ? 'removeClass' : 'addClass']('open');
					});
				});
			}
		}

		/**
		 * Deals with starting new hosting backup
		 *
		 * @type {Object}
		 */
		Sfb.StartHostingBackups = {

			/**
			 * Backup creator toggle
			 *
			 * @type {ElementSelector}
			 */
			Viewer: $.extend({}, ElementSelector, {

				element_selector: '.wps-page-hosting-backups input[name="start_hosting_backup"]',

				/**
				 * Refreshing period.
				 *
				 * This is how long the results will stay before we attempt reload.
				 *
				 * @type {Number}
				 */
				ttl: 30000,

				/**
				 * Shows up the in progress/successful/errored backup messages
				 * 
				 * @param {String} backup_state Whether the running backup is in progress/successful/errored
				 * @param {Boolean} first_backup Whether the running backup is the first one for that site
				 *  
				 */
				show_backup_result: function (backup_state, first_backup) {

					$('.wps-new-hosting-backup-state').show();
					if (backup_state === 'in progress') {
						window.hidden_result_notification = false;
						
						if (window.hidden_progress_loader===undefined || !window.hidden_progress_loader) {
							$('.wps-new-hosting-backup-state .wps-progress-loader').show();
							$('.wps-my-hosting-backups .wps-progress-loader-row').css("display", "table-row");
							$('.wps-new-hosting-backup-state .wps-progress-success').hide();
							$('.wps-new-hosting-backup-state .wps-progress-error').hide();

							if (first_backup) {
								$('.wps-page-hosting-backups .wps-my-hosting-backups').show();
								$('.wps-page-hosting-backups .wps-no-hosting-managed-backups').hide();
							}
						}
					} else if (backup_state === 'completed') {
						window.hidden_progress_loader = false;

						if (window.hidden_result_notification===undefined || !window.hidden_result_notification) {
							$('#my-hosting-backups-table').removeClass('in-progress-row');
							$('.wps-new-hosting-backup-state .wps-progress-loader').hide();
							$('.wps-my-hosting-backups .wps-progress-loader-row').hide();
							$('.wps-new-hosting-backup-state .wps-progress-success').show();
							$('.wps-new-hosting-backup-state .wps-progress-error').hide();
							if (first_backup) {
								$('.wps-page-hosting-backups .wps-my-hosting-backups').show();
								$('.wps-page-hosting-backups .wps-no-hosting-managed-backups').hide();
							}
						}	
					} else if (backup_state === 'errored') {
						window.hidden_progress_loader = false;

						if (window.hidden_result_notification===undefined || !window.hidden_result_notification) {
							$('#my-hosting-backups-table').removeClass('in-progress-row');
							$('.wps-new-hosting-backup-state .wps-progress-loader').hide();
							$('.wps-my-hosting-backups .wps-progress-loader-row').hide();
							$('.wps-new-hosting-backup-state .wps-progress-success').hide();
							$('.wps-new-hosting-backup-state .wps-progress-error').show();
							if (first_backup) {
								$('.wps-page-hosting-backups .wps-my-hosting-backups').show();
								$('.wps-page-hosting-backups .wps-no-hosting-managed-backups').hide();
							}
						}
					}
				},

				/**
				 * Enable/Disable the New Backup button while a backup is in progress.
				 */
				toggle_new_backup: function (toggle) {
					if (toggle === 'disable') {
						$('.wps-page-hosting-backups input[name="start_hosting_backup"]').addClass('button-disabled');
						$('.wps-page-hosting-backups input[name="start_hosting_backup"]').attr('disabled','disabled');
					} else {
						$('.wps-page-hosting-backups input[name="start_hosting_backup"]').removeClass('button-disabled');
						$('.wps-page-hosting-backups input[name="start_hosting_backup"]').removeAttr('disabled');
					}
				},

				/**
				 * Handles backup creator spawning clicks
				 *
				 * @param {Object} e Event
				 * @param {Boolean} first_backup Whether there any previous backups in there.
				 *
				 * @return {Boolean}
				 */
				handle_view: function (e, first_backup) {
					ttl = Sfb.StartHostingBackups.Viewer.ttl ? parseInt(Sfb.StartHostingBackups.Viewer.ttl, 10) : 30000;

					Sfb.StartHostingBackups.Viewer.show_backup_result('in progress', first_backup);
					Sfb.StartHostingBackups.Viewer.toggle_new_backup('disable');

					$('#my-hosting-backups-table').addClass('in-progress-row');
					var snapshot_href = ajaxurl + '?action=snapshot-hosting_backup-create';

					(function snapshot_new_hosted_backup(older_backup) {
						// if ($('.my-hosting-backups-table').length && $('.my-hosting-backups-table').is('.show')) {

							var data = {
								older_backup: older_backup,
								security: jQuery( '#snapshot-ajax-nonce' ).val()
							};

							snapshot_ajax_nhb_xhr = jQuery.ajax({
								type: 'POST',
								url: snapshot_href,
								data: data,
								cache: false,
								dataType: 'json',
								error: function (jqXHR, textStatus, errorThrown) {
									Sfb.StartHostingBackups.Viewer.show_backup_result('errored', first_backup);
									Sfb.StartHostingBackups.Viewer.toggle_new_backup('enable');
								},
								success: function (reply_data) {

									if (reply_data.is_done === false) {
										if (reply_data.error !== undefined && reply_data.error === true) {
											Sfb.StartHostingBackups.Viewer.show_backup_result('errored', first_backup);
											Sfb.StartHostingBackups.Viewer.toggle_new_backup('enable');
										} else {
											Sfb.StartHostingBackups.Viewer.show_backup_result('in progress', first_backup);

											if (reply_data.older_backup === undefined || reply_data.older_backup !== true) {
												setTimeout(function () {
													snapshot_new_hosted_backup(false);
												}, ttl);
												Sfb.StartHostingBackups.Viewer.toggle_new_backup('disable');
											} else {
												setTimeout(function () {
													snapshot_new_hosted_backup(true);
												}, ttl);
												Sfb.StartHostingBackups.Viewer.toggle_new_backup('disable');
											}
										}
									} else {
										Sfb.StartHostingBackups.Viewer.show_backup_result('completed', first_backup);
										Sfb.StartHostingBackups.Viewer.toggle_new_backup('enable');

										// Refresh the backup listing.
										Sfb.ListHostingBackups.snapshot_list_hosted_backups();
									}
								}
							});
						// }
					})();

					return Sfb.Util.stop_prop(e);
				},

				/**
				 * Initializes backup creator click listener
				 *
				 * @param {object} $el jQuery node
				 */
				initialize_listener: function ($el) {
					$el
						.off("click.snapshot")
						.on("click.snapshot", this.handle_view)
					;
				},

				/**
				 * Initializes backup creator
				 *
				 * @return {Boolean}
				 */
				run: function () {
					this.initialize_listeners();
					return true;
				}
			}),

			/**
			 * Initializes the whole backup creator section JS
			 *
			 * @return {Boolean}
			 */
			run: function () {
				Sfb.StartHostingBackups.Viewer.run();

				return true;
			}
		};

		/**
		 * Deals with restoring hosting backup
		 *
		 * @type {Object}
		 */
		Sfb.RestoreBackup = {
			/**
			 * Backup restore toggle
			 *
			 * @type {ElementSelector}
			 */
			Viewer: $.extend({}, ElementSelector, {
				
				element_selector: 'a.snapshot-hosting-backup-restore',

				/**
				 * Refreshing period.
				 *
				 * This is how long the results will stay before we attempt reload.
				 *
				 * @type {Number}
				 */
				ttl: 30000,

				/**
				 * Shows up the in progress/successful/errored backup messages
				 * 
				 * @param {String} backup_state Whether the running backup is in progress/successful/errored
				 * @param {String} backup_id The ID of the backup that failed to restore
				 *  
				 */
				show_restore_result: function (restore_state,backup_id) {

					$('.wps-new-hosting-backup-state').show();
					if (restore_state === 'in progress') {
						$('.wps-my-hosting-backups .wps-restore-progress-loader-row').css("display", "table-row");
						$('.wps-new-hosting-backup-state .wps-restore-progress-success').hide();
						$('.wps-new-hosting-backup-state .wps-restore-progress-error').hide();
					} else if (restore_state === 'completed') {
						$('#my-hosting-backups-table').removeClass('in-progress-row');
						$('.wps-my-hosting-backups .wps-restore-progress-loader-row').hide();
						$('.wps-new-hosting-backup-state .wps-restore-progress-success').show();
						$('.wps-new-hosting-backup-state .wps-restore-progress-error').hide();
					} else if (restore_state === 'errored') {
						$('#my-hosting-backups-table').removeClass('in-progress-row');
						$('.wps-my-hosting-backups .wps-restore-progress-loader-row').hide();
						$('.wps-new-hosting-backup-state .wps-restore-progress-success').hide();
						$('.wps-new-hosting-backup-state .wps-restore-progress-error').show();
						
						$('.wps-restore-progress-error .snapshot-hosting-backup-restore').attr("data-backup-id",backup_id);
					}
				},

				/**
				 * Handles backup restore clicks
				 *
				 * @param {Object} e Event
				 *
				 * @return {Boolean}
				 */
				handle_view: function (e) {
					$menu = $(e.target).parents(".wps-menu");
					$menu.removeClass('open');
					
					Sfb.RestoreBackup.Viewer.show_restore_result('in progress');
					ttl = Sfb.RestoreBackup.Viewer.ttl ? parseInt(Sfb.RestoreBackup.Viewer.ttl, 10) : 30000;

					var snapshot_href = ajaxurl + '?action=snapshot-hosting_backup-restore';

					// Hook into logging out, in order to re-login and be able to use clear_ongoing_backup(), whenever that's possible.
					Sfb.RestoreBackup.Viewer.perform_auth_check();
					window.clear_ongoing_backup_performed = false;
					
					$('#my-hosting-backups-table').addClass('in-progress-row');
					Sfb.StartHostingBackups.Viewer.toggle_new_backup('disable');

					(function snapshot_restore_hosted_backup() {
						backup_id = $(e.target).data('backup-id');

						var data = {
							backup_id: backup_id,
							security: jQuery( '#snapshot-ajax-nonce' ).val()
						};

						snapshot_ajax_rhb_xhr = jQuery.ajax({
							type: 'POST',
							url: snapshot_href,
							data: data,
							cache: false,
							dataType: 'json',
							error: function (jqXHR, textStatus, errorThrown) {
								$('.wps-restore-progress-error a.snapshot-hosting-backup-restore').attr("data-backup-id",backup_id);
								Sfb.RestoreBackup.Viewer.show_restore_result('errored', backup_id);
								Sfb.StartHostingBackups.Viewer.toggle_new_backup('enable');
							},
							success: function (reply_data) {
								if( typeof reply_data.action_id !== "undefined" && reply_data.action_id !== "" && reply_data.action_id ) {
									// Since we received an action_id for the attempted restore, lets go into the handle_ongoing_restore method,
									// where we'll handle the appropriate response about the restore's status.
									Sfb.RestoreBackup.Viewer.handle_ongoing_restore(reply_data.action_id, reply_data.api_key, reply_data.site_id);
								} else {
									$('.wps-restore-progress-error a.snapshot-hosting-backup-restore').attr("data-backup-id",backup_id);
									Sfb.RestoreBackup.Viewer.show_restore_result('errored', backup_id);
									Sfb.StartHostingBackups.Viewer.toggle_new_backup('enable');
								}
							}
						});
					})();

					return Sfb.Util.stop_prop(e);
				},

				/**
				 * Handles the UI of an initiated restore.
				 *
				 * @param {string} action_id API ID of the actual restore action.
				 * @param {string} api_key WPMUDEV Dashboard ID.
				 * @param {string} site_id WPMUDEV Hosting site ID.
				 */
				handle_ongoing_restore: function (action_id, api_key, site_id) {
					ttl = 30000;
					var api_call_href = 'https://premium.wpmudev.org/api/hosting/v1/' + site_id + '/actions/' + action_id;

					snapshot_ajax_rs_xhr = jQuery.ajax({
						type: 'GET',
						url: api_call_href,
						headers: {
							'Authorization': api_key,
						},
						cache: false,
						timeout: 60000,
						dataType: 'json',
						error: function (jqXHR, textStatus, errorThrown) {
							setTimeout(function () {
								Sfb.RestoreBackup.Viewer.handle_ongoing_restore(action_id, api_key, site_id);
							}, ttl);

						},
						success: function (reply_data) {
							if (reply_data.status !== undefined && reply_data.status === 'completed') {
								Sfb.StartHostingBackups.Viewer.toggle_new_backup('enable');
								Sfb.RestoreBackup.Viewer.show_restore_result('completed');

								Sfb.RestoreBackup.Viewer.perform_auth_check();
								// If no clear_ongoing_backup() is performed successfully already, do that as the last part of the restore process. 
								if (!window.clear_ongoing_backup_performed) {
									Sfb.RestoreBackup.Viewer.clear_ongoing_backup();
								}
							} else if (reply_data.status !== undefined && reply_data.status === 'errored') {
								Sfb.StartHostingBackups.Viewer.toggle_new_backup('enable');
								Sfb.RestoreBackup.Viewer.show_restore_result('errored', backup_id);
							} else {
								Sfb.RestoreBackup.Viewer.show_restore_result('in progress');
								setTimeout(function () {
									Sfb.RestoreBackup.Viewer.handle_ongoing_restore(action_id, api_key, site_id);
								}, ttl);
							}
						}
					});
				},

				/**
				 * When we restore a backup that was made through the Snapshot UI,
				 * the restored db will always say that there is a backup in progress, even though there isn't.
				 * Lets delete that entry.
				 */
				clear_ongoing_backup: function (nonce) {
					var snapshot_href = ajaxurl + '?action=snapshot-ongoing_backup_after_restore';

					// Nonce will be either retrieved from when we'll re-login (if we got logged out), or from the current open screen (if we didn't get logged out).
					security = nonce || $( '#snapshot-ajax-nonce' ).val();
					var data = {
						security: security
					};

					snapshot_ajax_rs_xhr = jQuery.ajax({
						type: 'POST',
						url: snapshot_href,
						cache: false,
						data: data,
						timeout: 60000,
						dataType: 'json',
						error: function (jqXHR, textStatus, errorThrown) {},
						success: function (reply_data) {
							window.clear_ongoing_backup_performed = true;
						}
					});

				},

				perform_auth_check: function () {
					$( document ).on(
						'heartbeat-tick.wp-auth-check',
						Sfb.RestoreBackup.Viewer.check_auth_context
					);

					if ( ( ( wp || {} ).heartbeat || {} ).connectNow ) {
						wp.heartbeat.connectNow();
						return true;
					}
					return false;
				},
				clear_auth_context_check_and_continue: function () {
					$( document ).off(
						'heartbeat-tick.wp-auth-check',
						Sfb.RestoreBackup.Viewer.check_auth_context
					);
					url = window.location.pathname + '?page=snapshot_pro_hosting_backups';

					// New auth context - exchange nonces before continuing.
					$.get( url, function ( data ) {
						var nonce = $( data ).find( '#snapshot-ajax-nonce' ).val();
						Sfb.RestoreBackup.Viewer.clear_ongoing_backup(nonce);
					} );
				},
				check_auth_context: function ( event, data ) {
					// Did we just get logged out?
					if ( ! ( 'wp-auth-check' in data ) ) {
						return false;
					}
					if (data['wp-auth-check']) {
						return true;
					}

					var parent = window,
						$root = $( '#wp-auth-check-wrap' ),
						$iframe = $root.find( 'iframe' )
					;

					// Handle the login popup.
					if ( $root.length && !$root.is( '.snapshot-bound' ) && $iframe.length ) {
						$root.addClass( 'snapshot-bound' );
						$iframe.on( 'load', function() {
							var $body = $( this ).contents().find( 'body' );
							if ( $body.is( '.interim-login-success' ) ) {
								setTimeout( function() {
									Sfb.RestoreBackup.Viewer.clear_auth_context_check_and_continue();
									$root.removeClass( 'snapshot-bound' );
								} );
							}
						});
					}
				},

				/**
				 * Initializes backup creator click listener
				 *
				 * @param {object} $el jQuery node
				 */
				initialize_listener: function ($el) {
					$el
						.off("click.snapshot")
						.on("click.snapshot", this.handle_view)
					;
				},

				/**
				 * Initializes backup creator
				 *
				 * @return {Boolean}
				 */
				run: function () {
					this.initialize_listeners();
					return true;
				}
			}),

			/**
			 * Initializes the whole backup creator section JS
			 *
			 * @return {Boolean}
			 */
			run: function () {
				Sfb.RestoreBackup.Viewer.run();

				return true;
			}
		}

		/**
		 * Deals with exporting a hosting backup
		 *
		 * @type {Object}
		 */
		Sfb.ExportBackup = {
			/**
			 * Backup export toggle
			 *
			 * @type {ElementSelector}
			 */
			Viewer: $.extend({}, ElementSelector, {
				
				element_selector: 'a.snapshot-hosting-backup-export',

				/**
				 * Handles backup export clicks
				 *
				 * @param {Object} e Event
				 *
				 * @return {Boolean}
				 */
				handle_view: function (e) {

					var snapshot_href = ajaxurl + '?action=snapshot-hosting_backup-export';
					clearTimeout(window.fadeOutTimeout);

					(function snapshot_export_hosted_backup( backup_id ) {
						if( typeof backup_id === "undefined" || backup_id === "" ) {
							backup_id = $(e.target).data('backup-id');
						}
						var data = {
							backup_id: backup_id,
							security: jQuery( '#snapshot-ajax-nonce' ).val()
						};

						snapshot_ajax_ehb_xhr = jQuery.ajax({
							type: 'POST',
							url: snapshot_href,
							data: data,
							cache: false,
							dataType: 'json',
							error: function (jqXHR, textStatus, errorThrown) {
								var export_sent = false;
								Sfb.ExportBackup.Viewer.show_export_notifications(export_sent, reply_data.data);
							},
							success: function (reply_data) {
								if(reply_data.success !== undefined && reply_data.success) {
									var export_sent = true;
									Sfb.ExportBackup.Viewer.show_export_notifications(export_sent, reply_data.data);
								}
								
								if(reply_data.success !== undefined && ! reply_data.success) {
									var export_sent = false;
									Sfb.ExportBackup.Viewer.show_export_notifications(export_sent, reply_data.data);
								}
							}
						});
					})();

					return Sfb.Util.stop_prop(e);
				},

				/**
				 * Deals with building, showing and add events for the export notifications
				 *
				 * @param {Boolean} sent whether the export attempt was successful
				 */
				show_export_notifications: function (sent, message) {
					if (sent) {
						var shown_notification = "wps-success-message",
							hidden_notification = "wps-error-message";
					} else {
						var shown_notification = "wps-error-message",
							hidden_notification = "wps-success-message";						
					}
					$('.snapshot-three.wps-message.' + hidden_notification).hide();

					$('.snapshot-three.wps-message.' + shown_notification + ' p').html('<i class="wps-icon i-close wps-popup-close wps-dismiss-notice"></i>' + message);
					$('.snapshot-three.wps-message.' + shown_notification).addClass("wps-hosting-backups-message").css({"display": "block", "opacity": "1", "visibility": "visible"}).attr('title', '');
					$('.wps-hosting-backups-message .wps-dismiss-notice').css({"color": "#888888", "border": "none"});

					window.fadeOutTimeout = setTimeout(function(){
						$('.snapshot-three.wps-message.' + shown_notification).fadeOut();
					}, 10000);

					$('.wps-hosting-backups-message i.wps-dismiss-notice').on('click', function (e) {
						e.preventDefault();
						$('.wps-hosting-backups-message').fadeOut();
					});

					if (!sent) {
						$('.wps-error-message-wrap a.snapshot-hosting-backup-export').on("click", function (e) {
							e.preventDefault();
							$('.snapshot-three.wps-message.wps-error-message').hide();
							Sfb.ExportBackup.Viewer.handle_view(e);
						});
					}
				},

				/**
				 * Initializes backup exporter click listener
				 *
				 * @param {object} $el jQuery node
				 */
				initialize_listener: function ($el) {
					$el
						.off("click.snapshot")
						.on("click.snapshot", this.handle_view)
					;
				},

				/**
				 * Initializes backup creator
				 *
				 * @return {Boolean}
				 */
				run: function () {
					this.initialize_listeners();
					return true;
				}
			}),

			/**
			 * Initializes the whole backup creator section JS
			 *
			 * @return {Boolean}
			 */
			run: function () {
				Sfb.ExportBackup.Viewer.run();

				return true;
			}
		};

		window.Sfb = Sfb;

		$(function () {
			window.hidden_progress_loader = false;
			window.hidden_result_notification = false;
			window.clear_ongoing_backup_performed = false;
			Sfb.StartHostingBackups.run();
			
			// When loading the page, fetch the hosting backups list.
			Sfb.ListHostingBackups.snapshot_list_hosted_backups();
			Sfb.DealWithCurrentBackup.show_current_backup_state();

			$('.wps-no-hosting-managed-backups input[name="start_first_hosting_backup"]').on("click", function (e) {
				e.preventDefault();
				Sfb.StartHostingBackups.Viewer.handle_view(e, true);
			});

			$('.wps-new-hosting-backup-state a.retry-hosting-backup-creation').on("click", function (e) {
				e.preventDefault();
				Sfb.StartHostingBackups.Viewer.handle_view(e, false);
			});

			$('input[name="wps-managed-backups-menu"]').on('change', function (e) {
				var value = $(this).filter(':checked').val();
				$('select[name="wps-managed-backups-menu-mobile"]').val(value).change();
				$('.wps-managed-backups-pages > .wpmud-box').addClass('hidden').filter('.' + value).removeClass('hidden');
			});
	
			$('select[name="wps-managed-backups-menu-mobile"]').on('change', function (e) {
				var value = $(this).val();
				$('input[name="wps-managed-backups-menu"][value="' + value + '"]').attr('checked', 'checked');
				$('.wps-managed-backups-pages > .wpmud-box').addClass('hidden').filter('.' + value).removeClass('hidden');
			});

			// Dismiss backup creation/restore notifications.
			$('div.wps-new-hosting-backup-state i.wps-dismiss-notice').on('click', function (e) {
				e.preventDefault();
				var parents = $(this).parents();
				if (parents.eq(1).is('.wps-progress-loader')){
					if (window.hidden_progress_loader===undefined || !window.hidden_progress_loader) {
						// Remember that we have hidden the progress notification.
						// We'll show the progress notification again on this page load, only if the current backup is finished.
						window.hidden_progress_loader = true;
					}
				} else if (parents.eq(1).is('.wps-progress-success') || parents.eq(1).is('.wps-progress-error')){
					if (window.hidden_result_notification===undefined || !window.hidden_result_notification) {
						// Remember that we have hidden the success/failure notification.
						// We'll show the success/failure notification again on this page load, only if we start a new backup.
						window.hidden_result_notification = true;
					}
				}
				parents.eq(1).hide();
				
			});

			// Dismiss permanently the managed backup notice.
			$('.wps-managed-backup-notice .wps-dismiss-notice').on('click', function (e) {
				e.preventDefault();
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						'action' : 'dismiss_managed_backups_notice'
					}
				});
				$(this).parent().hide().remove();
			});

			$('a.wps-reload-backup-listing').on('click', function (e) {
				e.preventDefault();
				Sfb.ListHostingBackups.snapshot_list_hosted_backups();
			});

			$('.wps-message').off('click');
		});
	};

})(jQuery);
