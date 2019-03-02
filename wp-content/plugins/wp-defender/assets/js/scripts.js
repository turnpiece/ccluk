jQuery(function ($) {
    $('body').on('change', '.toggle-checkbox', function (e) {
        if ($(this).prop('checked') == true) {
            $('label[for="' + $(this).attr('id') + '"]').attr('aria-checked', true);
        } else {
            $('label[for="' + $(this).attr('id') + '"]').attr('aria-checked', false);
        }
    });
    //blacklist helper
    if ($('.blacklist-widget').size() > 0) {
        $('.blacklist-widget').submit(function () {
            var that = $(this);
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: that.serialize(),
                success: function (data) {
                    var parent = that.closest('.sui-box');
                    parent.replaceWith(data.data.html);
                }
            })
            return false;
        }).submit();
    }
    $('body').on('submit', '.toggle-blacklist-widget', function () {
        var that = $(this);
        var overlay = Defender.createOverlay();
        var parent = that.closest('.sui-box');
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: that.serialize(),
            beforeSend: function () {
                parent.prepend(overlay);
            },
            success: function (data) {
                if (data.success == true) {
                    parent.replaceWith(data.data.html);
                } else {
                    overlay.remove();
                    Defender.showNotification('error', data.data.message);
                }
            }
        })
        return false;
    })
    $('body').on('change', '#toggle_blacklist', function () {
        $('.toggle-blacklist-widget').submit();
    })

    if ($('#activator').size() > 0) {
        var listen = setInterval(function () {
            if (SUI.dialogs !== undefined) {
                SUI.dialogs['activator'].show();
                clearInterval(listen);
            }
        }, 500)
    }

    $('.change-one-time-pass-email').click(function () {
        WDP.showOverlay("#edit-one-time-password-email", {
            title: defender_adtools.edit_email_title,
            class: 'wd-one-time-pass-email'
        });
    });

    if ($('#requirement').size() > 0) {
        WDP.showOverlay("#requirement", {
            class: 'no-close wp-defender wd-requirement'
        });
    }
    if ($('#wpmudev-auth-modal').size() > 0) {
        WDP.showOverlay("#wpmudev-auth-modal", {
            class: 'no-close wp-defender wpmudev-auth-modal',
            title: 'Create Account <span>Already have an account? <a href="">Log in</a></span>'
        });
    }

    $('body').on('submit', '.activate-picker form:not(.skip-activator)', function () {
        var that = $(this);
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: that.serialize(),
            beforeSend: function () {
                that.find('.sui-button').attr('disabled', 'disabled');
            },
            success: function (data) {
                that.find('.sui-button').removeAttr('disabled');
                if (data.success == 1) {
                    $('.activate-picker').addClass('wd-hide');
                    //$('.activate-picker').closest('.box').attr('style', 'padding-bottom:150px !important');
                    $('.activate-progress').removeClass('wd-hide');
                    //remove skip button
                    $('.skip-activator').hide();
                    var i = 0;
                    progress();

                    function progress() {
                        if (i < data.data.activated.length) {
                            var text = dashboard[data.data.activated[i]];
                            $('.activate-progress').find('.status-text').html(text);
                            i++;
                            setTimeout(function () {
                                var process = parseFloat(((i / data.data.activated.length) * 100)).toFixed(2) + '%';
                                $('.scan-progress-text span').html(process);
                                $('.scan-progress-bar span').css('width', process);
                                setTimeout(progress, 1000);
                            }, 2000)
                        } else {
                            location.reload();
                        }
                    }
                }
            }
        })
        return false;
    })
    $('body').on('click', '[rel="show-filter"]', function () {
        var target = $(this).data('target');
        $(target).toggleClass('sui-open')
    })

    $('body').on('submit', '.skip-activator', function () {
        var that = $(this);
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: that.serialize(),
            beforeSend: function () {
                that.find('.sui-button').attr('disabled', 'disabled');
            },
            success: function (data) {
                location.reload();
            }
        });
        return false;
    });
    $('body').on('click', '.dev-overlay', function (e) {
        if ($(this).hasClass('scanning') || $(this).hasClass('migrate-iplockout') || $(this).hasClass('wd-requirement')) {
            return;
        }
        var target = $(e.target);
        if (target.hasClass('box-scroll')) {
            WDP.closeOverlay();
        }
    })

    $('body').on('change', '.mobile-nav', function () {
        var url = $(this).val();
        if (url.length > 0) {
            location.href = url;
        }
    })
    $('.wp-defender a[disabled="disabled"]').click(function (e) {
        e.preventDefault()
    })
    $('body').on('click', '[rel="input_value"]', function () {
        var target = $('[name="' + $(this).data('target') + '"]');
        $(target).val($(this).data('value'));
    })
})
window.Defender = window.Defender || {};

//Added extra parameter to allow for some actions to keep modal open
Defender.showNotification = function (type, message, closeModal) {
    var jq = jQuery;
    if (jq('body').find('.sui-notice-floating').size() > 0) {
        return;
    }
    var div = jq('<div class="sui-notice-floating"/>');
    if (type == 'error') {
        div.addClass('sui-notice-error');
    } else {
        div.addClass('sui-notice-success');
    }
    div.html('<p>' + message + '</p>'); //Decode the message incase it was esc_html
    div.hide();
    jq('#wp-defender').prepend(div);
    var close_modal = (typeof closeModal === 'undefined') ? true : closeModal;
    div.fadeIn(300, function () {
        //Check if close is enabled
        if (close_modal) {
            setTimeout(function () {
                div.fadeOut(200, function () {
                    div.remove();
                });
            }, 5000);
        }
    });
    //An action has to be done. So we cant do this
    if (close_modal) {
        div.on('click', function () {
            div.fadeOut(200, function () {
                div.remove();
            });
        });
    }

};
Defender.createOverlay = function () {
    var jq = jQuery;
    var div = jq('<div class="wd-overlay"/>');
    div.html('<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>');
    return div;
};
