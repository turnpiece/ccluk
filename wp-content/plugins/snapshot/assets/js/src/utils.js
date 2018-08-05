;(function ($) {
    SS_UTILS.openModal = function(title, content, onRender, onClose){
        var newModal = $($("#ss-modal-template").html());

        newModal.find(".wpmud-box-title > h3").html(title);
        newModal.find(".wpmud-box-content").html(content);
        $("#wpbody-content").append(newModal);
        if($.isFunction(onRender))onRender();
        newModal.fadeIn();
        newModal.on("click", ".wpmud-box-title > .close", function(){
            newModal.fadeOut();
            setTimeout(function(){
                newModal.remove();
                if($.isFunction(onClose)) onClose();
            },400);
        });
    };
})(jQuery);