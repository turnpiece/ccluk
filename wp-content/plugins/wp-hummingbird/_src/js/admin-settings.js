import Fetcher from './utils/fetcher';

( function( $ ) {
    'use strict';
    WPHB_Admin.settings = {

        module: 'settings',

        init: function () {

            let self = this;
            let body = $('body');

            // Save settings
            body.on('submit', '.settings-frm', function (e) {
                e.preventDefault();
                const form_data = $(this).serialize();
                body.find('.sui-wrap').toggleClass('sui-color-accessible');

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