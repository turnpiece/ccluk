jQuery(function ($) {
    //bind form handler for every form inside scan section
    WDScan.formHandler();
    WDScan.formatCode();
    WDScan.initAppear();
    WDScan.showNextIssue();
    WDScan.typeFilter();
    //bind handler for new scan form
    $('div.wdf-scanning').on('form-submitted', function (e, data, form) {
        if (form.attr('id') != 'start-a-scan') {
            return;
        }

        if (data.success == true) {
            location.reload();
        } else {
            Defender.showNotification('error', data.data.message);
        }
    });

    //processing scan
    //show the scan dialog
    if ($('#scanning').size() > 0) {
        var listen = setInterval(function () {
            if (SUI.dialogs !== undefined) {
                SUI.dialogs['scanning'].show();
                clearInterval(listen);
            }
        }, 500)
    }
    //show scan progress
    if ($('#process-scan').size() > 0) {
        $('#process-scan').submit();
        $('div.wdf-scanning').on('form-submitted', function (e, data, form) {
            if (form.attr('id') != 'process-scan') {
                return;
            }
            if (data.success == true) {
                location.reload();
            } else {
                $('.sui-progress-state-text').text(data.data.statusText);
                $('.sui-progress-text span').text(data.data.percent + '%');
                $('.sui-progress-bar span').css('width', data.data.percent + '%');
                setTimeout(function () {
                    $('#process-scan').submit();
                }, 1500);
            }
        })
        $('div.wdf-scanning').on('form-submitted-error', function (e, data, form, xhr) {
            if (form.attr('id') != 'process-scan') {
                return;
            }
            //try to reup
            setTimeout(function () {
                $('#process-scan').submit();
            }, 1500);
        })
    }

    //ignore form
    $('div.wdf-scanning').on('form-submitted', function (e, data, form) {
        if (!form.hasClass('ignore-item')) {
            return;
        }

        if (data.success == true) {
            //show notification
            Defender.showNotification('success', data.data.message);
            //remove the line
            $('#' + data.data.mid).fadeOut('200', function () {
                $('#' + data.data.mid).next('.sui-accordion-item-content').remove();
                $('#' + data.data.mid).remove();
                WDScan.handleFileIssues(data);
            })
        } else {
            Defender.showNotification('error', data.data.message);
        }
    });
    //restore an ignore
    $('div.wdf-scanning').on('form-submitted', function (e, data, form) {
        if (!form.hasClass('ignore-restore')) {
            return;
        }

        if (data.success == true) {
            //show notification
            Defender.showNotification('success', data.data.message);
            $('#' + data.data.mid).fadeOut('200', function () {
                $('#' + data.data.mid).remove();
                WDScan.handleFileIssues(data);
                var count = $('#scan-result-table tbody tr').size();
                if (count === 0) {
                    location.reload();
                }
            })
        } else {
            Defender.showNotification('error', data.data.message);
        }
    });
    //delete mitem
    $('body').on('click', '.delete-mitem', function () {
        var parent = $(this).closest('form');
        var confirm_box = parent.find('.confirm-box');
        $(this).addClass('wd-hide');
        confirm_box.removeClass('wd-hide');
        confirm_box.find('.sui-button-ghost').unbind('click').bind('click', function () {
            confirm_box.addClass('wd-hide');
            parent.find('.delete-mitem').removeClass('wd-hide');
        })
    });
    $('div.wdf-scanning').on('form-submitted', function (e, data, form) {
        if (!form.hasClass('delete-item')) {
            return;
        }
        if (data.success == true) {
            //show notification
            Defender.showNotification('success', data.data.message);
            //close the modal form

            $('#' + data.data.mid).fadeOut('200', function () {
                $('#' + data.data.mid).next('.sui-accordion-item-content').remove();
                $('#' + data.data.mid).remove();
                WDScan.handleFileIssues(data);
            })
        } else {
            Defender.showNotification('error', data.data.message);
        }
    });
    $('div.wdf-scanning').on('form-submitted', function (e, data, form) {
        if (!form.hasClass('pull-src')) {
            return;
        }

        if (data.success == true) {
            current_issue = null;
            var parent = form.closest('.source-code');
            parent.html(data.data.html);

            // hljs.highlightBlock(parent.find('pre code'));
            parent.find('pre code').each(function (i, block) {
                hljs.highlightBlock(block);
                hljs.lineNumbersBlock(block);
            });
        } else {
            Defender.showNotification('error', data.data.message);
        }
    })
    //resolve item
    $('div.wdf-scanning').on('form-submitted', function (e, data, form) {
        if (!form.hasClass('resolve-item')) {
            return;
        }

        if (data.success == true) {
            //show notification
            Defender.showNotification('success', data.data.message);
            //close the modal form
            $('#' + data.data.mid).fadeOut('200', function () {
                $('#' + data.data.mid).next('.sui-accordion-item-content').remove();
                $('#' + data.data.mid).remove();
                WDScan.handleFileIssues(data);
            })
        } else {
            Defender.showNotification('error', data.data.message);
        }
    });
    $('div.wdf-scanning').on('form-submitted', function (e, data, form) {
        if (!form.hasClass('scan-settings')) {
            return;
        }

        if (data.success == true) {
            //show notification
            Defender.showNotification('success', data.data.message);
            //close any dialog if any
            $.each(SUI.dialogs, function (i, v) {
                v.hide();
            })
        } else {
            Defender.showNotification('error', data.data.message);
        }
    });

    $('select[name="frequency"]').change(function () {
        if ($(this).val() == '1') {
            $(this).closest('.sui-form-field').next('div.sui-form-field').hide();
        } else {
            $(this).closest('.sui-form-field').next('div.sui-form-field').show();
        }
    }).change();

    //bulk
    $('#apply-all').click(function () {
        $('.scan-chk').prop('checked', $(this).prop('checked'));
    });
    $('select[name="bulk"]').change(function () {
        if ($(this).val() != "") {
            $('.scan-bulk-frm button').removeAttr('disabled');
        } else {
            $('.scan-bulk-frm button').attr('disabled', 'disabled');
        }
    })
    $('.scan-bulk-frm').submit(function () {
        var data = $(this).serialize();
        $('.scan-chk').each(function () {
            if ($(this).prop('checked') == true) {
                data += '&items[]=' + $(this).val();
            }
        })
        var that = $(this);
        $.ajax({
            type: 'POST',
            data: data,
            url: ajaxurl,
            beforeSend: function () {
                that.find('button').attr('disabled', 'disabled');
            },
            success: function (data) {
                if (data.success) {
                    setTimeout(function () {
                        location.reload();
                    }, 1000)
                    Defender.showNotification('success', data.data.message);
                } else {
                    that.find('button').removeAttr('disabled');
                    Defender.showNotification('error', data.data.message);
                }
            }
        })
        return false;
    });
})

window.WDScan = window.WDScan || {};
WDScan.formHandler = function () {
    var jq = jQuery;
    jq('body').on('submit', '.scan-frm', function () {
        var data = jq(this).serialize();
        var that = jq(this);
        jq.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            beforeSend: function () {
                that.find('.sui-button-blue').attr('disabled', 'disabled');
            },
            success: function (data) {
                if (data.data != undefined && data.data.url != undefined) {
                    location.href = data.data.url;
                } else {
                    that.find('.sui-button').removeAttr('disabled');
                    jq('div.wdf-scanning').trigger('form-submitted', [data, that])
                }
            },
            error: function (xhr) {
                jq('div.wdf-scanning').trigger('form-submitted-error', [data, that, xhr])
            }
        })
        return false;
    })
}

WDScan.formatCode = function () {
    jQuery('pre code').each(function (i, block) {
        hljs.highlightBlock(block);
        hljs.lineNumbersBlock(block);
    });
}

//Refresh file issues counts
WDScan.handleFileIssues = function (data) {
    var jq = jQuery;
    jq.each(data.data.counts, function (k, v) {
        jq('.' + k).html(v);
    })
}
var current_issue = null;
WDScan.initAppear = function () {
    jQuery('.sui-accordion-item').click(function () {
        var that = jQuery(this);
        current_issue = null;
        if (that.hasClass('source-pulled')) {
            return;
        }
        var container = jQuery(this).next('.sui-accordion-item-content').first();
        var form = container.find('.pull-src');
        if (form.size() > 0) {
            form.submit();
        }
        that.addClass('source-pulled');
    })
}

WDScan.showNextIssue = function () {
    jQuery('body').on('click', '#next_issue', function () {
        var parent = jQuery(this).closest('.sui-box').find('.inner-sourcecode').first();
        var issues = parent.find('del');
        if (issues.size() == 0) {
            return;
        }
        if (current_issue === null) {
            current_issue = 0;
        } else {
            current_issue = current_issue + 1;
            if (issues[current_issue] === undefined) {
                current_issue = 0;
            }
        }
        var pos = jQuery(issues[current_issue]).position();
        parent.scrollTop(pos.top);
    })
}

WDScan.typeFilter = function () {
    if (jQuery('#type-filter').size() > 0) {
        var urlOrigin = scan.url;
        jQuery('#type-filter').change(function () {
            var type = jQuery(this).val();
            if (type !== "") {
                urlOrigin += '&type=' + type;
            }
            location.href = urlOrigin;
        })
    }
}