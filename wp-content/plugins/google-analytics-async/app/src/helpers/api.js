/**
 * API related helper functions for admin.
 *
 * @since 3.2.4
 * @author Joel James <joel@incsub.com>
 *
 * Beehive, Copyright 2007-2019 Incsub (http://incsub.com).
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import apiFetch from '@wordpress/api-fetch'
import {getOption} from './utils'

// Setup middlewares.
apiFetch.use(apiFetch.createNonceMiddleware(window.beehiveVars.rest.nonce))
apiFetch.use(apiFetch.createRootURLMiddleware(window.beehiveVars.rest.base))

/**
 * Send API rest GET request using apiFetch.
 *
 * This is a wrapper function to include nonce and
 * our custom route base url.
 *
 * @param {object} options apiFetch options.
 *
 * @since 3.2.4
 *
 * @return {string}
 **/
export function restGet(options) {
	options = options || {}

	options.method = 'GET'

	// Add param support.
	if (options.params) {
		const urlParams = new URLSearchParams(Object.entries(options.params))

		options.path = options.path + '?' + urlParams
	}

	return apiFetch(options).catch((error) => {
		return error
	})
}

/**
 * Send API rest POST request using apiFetch.
 *
 * @param {object} options apiFetch options.
 *
 * @since 3.2.4
 *
 * @return {string}
 **/
export function restPost(options) {
	options = options || {}

	options.method = 'POST'

	return apiFetch(options).catch((error) => {
		return error
	})
}

/**
 * Send API rest GET request for stats using apiFetch.
 *
 * This is a wrapper function to include nonce and
 * our custom route base url.
 * Also this function will switch between GA4 and UA based
 * on the type selected.
 *
 * @param {object} options apiFetch options.
 *
 * @since 3.2.4
 *
 * @return {string}
 **/
export function restGetStats(options) {
	// v1 for UA and v2 for GA4 stats.
	let version = getOption( 'statistics_type', 'google', 'ua' ) === 'ua' ? 'v1/' : 'v2/'
	// Append version prefix.
	options.path = version + options.path

	return restGet(options)
}
