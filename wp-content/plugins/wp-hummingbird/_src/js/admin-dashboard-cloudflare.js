import Fetcher from './utils/fetcher';

( function( $ ) {
    WPHB_Admin.DashboardCloudFlare = {
        init: function( settings ) {
            this.currentStep = settings.currentStep;
            this.data = settings;
            this.email = settings.email;
            this.apiKey = settings.apiKey;
            this.$stepsContainer = $('#cloudflare-steps');
            this.$infoBox = $( '#cloudflare-info' );
            this.$spinner = $( '.cloudflare-spinner' );
            this.$deactivateButton = $('.cloudflare-deactivate.button');

            this.renderStep( this.currentStep );

            $('body').on( 'click', '.cloudflare-clear-cache.button', function(e ) {
                e.preventDefault();
                this.purgeCache.apply( $(e.target), [this] );
            }.bind(this));

        },

        purgeCache: function( self ) {
            // Show spinner
			const $button = this;
			$button.attr( 'disabled', true );
			self.showSpinner();

            Fetcher.cloudflare.purgeCache()
                .then( () => {
                    // Show notice
					const $notice = $('#wphb-notice-cloudflare-purge-cache');
					window.scrollTo(0, 0);
					$notice.slideDown();
					setTimeout(function() {
						$notice.slideUp();
					}, 5e3);
                    // Remove spinner
					$button.removeAttr( 'disabled' );
					self.hideSpinner();
                });
        },

        renderStep: function( step ) {
            const template = WPHB_Admin.DashboardCloudFlare.template( '#cloudflare-step-' + step );
            const content = template( this.data );
            const self = this;

            if ( content ) {
                this.currentStep = step;
                this.$stepsContainer
                    .hide()
                    .html( template( this.data ) )
                    .fadeIn()
                    .find( 'form' )
                    .on( 'submit', function( e ) {
                        e.preventDefault();
                        self.submitStep.call( self, $(this) );
                    });

                this.$spinner = this.$stepsContainer.find( '.cloudflare-spinner' );
            }

            this.bindEvents();
        },

        bindEvents: function() {
            const $howToInstructions = $('#cloudflare-how-to');

            $howToInstructions.hide();

            $('#cloudflare-how-to-title > a').click( function( e ) {
                e.preventDefault();
                $howToInstructions.toggle();
            });

            this.$stepsContainer.find( 'select' ).each( function() {
				window.WDP.wpmuSelect( this );
            });

            if ( 'final' === this.currentStep ) {
                this.$deactivateButton.removeClass( 'hidden' );
            } else {
                this.$deactivateButton.addClass( 'hidden' );
            }
        },

        emptyInfoBox: function() {
            this.$infoBox.html('');
            this.$infoBox.removeClass();
        },

        showInfoBox: function( message ) {
            this.$infoBox.addClass( 'wphb-notice' );
            this.$infoBox.addClass( 'wphb-notice-error' );
            this.$infoBox.html( message + '' );
        },

        showSpinner: function() {
            this.$spinner.css( 'visibility', 'visible' );
        },

        hideSpinner: function() {
            this.$spinner.css( 'visibility', 'hidden' );
        },

        submitStep: function( $form ) {
			const self = this;

			$form.find( 'input[type=submit]' ).attr( 'disabled', 'true' );
			this.emptyInfoBox();
			this.showSpinner();

			Fetcher.cloudflare.connect( this.currentStep, $form.serialize(), this.data )
                .then( ( response ) => {
					self.data = response.newData;
					self.renderStep( response.nextStep );

					if ( response.nextStep === 'final' ) {
						window.location.href = response.redirect;
					}
                })
				.catch( ( error ) => {
					self.showInfoBox( error );
				});

			$form.find( 'input[type=submit]' ).removeAttr( 'disabled' );
			self.hideSpinner();
        }
    };

    WPHB_Admin.DashboardCloudFlare.template = _.memoize(function ( id ) {
        let compiled,
            options = {
                evaluate:    /<#([\s\S]+?)#>/g,
                interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
                variable:    'data'
            };

        return function ( data ) {
            _.templateSettings = options;
            compiled = compiled || _.template( $( id ).html() );
            return compiled( data );
        };
    });
}(jQuery));
