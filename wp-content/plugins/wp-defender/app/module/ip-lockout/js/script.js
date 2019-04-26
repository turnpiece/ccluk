jQuery(function ($) {
    //bind form handler for every form inside scan section
    WDIP.formHandler();
    WDIP.listenFilter();
    WDIP.pullSummaryData();
    WDIP.initDatepicker();

    $('div.iplockout').on('form-submitted', function (e, data, form) {
        if (form.attr('id') != 'settings-frm') {
            return;
        }
        if (data.success == true) {
            Defender.showNotification('success', data.data.message);
        } else {
            Defender.showNotification('error', data.data.message);
        }
    });
    setTimeout(function () {
        if ($('#moving-data').size() > 0) {
            $('#moving-data').submit();
        }
    }, 1000);
    $('div.iplockout').on('form-submitted', function (e, data, form) {
        if (form.attr('id') != 'moving-data') {
            return;
        }
        if (data.success == true) {
            setTimeout(function () {
                location.reload();
            }, 1000)
            $('.scan-progress-text span').text('100%');
            $('.scan-progress-bar span').css('width', '100%');
            Defender.showNotification('success', data.data.message);
        } else {
            var progress = data.data.progress;
            $('.scan-progress-text span').text(progress + '%');
            $('.scan-progress-bar span').css('width', progress + '%');
            setTimeout(function () {
                $('#moving-data').submit();
            }, 1000);
        }
    });
    $('body').on('change', '.single-select, #apply-all', function () {
        var inputs = $('input[name="ids[]"]:checked');
        var ids = [];
        inputs.each(function (index, input) {
            ids.push($(input).val());
        });
        $('.ids').val(ids.join(','));
    })
    $('.deactivate-login-lockout').click(function () {
        $('.ip-frm').append('<input type="hidden" name="login_protection" value="0"/>');
        $(this).attr('disabled', 'disabled');
        $('.ip-frm').submit();
    });
    $('.deactivate-404-lockout').click(function () {
        $('.ip-frm').append('<input type="hidden" name="detect_404" value="0"/>');
        $(this).attr('disabled', 'disabled');
        $('.ip-frm').submit();
    });
    //media uploader
    var mediaUploader;
    $('.file-picker').click(function () {
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        // Extend the wp.media object
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose an Import file',
            button: {
                text: 'Choose File'
            }, multiple: false
        });

        // When a file is selected, grab the URL and set it as the text field's value
        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#file_import').val(attachment.id);
            $('.upload-input').addClass('sui-has_file');
            $('.upload-input .sui-upload-file span').text(attachment.filename);
        });
        // Open the uploader dialog
        mediaUploader.open();
    })
    $('.file-picker-remove').click(function () {
        $('.upload-input').removeClass('sui-has_file');
        $('#file_import').val('');
    })
    $('#apply-all').click(function () {
        $('.single-select').prop('checked', $(this).prop('checked'));
    });
    $('.btn-import-ip').click(function () {
        var that = $(this);
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'wd_import_ips',
                file: $('#file_import').val()
            }, beforeSend: function () {
                that.attr('disabled', 'disabled');
            },
            success: function (data) {
                that.removeAttr('disabled');
                if (data.success == 1) {
                    Defender.showNotification('success', data.data.message);
                    setTimeout(function () {
                        location.reload();
                    }, 2000)
                } else {
                    Defender.showNotification('error', data.data.message);
                }
            }
        })
    });
    $('.download-geo-ip').click(function () {
        var that = $(this);
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'downloadGeoIPDB',
                _wpnonce: that.data('nonce')
            }, beforeSend: function () {
                that.attr('disabled', 'disabled');
                that.addClass('sui-button-onload');
            },
            success: function (data) {
                if (data.success == 1) {
                    Defender.showNotification('success', data.data.message);
                    location.reload();
                } else {
                    Defender.showNotification('error', data.data.message);
                }
            }
        })
    })
    $('select[name="report_frequency"]').change(function () {
        if ($(this).val() == '1') {
            $(this).closest('.schedule-box').find('div.days-container').hide();
        } else {
            $(this).closest('.schedule-box').find('div.days-container').show();
        }
    }).change();
    var last_date = $('#wd_range_from').val();
    $('#wd_range_from').change(function () {
        if (last_date !== $(this).val()) {
            query = WDIP.buildFilterQuery();
            WDIP.ajaxPull(query, function () {

            });
        }
        last_date = $(this).val();
    })
    $('body').on('click', '.ip-action', function (e) {
        e.preventDefault();
        var that = $(this);
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'lockoutIPAction',
                id: that.data('id'),
                type: that.data('type'),
                nonce: that.data('nonce')
            },
            success: function (data) {
                if (data.success == 1) {
                    that.parent().html(data.data.message);
                }
            }
        })
    })

    $('body').on('click', '.lockout-nav', function (e) {
        e.preventDefault();
        var query = WDIP.buildFilterQuery();
        if (order !== false && orderby !== false) {
            query += '&order=' + order + '&orderby=' + orderby;
        }
        query += '&paged=' + $(this).data('paged');
        WDIP.ajaxPull(query, function () {

        });
    });
    $('body').on('click', '.empty-logs', function () {
        var that = $(this);
        cleaningLog(that);
    });
    if ($('#defLockoutUpgrade').size() > 0) {
        $('body').addClass('wpmud');
        WDP.showOverlay("#defLockoutUpgrade", {
            title: 'Updating...',
            class: 'no-close migrate-iplockout wp-defender'
        });
    }

    function cleaningLog(button) {
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'lockoutEmptyLogs',
                nonce: button.data('nonce')
            },
            beforeSend: function () {
                button.attr('disabled', 'disabled');
                button.text('Deleting logs...');
                button.css('cursor', 'wait');
            },
            success: function (data) {
                if (data.success) {
                    Defender.showNotification('success', data.data.message);
                    button.removeAttr('disabled');
                    location.reload();
                } else {
                    cleaningLog(button);
                }
            }
        })
    }

    $('input[name="login_protection"], input[name="detect_404"]').change(function () {
        $('#settings-frm').submit();
    })

    $('#bulk-select').on('click', function () {
        $('.single-select').prop('checked', $(this).prop('checked'))
    })
    var order = false;
    var orderby = false;
    $('#lockout-logs-sort').change(function () {
        var value = $(this).val();
        var query = WDIP.buildFilterQuery();
        if (value === 'latest') {
            query += '&orderby=id&order=desc'
            order = 'desc';
            orderby = 'id';
        } else if (value === 'oldest') {
            query += '&orderby=id&order=asc'
            order = 'asc';
            orderby = 'id';
        } else if (value === 'ip') {
            query += '&orderby=ip&order=asc'
            order = 'asc';
            orderby = 'ip';
        }
        WDIP.ajaxPull(query, function () {

        });
    })
});

window.WDIP = window.WDIP || {};
WDIP.formHandler = function () {
    var jq = jQuery;
    jq('body').on('submit', '.ip-frm', function () {
        var data = jq(this).serialize();
        var that = jq(this);
        jq.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            beforeSend: function () {
                that.find('.sui-button').attr('disabled', 'disabled');
            },
            success: function (data) {
                if (data.data != undefined && data.data.reload != undefined) {
                    setTimeout(function () {
                        location.reload();
                    }, data.data.reload * 1000)
                    if (data.data.message != undefined) {
                        if (data.success) {
                            Defender.showNotification('success', data.data.message);
                        } else {
                            Defender.showNotification('error', data.data.message);
                        }
                    }
                } else if (data.data != undefined && data.data.url != undefined) {
                    location.href = data.data.url;
                } else {
                    var buttons = that.find('.sui-button');
                    if (buttons.size() > 0) {
                        buttons.removeAttr('disabled');
                    }
                    jq('div.iplockout').trigger('form-submitted', [data, that])
                }
            }
        })
        return false;
    })
};
WDIP.listenFilter = function () {
    var jq = jQuery;
    jq('body').on('submit', '.lockout-logs-filter form', function () {
        var query = WDIP.buildFilterQuery();
        WDIP.ajaxPull(query, function () {
        })
        return false;
    })
};
var isFirst = true;
var urlOrigin = location.href;
WDIP.ajaxPull = function (query, callback) {
    var jq = jQuery;
    var overlay = Defender.createOverlay();
    jq.ajax({
        type: 'GET',
        url: ajaxurl,
        data: query + '&action=lockoutLoadLogs',
        beforeSend: function () {
            jq('.lockout-logs-container').prepend(overlay);
        },
        success: function (data) {
            jq('.lockout-logs-container table').replaceWith(jq(data.data.html).find('table').first());
            jq('.lockout-logs-container .sui-pagination-wrap').replaceWith(jq(data.data.html).find('.sui-pagination-wrap').first());
            //jq('.lockout-logs-container').replaceWith(jq(data.data.html));
            //rebind according
            jq('.sui-accordion').each(function () {
                SUI.suiAccordion(this);
            });
            overlay.remove();
            if (isFirst == false) {
                //window.history.pushState(null, document.title, urlOrigin + '&' + query);
            } else {
                isFirst = false;
            }
            callback();
        }
    })
}

WDIP.buildFilterQuery = function () {
    var jq = jQuery;
    var form = jq('.lockout-logs-filter form');
    var inputs = form.find(':input');
    var query = [];
    inputs.each(function () {
        if (jq(this).attr('name') !== undefined) {
            query.push(jq(this).attr('name') + '=' + jq(this).val());
        }
    });
    //need to input the date range too
    var range = jq('#wd_range_from').val();
    range = range.split('-');
    query.push('date_from=' + jq.trim(range[0]));
    query.push('date_to=' + jq.trim(range[1]));
    return query.join('&');
};

WDIP.pullSummaryData = function () {
    var jq = jQuery;
    var box = jq('#lockoutSummary');
    if (box.size() > 0) {
        jq.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'lockoutSummaryData',
                nonce: jq('#summaryNonce').val()
            },
            success: function (data) {
                if (data.success == true) {
                    jq('.lockoutToday').text(data.data.lockoutToday);
                    jq('.lockoutThisMonth').text(data.data.lockoutThisMonth);
                    jq('.lastLockout').text(data.data.lastLockout);
                    jq('.loginLockoutThisWeek').text(data.data.loginLockoutThisWeek);
                    jq('.lockout404ThisWeek').text(data.data.lockout404ThisWeek);
                    box.find('.wd-overlay').remove();
                }
            }
        })
    }
}

WDIP.initDatepicker = function () {
    //calendar
    if (jQuery('#wd_range_from').size() > 0) {
        var start = moment().subtract(7, 'days');
        var end = moment();
        var maxDate = end;
        var minDate = moment().subtract(30, 'days');
        jQuery('#wd_range_from').daterangepicker({
            //startDate: start,
            //endDate: end,
            autoApply: true,
            maxDate: maxDate,
            minDate: minDate,
            "linkedCalendars": false,
            showDropdowns: false,
            applyClass: 'wd-hide',
            cancelClass: 'wd-hide',
            alwaysShowCalendars: true,
            opens: 'left',
            dateLimit: {
                days: 90
            },
            locale: {
                "format": "MM/DD/YYYY",
                "separator": " - "
            },
            template: '<div class="daterangepicker wd-calendar wp-defender dropdown-menu"> ' +
                '<div class="ranges"> ' +
                '<div class="range_inputs"> ' +
                '<button class="applyBtn" disabled="disabled" type="button"></button> ' +
                '<button class="cancelBtn" type="button"></button> ' +
                '</div> ' +
                '</div> ' +
                '<div class="calendar left"> ' +
                '<div class="calendar-table"></div> ' +
                '</div> ' +
                '<div class="calendar right"> ' +
                '<div class="calendar-table"></div> ' +
                '</div> ' +
                '</div>',
            showCustomRangeLabel: false,
            ranges: {
                'Today': [moment(), moment()],
                '7 Days': [moment().subtract(6, 'days'), moment()],
                '30 Days': [moment().subtract(29, 'days'), moment()]
            }
        });
    }
}