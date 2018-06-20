import Fetcher from './utils/fetcher';

( function( $ ) {
    'use strict';

    let WPHB_Admin = {
        modules: [],
        // Common functionality to all screens
        init: function() {
			// Dismiss notice via an ajax call.
            let notice = document.querySelector('#wphb-dismissable > .sui-notice-dismiss');

            if ( notice ) {
				notice.addEventListener('click', () => {
					const notice_id = notice.parentElement.getAttribute('data-id');
					Fetcher.notice.dismiss(notice_id);
				});
            }
        },
        initModule: function( module ) {
            if ( this.hasOwnProperty( module ) ) {
                this.modules[ module ] = this[ module ].init();
                return this.modules[ module ];
            }

            return {};
        },
        getModule: function( module ) {
            if ( typeof this.modules[ module ] !== 'undefined' )
                return this.modules[ module ];
            else
                return this.initModule( module );
        }
    };

    WPHB_Admin.utils = {
        membershipModal: {
            open: function() {
                SUI.dialogs['wphb-upgrade-membership-modal'].show();
            }
        },

        post: function( data, module ) {
            data.action = 'wphb_ajax';
            data.module = module;
            return $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: data
            });
        }
    };

	/**
     * Admin notices.
	 */
	WPHB_Admin.notices = {
		init: function() {},
		/**
		 * Show notice.
		 *
		 * @since 1.8
		 *
		 * @param id       ID of notice element.
		 * @param top      Scroll to top.
		 * @param type     Error or success.
		 * @param message  Message to display.
		 *
		 * @var {array} wphb
		 */
        show: function( id, top = false, type = '', message = wphb.strings.successUpdate ) {
			const notice = $('#' + id);

			if ( top ) {
				window.scrollTo(0,0);
			}

			if ( '' !== type ) {
				// Remove set classes if doing multiple calls per page load.
				notice.removeClass('sui-notice-error');
				notice.removeClass('sui-notice-success');
				notice.addClass('sui-notice-' + type);
			}

			notice.find('p').html(message);

			notice.slideDown();
			setTimeout( function() {
				notice.slideUp();
			}, 5000 );
        }
    };

    window.WPHB_Admin = WPHB_Admin;

}( jQuery ) );