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

    $('body').on('change', '#forceAuth', function (e) {
        if ($(this).prop('checked') == true) {
            $('#forceAuthRoles').attr('aria-hidden', false)
        } else {
            $('#forceAuthRoles').attr('aria-hidden', true)
        }
    });

    $('body').on('change', '.toggle-checkbox', function (e) {
        console.log($(this).attr('id'));
        if ($(this).prop('checked') == true) {
            $('label[for="' + $(this).attr('id') + '"]').attr('aria-checked', true);
        } else {
            $('label[for="' + $(this).attr('id') + '"]').attr('aria-checked', false);
        }
    });

    $('body').on('change', '#customGraphic', function (e) {
        if ($(this).prop('checked') == true) {
            $('#customGraphicContainer').attr('aria-hidden', false);
        } else {
            $('#customGraphicContainer').attr('aria-hidden', true);
        }
    })
    $('body').on('change', '#redirectTraffic', function (e) {
        if ($(this).prop('checked') == true) {
            $('#redirectTrafficContainer').attr('aria-hidden',false);
        } else {
            $('#redirectTrafficContainer').attr('aria-hidden',true);
        }
    })

    $('body').on('click', '.2f-send-test-email', function () {
        var jq = jQuery,
            parentForm = jq('#edit-one-time-password-email form'),
            that = jq(this),
            data = parentForm.serialize();
        data = data + '&action=testTwoFactorOPTEmail';
        // return;
        jq.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            beforeSend: function () {
                parentForm.find('button[type="button"]').attr('disabled', 'disabled');
            },
            success: function (data) {
                var notificationType = 'success';
                if (!data.success) {
                    notificationType = 'error';
                }
                parentForm.find('button[type="button"]').removeAttr('disabled');
                Defender.showNotification(notificationType, data.data.message);
            }
        })
        return false;
    });

    $('body').on('click', '.save-2f-opt-email', function () {
        var jq = jQuery,
            parentForm = jq('#edit-one-time-password-email form'),
            that = jq(this),
            data = parentForm.serialize();
        data = data + '&action=saveTwoFactorOPTEmail';
        // return;
        jq.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            beforeSend: function () {
                parentForm.find('button[type="button"]').attr('disabled', 'disabled');
            },
            success: function (data) {
                var notificationType = 'success';
                if (!data.success) {
                    notificationType = 'error';
                }
                parentForm.find('button[type="button"]').removeAttr('disabled');
                Defender.showNotification(notificationType, data.data.message);
                if (data.data.reload != undefined) {
                    location.reload();
                }
            }
        })
        return false;
    });
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
