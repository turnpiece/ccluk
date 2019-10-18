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
    jq('.sui-wrap').prepend(div);
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