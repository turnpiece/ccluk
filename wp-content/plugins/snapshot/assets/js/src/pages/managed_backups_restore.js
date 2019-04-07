;(function ($) {
    // page ID or "slug"
    window.SS_PAGES.snapshot_page_snapshot_pro_managed_backups_restore = function () {
        /* Handler for Backup/Restore user Aborts */

        var snapshot_ajax_hdl_xhr = null,
            snapshot_ajax_user_aborted = false,
            backup,
            restore_in_progress = false

        function snapshot_button_abort_proc() {
            if (snapshot_ajax_hdl_xhr !== null) {
                snapshot_ajax_hdl_xhr.abort();
            }
            snapshot_ajax_user_aborted = true;

            $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html(snapshot_messages.restore_aborted);
            $(".wpmud-box-title .wps-title-result").removeClass('hidden');
            $(".wpmud-box-title .wps-title-progress").addClass('hidden');
            $("#wps-build-progress").addClass('hidden');

            return false;
        }

        $("#wps-show-full-log").on('click', function (e) {
            e.preventDefault();
            var $self = $(this);
            if ($('#wps-log').is('.hidden')) {
                $self.text($self.attr('data-wps-hide-title'));
                $('#wps-log').removeClass("hidden");
            } else {
                $self.text($self.attr('data-wps-show-title'));
                $('#wps-log').addClass("hidden");
            }
        });

        $("#wps-build-error-again").on('click', function (e) {
            e.preventDefault();
            $('#wps-build-error').addClass("hidden");
            $('#wps-build-progress').removeClass("hidden");
            $(".wpmud-box-title .wps-title-result").addClass("hidden");
            $(".wpmud-box-title .wps-title-progress").removeClass("hidden");
            $("form#managed-backup-restore").submit();
        });

        $("#wps-build-error-back").on('click', function (e) {
            e.preventDefault();
            $('#wps-build-error').addClass("hidden");
            $('#wps-build-progress').removeClass("hidden");
            $('#managed-backup-restore').removeClass('hidden');
            $('#container.wps-page-builder').addClass('hidden');
            $(".wpmud-box-title .wps-title-result").addClass("hidden");
            $(".wpmud-box-title .wps-title-progress").removeClass("hidden");

        });

        $('#wps-cancel').off().on('click', function (e) {
            e.preventDefault();
            snapshot_button_abort_proc();
        });

        $("form#managed-backup-restore").off().submit(function (e) {
            e.preventDefault();
            /* Hide the form while processing */
            $('#managed-backup-restore').addClass('hidden');

            // Clear the yellow warning box
            $("#snapshot-ajax-warning").html('');
            $("#snapshot-ajax-warning").hide();

            // Show the progress bar container.
            $('#container.wps-page-builder').removeClass('hidden');

            var prm = new $.Deferred(),
                $archive = $("form#managed-backup-restore :hidden.archive"),
                $restore = $("form#managed-backup-restore :text.location"),
                $creds = $("form#managed-backup-restore .request-filesystem-credentials-form input"),
                $security = $("form#managed-backup-restore :hidden#snapshot-ajax-nonce"),
                rq = {},
				perform_auth_check = function ( request ) {
					window._snapshot_last_request = request;
					$( document ).on(
						'heartbeat-tick.wp-auth-check',
						check_auth_context
					);
					if ( ( ( wp || {} ).heartbeat || {} ).connectNow ) {
						wp.heartbeat.connectNow();
						return true;
					}
					return false;
				},
				clear_auth_context_check_and_continue = function () {
					$( document ).off(
						'heartbeat-tick.wp-auth-check',
						check_auth_context
					);
					var request = window._snapshot_last_request,
						url = window.location.pathname + '?page=snapshot_pro_managed_backups';

					// New auth context - exchange nonces before continuing.
					$.get( url, function ( data ) {
						var $nonce = $( data ).find( '#snapshot-ajax-nonce' ),
							nonce = $nonce.val();
						$security.val( nonce );
						request.security = nonce;
						callback( request );
					} );
				},
				check_auth_context = function ( event, data ) {
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
									clear_auth_context_check_and_continue();
									$root.removeClass( 'snapshot-bound' );
								} );
							}
						});
					}
				},
                callback = function (request) {
                    request.action = "snapshot-full_backup-restore";
                    $.post(ajaxurl, request, function () {}, 'json')
                        .then(function (data) {
                            if (!data || (data || {}).error) {
                                $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html("Error restoring backup");
                                $(".wpmud-box-title .wps-title-result").removeClass('hidden');
                                $(".wpmud-box-title .wps-title-progress").addClass('hidden');
                                $("#wps-build-progress").addClass('hidden');
                                prm.resolve();
                                return false;
                            }
                            if (data.task !== 'clearing') {
                                var cls = 'fetching' === data.task ? 'fetch' : 'process';
                                restore_progress_display(cls);
                                callback(request);
                            } else {
                                restore_progress_display((data.status ? 'done' : 'error'));
                                restore_in_progress = false;
                                prm.resolve();
                            }
                        })
                        .fail(function () {
							if ( perform_auth_check( request ) ) {
								// Let's first try to see if we got logged out.
								// If so, we might be able to handle that.
								return;
							}
                            prm.resolve();
                            $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html("Restoration failed");
                            $(".wpmud-box-title .wps-title-result").removeClass('hidden');
                            $(".wpmud-box-title .wps-title-progress").addClass('hidden');
                            $("#wps-build-progress").addClass('hidden');
                            return false;
                        })
                    ;
                };

            rq = {
                idx: backup,
                archive: $archive.val(),
                restore: $restore.val(),
                credentials: {},
                security: $security.val()
            };
            $creds.each(function () {
                var $me = $(this);
                if ($me.is(":radio") || $me.is(":checkbox")) {
                    if ($me.is(":checked")) rq.credentials[$me.attr("name")] = $me.val();
                } else if ($me.is(":text") || $me.is(":password") || $me.is(":hidden")) {
                    rq.credentials[$me.attr("name")] = $me.val();
                }
            });

            restore_in_progress = true;
            restore_progress_display('fetch');

            $(window).on("beforeunload.snapshot-restore", function (e) {
                var msg = "You still have a restore active, navigating off this page will stop it mid-process";
                e.returnValue = msg;
                return msg;
            });

            callback(rq);

            return prm.promise().always(function () {
                $(window).off("beforeunload.snapshot-restore");
            });
        });

        function restore_progress_display (progress) {
            if(progress === 'error'){
                $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html("Restoration failed");
                $(".wpmud-box-title .wps-title-result").removeClass('hidden');
                $(".wpmud-box-title .wps-title-progress").addClass('hidden');
                $("#wps-build-progress").addClass('hidden');
            } else if(progress === 'done'){
                $("#wps-build-progress .wps-total-status .wps-loading-number").html('100%');
                $("#wps-build-progress .wps-total-status .wps-loading-bar span").width('100%');
                $("#wps-build-error").addClass("hidden");
                $("#wps-build-progress").addClass("hidden");
                $("#wps-build-success").removeClass("hidden");
            } else if(progress === 'fetch'){
                $("#wps-build-progress .wps-total-status .wps-loading-number").html('40%');
                $("#wps-build-progress .wps-total-status .wps-loading-bar span").width('40%');
            } else if(progress === 'process'){
                $("#wps-build-progress .wps-total-status .wps-loading-number").html('80%');
                $("#wps-build-progress .wps-total-status .wps-loading-bar span").width('80%');
            }
        }
    };
})(jQuery);
