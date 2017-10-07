import Clipboard from './utils/clipboard';
import Fetcher from './utils/fetcher';

( function( $ ) {
    'use strict';
    WPHB_Admin.caching = {

        module: 'caching',
        selectedServer: '',
        $serverSelector: null,
        $serverInstructions: [],
        $snippets: [],

        init: function () {
            let self                    = this,
                cloudflareLink          = $('#wphb-box-caching-settings #connect-cloudflare-link');

			new Clipboard('.wphb-code-snippet .button');

            if ( wphbCachingStrings )
                self.strings = wphbCachingStrings;

            cloudflareLink.on('click', function(e) {
                e.preventDefault();
				$('#wphb-server-type').val('cloudflare').trigger('wpmu:change');
				self.hideCurrentInstructions();
                self.setServer('cloudflare');
				self.showServerInstructions('cloudflare');
				self.selectedServer = 'cloudflare';
				$('html, body').animate({ scrollTop: $('#cloudflare-steps').offset().top }, 'slow');
            });

            this.$serverSelector = $( '#wphb-server-type' );
            this.selectedServer  = this.$serverSelector.val();

            self.$snippets.apache    = $('#wphb-code-snippet-apache').find('pre').first();
			self.$snippets.LiteSpeed    = $('#wphb-code-snippet-litespeed').find('pre').first();
            self.$snippets.nginx     = $('#wphb-code-snippet-nginx').find('pre').first();

            let instructionsList = $( '.wphb-server-instructions' );
            instructionsList.each( function() {
                self.$serverInstructions[ $(this).data('server') ] = $(this);
            });

            this.showServerInstructions( this.selectedServer );

            this.$serverSelector.change( function() {
                let value = $(this).val();
                self.hideCurrentInstructions();
                self.showServerInstructions( value );
                self.setServer(value);
                self.selectedServer = value;
				// Update tab size on select change.
                self.updateTabSize();
                $('.hb-server-type').val( value );
            });

            let expiryInput = $("input[name='expiry-set-type']");
            let expirySettingsForm = $('.settings-form');
			expiryInput.each( function () {
                if ( this.checked ) {
                    if ( 'expiry-all-types' === $(this).attr('id') ) {
						expirySettingsForm.find( "[data='expiry-single-type']" ).hide();
						expirySettingsForm.find( "[data='expiry-all-types']" ).show();
                    } else if ( 'expiry-single-type' === $(this).attr('id') ) {
						expirySettingsForm.find( "[data='expiry-all-types']" ).hide();
						expirySettingsForm.find( "[data='expiry-single-type']" ).show();
                    }
                }
            });
			expiryInput.on( 'click', function () {
                if ( 'expiry-all-types' === $(this).attr('id') ) {
					expirySettingsForm.find( "[data='expiry-single-type']" ).hide();
					expirySettingsForm.find( "[data='expiry-all-types']" ).show();
                } else if ( 'expiry-single-type' === $(this).attr('id') ) {
					expirySettingsForm.find( "[data='expiry-all-types']" ).hide();
					expirySettingsForm.find( "[data='expiry-single-type']" ).show();
                }
			});

            return this;
        },

        setServer: function( value ) {
            Fetcher.caching.setServer( value );
        },

		updateTabSize: function() {
			let jq      = $( '#wphb-server-instructions-' + this.selectedServer.toLowerCase() ).find( '.tabs' ),
                current = jq.find('.tab > input:checked').parent(),
				content = current.find('.content');

			jq.height( content.outerHeight() + current.outerHeight() - 6 );
        },

        hideCurrentInstructions: function() {
            let selected = this.selectedServer;
            if ( this.$serverInstructions[ selected ] ) {
                this.$serverInstructions[ selected ].hide();
            }
        },

        showServerInstructions: function( server ) {
            if ( typeof this.$serverInstructions[ server ] !== 'undefined' ) {
                let serverTab = this.$serverInstructions[ server ];
				serverTab.show();
                // Show tab.
				serverTab.find('.tab:first-child > label').trigger('click');
            }

            if ( 'apache' === server || 'LiteSpeed' === server ) {
                $( '.enable-cache-wrap-' + server ).show();
            }
            else {
                $( '#enable-cache-wrap' ).hide();
            }
        },

        reloadSnippets: function() {
            let self = this;
            let stop = false;

            for ( let i in self.$snippets ) {
                if ( self.$snippets.hasOwnProperty( i ) ) {
                    Fetcher.caching.reloadSnippets( i )
                        .then( ( response ) => {
                            if ( stop ) {
                                return;
                            }

                            self.$snippets[response.type].text( response.code );

                            // Make sure that we only do things when server displayed is the processed one
                            if ( response.type !== self.selectedServer ) {
                                return;
                            }

                            if ( 'apache' === response.type && response.updatedFile ) {
                                $( '#wphb-notice-code-snippet-htaccess-updated' ).show();
                                location.href = self.strings.recheckURL + '&caching-updated=true';
                            } else if ( 'apache' === response.type && self.strings.cacheEnabled && ! response.updatedFile ) {
                                $( '#wphb-notice-code-snippet-htaccess-error' ).show();
                                location.href = self.strings.htaccessErrorURL;
                            } else {
                                $( '#wphb-notice-code-snippet-updated' ).show();
                                location.href = self.strings.recheckURL + '&caching-updated=true';
                            }
                        });
                }
            }
        }
    };
}( jQuery ));