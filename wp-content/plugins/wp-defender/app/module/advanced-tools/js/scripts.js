jQuery(function ($) {
    Adtools.formHandler();

    $('div.advanced-tools').on('form-submitted', function (e, data, form) {
        if (form.attr('id') === 'advanced-settings-frm' || form.attr('id') === 'ad-mask-settings-frm') {
            if (data.success == true) {
                Defender.showNotification('success', data.data.message);
            } else {
                Defender.showNotification('error', data.data.message);
            }
        }
    })
    $('.deactivate-2factor').click(function () {
        $('#advanced-settings-frm').append('<input type="hidden" name="enabled" value="0"/>');
        $(this).attr('disabled', 'disabled');
        $('#advanced-settings-frm').submit();
    });
    $('.deactivate-atmasking').click(function () {
        $('#ad-mask-settings-frm').append('<input type="hidden" name="enabled" value="0"/>');
        $(this).attr('disabled', 'disabled');
        $('#ad-mask-settings-frm').submit();
    })

    $('body').on('change', '#toggle_force_auth', function (e) {
        if ($(this).prop('checked') == true) {
            $(this).closest('.column').find('.well').removeClass('is-hidden')
        } else {
            $(this).closest('.column').find('.well').addClass('is-hidden')
        }
    });
    $('body').on('change', '#customGraphic', function (e) {
        if ($(this).prop('checked') == true) {
            $(this).closest('.column').find('.well').removeClass('is-hidden')
        } else {
            $(this).closest('.column').find('.well').addClass('is-hidden')
        }
    })
    $('body').on('change', '#redirectTraffic', function (e) {
        if ($(this).prop('checked') == true) {
            $(this).closest('.column').find('.well').removeClass('is-hidden')
        } else {
            $(this).closest('.column').find('.well').addClass('is-hidden')
        }
    })


    var mediaUploader;
    $('.file-picker').click(function () {
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        // Extend the wp.media object
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose an image file',
            button: {
                text: 'Choose File'
            }, multiple: false,
            library: {
                type: ['image']
            }
        });

        // When a file is selected, grab the URL and set it as the text field's value
        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            if ($.inArray(attachment.mime, ["image/jpeg", "image/png", "image/gif"]) > -1) {
                $('#customGraphicURL').val(attachment.url);
                $('#customGraphicIMG').attr('src', attachment.url);
            } else {
                Defender.showNotification('error', 'Invalid image file type');
            }
        });
        // Open the uploader dialog
        mediaUploader.open();
    })
});
window.Adtools = window.Adtools || {};
Adtools.formHandler = function () {
    var jq = jQuery;
    jq('body').on('submit', '.advanced-settings-frm', function () {
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
                    jq('div.advanced-tools').trigger('form-submitted', [data, that])
                }
            }
        })
        return false;
    })
}