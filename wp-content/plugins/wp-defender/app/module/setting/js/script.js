jQuery(function ($) {
    Settings.formHandler();

    $('body').on('submit', '.wd_reset_settings', function (e) {
        e.preventDefault();
        var that = $(this);
        $.ajax({
            url: ajaxurl,
            data: that.serialize(),
            type: 'POST',
            beforeSend: function () {
                that.find('button').attr('disabled', 'disabled');
                that.find('button[type="submit"]').addClass('sui-button-onload')
            },
            success: function (data) {
                console.log(data);
                Defender.showNotification('success', data.data.message);
                location.reload();
            }
        })
        return false;
    })
});

window.Settings = window.Settings || {};
Settings.formHandler = function () {
    var jq = jQuery;
    jq('body').on('submit', '.settings-frm', function () {
        var data = jq(this).serialize();
        var that = jq(this);
        jq.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            beforeSend: function () {
                that.find('button[type="submit"]').attr('disabled', 'disabled');
            },
            success: function (data) {
                if (data.data.reload != undefined) {
                    Defender.showNotification('success', data.data.message);
                    location.reload();
                } else if (data.data != undefined && data.data.url != undefined) {
                    location.href = data.data.url;
                } else {
                    that.find('button[type="submit"]').removeAttr('disabled');
                    jq('div.settings').trigger('form-submitted', [data, that])
                }
            }
        })
        return false;
    })
}
