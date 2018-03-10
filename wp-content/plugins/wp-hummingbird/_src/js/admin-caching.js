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
        selectedExpiryType: '',

        init: function () {
            let self                    = this,
                cloudflareLink          = $('#wphb-box-caching-settings #connect-cloudflare-link, #wphb-box-caching-summary #connect-cloudflare-link'),
                configureLink           = $('#configure-link'),
                cloudFlareDismissLink   = $('#dismiss-cf-notice'),
                cloudFlareDashNotice    = $('.cf-dash-notice'),
                hash                    = window.location.hash,
                viewSnippetLink         = $('#view-snippet-code');

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
            configureLink.on('click', function(e) {
                e.preventDefault();
				$('html, body').animate({ scrollTop: $('#wphb-box-caching-settings').offset().top }, 'slow');
            });
            if (hash) {
                console.log(hash);
                $('html, body').animate({ scrollTop: $(hash).offset().top }, 'slow');
            }

            this.$serverSelector = $( '#wphb-server-type' );
            this.selectedServer  = this.$serverSelector.val();

            self.$snippets.apache    = $('#wphb-code-snippet-apache').find('pre').first();
			self.$snippets.LiteSpeed    = $('#wphb-code-snippet-litespeed').find('pre').first();
            self.$snippets.nginx     = $('#wphb-code-snippet-nginx').find('pre').first();

            viewSnippetLink.on('click', function(e) {
                e.preventDefault();
                let serverInstructions = $( '#wphb-server-instructions-' + self.selectedServer.toLowerCase() );
                $('#manual-' + self.selectedServer.toLowerCase() ).trigger("click");
                let caching = window.WPHB_Admin.getModule( 'caching' );
                caching.updateTabSize();
                $('html, body').animate({ scrollTop: serverInstructions.offset().top - 50 }, 'slow');
            });

            let instructionsList = $( '.wphb-server-instructions' );
            instructionsList.each( function() {
                self.$serverInstructions[ $(this).data('server') ] = $(this);
            });

            let expirySelectors = $( '.wphb-expiry-select' );
            let expiryChangeNotice = $( '#wphb-expiry-change-notice' );

            expirySelectors.each( function() {
                const type = $(this).data('type');
                if ( type ) {
                    $(this).change( function() {
                        // Expiration selector has changed
                        ( function() {
                            let expiry_times = [];
                            if ( 'all' === type ) {
                                expiry_times = self.getExpiryTimes( 'all' );
                            } else {
                                expiry_times = self.getExpiryTimes();
                            }
                            // Reload the code snippet
                            self.reloadSnippets( expiry_times );
                            expiryChangeNotice.slideDown();

                        })( this );
                    });
                } else {
                    $(this).change( function () {
                        expiryChangeNotice.slideDown();
                    })
                }

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
                        self.selectedExpiryType = 'all';
                    } else if ( 'expiry-single-type' === $(this).attr('id') ) {
						expirySettingsForm.find( "[data='expiry-all-types']" ).hide();
						expirySettingsForm.find( "[data='expiry-single-type']" ).show();
                        self.selectedExpiryType = 'single';
                    }
                }
            });
			expiryInput.on( 'click', function () {
                let expiry_times = [];
                if ( 'expiry-all-types' === $(this).attr('id') ) {
					expirySettingsForm.find( "[data='expiry-single-type']" ).hide();
					expirySettingsForm.find( "[data='expiry-all-types']" ).show();
                    expiry_times = self.getExpiryTimes( 'all' );
                    self.selectedExpiryType = 'all';
                } else if ( 'expiry-single-type' === $(this).attr('id') ) {
					expirySettingsForm.find( "[data='expiry-all-types']" ).hide();
					expirySettingsForm.find( "[data='expiry-single-type']" ).show();
                    expiry_times = self.getExpiryTimes();
                    self.selectedExpiryType = 'single';
                }

                // Reload the code snippet
                self.reloadSnippets( expiry_times );
			});

            $( '.tab label' ).on( 'click', function() {
                $( this ).parent().parent().find( '.tab label.active' ).removeClass( 'active' );
                $( this ).addClass( 'active' );
            });

            cloudFlareDismissLink.click( function(e) {
                e.preventDefault();
                Fetcher.notice.dismissCloudflareDash();
                cloudFlareDashNotice.slideUp();
                cloudFlareDashNotice.parent().addClass('no-background-image');

            });

            let activateButton = $( '.activate-button' );
            activateButton.click( function () {
                let expiry_times = [];
                if ( '' !== self.selectedExpiryType ) {
                    if ('all' === self.selectedExpiryType) {
                        expiry_times = self.getExpiryTimes('all');
                    } else {
                        expiry_times = self.getExpiryTimes();
                    }
                    Fetcher.caching.setExpiration( self.selectedExpiryType, expiry_times );
                }
            });

			/**
			 * Parse rss cache settings.
			 */
			$('.box-caching-rss .box-footer').on('click', '.button[type="submit"]', function (e) {
                e.preventDefault();

				const spinner = $(this).parent().find('.spinner');
				const settings_form = $('.box-caching-rss').closest('section').find('form[id="rss-caching-settings"]');

				// Make sure a positive value is always reflected for the rss expiry time input.
                let rss_expiry_time = settings_form.find('#rss-expiry-time');
                rss_expiry_time.val( Math.abs( rss_expiry_time.val() ) );

				spinner.addClass('visible');

				Fetcher.caching.saveSettings( settings_form.serialize() )
					.then( ( response ) => {
						spinner.removeClass('visible');

						if ( 'undefined' !== typeof response && response.success ) {
							self.showNotice( 'success' );
						} else {
							self.showNotice( 'error', wphb.strings.errorSettingsUpdate );
						}
					});
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

        reloadSnippets: function( expiry_times ) {
            let self = this;
            let stop = false;

            for ( let i in self.$snippets ) {
                if ( self.$snippets.hasOwnProperty( i ) ) {
                    Fetcher.caching.reloadSnippets( i, expiry_times )
                        .then( ( response ) => {
                            if ( stop ) {
                                return;
                            }

                            self.$snippets[response.type].text( response.code );
                        });
                }
            }
        },

        getExpiryTimes: function( type ) {
            let expiry_times = [];
            if ( 'all' === type ){
                let all = $('#set-expiry-all').val();
                expiry_times = {
                    expiry_javascript: all,
                    expiry_css: all,
                    expiry_media: all,
                    expiry_images: all,
                }
            } else {
                expiry_times = {
                    expiry_javascript: $('#set-expiry-javascript').val(),
                    expiry_css: $('#set-expiry-css').val(),
                    expiry_media: $('#set-expiry-media').val(),
                    expiry_images: $('#set-expiry-images').val(),
                };
            }
            return expiry_times;
        },

		/**
		 * Notice on settings update.
		 *
		 * @param type
		 * @param message
		 */
		showNotice: function ( type, message = '' ) {
			const notice = $('#wphb-notice-rss-cache');

			// Remove set classes if doing multiple calls per page load.
			notice.removeClass('wphb-notice-error');
			notice.removeClass('wphb-notice-success');

			window.scrollTo( 0, 0 );
			notice.addClass('wphb-notice-' + type);

			if ( '' !== message ) {
				notice.find('p').html(message);
			}

			notice.slideDown();
			setTimeout( function() {
				notice.slideUp();
			}, 5000 );
		}
    };
}( jQuery ));