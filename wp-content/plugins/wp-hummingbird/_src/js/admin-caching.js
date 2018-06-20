import Fetcher from './utils/fetcher';

( function( $ ) {
    'use strict';
    WPHB_Admin.caching = {

        module: 'caching',
		selectedServer: '',
		serverSelector: null,
        serverInstructions: [],
        snippets: [],
        selectedExpiryType: 'all',

        init: function () {
            let self            = this,
				hash            = window.location.hash,
				pageCachingForm = $('form[id="page-caching-form"]'),
				rssForm         = $('form[id="rss-caching-settings"]'),
				gravatarDiv     = $('div[id="wphb-box-caching-gravatar"]'),
				cachingHeader   = $('.box-caching-status .sui-box-header'),
				expiryForm      = $('form[id="expiry-settings"]'),
				settingsForm    = $('form[id="other-caching-settings"]');

            // Define selected server.
            self.serverSelector = $('#wphb-server-type');
            self.selectedServer = self.serverSelector.val();

			/** @var {array} wphbCachingStrings */
            if ( wphbCachingStrings ) {
				self.strings = wphbCachingStrings;
			}

            if ( hash && $(hash).length ) {
                setTimeout(function () {
					$('html, body').animate({ scrollTop: $(hash).offset().top }, 'slow');
                }, 300);
			} else if ( '#connect-cloudflare' === hash ) {
                self.setCloudflare();
			}

			/**
			 * PAGE CACHING
			 *
			 * @since 1.7.0
			 */

			// Save page caching settings.
			pageCachingForm.on('submit', (e) => {
				e.preventDefault();
				self.saveSettings( 'page_cache', pageCachingForm );
			});

			// Clear page cache.
			pageCachingForm.on('click', '.sui-box-header .sui-button', (e) => {
				e.preventDefault();
				self.clearCache( 'page_cache', pageCachingForm );
			});

			/**
			 * BROWSER CACHING
			 */

			// Init server instructions tabs.
			$('.wphb-server-instructions').each( function() {
				self.serverInstructions[ $(this).data('server') ] = $(this);
			});
			self.showServerInstructions( this.selectedServer );

            // This is used to trigger the resizing of the tabs.
            $(window).trigger( 'resize' );

			// Init code snippets.
			self.snippets.apache    = $('.apache-instructions').find('pre.sui-code-snippet');
			self.snippets.LiteSpeed = $('.litespeed-instructions').find('pre.sui-code-snippet');
			self.snippets.nginx     = $('#wphb-server-instructions-nginx').find('pre.sui-code-snippet');

			// Server type changed.
			self.serverSelector.change( function() {
				let value = $(this).val();
				self.hideCurrentInstructions();
				self.showServerInstructions( value );
				self.setServer( value );
				self.selectedServer = value;
				$('.hb-server-type').val( value );
                // This is used to trigger the resizing of the tabs.
                $(window).trigger( 'resize' );
			});

			// Expiry time change between all types and individual type.
			let expiryInput        = $("input[name='expiry-set-type']");
			let expirySettingsForm = $('.sui-box-settings-row');
			expiryInput.change( function() {
				if ( 'all' === this.value ) {
					expirySettingsForm.find( "[data-type='expiry-single']" ).addClass( 'sui-hidden' );
					expirySettingsForm.find( "[data-type='expiry-all']" ).removeClass( 'sui-hidden' );
				}
				else if ( 'single' === this.value ) {
					expirySettingsForm.find( "[data-type='expiry-all']" ).addClass( 'sui-hidden' );
					expirySettingsForm.find( "[data-type='expiry-single']" ).removeClass( 'sui-hidden' );
					self.selectedExpiryType = 'single';
				}

				// Reload the code snippet
				self.reloadSnippets( self.getExpiryTimes( this.value ) );
			});

			// Expiry value changed.
			expiryForm.on('change', 'select[name^="set-expiry"]', function() {
				self.reloadSnippets( self.getExpiryTimes( self.selectedExpiryType ) );
				$('#wphb-expiry-change-notice').slideDown();
			});

			// Re-check expiry button clicked.
			cachingHeader.on('click', 'input[type="submit"]', (e) => {
				e.preventDefault();

				const spinner = cachingHeader.find('.spinner');
				const button = cachingHeader.find('input[type="submit"]');

				button.addClass('disabled');
				spinner.addClass('visible');

				Fetcher.caching.recheckExpiry()
					.then( ( response ) => {
						button.removeClass('disabled');
						spinner.removeClass('visible');

						if ( 'undefined' !== typeof response && response.success ) {
							WPHB_Admin.notices.show( 'wphb-ajax-update-notice', true, 'success', self.strings.successRecheckStatus );
							self.reloadExpiryTags( response.expiry_values );
						}
						else {
							WPHB_Admin.notices.show( 'wphb-ajax-update-notice', true, 'error', self.strings.errorRecheckStatus );
						}
					});
			});

			// Update .htaccess clicked.
			expiryForm.on('submit', (e) => {
				e.preventDefault();

				const button = $('.update-htaccess');
				const spinner = $('.wphb-expiry-changes .spinner');
				const notice = $('#wphb-expiry-change-notice');

				button.addClass('disabled');
				spinner.addClass('visible');

				let expiry_times = self.getExpiryTimes( self.selectedExpiryType );
				Fetcher.caching.setExpiration( expiry_times );

                // Set timeout to allow new expiry values to be saved.
                setTimeout(
                    function()
                    {
                        Fetcher.caching.updateHtaccess()
                            .then( ( response ) => {
                                button.removeClass('disabled');
                                spinner.removeClass('visible');
                                notice.slideUp('slow');

                                if ( 'undefined' !== typeof response && response.success ) {
                                    WPHB_Admin.notices.show( 'wphb-ajax-update-notice', true, 'success', wphb.strings.htaccessUpdated );
                                }
                                else {
                                    WPHB_Admin.notices.show( 'wphb-ajax-update-notice', true, 'error', self.strings.htaccessUpdatedFailed );
                                }
                            });
                    }, 1000);

			});

			// View code clicked (when rules already in .htaccess and expiry values are updated).
			$('#view-snippet-code').on('click', function(e) {
				e.preventDefault();
				let serverInstructions = $( '#wphb-server-instructions-' + self.selectedServer.toLowerCase() );
				let selectedServer     = self.selectedServer.toLowerCase();

				$( '#auto-' + selectedServer ).removeClass('active');
				$( '#manual-' + selectedServer ).trigger("click").addClass('active');

                // This is used to trigger the resizing of the tabs.
                $(window).trigger( 'resize' );
				$('html, body').animate({ scrollTop: serverInstructions.offset().top - 50 }, 'slow');
			});

			// Activate button clicked.
			$('.activate-button').on('click', function(e) {
                e.preventDefault();
                $(this).addClass('sui-button-onload');
                // Update expiration times.
                let expiry_times = self.getExpiryTimes( self.selectedExpiryType );
                Fetcher.caching.setExpiration( expiry_times );
                let redirect = $(this).attr('href');
                // Set timeout to allow new expiry values to be saved.
                setTimeout(
                    function()
                    {
                        window.location = redirect;
                    }, 1000);
			});

			/**
			 * CLOUDFLARE
			 */

			// Connect Cloudflare link clicked.
			$('.connect-cloudflare-link').on('click', function(e) {
                e.preventDefault();
                window.location.hash = 'connect-cloudflare';
				self.setCloudflare();
            });

			// "# of your cache types don’t meet the recommended expiry period" notice clicked.
			$('#configure-link').on('click', function(e) {
                e.preventDefault();
				$('html, body').animate({ scrollTop: $('#wphb-box-caching-settings').offset().top }, 'slow');
            });

			// Cloudflare blue notice dismiss link
			$('#dismiss-cf-notice').on( 'click', function(e) {
				e.preventDefault();
				Fetcher.notice.dismissCloudflareDash();
				$('.cf-dash-notice').slideUp().parent().addClass('no-background-image');
			});

			/**
			 * GRAVATAR CACHING
			 *
			 * @since 1.9.0
			 */

			// Clear cache.
			gravatarDiv.on('click', '.sui-box-header .sui-button', (e) => {
				e.preventDefault();
				self.clearCache( 'gravatar', gravatarDiv );
			});

			/**
			 * RSS CACHING
			 *
			 * @since 1.8
			 */

			// Parse rss cache settings.
			rssForm.on('submit', (e) => {
				e.preventDefault();

				// Make sure a positive value is always reflected for the rss expiry time input.
				let rss_expiry_time = rssForm.find('#rss-expiry-time');
				rss_expiry_time.val( Math.abs( rss_expiry_time.val() ) );

				self.saveSettings( 'rss', rssForm );
			});

			/**
			 * SETTINGS
			 *
			 * @since 1.8.1
			 */

			// Parse page cache settings.
			settingsForm.on('submit', (e) => {
				e.preventDefault();

				// Hide the notice if it is showing.
				const detection = $( 'input[name="detection"]:checked', settingsForm ).val();
				if ( 'auto' === detection || 'none' === detection ) {
					$('.wphb-notice.notice-info').slideUp();
				}

				self.saveSettings( 'other_cache', settingsForm );
			});

			return this;
        },

		/**
		 * Process form submit from page caching, rss and settings forms.
		 *
		 * @since 1.9.0
		 *
		 * @param {string} module  Module name.
		 * @param {object} form    Form.
		 */
		saveSettings: ( module, form ) => {
			const spinner = form.find('.sui-box-footer .spinner');
			spinner.addClass('visible');

			Fetcher.caching.saveSettings( module, form.serialize() )
				.then( ( response ) => {
					spinner.removeClass('visible');

					if ( 'undefined' !== typeof response && response.success ) {
						WPHB_Admin.notices.show( 'wphb-ajax-update-notice', true, 'success' );
					}
					else {
						WPHB_Admin.notices.show( 'wphb-ajax-update-notice', true, 'error', wphb.strings.errorSettingsUpdate );
					}
				});
		},

		/**
		 * Unified clear cache method that clears: page cache, gravatar cache and browser cache.
		 *
		 * @since 1.9.0
		 *
		 * @param {string} module  Module for which to clear the cache.
		 * @param {object} form    Form from which the call was made.
		 */
		clearCache: ( module, form ) => {
			const spinner = form.find('.sui-box-header .spinner');
			spinner.addClass('visible');

			Fetcher.caching.clearCache( module )
				.then( ( response ) => {
					if ( 'undefined' !== typeof response && response.success ) {
						if ( 'page_cache' === module ) {
							WPHB_Admin.notices.show( 'wphb-ajax-update-notice', true, 'success', wphbCachingStrings.successPageCachePurge );
						}
						else if ( 'gravatar' === module ) {
							WPHB_Admin.notices.show( 'wphb-ajax-update-notice', true, 'success', wphbCachingStrings.successGravatarPurge );
						}
					}
					else {
						WPHB_Admin.notices.show( 'wphb-ajax-update-notice', true, 'error', wphbCachingStrings.errorCachePurge );
					}

					spinner.removeClass('visible');
				});
		},

        setServer: function( value ) {
            Fetcher.caching.setServer( value );
        },

		setCloudflare: function() {
			$('#wphb-server-type').val('cloudflare').trigger('sui:change');
			this.hideCurrentInstructions();
			this.setServer('cloudflare');
			this.showServerInstructions('cloudflare');
			this.selectedServer = 'cloudflare';

			setTimeout(function () {
				$('html, body').animate({ scrollTop: $('#cloudflare-steps').offset().top }, 'slow');
			}, 300);
		},

        reloadExpiryTags: function( expiry_values ) {
            for (let k in expiry_values){
                if (expiry_values.hasOwnProperty(k)) {
                    $('#wphb-caching-expiry-' + k).text(expiry_values[k]);
                }
            }
        },

        hideCurrentInstructions: function() {
            if ( this.serverInstructions[ this.selectedServer ] ) {
                this.serverInstructions[ this.selectedServer ].addClass( 'sui-hidden' );
            }
        },

        showServerInstructions: function( server ) {
            if ( typeof this.serverInstructions[ server ] !== 'undefined' ) {
                let serverTab = this.serverInstructions[ server ];
				serverTab.removeClass( 'sui-hidden' );
                // Show tab.
				serverTab.find('.sui-tab:first-child > label').trigger('click');
            }

            if ( 'apache' === server || 'LiteSpeed' === server ) {
                $( '.enable-cache-wrap-' + server ).removeClass( 'sui-hidden' );
            } else {
                $( '#enable-cache-wrap' ).addClass( 'sui-hidden' );
            }
        },

        reloadSnippets: function( expiry_times ) {
            let self = this;
            let stop = false;

            for ( let i in self.snippets ) {
                if ( self.snippets.hasOwnProperty( i ) ) {
                    Fetcher.caching.reloadSnippets( i, expiry_times )
                        .then( ( response ) => {
                            if ( stop ) {
                                return;
                            }

                            self.snippets[response.type].text( response.code );
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
            }
            else {
                expiry_times = {
                    expiry_javascript: $('#set-expiry-javascript').val(),
                    expiry_css: $('#set-expiry-css').val(),
                    expiry_media: $('#set-expiry-media').val(),
                    expiry_images: $('#set-expiry-images').val(),
                };
            }
            return expiry_times;
        }
    };
}( jQuery ));