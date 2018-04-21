import Fetcher from './utils/fetcher';
import Clipboard from './utils/clipboard';

(function($) {
    WPHB_Admin.gzip = {
        module: "gzip",
        selectedServer: "",
        $serverSelector: null,
        $serverInstructions: [],

        init: function() {
            const self = this;

            this.$serverSelector = $("#wphb-server-type");
            this.selectedServer = this.$serverSelector.val();
            let instructionsList = $(".wphb-server-instructions"),
                configureLink = $("#configure-gzip-link"),
                troubleshootingLink = $("#troubleshooting-link"),
                troubleshootingLinkLiteSpeed = $("#troubleshooting-link-litespeed");

            new Clipboard('.wphb-code-snippet .button');
            
            instructionsList.each(function() {
                self.$serverInstructions[$(this).data("server")] = $(this);
            });
            this.showServerInstructions(this.selectedServer);
            this.$serverSelector.change(function() {
                const value = $(this).val();
                self.hideCurrentInstructions();
                self.showServerInstructions(value);
                self.setServer(value);
                self.selectedServer = value;
                // Update tab size on select change.
                self.updateTabSize();
            });
            configureLink.on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: $('#wphb-box-gzip-settings').offset().top -50 }, 'slow');
            });
            troubleshootingLink.on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: $('#troubleshooting-gzip').offset().top }, 'slow');
            });
            troubleshootingLinkLiteSpeed.on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: $('#troubleshooting-gzip-litespeed').offset().top }, 'slow');
            });
            return this;
        },

        hideCurrentInstructions: function() {
            const selected = this.selectedServer;
            if (this.$serverInstructions[selected]) {
                this.$serverInstructions[selected].hide();
            }
        },

        showServerInstructions: function(server) {
            if (typeof this.$serverInstructions[server] !== "undefined") {
                this.$serverInstructions[server].show();
            }
            if ("apache" === server || 'LiteSpeed' === server) {
                $("#enable-cache-wrap").show();
            } else {
                $("#enable-cache-wrap").hide();
            }
        },
        updateTabSize: function() {
            let jq      = $( '#wphb-server-instructions-' + this.selectedServer.toLowerCase() ).find( '.sui-tabs' ),
                current = jq.find('.sui-tab > input:checked').parent(),
                content = current.find('.sui-tab-content');

            jq.height( content.outerHeight() + current.outerHeight() - 6 );
        },

        setServer: function( value ) {
            Fetcher.caching.setServer( value );
        },
    };
})(jQuery);