jQuery(function ($) {
    //bind form handler for every form inside scan section
    WDAudit.formHandler();
    WDAudit.listenFilter();
    WDAudit.filterForm();
    WDAudit.initDatepicker();
    $('div.auditing').on('form-submitted', function (e, data, form) {
        if (form.attr('id') != 'active-audit') {
            return;
        }
        if (data.success == true) {
            location.reload();
        } else {
            Defender.showNotification('error', data.data.message);
        }
    });
    var last_date = null;
    $('#wd_range_from').change(function () {
        if (last_date !== null && last_date !== $(this).val()) {
            var date = $(this).val().split('-');
            query = [];
            query.push('date_from=' + $.trim(date[0]));
            query.push('date_to=' + $.trim(date[1]));
            WDAudit.ajaxPull(query.join('&'));
        }
        last_date = $(this).val();
    })

    $('.deactivate-audit').click(function () {
        $('.audit-frm').append('<input type="hidden" name="enabled" value="0"/>');
        $(this).attr('disabled', 'disabled');
        $('.audit-frm').submit();
    });

    $('div.auditing').on('form-submitted', function (e, data, form) {
        if (!form.hasClass('audit-settings')) {
            return;
        }
        if (data.success == true) {
            Defender.showNotification('success', data.data.message);
        } else {
            Defender.showNotification('error', data.data.message);
        }
    });

    $('select[name="frequency"]').change(function () {
        if ($(this).val() == '1') {
            $(this).closest('.schedule-box').find('div.days-container').hide();
        } else {
            $(this).closest('.schedule-box').find('div.days-container').show();
        }
    }).change();

    $('body').on('click', '.nav a', function (e) {
        e.preventDefault();
        if ($(this).attr('disabled') == 'disabled') {
            return;
        }
        var query = WDAudit.buildFilterQuery();
        WDAudit.ajaxPull(query + '&paged=' + $(this).attr('href'), function () {

        });
    });
    $('body').on('click', 'a.afilter', function (e) {
        e.preventDefault();
        var query = $(this).attr('href').replace('#', '');
        WDAudit.ajaxPull(query, function () {

        });
    })
    $('div.auditing').on('form-submitted', function (e, data, form) {
        if (!form.hasClass('audit-widget')) {
            return;
        }
        if (data.success == true) {
            form.closest('.sui-box').replaceWith($(data.data.html))
        } else {
            Defender.showNotification('error', data.data.message);
        }
    });
    $('div.auditing').on('form-submitted', function (e, data, form) {
        if (!form.hasClass('count-7-days')) {
            return;
        }
        if (data.success == true) {
            if (data.data.eventWeek > 0) {
                $('.issues-count').html(data.data.eventWeek);
            }
        } else {
            Defender.showNotification('error', data.data.message);
        }
    });

    if ($('.audit-widget').size() > 0) {
        $('.audit-widget').submit();
    }

    if ($('.count-7-days').size() > 0) {
        $('.count-7-days').submit();
    }

    $('#toggle_audit_logging').change(function () {
        if ($(this).prop('checked') == false) {
            $('.active-audit').submit();
        }
    })
    if ($('#audit-table-container').size() > 0) {
        if ($('#audit-table-container').find('table').size() == 0) {
            var query = WDAudit.buildFilterQuery();
            var parts = WDAudit.getHashParams();
            if (parts.context !== undefined) {
                query += '&context=' + parts.context;
            }
            if (parts.term !== undefined) {
                query += '&term=' + parts.term;
            }
            WDAudit.ajaxPull(query, function () {
                jQuery("#audit-table-container select").each(function () {

                });
            });
        }
    }

    $('body').on('click', '.audit-csv', function () {
        var query = WDAudit.buildFilterQuery();
        query = query + '&action=exportAsCvs';
        location.href = ajaxurl + '?' + query;
        // var that = $(this);
        // $.ajax({
        //     type: 'POST',
        //     url: ajaxurl,
        //     data: query,
        //     beforeSend: function () {
        //         that.attr('disabled', 'disabled');
        //     },
        //     success: function (data) {
        //         if (data.success == 1) {
        //             that.removeAttr('disabled');
        //         }
        //     }
        // })
    })
    $('body').on('click', '.sui-active-filter-remove', function () {
        var target = $('#' + $(this).data('target'));
        if (target.is(':checkbox')) {
            target.prop('checked', false)
        } else {
            target.val('');
        }
        query = WDAudit.buildFilterQuery();
        WDAudit.ajaxPull(query);
        if (target.is(':checkbox')) {
            $('[data-target="all_type"]:nth-child(2)').click();
        }
        $(this).parent().remove();
        if ($('.sui-active-filter-remove').size() == 0) {
            $('.filter-container').addClass('wd-hide');
        }
    })
});
var count;

window.WDAudit = window.WDAudit || {};
WDAudit.formHandler = function () {
    var jq = jQuery;
    jq('body').on('submit', '.audit-frm', function () {
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
                if (data.data != undefined && data.data.notification != undefined) {
                    if (data.data.notification == 0) {
                        jq('.defender-audit-frequency').html(data.data.text);
                        jq('.defender-audit-schedule').html('');
                    } else {
                        jq('.defender-audit-frequency').html(data.data.frequency);
                        jq('.defender-audit-schedule').html(data.data.schedule);
                    }
                }
                if (data.data != undefined && data.data.reload != undefined) {
                    Defender.showNotification('success', data.data.message);
                    location.reload();
                } else if (data.data != undefined && data.data.url != undefined) {
                    location.href = data.data.url;
                } else {
                    that.find('.sui-button').removeAttr('disabled');
                    jq('div.auditing').trigger('form-submitted', [data, that])
                }
            }
        })
        return false;
    })
}

WDAudit.initDatepicker = function () {
    //calendar
    if (jQuery('#wd_range_from').size() > 0) {
        var start = moment().subtract(7, 'days');
        var end = moment();
        var maxDate = end;
        var minDate = moment().subtract(1, 'years');
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

WDAudit.listenForEvents = function () {
    var jq = jQuery;
    var query = WDAudit.buildFilterQuery();
    if (count == null) {
        count = jq('.bulk-nav').first().data('total');
    }
    query += '&lite=1&count=' + count;
    WDAudit.ajaxPull(query, function () {
        setTimeout(WDAudit.listenForEvents, 15000);
    })
}

WDAudit.filterForm = function () {
    var jq = jQuery;
    jq('body').on('submit', '.audit-filter form', function () {
        var query = WDAudit.buildFilterQuery();
        WDAudit.ajaxPull(query, function () {

        })
        var that = jq(this);
        var inputs = that.find(':input');
        jq('.filter-container').removeClass('wd-hide');
        jq('.sui-pagination-active-filters').html('');
        inputs.each(function () {
            if (jq(this).val().length && jq(this).data('name') != undefined) {
                if (jq(this).attr('type') === 'checkbox' && jq(this).prop('checked') === false) {

                } else {
                    var html = jq(this).data('name') + ':' + jq(this).val();
                    html += '<span class="sui-active-filter-remove" data-target="' + jq(this).attr('id') + '"></span>';
                    jq('.sui-pagination-active-filters').append(jq('<span class="sui-active-filter"/>').html(html));
                }
            }
        })
        return false;
    })
}

WDAudit.listenFilter = function () {
    //parse the URL
    var jq = jQuery;

    var parts = WDAudit.getHashParams();
    var filters = ['comment', 'system', 'media', 'settings', 'content', 'user'];
    var currentActive = [];
    for (var i in parts) {
        var v = parts[i];
        if (i.indexOf('event_type') !== -1 && filters.indexOf(v) !== -1) {
            currentActive.push(v);
        } else if (i === 'ip') {
            jq('#ip').val(parts.ip);
        }
    }
    var notActive = jq(filters).not(currentActive).get();
    jq.each(notActive, function (i, v) {
        jq('input[name="event_type[]"][value="' + v + '"]').prop('checked', false);
    });

    if (currentActive.length) {
        jq('.filter-container').removeClass('wd-hide');
        jq('.sui-pagination-active-filters').html('');
        jq.each(currentActive, function (i, v) {
            var input = jq('input[name="event_type[]"][value="' + v + '"]');
            var html = input.data('name') + ':' + input.val();
            html += '<span class="sui-active-filter-remove" data-target="' + input + '"></span>';
            jq('.sui-pagination-active-filters').append(jq('<span class="sui-active-filter"/>').html(html));
        })
    }
};

WDAudit.buildFilterQuery = function (currentInput) {
    var jq = jQuery;
    var form = jq('.audit-filter form');
    var inputs = form.find(':input');
    var query = [];
    inputs.push(jq('#wd_range_from'));
    inputs.each(function () {
        if (jq(this).attr('type') == 'checkbox') {
            if (jq(this).prop('checked') == true) {
                query.push(jq(this).attr('name') + '=' + jq(this).val());
            }
        } else if (jq(this).attr('name') != undefined) {
            if (jq(this).attr('name') == 'date_from') {
                var date = jq(this).val().split('-');
                query.push('date_from=' + jq.trim(date[0]));
                query.push('date_to=' + jq.trim(date[1]));
            } else {
                query.push(jq(this).attr('name') + '=' + jq(this).val());
            }
        }
    });
    return query.join('&');
}
var isFirst = true;
var urlOrigin = location.href;
WDAudit.ajaxPull = function (query, callback) {
    var overlay = Defender.createOverlay();
    var jq = jQuery;
    jq.ajax({
        type: 'GET',
        url: ajaxurl,
        data: query + '&action=auditLoadLogs',
        beforeSend: function () {
            if (query.indexOf('lite') == -1) {
                jq('#audit-table-container').prepend(overlay);
            }
        },
        success: function (data) {
            jq('[rel="show-filter"]').removeAttr('disabled');
            if (data.success == 1) {
                if (data.data.html != undefined) {
                    var html = jq(data.data.html);
                    jq('#audit-table-container').html(html).addClass('sui-row sui-flushed');
                    if (jQuery('.sui-accordion').length > 0) {
                        jQuery('.sui-accordion').each(function () {
                            SUI.suiAccordion(jQuery(this));
                        });
                    }
                } else {
                    jq('.new-event-count').html(data.data.message).removeClass('wd-hide');
                    count = data.data.count;
                    callback();
                }
                if (data.data.pagination !== undefined) {
                    jq('.sui-pagination-results').remove();
                    jq('.sui-pagination').remove();
                    jq('.sui-pagination-wrap').prepend(data.data.pagination);
                }
            } else {
                jq('#audit-table-container').html(data.data.html);
                overlay.remove();
            }
        }
    })
}

WDAudit.getHashParams = function () {

    var hashParams = {};
    var e,
        a = /\+/g,  // Regex for replacing addition symbol with a space
        r = /([^&;=]+)=?([^&;]*)/g,
        d = function (s) {
            return decodeURIComponent(s.replace(a, " "));
        },
        q = window.location.hash.substring(1);

    while (e = r.exec(q))
        hashParams[d(e[1])] = d(e[2]);

    return hashParams;
}