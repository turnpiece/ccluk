;(function ($) {
    // page ID or "slug"
    window.SS_PAGES.snapshot_page_snapshot_pro_managed_backups_restore = function () {
        /* Handler for Backup/Restore user Aborts */

        var snapshot_ajax_hdl_xhr = null,
            snapshot_ajax_user_aborted = false,
            backup

        function snapshot_button_abort_proc() {
            if (snapshot_ajax_hdl_xhr !== null) {
                snapshot_ajax_hdl_xhr.fail();
            }

            snapshot_ajax_user_aborted = true;

            $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html(snapshot_messages.restore_backup_aborted);
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
                $(this).html( snapshot_messages.hide_full_log );
            } else {
                $self.text($self.attr('data-wps-show-title'));
                $('#wps-log').addClass("hidden");
                $(this).html( snapshot_messages.show_full_log );
            }
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

        $('.snapshot-button-abort').on('click', function (e) {
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

                // When first submitting a restore request, do a restore->clear() first, to get rid of any residuals.
                initial_callback = function (request) {
                    restore_progress_display('fetch', 'init', '0' );
                    request.action = "snapshot-full_backup-restore";
                    initial_request = request;
                    initial_request.initial_callback = 1;
                    snapshot_ajax_hdl_xhr = $.post(ajaxurl, initial_request, function () {}, 'json')
                        .then(function (data) {
                            if (snapshot_ajax_user_aborted === true) {
                                prm.resolve();
                                return false;
                            }
                            if (!data || (data || {}).error || (data.status === false && data.task !=="fetching") ) {
                                if (!data) {
                                    $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html("Error restoring backup");
                                } else {
                                    var restore_error = restore_action_error( data.action );
                                    $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html( restore_error );
                                }
                                $(".wpmud-box-title .wps-title-result").removeClass('hidden');
                                $(".wpmud-box-title .wps-title-progress").addClass('hidden');
                                $("#wps-build-progress").addClass('hidden');
                                prm.resolve();
                                return false;
                            }
                            if (data.task === 'fetching') {
                                if (data.errors === true) {
                                    var restore_error = restore_action_error('init');
                                    $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html(restore_error);   
                                    $(".wpmud-box-title .wps-title-result").removeClass('hidden');
                                    $(".wpmud-box-title .wps-title-progress").addClass('hidden');
                                    $("#wps-build-progress").addClass('hidden');
                                    prm.resolve();                                
                                } else {
                                    if (data.status === true) {
                                        restore_progress_display('fetch', 'init', '100' );
                                        request.initial_callback = 0;
                                        callback(request);
                                    } else {
                                        request.initial_callback = 1;
                                        initial_callback(request);
                                    }
                                }
                            } else {
                                prm.resolve();
                                $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html("Restoration failed");
                                $(".wpmud-box-title .wps-title-result").removeClass('hidden');
                                $(".wpmud-box-title .wps-title-progress").addClass('hidden');
                                $("#wps-build-progress").addClass('hidden');
                                return false;
                            }
                        })
                        .fail(function () {
                            prm.resolve();
                            $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html("Restoration failed");
                            $(".wpmud-box-title .wps-title-result").removeClass('hidden');
                            $(".wpmud-box-title .wps-title-progress").addClass('hidden');
                            $("#wps-build-progress").addClass('hidden');
                            return false;
                        })
                    ;
                };

                callback = function (request) {
                    request.action = "snapshot-full_backup-restore";
                    snapshot_ajax_hdl_xhr = $.post(ajaxurl, request, function () {}, 'json')
                        .then(function (data) {
                            if (snapshot_ajax_user_aborted === true) {
                                prm.resolve();
                                return false;
                            }
                            if (!data || (data || {}).error || data.status === false ) {
                                if (!data) {
                                    $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html("Error restoring backup");
                                } else {
                                    var restore_error = restore_action_error( data.action );
                                    $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html( restore_error );
                                }
                                $(".wpmud-box-title .wps-title-result").removeClass('hidden');
                                $(".wpmud-box-title .wps-title-progress").addClass('hidden');
                                $("#wps-build-progress").addClass('hidden');
                                prm.resolve();
                                return false;
                            }
                            if (data.task !== 'clearing') {
                                var cls = 'fetching' === data.task ? 'fetch' : 'process',
                                    progress = 'fetch' === cls ? '100' : data.progress;
                                restore_progress_display(cls, data.action, progress );
                                callback(request);
                            } else {
                                restore_progress_display('finalizing', 'finish', '50' );
                                $('#wps-cancel').addClass('hidden');
                                setTimeout(function () {
                                    restore_progress_display((data.status ? 'done' : 'error'), 'finish', '100' );
                                }, 10000);
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

            $(window).on("beforeunload.snapshot-restore", function (e) {
                var msg = "You still have a restore active, navigating off this page will stop it mid-process";
                e.returnValue = msg;
                return msg;
            });

            initial_callback(rq);

            return prm.promise().always(function () {
                $(window).off("beforeunload.snapshot-restore");
            });
        });

        function restore_progress_display (progress, action, process ) {
            var done = '100' === process ? 'done' : '',
                process_text = '',
                calc = process;
                process = process + '%';
            $('#container.wps-page-builder tr .wps-log-progress .wps-spinner').addClass( 'hidden' );
            $('#container.wps-page-builder tr .wps-log-progress .snapshot-button-abort').addClass( 'hidden' );
            if( 'error' === progress ) {
                $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html("Restoration failed");
                $(".wpmud-box-title .wps-title-result").removeClass('hidden');
                $(".wpmud-box-title .wps-title-progress").addClass('hidden');
                $("#wps-build-progress").addClass('hidden');
            } else if( 'done' === progress ) {
                restore_build_progress( '100%' );
                restore_progress_log_display( 'finish', done, process, snapshot_messages.finalized_restoration );
                restore_progress_log_display( 'tableset', 'done', '100%', snapshot_messages.database_restored );
                $('#wps-build-progress .progress-action-title').html( $( '#wps-log-process-finish .wps-log-process' ).html() + '...' );
                $("#wps-build-error").addClass("hidden");
                $("#wps-build-progress-container").addClass("hidden");
                $("#wps-build-success").removeClass("hidden");
            } else if( 'fetch' === progress ) {
                if( 'done' === done ) {
                    restore_build_progress( '10%' );
                    restore_progress_log_display( 'init', done, process, snapshot_messages.determined );
                    $('#wps-build-progress .progress-action-title').html( snapshot_messages.restoring_files + '...' );
                    restore_progress_activity( 'fileset', 'tableset' );
                } else {
                    restore_build_progress( '0%' );
                    restore_progress_activity( 'init', '' );
                    restore_progress_activity( '', 'fileset' );
                    $('#wps-build-progress .progress-action-title').html( $( '#wps-log-process-init .wps-log-process' ).html() + '...' );
                }
                restore_progress_log_display( 'init', done, process, '' );
            } else if( 'process' === progress ) {
                if( 'fileset' === action || 'tableset' === action ) {
                    if( 'tableset' === action ) {
                        process_text = snapshot_messages.restoring_database;
                        var table_progress = parseInt( calc ) < 50 ? '50%' : '70%';
                        restore_build_progress( table_progress );
                        restore_progress_activity( '' , 'finish' );
                        restore_progress_log_display( 'fileset', 'done', '100%', snapshot_messages.files_restored );
                        restore_progress_log_display( 'init', 'done', '100%', snapshot_messages.determined );
                    } else {
                        var file_progress = parseInt( calc ) < 50 ? '10%' : '30%';
                        process_text = snapshot_messages.restoring_files;
                        restore_build_progress( file_progress );
                        restore_progress_log_display( 'init', 'done', '100%', snapshot_messages.determined );
                        restore_progress_activity( '' , 'tableset' );
                    }
                    restore_progress_activity( action, '' );
                    $('#wps-build-progress .progress-action-title').html( process_text + '...' );
                    restore_progress_log_display( action, done, process, process_text );
                }
            } else if( 'finalizing' === progress ) {
                // This stage is specifically for after the clearing stage, where we artificically show the user it's in the middle of that task for 5s.  
                restore_build_progress( '90%' );
                restore_progress_activity( 'finish' , '' );

                restore_progress_log_display( 'tableset', 'done', '100%', snapshot_messages.database_restored );

                $('#wps-build-progress .progress-action-title').html( snapshot_messages.finalizing_restoration + '...' );
                restore_progress_log_display( action, done, process, snapshot_messages.finalizing_restoration );
            }
        }

        function restore_progress_log_display( action, done, process, title ) {
            $('#container.wps-page-builder #wps-log-process-' + action + ' .wps-loading-status .wps-loading-number').html( process ).addClass( done );
            $('#container.wps-page-builder #wps-log-process-' + action + ' .wps-loading-status .wps-loading-bar span').width( process );
            $('#container.wps-page-builder #wps-log-process-' + action + ' .wps-loading-status .wps-loading-bar .wps-loader').addClass( done );
            if( title ) {
                $('#container.wps-page-builder #wps-log-process-' + action + ' .wps-log-process').html(title);
            }
        }

        function restore_build_progress( progress ) {
            $("#wps-build-progress .wps-total-status .wps-loading-number").html( progress );
            $("#wps-build-progress .wps-total-status .wps-loading-bar span").width( progress );
        }

        function restore_progress_activity( loader, abort ) {
            $('#container.wps-page-builder #wps-log-process-' + loader + ' .wps-spinner').removeClass( 'hidden' );
            $('#container.wps-page-builder #wps-log-process-' + abort + ' .snapshot-button-abort').removeClass( 'hidden' );
        }

        function restore_action_error( action ) {
            var error = snapshot_messages.fail_restore_start;
            switch( action ) {
                case 'init':
                    error += ' <span style="text-transform: lowercase;">' + snapshot_messages.determining + '</span>. ';
                    break;
                case 'fileset':
                    error += ' <span style="text-transform: lowercase;">' + snapshot_messages.restoring_files + '</span>. ';
                    break;
                case 'tableset':
                    error += ' <span style="text-transform: lowercase;">' + snapshot_messages.restoring_database + '</span>. ';
                    break;
                case 'finish':
                    error += ' <span style="text-transform: lowercase;">' + snapshot_messages.finalizing_restoration + '</span>. ';
                    break;
                default:
                    error = snapshot_messages.restore_fail + '. ';
            }
            error += snapshot_messages.fail_restore_end;

            return error;
        }
    };
})(jQuery);
