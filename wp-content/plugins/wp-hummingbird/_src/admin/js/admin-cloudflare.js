import Fetcher from './utils/fetcher';

( function( $ ) {
    WPHB_Admin.cloudflare = {
        module: 'cloudflare',
        $cfSelector: false,
        $spinner: false,

        init: function () {
            this.$spinner = $('.wphb-spinner');
            this.$cfSelector = $('#wphb-caching-cloudflare-summary-set-expiry');
            let self = this;
            if ( wphb.cloudflare.is.connected ) {
                this.$cfSelector.change( function() {
                    self.setExpiry.call( self, [this] );
                } );
            }

            return this;
        },

        setExpiry: function( selector ) {
            this.displaySpinner();
            const value = $(selector).val();
            Fetcher.cloudflare.setExpiration( value )
                .then( () => {
                    window.location.reload();
                });
        },

        displaySpinner: function() {
            this.$spinner.css( 'visibility', 'visible' );
        }
    };
}( jQuery ) );