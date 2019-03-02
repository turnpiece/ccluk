( function() {
    'use strict';

    const WPHB_Global = {
    	menuButton: document.querySelector('#wp-admin-bar-wphb-clear-cache > a'),
    	noticeButton: document.getElementById('wp-admin-notice-wphb-clear-cache'),
        ajaxurl: null,

        init: function() {
            /** @var {array} wphbGlobal */
            if ( wphbGlobal ) {
                this.ajaxurl = wphbGlobal.ajaxurl;
            } else {
                this.ajaxurl = ajaxurl;
            }
    		if ( this.menuButton ) {
    			this.clearCache( this.menuButton, 'wphb_front_clear_cache' );
			}
    		if ( this.noticeButton ) {
    			this.clearCache( this.noticeButton, 'wphb_global_clear_cache' );
			}
		},

		clearCache: ( sender, action ) => {
            sender.addEventListener('click', () => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', WPHB_Global.ajaxurl+'?action='+action);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload();
                    }
                    else {
                        console.log( 'Request failed.  Returned status of ' + xhr.status );
                    }
                };
                xhr.send();
            });
		}
	};

    document.addEventListener("DOMContentLoaded", function(){
        WPHB_Global.init();
    } );
}());
