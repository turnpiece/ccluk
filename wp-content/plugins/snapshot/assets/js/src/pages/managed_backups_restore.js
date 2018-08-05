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
