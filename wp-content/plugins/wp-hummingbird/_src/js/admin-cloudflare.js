import Fetcher from './utils/fetcher';

( function( $ ) {
    WPHB_Admin.cloudflare = {
        module: 'cloudflare',
        $cfSelector: false,

        init: function () {
            this.$cfSelector = $('#set-expiry-all');
            let cfSetExpiryButton = $('#set-cf-expiry-button');
            let self = this;
            if ( wphb.cloudflare.is.connected ) {
                cfSetExpiryButton.click( function(e) {
                    e.preventDefault();
                    self.setExpiry.call( self, self.$cfSelector );
                } );
            }

            return this;
        },

        setExpiry: function( selector ) {
            const value = $(selector).val();
            Fetcher.cloudflare.setExpiration( value )
                .then( () => {
                    window.location.reload();
                });
        }

    };
}( jQuery ) );