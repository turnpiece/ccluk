(function ($) {
    // page ID or "slug"
    window.SS_PAGES.snapshot_page_snapshot_pro_snapshots_restore = function () {

        /* Handler for Backup/Restore user Aborts */

        var snapshot_ajax_hdl_xhr = null,
            snapshot_ajax_user_aborted = false;

        function snapshot_button_abort_proc() {
            snapshot_ajax_hdl_xhr !== null && snapshot_ajax_hdl_xhr.abort();
            snapshot_ajax_user_aborted = true;

            $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html(snapshot_messages.restore_aborted);
            $(".wpmud-box-title .wps-title-result").removeClass('hidden');
            $(".wpmud-box-title .wps-title-progress").addClass('hidden');
            $("#wps-build-progress").addClass('hidden');

            return false;
        }

        $("#wps-show-full-log").on('click', function(e) {
            e.preventDefault();
	        $('#wps-log').toggle();
	        var $self = $(this);
	        var button_text = 'data-wps-' + ($('#wps-log').is(':visible') ? 'hide' :'show') + '-title';
			$self.text($self.attr(button_text));
        });

        $("#wps-build-error-again").on('click', function(e) {
            e.preventDefault();
            $('#wps-build-error').addClass("hidden");
            $('#wps-build-progress').removeClass("hidden");
            $(".wpmud-box-title .wps-title-result").addClass("hidden");
            $(".wpmud-box-title .wps-title-progress").removeClass("hidden");
            $("form#snapshot-edit-restore").submit();
        });

        $("#wps-build-error-back").on('click', function(e) {
            e.preventDefault();
            $('#wps-build-error').addClass("hidden");
            $('#wps-build-progress').removeClass("hidden");
            $('#snapshot-edit-restore').removeClass('hidden');
            $('#container.wps-page-builder').addClass('hidden');
            $(".wpmud-box-title .wps-title-result").addClass("hidden");
            $(".wpmud-box-title .wps-title-progress").removeClass("hidden");

        });

        $('#wps-cancel').off().on('click', function(e) {
            e.preventDefault();
            snapshot_button_abort_proc();
        });

        $("form#snapshot-edit-restore").off().submit(function() {

            /* From the form grab the Name and Notes field values. */
            var snapshot_item_key = $('input[name="item"]').val();
            var snapshot_blog_search = $('input[name="snapshot-blog-id-search"]').val();

            var snapshot_restore_plugin = $('input#snapshot-restore-option-plugins', this).attr('checked');

            var security = jQuery(':hidden#snapshot-ajax-nonce').val();
            if (snapshot_restore_plugin === "checked") {
                snapshot_restore_plugin = "yes";
            } else {
                snapshot_restore_plugin = "no";
            }

            var snapshot_restore_theme = $('input[name="restore-option-theme"]:checked', this).val();

            var snapshot_form_files_sections = [];
            var snapshot_form_files_option = $('input.snapshot-files-option:checked').val();
            if (snapshot_form_files_option == "all") {

            } else if (snapshot_form_files_option == "selected") {
                $('input.snapshot-backup-sub-options:checked', this).each(function() {
                    var cb_value = $(this).attr('value');
                    snapshot_form_files_sections[snapshot_form_files_sections.length] = cb_value;
                });

                // Do we have tables to process?
                if (snapshot_form_files_sections.length === 0) {
                    snapshot_form_files_option = "all";
                }
            }

            /* Build and array of the checked tables to backup */
            var snapshot_form_tables_array = [];
            var snapshot_form_tables_option = $('input.snapshot-tables-option:checked').val();

            var snapshot_form_blog_id = $('input#snapshot-blog-id').val();


            if (snapshot_form_tables_option === 'selected') {

                $('input.snapshot-table-item:checked', this).each(function() {
                    snapshot_form_tables_array[snapshot_form_tables_array.length] = $(this).attr('value');
                });

                // Do we have tables to process?
                if (snapshot_form_tables_array.length === 0) {
                    snapshot_form_tables_option = 'all';
                }

            } else if (snapshot_form_tables_option === 'all') {
                $('input.snapshot-table-item', this).each(function() {
                    snapshot_form_tables_array[snapshot_form_tables_array.length] = $(this).attr('value');
                });
            }

            var globalProgressTotal = snapshot_form_tables_array.length + 2;
            var globalProgressNow = 0;


            /* Hide the form while processing */
            $('#snapshot-edit-restore').addClass('hidden');

            // Clear the yellow warning box
            $("#snapshot-ajax-warning").html('');
            $("#snapshot-ajax-warning").hide();

            // Show the progress bar container.
            $('#container.wps-page-builder').removeClass('hidden');


            var tablesArray = [];
            var filesArray = [];

            var snapshot_item_data = $('input:radio[name="snapshot-restore-file"]').val();

            function snapshot_restore_tables_proc(action, idx) {
                var table_name, data;

                if (action === 'init') {

                    table_name = action;
                    var table_text = "Snapshot determining tables/files to restore";
                    var snapshot_item_html = '<div class="snapshot-item" id="snapshot-item-table-' + table_name + '"><div class="progress"><div class="percent">0%</div><div class="bar" style="width: 0px;"></div></div><button style="display: none;" class="snapshot-button-abort">Abort</button><div class="snapshot-text">' + table_text + '</div></div>';
                    $('#snapshot-progress-bar-container').append(snapshot_item_html);

                    $('#snapshot-progress-bar-container #snapshot-item-table-' + table_name + ' .progress .bar').width('0%');
                    $('#snapshot-progress-bar-container #snapshot-item-table-' + table_name + ' .progress .percent').html('0%');

                    $('#snapshot-progress-bar-container #snapshot-item-table-' + table_name + ' .snapshot-button-abort').show();
                    $('#snapshot-progress-bar-container #snapshot-item-table-' + table_name + ' button.snapshot-button-abort').click(function() {
                        snapshot_button_abort_proc();
                    });

                    data = {
                        'action': 'snapshot_restore_ajax',
                        'snapshot_action': action,
                        'item_key': snapshot_item_key,
                        'item_data': snapshot_item_data,
                        'snapshot-blog-id': snapshot_form_blog_id,
                        'snapshot-files-option': snapshot_form_files_option,
                        'snapshot-files-sections': snapshot_form_files_sections,
                        'snapshot-tables-option': snapshot_form_tables_option,
                        'snapshot-tables-array': snapshot_form_tables_array,
                        'snapshot_blog_search': snapshot_blog_search,
                        'security': security,
                    };

                    snapshot_ajax_hdl_xhr = $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: data,
                        dataType: 'json',
                        error: function(jqXHR, textStatus, errorThrown) {
                            if (jqXHR.responseText !== false && jqXHR.responseText !== '') {
                                $('#wps-build-error').removeClass('hidden').find('.wps-auth-message p').html(jqXHR.responseText);
                                $(".wpmud-box-title .wps-title-result").removeClass('hidden');
                                $(".wpmud-box-title .wps-title-progress").addClass('hidden');
                                $("#wps-build-progress").addClass('hidden');

                            } else {
                                $('#wps-build-error').removeClass('hidden')
                                    .find('.wps-auth-message p').html('<p>' + snapshot_messages.snapshot_failed + '</p>');
                                $('.wpmud-box-title .wps-title-progress').addClass('hidden');
                                $('.wpmud-box-title .wps-title-result').removeClass('hidden');
                                $('#wps-build-progress').addClass('hidden');

                            }
                        },
                        success: function(reply_data) {
                            var snapshot_item_html;

                            if (reply_data !== null) {
                                if (reply_data.errorStatus !== undefined) {

                                    if (reply_data.errorStatus === false) {

                                        if (snapshot_ajax_user_aborted === false) {

                                            var table_name = "init";
                                            $('#container.wps-page-builder #wps-log-process-init .wps-loading-status .wps-loading-bar span').width('100%');
                                            $('#container.wps-page-builder #wps-log-process-init .wps-loading-status .wps-loading-number').html('100%');
                                            $('#container.wps-page-builder #wps-log-process-init .wps-loading-status .wps-loading-bar .wps-loader').addClass('done');
                                            $('#container.wps-page-builder #wps-log-process-init .snapshot-button-abort').addClass('hidden');
                                            globalProgressNow++;

                                            var globalProgressNowPercent = Math.min(100, Math.round((globalProgressNow * 100) / globalProgressTotal));

                                            $("#wps-build-progress .wps-total-status .wps-loading-number").html(globalProgressNowPercent + '%');
                                            $("#wps-build-progress .wps-total-status .wps-loading-bar span").width(globalProgressNowPercent + '%');

                                            if (reply_data.MANIFEST.TABLES !== undefined && Object.keys(reply_data.MANIFEST.TABLES).length) {

                                                for (table_name in reply_data.MANIFEST.TABLES) {
                                                    if (!reply_data.MANIFEST.TABLES.hasOwnProperty(table_name)) continue;
                                                    var table_set = reply_data.MANIFEST.TABLES[table_name];

                                                    snapshot_item_html = $('<tr id="snapshot-item-table-' + table_set.table_name + '"></tr>')
                                                        .append($('#wps-log-process-template').html())
                                                        .find('.wps-log-process.name').html(table_set.label)
                                                        .end();

                                                    $('tr#wps-log-process-finish').before(snapshot_item_html);

                                                }
                                                tablesArray = reply_data.MANIFEST['TABLES-DATA'];
                                            }

                                            if (reply_data.MANIFEST['FILES-DATA'] !== undefined && Object.keys(reply_data.MANIFEST['FILES-DATA']).length) {
                                                filesArray = reply_data.MANIFEST['FILES-DATA'];

                                                snapshot_item_html = $('<tr id="snapshot-item-file"></tr>')
                                                    .append($('#wps-log-process-template').html())
                                                    .find('.wps-log-process.name').html('Files: <span class="snapshot-filename" style="font-style: italic;"></span>')
                                                    .end();

                                                $('tr#wps-log-process-finish').before(snapshot_item_html);
                                            }

                                            snapshot_restore_tables_proc('table', 0);
                                        }
                                    } else {
                                        if (reply_data.errorText !== undefined) {
                                            $("#wps-build-error .wps-auth-message p").html(reply_data.errorText);
                                            $("#wps-build-error").removeClass("hidden");
                                            $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                            $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                            $("#wps-build-progress").addClass("hidden");
                                        }
                                    }
                                } else {
                                    $("#wps-build-error .wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
                                    $("#wps-build-error").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                    $("#wps-build-progress").addClass("hidden");

                                }
                            } else {
                                $("#wps-build-error .wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
                                $("#wps-build-error").removeClass("hidden");
                                $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                $("#wps-build-progress").addClass("hidden");

                            }
                        }
                    });
                } else if (action === 'table') {

                    var table_idx = parseInt(idx);
                    var table_count = table_idx + 1;

                    /* If we reached the end of the tables send the finish. */
                    if (table_count > tablesArray.length) {

                        if (filesArray.length > 0) {
                            snapshot_restore_tables_proc('file', 0);
                        } else {
                            snapshot_restore_tables_proc('finish', 0);
                        }
                        return;

                    } else {

                        var table_data = tablesArray[table_idx];
                        table_name = table_data.table_name;

                        $('#snapshot-progress-bar-container #snapshot-item-table-' + table_name + ' button.snapshot-button-abort').show().click(function() {
                            snapshot_button_abort_proc();
                        });

                        data = {
                            'action': 'snapshot_restore_ajax',
                            'snapshot_action': action,
                            'item_key': snapshot_item_key,
                            'item_data': snapshot_item_data,
                            'snapshot-blog-id': snapshot_form_blog_id,
                            'snapshot_table': table_name,
                            'table_data': table_data,
                            'snapshot_blog_search': snapshot_blog_search,
                            'security': security,
                        };

                        snapshot_ajax_hdl_xhr = $.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: data,
                            timeout: 600000,
                            dataType: 'json',
                            error: function(jqXHR, textStatus, errorThrown) {
                                if (jqXHR.responseText !== false && jqXHR.responseText !== '') {
                                    $("#wps-build-error .wps-auth-message p").html(jqXHR.responseText);
                                    $("#wps-build-error").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                    $("#wps-build-progress").addClass("hidden");

                                } else {
                                    $("#wps-build-error .wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
                                    $("#wps-build-error").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                    $("#wps-build-progress").addClass("hidden");

                                }
                            },
                            success: function(reply_data) {
                                var snapshot_percent;

                                $('#snapshot-progress-bar-container').find('#snapshot-item-table-' + table_name).find('.snapshot-button-abort').hide();
                                $('#snapshot-progress-bar-container').find('#snapshot-item-table-' + table_name).find('.wps-spinner').hide();

                                if (reply_data.errorStatus !== undefined) {
                                    if (reply_data.errorStatus === false) {
                                        if (snapshot_ajax_user_aborted === false) {

                                            if (reply_data.table_data !== undefined) {
                                                table_data = reply_data.table_data;
                                                var rows_complete = parseInt(table_data.rows_start) + parseInt(table_data.rows_end);

                                                if (rows_complete > 0) {
                                                    snapshot_percent = Math.ceil(rows_complete / table_data.rows_total * 100);

                                                    $('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-loading-status .wps-loading-bar span').width(snapshot_percent + '%');
                                                    $('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-loading-status .wps-loading-number').html(snapshot_percent + '%');

                                                } else {

                                                    snapshot_percent = 100;
                                                    $('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-loading-status .wps-loading-bar span').width(snapshot_percent + '%');
                                                    $('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-loading-status .wps-loading-number').html(snapshot_percent + '%');

                                                }

                                                // Are we at 100%
                                                if (snapshot_percent == 100) {
                                                    $('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .snapshot-button-abort').hide();
                                                    $('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-spinner').hide();
                                                    $('#container.wps-page-builder #snapshot-item-table-' + table_data.table_name + ' .wps-loading-status .wps-loading-bar .wps-loader').addClass('done');

	                                                globalProgressNow++;


	                                                var globalProgressNowPercent = Math.min(100, Math.round((globalProgressNow * 100) / globalProgressTotal));

	                                                $("#wps-build-progress .wps-total-status .wps-loading-number").html(globalProgressNowPercent + '%');
	                                                $("#wps-build-progress .wps-total-status .wps-loading-bar span").width(globalProgressNowPercent + '%');

                                                }

                                            }

                                            snapshot_restore_tables_proc('table', table_count);
                                        }

                                    } else {
                                        $("#wps-build-error .wps-auth-message p").html(reply_data.errorText);
                                        $("#wps-build-error").removeClass("hidden");
                                        $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                        $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                        $("#wps-build-progress").addClass("hidden");

                                    }

                                } else {
                                    $("#wps-build-error .wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
                                    $("#wps-build-error").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                    $("#wps-build-progress").addClass("hidden");

                                }
                            }
                        });
                    }
                } else if (action == "file") {

                    var file_idx = parseInt(idx);
                    var file_count = file_idx + 1;
                    table_name = action;

                    /* If we reached the end of the tables send the finish. */
                    if (file_count > filesArray.length) {

                        if (filesArray.length > 0) {
                            snapshot_restore_tables_proc('finish', 0);
                        }
                        return;

                    } else {

                        var file_name = filesArray[file_idx];
                        file_name = basename(file_name);
                        $('#snapshot-item-' + table_name + ' .snapshot-filename').html(": " + file_name);

                        $('#snapshot-progress-bar-container #snapshot-item-file button.snapshot-button-abort').show();
                        $('#snapshot-progress-bar-container #snapshot-item-file button.snapshot-button-abort').click(function() {
                            snapshot_button_abort_proc();
                        });

                        data = {
                            'action': 'snapshot_restore_ajax',
                            'snapshot_action': action,
                            'item_key': snapshot_item_key,
                            'item_data': snapshot_item_data,
                            'file_data_idx': file_idx,
                            'snapshot-blog-id': snapshot_form_blog_id,
                            'security': security,

                        };

                        snapshot_ajax_hdl_xhr = $.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: data,
                            timeout: 600000,
                            dataType: 'json',
                            error: function(jqXHR, textStatus, errorThrown) {
                                if (jqXHR.responseText !== false && jqXHR.responseText !== '') {
                                    $("#wps-build-error").removeClass("hidden").find(".wps-auth-message p").html(jqXHR.responseText);
                                    $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                    $("#wps-build-progress").addClass("hidden");

                                } else {
                                    $("#wps-build-error .wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
                                    $("#wps-build-error").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                    $("#wps-build-progress").addClass("hidden");

                                }
                            },
                            success: function(reply_data) {
                                var snapshot_percent;

                                $('#snapshot-progress-bar-container #snapshot-item-table-' + table_name + ' .snapshot-button-abort').hide();
                                $('#snapshot-progress-bar-container #snapshot-item-table-' + table_name + ' .wps-spinner').hide();

                                if (reply_data.errorStatus !== undefined) {

                                    if (reply_data.errorStatus === false) {

                                        if (snapshot_ajax_user_aborted === false) {


                                            if (reply_data.file_data !== undefined) {

                                                if (file_count < filesArray.length) {

                                                    snapshot_percent = Math.ceil((file_count / filesArray.length) * 100);

                                                    $('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-loading-status .wps-loading-bar span').width(snapshot_percent + '%');
                                                    $('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-loading-status .wps-loading-number').html(snapshot_percent + '%');

                                                } else {

                                                    globalProgressNow++;

                                                    var globalProgressNowPercent = Math.min(100, Math.round((globalProgressNow * 100) / globalProgressTotal));

                                                    $("#wps-build-progress .wps-total-status .wps-loading-number").html(globalProgressNowPercent + '%');
                                                    $("#wps-build-progress .wps-total-status .wps-loading-bar span").width(globalProgressNowPercent + '%');


                                                    snapshot_percent = 100;
                                                    $('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-loading-status .wps-loading-bar span').width(snapshot_percent + '%');
                                                    $('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-loading-status .wps-loading-number').html(snapshot_percent + '%');
                                                    $('#container.wps-page-builder #snapshot-item-' + table_name + ' .wps-loading-status .wps-loading-bar .wps-loader').addClass('done');

                                                }

                                                // Are we at 100%? Hide the Abort button
                                                if (snapshot_percent === 100) {
                                                    $('#snapshot-progress-bar-container #snapshot-item-' + table_name + ' .snapshot-button-abort').hide();
                                                    $('#snapshot-progress-bar-container #snapshot-item-' + table_name + ' .wps-spinner').hide();
                                                    $('#snapshot-item-' + table_name + ' .snapshot-filename').html("");
                                                }
                                            }
                                            //snapshot_backup_tables_proc('file', file_count);
                                            snapshot_restore_tables_proc('file', file_count);
                                        }

                                    } else {
                                        $("#wps-build-error").removeClass("hidden").find(".wps-auth-message p").html(reply_data.errorText);
                                        $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                        $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                        $("#wps-build-progress").addClass("hidden");

                                    }

                                } else {
                                    $("#wps-build-error").removeClass("hidden").find(".wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
                                    $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                    $("#wps-build-progress").addClass("hidden");

                                }
                            }
                        });
                    }


                } else if (action == "finish") {

                    table_name = action;
                    $('#container.wps-page-builder #snapshot-item-table-' + table_name + ' .wps-loading-status .wps-loading-bar span').width('0%');
                    $('#container.wps-page-builder #snapshot-item-table-' + table_name + ' .wps-loading-status').addClass('wps-spinner');
                    $('#container.wps-page-builder #snapshot-item-table-' + table_name + ' .wps-loading-status .wps-loading-number').html('0%');

                    data = {
                        'action': 'snapshot_restore_ajax',
                        'snapshot_action': action,
                        'item_key': snapshot_item_key,
                        'item_data': snapshot_item_data,
                        'snapshot-blog-id': snapshot_form_blog_id,
                        'snapshot_restore_plugin': snapshot_restore_plugin,
                        'snapshot_restore_theme': snapshot_restore_theme,
                        'security': security,
                    };

                    snapshot_ajax_hdl_xhr = $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: data,
                        dataType: 'json',
                        error: function(jqXHR, textStatus, errorThrown) {
                            if (jqXHR.responseText !== false && jqXHR.responseText !== '') {
                                $("#wps-build-error .wps-auth-message p").html(jqXHR.responseText);
                                $("#wps-build-error").removeClass("hidden");
                                $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                $("#wps-build-progress").addClass("hidden");

                            } else {
                                $("#wps-build-error .wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
                                $("#wps-build-error").removeClass("hidden");
                                $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                $("#wps-build-progress").addClass("hidden");

                            }
                        },
                        success: function(reply_data) {
                            if (reply_data.errorStatus !== undefined) {

                                if (reply_data.errorStatus === false) {

                                    if (snapshot_ajax_user_aborted === false) {

                                        globalProgressNow++;

                                        var globalProgressNowPercent = Math.min(100, Math.round((globalProgressNow * 100) / globalProgressTotal));

                                        $("#wps-build-progress .wps-total-status .wps-loading-number").html(globalProgressNowPercent + '%');
                                        $("#wps-build-progress .wps-total-status .wps-loading-bar span").width(globalProgressNowPercent + '%');


                                        var table_name = "finish";
                                        $('#container.wps-page-builder #wps-log-process-finish .wps-loading-status .wps-loading-bar span').width('100%');
                                        $('#container.wps-page-builder #wps-log-process-finish .wps-loading-status .wps-loading-number').html('100%');
                                        $('#container.wps-page-builder #wps-log-process-finish .wps-loading-status .wps-loading-bar .wps-loader').addClass('done');
                                        $('#container.wps-page-builder #wps-log-process-finish .wps-loading-status').removeClass('wps-spinner');

                                        //$( "#wps-build-success .wps-auth-message.success" ).html('<p>'+reply_data['responseText']+'</p>');
                                        $("#wps-build-success-view").attr('href', $("#wps-build-success .wps-auth-message.success").find('a').attr('href'));
                                        $("#wps-build-error").addClass("hidden");
                                        $("#wps-build-progress").addClass("hidden");

                                        if (reply_data.restore_admin_url !== undefined) {
                                            $('a.restored-admin-url').attr('href', reply_data.restore_admin_url);
                                        }

	                                    if (reply_data.restore_site_url !== undefined) {
		                                    $('a.restored-site-url').attr('href', reply_data.restore_site_url);
	                                    }

                                        $("#wps-build-success").removeClass("hidden");
                                    }

                                } else {
                                    $("#wps-build-error .wps-auth-message p").html(reply_data.errorText);
                                    $("#wps-build-error").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                    $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                    $("#wps-build-progress").addClass("hidden");

                                }

                            } else {
                                $("#wps-build-error .wps-auth-message p").html('<p>' + snapshot_messages.snapshot_failed + '</p>');
                                $("#wps-build-error").removeClass("hidden");
                                $(".wpmud-box-title .wps-title-result").removeClass("hidden");
                                $(".wpmud-box-title .wps-title-progress").addClass("hidden");
                                $("#wps-build-progress").addClass("hidden");

                            }
                        }
                    });
                }
            }

            snapshot_restore_tables_proc('init', 0);

            return false;
        });
    };


    function basename(path) {
        if ((path !== undefined) && (path.length)) {
            return path.replace(/\\/g, '/').replace(/.*\//, '');
        } else {
            return path;
        }
    }

    function dirname(path) {
        return path.replace(/\\/g, '/').replace(/\/[^\/]*$/, '');
    }

})(jQuery);
