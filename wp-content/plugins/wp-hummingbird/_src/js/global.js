import WPHBReports from './performance/reports';

( function() {
  'use strict';

  const WPHBGlobal = {
    menuButton: document.querySelector('#wp-admin-bar-wphb-clear-cache > a'),
    noticeButton: document.getElementById('wp-admin-notice-wphb-clear-cache'),
    reportButton: document
        .getElementById('wp-admin-bar-wphb-performance-report'),
    ajaxurl: null,

    init: function() {
      /** @var {array} wphbGlobal */
      if ( wphbGlobal ) {
        this.ajaxurl = wphbGlobal.ajaxurl;
      } else {
        this.ajaxurl = ajaxurl;
      }

      WPHBReports.init();

      if ( this.menuButton ) {
        this.menuButton.addEventListener(
            'click',
            () => this.post(WPHBGlobal.ajaxurl, 'wphb_front_clear_cache')
        );
      }

      if ( this.noticeButton ) {
        this.noticeButton.addEventListener(
            'click',
            () => this.post(WPHBGlobal.ajaxurl,'wphb_global_clear_cache')
        );
      }

      if ( this.reportButton ) {
        this.reportButton.addEventListener(
            'click',
            () => SUI.dialogs['wphb-performance-dialog'].show()
        );
      }
    },

    post: (url, action) => {
      const xhr = new XMLHttpRequest();
      xhr.open( 'POST', url+'?action='+action );
      xhr.onload = function() {
        if (xhr.status === 200) {
          location.reload();
        } else {
          console.log( 'Request failed.  Returned status of ' + xhr.status );
        }
      };
      xhr.send();
    },
  };

  document.addEventListener('DOMContentLoaded', function() {
    WPHBGlobal.init();
  } );
}());
