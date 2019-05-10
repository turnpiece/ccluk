import Fetcher from '../utils/fetcher';

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
            this.$body = $('body');

            this.renderStep( this.currentStep );

            this.$body.on( 'click', 'input[type="submit"].cloudflare-clear-cache', function(e ) {
                e.preventDefault();
                this.purgeCache.apply( $(e.target), [this] );
            }.bind(this));

            this.$body.on( 'click', '#cf-recheck-zones', function(e ) {
                e.preventDefault();
                $('#cf-recheck-zones').addClass('sui-button-onload');
                this.updateZones.apply( $(e.target), [this] );
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
					WPHB_Admin.notices.show( 'wphb-ajax-update-notice', true, 'success', wphbCachingStrings.successCloudflarePurge );
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

            $('a.cloudflare-how-to-title').click( function( e ) {
                e.preventDefault();
                $howToInstructions.toggle();
            });

            this.$stepsContainer.find( 'select' ).each( function() {
				SUI.suiSelect( this );
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

        updateZones: function( self ) {
            self.hideInfoBox();
            Fetcher.cloudflare.recheckZones()
                .then( ( response ) => {
                    self.data.zones = response.zones;
                    self.renderStep(self.currentStep);
                    $('#cf-recheck-zones').removeClass('sui-button-onload');

                }).catch( ( error ) => {
                    self.showInfoBox( error, 'warning' );
                    $('#cf-recheck-zones').removeClass('sui-button-onload');
            });
        },

        showInfoBox: function( message, notice_class = 'error' ) {
            this.$infoBox.addClass( 'sui-notice' );
            this.$infoBox.addClass( 'sui-notice-' + notice_class );
            this.$infoBox.addClass( 'sui-notice-sm' );
            this.$infoBox.html( '<p>' + message + ' </p>' );
        },

        hideInfoBox: function() {
            this.$infoBox.removeClass();
            this.$infoBox.html( '' );
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
