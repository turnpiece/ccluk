import Fetcher from './utils/fetcher';

( function( $ ) {
    'use strict';
    WPHB_Admin.settings = {

        module: 'settings',

        init: function () {

            let self = this;
            let body = $('body');
            let wrap = body.find('.sui-wrap');

            // Save settings
            body.on('submit', '.settings-frm', function (e) {
                e.preventDefault();
                const form_data = $(this).serialize();

                if ( $('#color_accessible').is(':checked') ) {
                    wrap.addClass('sui-color-accessible');
                } else {
                    wrap.removeClass('sui-color-accessible');
                }
                Fetcher.settings.saveSettings( form_data )
                    .then( () => {
						WPHB_Admin.notices.show('wphb-ajax-update-notice', true);
                    });
                return false;
            });

            return this;
        },

    };
}( jQuery ));