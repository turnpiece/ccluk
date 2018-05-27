jQuery(function ($) {
    //bind form handler for every form inside scan section
    WDIP.formHandler();
    WDIP.listenFilter();
    WDIP.pullSummaryData();
    WDIP.showHideLog();

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
            location.reload();
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
            $('#import').val(attachment.url);
            $('#file_import').val(attachment.id);
        });
        // Open the uploader dialog
        mediaUploader.open();
    })
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
    $('select[name="report_frequency"]').change(function () {
        if ($(this).val() == '1') {
            $(this).closest('.schedule-box').find('div.days-container').hide();
        } else {
            $(this).closest('.schedule-box').find('div.days-container').show();
        }
    }).change();

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

    $('#lockout-logs-sort').change(function () {
        var value = $(this).val();
        if (value === 'latest') {
            query = 'orderby=id&order=desc'
        } else if (value === 'oldest') {
            query = 'orderby=id&order=asc'
        } else if (value === 'ip') {
            query = 'orderby=ip&order=asc'
        }
        console.log(query);
        WDIP.ajaxPull(query);
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
                that.find('.button').attr('disabled', 'disabled');
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
                    var buttons = that.find('.button');
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
    var form = jq('.lockout-logs-filter form');
    form.on('submit', function () {
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
            jq('.lockout-logs-container .nav').replaceWith(jq(data.data.html).find('.nav').first());
            //jq('.lockout-logs-container').replaceWith(jq(data.data.html));
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

WDIP.showHideLog = function () {
    var jq = jQuery;
    jq('body').on('click', '.show-hide-log', function (e) {
        if (jq(e.target).is('input')) {
            return;
        }
        var target = jq(this).next('tr.table-info');
        if (target.hasClass('wd-hide')) {
            target.removeClass('wd-hide');
            jq(this).addClass('opened');
            jq(this).find('i').removeClass().addClass('dev-icon dev-icon-caret_up')
        } else {
            target.addClass('wd-hide');
            jq(this).removeClass('opened');
            jq(this).find('i').removeClass().addClass('dev-icon dev-icon-caret_down')
        }
    })
}