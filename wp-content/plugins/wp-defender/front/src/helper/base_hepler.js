const wpi18n = wp.i18n;
import {FilterXSS, safeAttrValue} from 'xss';

var options = {
	whiteList: {
		a: ['href', 'title', 'target'],
		span: ['class'],
		strong: ['*']
	},
	safeAttrValue: function (tag, name, value, cssFilter) {
		if (tag === 'a' && name === 'href' && value === '%s') {
			return '%s'
		}

		return safeAttrValue(tag, name, value, cssFilter)
	}

};
let filter = new FilterXSS(options)
var requests = [];
export default {
	methods: {
		/**
		 * Translate function, from wp i18n
		 * @param text
		 * @returns {*}
		 * @private
		 */
		__: function (text) {
			let strings = wpi18n.__(text, 'wpdef');
			return filter.process(strings)
		},
		/**
		 * escape the text for prevent xss
		 * @param text
		 */
		xss: function (text) {
			return filter.process(text);
		},
		/**
		 * A helper for sprintf
		 * @param text
		 * @returns {*}
		 */
		vsprintf: function (text) {
			return wpi18n.sprintf.apply(null, arguments);
		},
		/**
		 * Return site URL
		 * @returns {default.methods.siteUrl|(function())|default.methods.siteUrl|siteUrl}
		 */
		siteUrl: function (path) {
			if (path !== undefined) {
				return defender.site_url + path;
			}
			return defender.site_url;
		},
		/**
		 * Return admin or network admin URL
		 * @param path
		 * @returns {default.methods.adminUrl|adminUrl|__webpack_exports__.a.methods.adminUrl|*}
		 */
		adminUrl: function (path) {
			if (path !== undefined) {
				return defender.admin_url + path;
			}
			return defender.admin_url;
		},
		/**
		 * We will return
		 * @param path
		 * @returns {*}
		 */
		assetUrl: function (path) {
			return defender.defender_url + path;
		},
		/**
		 * return high contrast class
		 */
		maybeHighContrast: function () {
			return {'sui-color-accessible': defender.misc.high_contrast === true};
		},
		/**
		 *
		 * @returns {boolean}
		 */
		maybeHideBranding: function () {
			return defender.whitelabel.hide_branding;
		},
		/**
		 * show campain URL from WPMUDEV
		 * @param slug
		 * @returns {string}
		 */
		campaign_url: function (slug) {
			return 'https://premium.wpmudev.org/project/wp-defender/?utm_source=defender&utm_medium=plugin&utm_campaign=' + slug;
		},
		/**
		 *
		 * @param method
		 * @param endpoint
		 * @param data
		 * @param callback
		 * @param preventLoadingState
		 */
		httpRequest: function (method, endpoint, data, callback, preventLoadingState) {
			let that = this;
			if (preventLoadingState === undefined) {
				this.state.on_saving = true;
			}
			let url = ajaxurl + '?action=' + this.endpoints[endpoint] + '&_wpnonce=' + this.nonces[endpoint];
			var request = jQuery.ajax({
				url: url,
				method: method,
				data: data,
				success: function (response) {
					let data = response.data;
					that.state.on_saving = false;
					if (data !== undefined && data.message !== undefined) {
						if (response.success) {
							Defender.showNotification('success', data.message);
						} else {
							Defender.showNotification('error', data.message);
						}
					}
					if (callback !== undefined) {
						callback(response);
					}
				}
			})
			requests.push(request);
		},
		/**
		 * A shorthand for httpRequest, make a get request
		 * @param endpoint
		 * @param data
		 * @param callback
		 * @param preventLoadingState
		 */
		httpGetRequest: function (endpoint, data, callback, preventLoadingState) {
			this.httpRequest('get', endpoint, data, callback, preventLoadingState)
		},
		/**
		 * A shorthand for httpRequest, make a post request
		 * @param endpoint
		 * @param data
		 * @param callback
		 * @param preventLoadingState
		 */
		httpPostRequest: function (endpoint, data, callback, preventLoadingState) {
			this.httpRequest('post', endpoint, data, callback, preventLoadingState);
		},
		/**
		 * Abort all ajax requests
		 */
		abortAllRequests: function () {
			for (var i = 0; i < requests.length; i++) {
				requests[i].abort();
			}
		},
		/**
		 * https://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript#answer-3855394
		 *
		 * @param query
		 * @returns {{}}
		 */
		getQueryStringParams: (query) => {
			return query
				? (/^[?#]/.test(query) ? query.slice(1) : query)
					.split('&')
					.reduce((params, param) => {
							let [key, value] = param.split('=');
							params[key] = value ? decodeURIComponent(value.replace(/\+/g, ' ')) : '';
							return params;
						}, {}
					)
				: {}
		},
		/**
		 * Rebind SUI components
		 */
		rebindSUI: function () {
			jQuery('select:not([multiple])').each(function () {
				SUI.suiSelect(this);
			});
			jQuery('.sui-accordion').each(function () {
				SUI.suiAccordion(this);
			});
			var mainEl = jQuery('.sui-wrap');
			SUI.dialogs = {};

			// Init the dialog elements.
			jQuery('.sui-dialog').each(function () {
				SUI.dialogs[this.id] = new A11yDialog(this, mainEl);
			});
		}
	},
};