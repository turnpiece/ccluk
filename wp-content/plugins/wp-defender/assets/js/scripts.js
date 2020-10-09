window.Defender = window.Defender || {};

//Added extra parameter to allow for some actions to keep modal open
Defender.showNotification = function (type, message, closeModal = false) {
    var jq = jQuery;
    if (jq('body').find('#defender-notification').size() === 0) {
        var html = '<div class="sui-floating-notices"><div role="alert" id="defender-notification" class="sui-notice" aria-live="assertive"></div></div>';
        jq('.sui-wrap').prepend(html);
    }
    var options = {
        type: 'green',
        icon: 'info',
        dismiss: {
            show: closeModal,
            label: 'Click to close',
            tooltip: 'Dismiss'
        },
        autoclose: {
            timeout: 3000
        }
    }
    if (type === 'error') {
        options.type = 'red';
    }
    SUI.openNotice('defender-notification', '<p>' + message + '</p>', options);
}