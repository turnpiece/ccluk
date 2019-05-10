import assign from 'lodash/assign';

/**
 * Fetcher.
 *
 * @constructor
 */
function Fetcher() {
  const fetchUrl = ajaxurl;
  const fetchNonce = wphb.nonces.HBFetchNonce;
  const actionPrefix = 'wphb_';
  const actionPrefixPro = 'wphb_pro_';

  /**
   * Request ajax with a promise.
   *
   * @param {string} action
   * @param {object} data
   * @param {string} method
   * @return {Promise<any>}
   */
  function request( action, data = {}, method = 'GET' ) {
    data.nonce = fetchNonce;
    data.action = action;
    const args = {data, method};
    args.url = fetchUrl;
    const Promise = require('es6-promise').Promise;
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
       * @param {string} id
       * @return {Promise<any>}
       */
      dismiss: ( id ) => {
        const action = actionPrefix + 'notice_dismiss';
        return request( action, {id}, 'POST' );
      },

      /**
       * Dismiss CloudFlare dash notice
       * @return {Promise<any>}
       */
      dismissCloudflareDash: () => {
        const action = actionPrefix + 'cf_notice_dismiss';
        return request( action, {}, 'POST' );
      },
    },

    /**
     * Caching module actions.
     */
    caching: {
      /**
       * Activate browser caching.
       * @since 1.9.0
       * @return {Promise<any>}
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
       * @since 1.9.0
       * @param {string} module
       * @param {string} data  Serialized form data.
       * @return {Promise<any>}
       */
      saveSettings: ( module, data ) => {
        const action = actionPrefix + module + '_save_settings';
        return request( action, {data}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Clear cache for selected module.
       * @since 1.9.0
       * @param {string} module
       * @return {Promise<any>}
       */
      clearCache: ( module ) => {
        const action = actionPrefix + 'clear_module_cache';
        return request( action, {module}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Set expiration for browser caching.
       * @param {object} expiry_times Type expiry times.
       * @return {Promise<any>}
       */
      setExpiration: ( expiry_times ) => {
        const action = actionPrefix + 'caching_set_expiration';
        return request( action, {expiry_times}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Set server type.
       * @param {string} value Server type.
       * @return {Promise<any>}
       */
      setServer: ( value ) => {
        const action = actionPrefix + 'caching_set_server_type';
        return request( action, {value}, 'POST' );
      },

      /**
       * Reload snippet.
       * @param {string} type Server type.
       * @param {object} expiry_times Type expiry times.
       * @return {Promise<any>}
       */
      reloadSnippets: ( type, expiry_times ) => {
        const action = actionPrefix + 'caching_reload_snippet';
        return request( action, {type, expiry_times}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Update htaccess file.
       * @return {Promise<any>}
       */
      updateHtaccess: () => {
        const action = actionPrefix + 'caching_update_htaccess';
        return request( action, {}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Re-check expiry in meta box header button action.
       * @return {Promise<any>}
       */
      recheckExpiry: () => {
        const action = actionPrefix + 'caching_recheck_expiry';
        return request( action, {}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Clear cache for post.
       * @param {int} postId
       * @return {Promise<any>}
       */
      clearCacheForPost: ( postId ) => {
        const action = actionPrefix + 'gutenberg_clear_post_cache';
        return request( action, {postId}, 'POST' );
      },
    },

    /**
     * Cloudflare module actions.
     */
    cloudflare: {
      /**
       * Connect to Cloudflare.
       * @param {string} step
       * @param {string} formData
       * @param {array} cfData
       * @return {Promise<any>}
       */
      connect: ( step, formData, cfData ) => {
        const action = actionPrefix + 'cloudflare_connect';
        return request( action, {step, formData, cfData}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Set expiry for Cloudflare cache.
       * @param {object} value Expiry value.
       * @return {Promise<any>}
       */
      setExpiration: ( value ) => {
        const action = actionPrefix + 'cloudflare_set_expiry';
        return request( action, {value}, 'POST' );
      },

      /**
       * Purge Cloudflare cache.
       * @return {Promise<any>}
       */
      purgeCache: () => {
        const action = actionPrefix + 'cloudflare_purge_cache';
        return request( action, {}, 'POST' );
      },

      /**
       * Recheck Cloudflare zones.
       * @return {Promise<any>}
       */
      recheckZones: () => {
        const action = actionPrefix + 'cloudflare_recheck_zones';
        return request( action, {}, 'POST' );
      },
    },

    /**
     * Dashboard module actions.
     */
    dashboard: {
      /**
       * Skip quick setup.
       * @return {Promise<any>}
       */
      skipSetup: () => {
        const action = actionPrefix + 'dash_skip_setup';
        return request( action, {}, 'POST' );
      },
    },

    /**
     * Asset Optimization module actions.
     */
    minification: {
      /**
       * Toggle CDN settings.
       * @param {string} value CDN checkbox value.
       * @return {Promise<any>}
       */
      toggleCDN: ( value ) => {
        const action = actionPrefix + 'minification_toggle_cdn';
        return request( action, {value}, 'POST' );
      },

      /**
       * Toggle logs settings.
       * @param {string} value
       * @return {Promise<any>}
       */
      toggleLog: ( value ) => {
        const action = actionPrefix + 'minification_toggle_log';
        return request( action, {value}, 'POST' );
      },

      /**
       * Toggle minification advanced mode.
       * @param {string} value
       * @return {Promise<any>}
       */
      toggleView: ( value ) => {
        const action = actionPrefix + 'minification_toggle_view';
        return request( action, {value}, 'POST' );
      },

      /**
       * Start minification check.
       * @return {Promise<any>}
       */
      startCheck: () => {
        const action = actionPrefix + 'minification_start_check';
        return request( action, {}, 'POST' );
      },

      /**
       * Do a step in minification process.
       * @param {int} step
       * @return {Promise<any>}
       */
      checkStep: ( step ) => {
        const action = actionPrefix + 'minification_check_step';
        return request( action, {step}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Finish minification process.
       * @return {Promise<any>}
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
       * @return {Promise<any>}
       */
      cancelScan: function cancelScan() {
        const action = actionPrefix + 'minification_cancel_scan';
        return request( action, {}, 'POST' );
      },

      /**
       * Process critical css form.
       * @since 1.8
       * @param {string} form
       * @return {Promise<any>}
       */
      saveCriticalCss: ( form ) => {
        const action = actionPrefix + 'minification_save_critical_css';
        return request( action, {form}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Update custom asset path
       * @since 1.9
       * @param {string} value
       * @return {Promise<any>}
       */
      updateAssetPath: ( value ) => {
        const action = actionPrefix + 'minification_update_asset_path';
        return request( action, {value}, 'POST' );
      },

      /**
       * Reset individual file.
       * @since 1.9.2
       * @param {string} value
       * @return {Promise<any>}
       */
      resetAsset: ( value ) => {
        const action = actionPrefix + 'minification_reset_asset';
        return request( action, {value}, 'POST' );
      },

      /**
       * Save settings in network admin.
       * @since 2.0.0
       * @param {string} settings
       * @return {Promise<any>}
       */
      saveNetworkSettings: ( settings ) => {
        const action = actionPrefix + 'minification_update_network_settings';
        return request( action, {settings}, 'POST' );
      },
    },

    /**
     * Performance module actions.
     */
    performance: {
      /**
       * Run performance test.
       * @return {Promise<any>}
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
       * @param {string} data From data.
       * @return {Promise<any>}
       */
      savePerformanceTestSettings: ( data ) => {
        const action = actionPrefix + 'performance_save_settings';
        return request( action, {data}, 'POST' );
      },
    },

    /**
     * Advanced tools module actions.
     */
    advanced: {
      /**
       * Save settings from advanced tools general and db cleanup sections.
       * @param {string} data  Type.
       * @param {string} form  Serialized form.
       * @return {Promise<any>}
       */
      saveSettings: ( data, form ) => {
        const action = actionPrefix + 'advanced_save_settings';
        return request( action, {data, form}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Delete selected data from database.
       * @param {string} data
       * @return {Promise<any>}
       */
      deleteSelectedData: ( data ) => {
        const action = actionPrefix + 'advanced_db_delete_data';
        return request( action, {data}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Schedule cleanup cron.
       * @return {Promise<any>}
       */
      scheduleCleanup: () => {
        const action = actionPrefixPro + 'advanced_db_schedule';
        return request( action, {}, 'POST' );
      },
    },

    /**
     * Logger module actions.
     *
     * @since 1.9.2
     */
    logger: {
      /**
       * Clear logs.
       * @param {string} module  Module slug.
       * @return {Promise<any>}
       */
      clear: ( module ) => {
        const action = actionPrefix + 'logger_clear';
        return request( action, {module}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },
    },

    /**
     * Settings actions.
     */
    settings: {
      /**
       * Save settings from HB admin settings.
       * @param {string} form_data
       * @return {Promise<any>}
       */
      saveSettings: ( form_data ) => {
        const action = actionPrefix + 'admin_settings_save_settings';
        return request( action, {form_data}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Reset plugin settings.
       * @since 2.0.0
       * @return {Promise<any>}
       */
      resetSettings: () => {
        const action = actionPrefix + 'reset_settings';
        return request( action, {}, 'POST' );
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
       * @param {string} module  Module name.
       * @param {string} setting Setting name.
       * @param {string} email   Email.
       * @param {string} name    User.
       * @return {Promise<any>}
       */
      addRecipient: ( module, setting, email, name ) => {
        const action = actionPrefixPro + 'add_recipient';
        return request( action, {module, setting, email, name}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },

      /**
       * Save report settings for Performance and Uptime modules.
       * @param {string} module  Module name.
       * @param {array}  data    From data.
       * @return {Promise<any>}
       */
      saveReportsSettings: ( module, data ) => {
        const action = actionPrefixPro + 'save_report_settings';
        return request( action, {module, data}, 'POST' )
            .then( ( response ) => {
              return response;
            });
      },
    },
  };

  assign( this, methods );
}

const HBFetcher = new Fetcher();
export default HBFetcher;

/**
 * Check status.
 * @param {object|string} response
 * @return {*}
 */
function checkStatus( response ) {
  if ( typeof response !== 'object' ) {
    response = JSON.parse( response );
  }
  if ( response.success ) {
    return response.data;
  }

  const data = response.data || {};
  const error = new Error( data.message || 'Error trying to fetch response from server' );
  error.response = response;
  throw error;
}
