import assign from 'lodash/assign';

function Fetcher() {
    let   fetchUrl        = ajaxurl;
    let   fetchNonce      = wphb.nonces.HBFetchNonce;
    const actionPrefix    = 'wphb_';
    const actionPrefixPro = 'wphb_pro_';

    function request( action, data = {}, method = 'GET' ) {
        data.nonce  = fetchNonce;
        data.action = action;
        let args = { data, method };
        args.url = fetchUrl;
        let Promise = require('es6-promise').Promise;
        return new Promise( ( resolve, reject ) => {
            jQuery.ajax( args ).done( resolve ).fail( reject );
        })
            .then( ( response ) => checkStatus( response ) );

    }

    const methods = {
		/**
         * Notices actions.
		 */
		notice: {
			/**
             * Dismiss notice
			 * @param id Notice id.
			 */
			dismiss: ( id ) => {
		        const action = actionPrefix + 'notice_dismiss';
		        return request( action, { id }, 'POST' );
            },
            /**
             * Dismiss CloudFlare dash notice
             */
            dismissCloudflareDash: () => {
                const action = actionPrefix + 'cf_notice_dismiss';
                return request( action, {}, 'POST' );
            }
        },
		/**
		 * Caching module actions.
         */
        caching: {
            /**
             * Set server type.
             *
             * @param value Server type.
             */
            setServer: ( value ) => {
                const action = actionPrefix + 'caching_set_server_type';
                return request( action, { value }, 'POST' );
            }
        },

        /**
         * CLoudflare module actions.
         */
        cloudflare: {
			/**
			 * Connect to Cloudflare.
			 *
			 * @param step
			 * @param formData
			 * @param cfData
			 */
			connect: ( step, formData, cfData ) => {
				const action = actionPrefix + 'cloudflare_connect';
                return request( action, { step, formData, cfData }, 'POST' )
                    .then( ( response ) => {
                        return response;
                    });
            },

            /**
             * Set expiry for Cloudflare cache.
             *
             * @param value Expiry value.
             */
            setExpiration: ( value ) => {
                const action = actionPrefix + 'cloudflare_set_expiry';
                return request( action, { value }, 'POST' );
            },

			/**
             * Purge Cloudflare cache.
			 */
			purgeCache: () => {
                const action = actionPrefix + 'cloudflare_purge_cache';
                return request( action, {}, 'POST' );
            }
        },

        /**
         * Dashboard module actions.
         */
        dashboard: {
            /**
             * Toggle global minification settings for network installs.
             *
             * @param value Accepts: 'super-admins', 'false' and 'true'. Default: 'true'.
             */
            toggleMinification: ( value ) => {
                const action = actionPrefix + 'dash_toggle_network_minification';
                return request( action, { value }, 'POST' );
            },

			/**
             * Skip quick setup.
			 */
			skipSetup: () => {
                const action = actionPrefix + 'dash_skip_setup';
                return request( action, {}, 'POST' );
            }
        },

        /**
         * Minification module actions.
         */
        minification: {
            /**
             * Toggle CDN settings.
             *
             * @param value CDN checkbox value.
             */
            toggleCDN: ( value ) => {
                const action = actionPrefix + 'minification_toggle_cdn';
                return request( action, { value }, 'POST' );
            },

            /**
             * Toggle minificatiojn settings on per site basis.
             *
             * @param value
             */
            toggleMinification: ( value ) => {
                const action = actionPrefix + 'minification_toggle_minification';
                return request( action, { value }, 'POST' );
            },

            /**
             * Start minification check.
             *
             * @param progress
             */
            startCheck: () => {
                const action = actionPrefix + 'minification_start_check';
                return request( action, {}, 'POST' );
            },

            /**
             * Do a step in minification process.
             *
             * @param progress
             * @param step
             */
            checkStep: ( step ) => {
                const action = actionPrefix + 'minification_check_step';
                return request( action, { step }, 'POST' )
                    .then( ( response ) => {
                        return response;
                    });
            },

            /**
             * Finish minification process.
             */
            finishCheck: () => {
                const action = actionPrefix + 'minification_finish_scan';
                return request( action, {}, 'POST' );
            },

			/**
             * Cancel minification scan.
			 */
			cancelScan: function cancelScan() {
				const action = actionPrefix + 'minification_cancel_scan';
				return request( action, {}, 'POST' );
			}
        },

        /**
         * Performance module actions.
         */
        performance: {
			/**
             * Run performance test.
			 */
			runTest: () => {
                const action = actionPrefix + 'performance_run_test';
                return request( action, {}, 'POST' )
                    .then( ( response ) => {
                       return response;
                    });
            },

            /**
             * Add a single email/name recipient to the reports list.
             *
             * @param email
             * @param name
             */
            addRecipient: ( email, name ) => {
                const action = actionPrefixPro + 'performance_add_recipient';
                return request( action, { email, name }, 'POST' )
                    .then( ( response ) => {
                        return response;
                    });
            },

            /**
             * Save reporting settings on minification page.
             *
             * @param data From data.
             */
            saveReportsSettings: ( data ) => {
                const action = actionPrefixPro + 'performance_save_reports_settings';
                return request( action, { data }, 'POST' );
            }
        }
    };

    assign( this, methods );
}

const HBFetcher = new Fetcher();
export default HBFetcher;

function checkStatus( response ) {
    if ( typeof response !== 'object' ) {
        response = JSON.parse( response );
    }
    if ( response.success ) {
        return response.data;
    }

    let data = response.data || {};
    const error = new Error( data.message || 'Error trying to fetch response from server' );
    error.response = response;
    throw error;
}