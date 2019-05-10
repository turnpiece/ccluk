import Fetcher from '../utils/fetcher';

( function( $ ) {
    'use strict';
    WPHB_Admin.settings = {

        module: 'settings',

        init: function () {

            let body = $('body');
            let wrap = body.find('.wrap-wphb-settings');

            // Save settings
            body.on('click', 'button.sui-button', function (e) {
                e.preventDefault();
                const form_data = body.find('.settings-frm').serialize();

                const contrastDiv = $('#color_accessible');
                if ( contrastDiv.length ) {
                    if ( contrastDiv.is(':checked') ) {
                        wrap.addClass('sui-color-accessible');
                    } else {
                        wrap.removeClass('sui-color-accessible');
                    }
                }
                Fetcher.settings.saveSettings( form_data )
                    .then( () => {
						WPHB_Admin.notices.show('wphb-ajax-update-notice', true);
                    });
                return false;
            });

            /**
             * Parse remove settings change.
             */
            $('input[name=remove_settings]').on('change', function (e) {
                const otherClass = 'remove_settings-false' === e.target.id ? 'remove_settings-true' : 'remove_settings-false';
                e.target.parentNode.classList.add('active');
                document.getElementById(otherClass).parentNode.classList.remove('active');
            });

            /**
             * Parse remove data change.
             */
            $('input[name=remove_data]').on('change', function (e) {
                const otherClass = 'remove_data-false' === e.target.id ? 'remove_data-true' : 'remove_data-false';
                e.target.parentNode.classList.add('active');
                document.getElementById(otherClass).parentNode.classList.remove('active');
            });

            return this;
        },

        /**
         * Parse confirm settings reset from the modal.
         *
         * @since 2.0.0
         */
        confirmReset: () => {
            Fetcher.settings.resetSettings()
                .then( () => {
                    window.location.href = wphb.urls.resetSettings;
                });
        }

    };
}( jQuery ));
