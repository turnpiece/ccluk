/**
 * Assets helper functions for admin.
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

import store from '@/store/store'

/**
 * Get a single option value.
 *
 * @param {string} key Option key.
 * @param {string} group Option group.
 * @param {string|boolean|array|integer|object} value Default value.
 * @param {boolean|null} network Network flag.
 *
 * @since 3.2.4
 *
 * @return {string|boolean}
 */
export function getOption(key, group, value = false, network = null) {
	let settings

	network = network == null ? isNetwork() : network

	if (network) {
		settings = store.state.settings.network
	} else {
		settings = store.state.settings.site
	}

	// Only if set.
	if (settings[group] && settings[group][key]) {
		value = settings[group][key]
	}

	return value
}

/**
 * Set a single option value.
 *
 * Note: This will not update the value in db.
 * You need to call saveOptions() for that.
 *
 * @param {string} key Option key.
 * @param {string} group Option group.
 * @param {string|boolean|array} value Default value.
 * @param {boolean|null} network Network flag.
 *
 * @since 3.2.4
 *
 * @return {string|boolean}
 */
export function setOption(key, group, value, network = null) {
	network = network == null ? isNetwork() : network

	store.dispatch('settings/setOption', {
		key: key,
		group: group,
		value: value,
		network: network,
	})
}

/**
 * Update the latest values in db.
 *
 * We will take the latest value from settings
 * vuex store and update it.
 *
 * @param {boolean|null} network Network flag.
 *
 * @since 3.2.4
 *
 * @return {string | string}
 */
export async function saveOptions(network = null) {
	network = network == null ? isNetwork() : network

	return await store.dispatch('settings/saveOptions', {
		network: network,
	})
}

/**
 * Check if current environment is network admin.
 *
 * We will use the localized var from PHP.
 *
 * @since 3.2.4
 *
 * @return {boolean}
 */
export function isNetwork() {
	return window.beehiveVars.flags.network > 0
}

/**
 * Check if current site is multisite.
 *
 * We will use the localized var from PHP.
 *
 * @since 3.2.4
 *
 * @return {boolean}
 */
export function isMultisite() {
	return window.beehiveVars.flags.multisite > 0
}

/**
 * Check if the plugin is in subsite of a network.
 *
 * We will use the localized var from PHP.
 *
 * @since 3.2.4
 *
 * @return {boolean}
 */
export function isSubsite() {
	return isMultisite() && !isNetwork()
}

/**
 * Check if the plugin is networkwide active.
 *
 * We will use the localized var from PHP.
 *
 * @since 3.2.4
 *
 * @return {boolean}
 */
export function isNetworkWide() {
	return window.beehiveVars.flags.networkwide > 0
}

/**
 * Check if the current user is super admin.
 *
 * We will use the localized var from PHP.
 *
 * @since 3.2.4
 *
 * @return {boolean}
 */
export function isSuperAdmin() {
	return window.beehiveVars.flags.super_admin > 0
}

/**
 * Check if the current user is the admin.
 *
 * We will use the localized var from PHP.
 *
 * @since 3.2.4
 *
 * @return {boolean}
 */
export function isAdmin() {
	return window.beehiveVars.flags.admin > 0
}

/**
 * Get the full image url for admin.
 *
 * @param path Image name.
 *
 * @since 3.2.4
 *
 * @return {string | string}
 */
export function imageUrl(path) {
	const beehiveUrl = window.beehiveVars.urls.base

	return beehiveUrl + 'app/assets/img/' + path
}

/**
 * Check if the tracking ID is in valid format.
 *
 * @param {string} id Tracking ID.
 *
 * @since 3.2.4
 *
 * @return {boolean}
 */
export function isValidGAID(id) {
	return /^ua-\d{4,9}-\d{1,4}$/i.test(id)
}

/**
 * Check if the tracking ID is in valid GA4 format.
 *
 * @param {string} id Tracking ID.
 *
 * @since 3.3.3
 *
 * @return {boolean}
 */
export function isValidGA4ID(id) {
	return /^g-[a-zA-Z0-9]{8,10}$/i.test(id)
}

/**
 * Check if a stats item can be viewed by current user.
 *
 * This can be checked only in Statistics and Dashboard
 * widget pages unless you pass permitted items in beehiveModuleVars.
 *
 * @param {string} section Stats section.
 * @param {string} type Stats item.
 *
 * @since 3.2.4
 *
 * @return {string|boolean}
 */
export function canViewStats(section, type) {
	// Super admin can view all stats in network admin.
	if (isNetwork() || isSuperAdmin()) {
		return true
	}

	// Admins can view everything on their site.
	if (!isNetwork() && isAdmin()) {
		return true
	}

	// Get available permissions.
	let permissions = store.state.helpers.permissions

	// User has the custom capability.
	if (permissions.has_custom_cap && permissions.has_custom_cap > 0) {
		return true
	}

	// Type not found.
	if (!permissions[type] || permissions[type].length <= 0) {
		return false
	}

	// Section found.
	return permissions[type].includes(section)
}

/**
 * Check if a the current user has access to statistics menu.
 *
 * @since 3.3.5
 *
 * @return {boolean}
 */
export function hasStatisticsAccess() {
	return window.beehiveModuleVars.show_statistics > 0
}

/**
 * Check if a the current user has access to Beehive menu.
 *
 * @since 3.3.5
 *
 * @return {boolean}
 */
export function hasSettingsAccess() {
	return window.beehiveModuleVars.show_settings > 0
}

/**
 * Check if a the current user has access to permissions settings.
 *
 * @since 3.3.5
 *
 * @return {boolean}
 */
export function hasPermissionsAccess() {
	return window.beehiveModuleVars.show_permissions > 0
}

/**
 * Check if the whitelabel hide docs option is active.
 *
 * We will use the localized var from PHP.
 *
 * @since 3.3.8
 *
 * @return {boolean}
 */
export function hideDocLinks() {
	return window.beehiveVars.whitelabel.hide_doc_link > 0
}
