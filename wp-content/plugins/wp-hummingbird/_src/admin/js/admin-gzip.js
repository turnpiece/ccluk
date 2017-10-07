import Fetcher from './utils/fetcher';

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
            let instructionsList = $(".wphb-server-instructions");
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
            });
            $("#toggle-apache-instructions").click(function(e) {
                e.preventDefault();
                $(".apache-instructions").toggle();
            });
            $("#toggle-litespeed-instructions").click(function(e) {
                e.preventDefault();
                $(".litespeed-instructions").toggle();
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

        setServer: function( value ) {
            Fetcher.caching.setServer( value );
        },
    };
})(jQuery);