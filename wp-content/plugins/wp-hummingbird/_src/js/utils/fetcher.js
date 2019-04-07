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
			 * Activate browser caching.
			 *
			 * @since 1.9.0
			 */
			activate: () => {
        		const action = actionPrefix + 'caching_activate';
        		return request( action, {}, 'POST' )
					.then( ( response ) => {
						return response;
					});
			},

			/**
			 * Unified save settings method.
			 *
			 * @since 1.9.0
			 */
			saveSettings: ( module, data ) => {
				const action = actionPrefix + module + '_save_settings';
				return request( action, { data }, 'POST' )
					.then( ( response ) => {
						return response;
					});
			},

			/**
			 * Clear cache for selected module.
			 *
			 * @since 1.9.0
			 */
			clearCache: ( module ) => {
				const action = actionPrefix + 'clear_module_cache';
				return request( action, { module }, 'POST' )
					.then( ( response ) => {
						return response;
					});
			},

			/**
             * Set expiration for browser caching.
             *
             * @param expiry_times Type expiry times.
             */
            setExpiration: ( expiry_times ) => {
                const action = actionPrefix + 'caching_set_expiration';
                return request( action, { expiry_times }, 'POST' )
					.then( ( response ) => {
						return response;
					});
            },
            /**
             * Set server type.
             *
             * @param value Server type.
             */
            setServer: ( value ) => {
                const action = actionPrefix + 'caching_set_server_type';
                return request( action, { value }, 'POST' );
            },

            /**
             * Reload snippet.
             *
             * @param type Server type.
             * @param expiry_times Type expiry times.
             */
            reloadSnippets: ( type, expiry_times ) => {
                const action = actionPrefix + 'caching_reload_snippet';
                return request( action, { type, expiry_times }, 'POST' )
                    .then( ( response ) => {
                        return response;
                    });
            },

			/**
			 * Update htaccess file.
			 *
			 * @returns {*}
			 */
			updateHtaccess: () => {
            	const action = actionPrefix + 'caching_update_htaccess';
				return request( action, {}, 'POST' )
					.then( ( response ) => {
						return response;
					});
			},

            /**
             * Toggle Ability for subsite admins to turn off page caching.
             *
             * @param value checkbox value.
             */
            toggleSubsitePageCaching: ( value ) => {
                const action = actionPrefix + 'caching_toggle_admin_subsite_page_caching';
                return request( action, { value }, 'POST' );
            },

			/**
			 * Re-check expiry in meta box header button action.
			 */
			recheckExpiry: () => {
				const action = actionPrefix + 'caching_recheck_expiry';
				return request( action, {}, 'POST' )
					.then( ( response ) => {
						return response;
					});
			},

            clearCacheForPost: ( postId ) => {
				const action = actionPrefix + 'gutenberg_clear_post_cache';
                return request( action, { postId }, 'POST' );
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
            },

			/**
             * Recheck Cloudflare zones.
			 */
			recheckZones: () => {
                const action = actionPrefix + 'cloudflare_recheck_zones';
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
         * Asset Optimization module actions.
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
			 * Toggle logs settings.
			 *
			 * @param value
			 * @returns {*}
			 */
			toggleLog: ( value ) => {
            	const action = actionPrefix + 'minification_toggle_log';
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
			 * Toggle minification advanced mode.
			 *
			 * @param value
			 */
			toggleView: ( value ) => {
            	const action = actionPrefix + 'minification_toggle_view';
            	return request( action, { value }, 'POST' );
			},

            /**
             * Start minification check.
             */
            startCheck: () => {
                const action = actionPrefix + 'minification_start_check';
                return request( action, {}, 'POST' );
            },

            /**
             * Do a step in minification process.
             *
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
                return request( action, {}, 'POST' )
					.then( ( response ) => {
						return response;
					});
            },

			/**
             * Cancel minification scan.
			 */
			cancelScan: function cancelScan() {
				const action = actionPrefix + 'minification_cancel_scan';
				return request( action, {}, 'POST' );
			},

			/**
			 * Process critical css form.
			 *
			 * @since 1.8
			 */
			saveCriticalCss: ( form ) => {
				const action = actionPrefix + 'minification_save_critical_css';
				return request( action, { form }, 'POST' )
					.then( ( response ) => {
						return response;
					});
			},

			/**
			 * Update custom asset path
			 *
			 * @since 1.9
			 *
			 * @param value
			 */
			updateAssetPath: ( value ) => {
				const action = actionPrefix + 'minification_update_asset_path';
				return request( action, { value }, 'POST' );
			},

			/**
			 * Reset individual file.
			 *
			 * @since 1.9.2
			 *
			 * @param {string} value
			 *
			 * @returns {*}
			 */
			resetAsset: ( value ) => {
				const action = actionPrefix + 'minification_reset_asset';
				return request( action, { value }, 'POST' );
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
             * Save performance test settings.
             *
             * @param data From data.
             */
            savePerformanceTestSettings: ( data ) => {
                const action = actionPrefix + 'performance_save_settings';
                return request( action, { data }, 'POST' );
            }
        },

		/**
		 * Advanced tools module actions.
		 */
		advanced: {
			/**
			 * Save settings from advanced tools general and db cleanup sections.
			 *
			 * @param data
			 * @param form
			 */
			saveSettings: ( data, form ) => {
				const action = actionPrefix + 'advanced_save_settings';
				return request( action, { data, form }, 'POST' )
					.then( ( response ) => {
						return response;
					});
			},

			/**
			 * Delete selected data from database.
			 *
			 * @param data
			 */
			deleteSelectedData: ( data ) => {
				const action = actionPrefix + 'advanced_db_delete_data';
				return request( action, { data }, 'POST' )
					.then( ( response ) => {
						return response;
					});
			},

			/**
			 * Schedule cleanup cron.
			 */
			scheduleCleanup: () => {
				const action = actionPrefixPro + 'advanced_db_schedule';
				return request( action, {}, 'POST' );
			}
		},

		/**
		 * Logger module actions.
		 *
		 * @since 1.9.2
		 */
		logger: {
			/**
			 * Clear logs.
			 *
			 * @param {string} module  Module slug.
			 */
			clear: ( module ) => {
				const action = actionPrefix + 'logger_clear';
				return request( action, { module }, 'POST' )
					.then( ( response ) => {
						return response;
					});
			}
		},

		/**
		 * Settings actions.
		 */
		settings: {
			/**
			 * Save settings from HB admin settings.
			 *
			 * @param form_data
			 */
			saveSettings: ( form_data ) => {
				const action = actionPrefix + 'admin_settings_save_settings';
				return request( action, { form_data }, 'POST' )
					.then( ( response ) => {
						return response;
					});
			},
		},

		/**
		 * Common actions that are used by several modules.
		 *
		 * @since 1.9.3
		 */
		common: {
			/**
			 * Add recipient for Performance and Uptime reports.
			 *
			 * @param {string} module  Module name.
			 * @param {string} setting Setting name.
			 * @param {string} email   Email.
			 * @param {string} name    User.
			 */
			addRecipient: ( module, setting, email, name ) => {
				const action = actionPrefixPro + 'add_recipient';
				return request( action, { module, setting, email, name }, 'POST' )
					.then( ( response ) => {
					return response;
				});
			},

			/**
			 * Save report settings for Performance and Uptime modules.
			 *
			 * @param {string} module  Module name.
			 * @param {array}  data    From data.
			 */
			saveReportsSettings: ( module, data ) => {
				const action = actionPrefixPro + 'save_report_settings';
				return request( action, { module, data }, 'POST' )
					.then( ( response ) => {
						return response;
					});
			},
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