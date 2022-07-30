import { isNetwork } from '@/helpers/utils'
import { restGet, restPost } from '@/helpers/api'

/**
 * Centralized management of plugin settings data.
 *
 * We use Vuex to get the settings using API and then
 * store it in a common store. We can access/update the
 * settings from anywhere in the app using available actions.
 * For easier usage, we have some helper functions available
 * in helpers/utils.js
 *
 * @since 3.2.4
 */
const settings = {
	namespaced: true,
	state: {
		site: window.beehiveVars.settings.site,
		network: window.beehiveVars.settings.network,
	},

	getters: {
		/**
		 * Get the all settings object.
		 *
		 * This getter will automatically return the settings
		 * based on the current environment.
		 *
		 * @param {object} state Current state.
		 *
		 * @return {*}
		 */
		get: (state) => {
			if (isNetwork()) {
				return state.network
			} else {
				return state.site
			}
		},
	},

	mutations: {
		/**
		 * Update a single value in store.
		 *
		 * This will only update the value in store.
		 * To update in db, you need to call updateValues
		 * mutation.
		 *
		 * @param {object} state Current state.
		 * @param {object} data Data to update.
		 */
		setValue: (state, data) => {
			const network = data.hasOwnProperty('network')
				? data.network
				: isNetwork()

			if (network) {
				if (!state.network.hasOwnProperty(data.group)) {
					state.network[data.group] = {}
				}
				state.network[data.group][data.key] = data.value
			} else {
				if (!state.site.hasOwnProperty(data.group)) {
					state.site[data.group] = {}
				}
				state.site[data.group][data.key] = data.value
			}
		},

		/**
		 * Update the whole settings data in state.
		 *
		 * This will replace the current state with the
		 * data provided.
		 *
		 * @param {object} state Current state.
		 * @param {object} data Custom params.
		 */
		updateValues: (state, data) => {
			const network = data.hasOwnProperty('network')
				? data.network
				: isNetwork()

			if (network) {
				window.beehiveVars.settings.network = data.settings
				state.network = data.settings
			} else {
				window.beehiveVars.settings.site = data.settings
				state.site = data.settings
			}
		},
	},

	actions: {
		/**
		 * Re-initialize the settings store forcefully.
		 *
		 * We need to call the API and get the settings,
		 * then update the store state with the new values.
		 *
		 * @param commit Commit
		 * @param {object} data Custom params.
		 *
		 * @return {Promise<void>}
		 */
		reInit: async ({ commit }, data = {}) => {
			const network = data.hasOwnProperty('network')
				? data.network
				: isNetwork()

			restGet({
				path: 'v1/settings',
				params: {
					network: network ? 1 : 0,
				},
			}).then((response) => {
				if (response.success && response.data) {
					commit('updateValues', {
						network: network,
						settings: response.data,
					})
					if (data.callback) {
						data.callback()
					}
				}
			})
		},

		/**
		 * Set a single option value after validation.
		 *
		 * Make sure all required items are provided, then
		 * call the mutation.
		 *
		 * @param commit Commit.
		 * @param {object} data Custom params.
		 */
		setOption: ({ commit }, data = {}) => {
			data.network = data.hasOwnProperty('network')
				? data.network
				: isNetwork()

			// Only if all required items are found.
			if (
				data.hasOwnProperty('key') &&
				data.hasOwnProperty('group') &&
				data.hasOwnProperty('value')
			) {
				commit('setValue', data)
			}
		},

		/**
		 * Update the settings values in db.
		 *
		 * Use the API and update the whole values.
		 * Only do this when required.
		 *
		 * @param {object} Commit and State.
		 * @param {object} data Custom params.
		 *
		 * @return {boolean}
		 */
		saveOptions: async ({ commit, state }, data = {}) => {
			const network = data.hasOwnProperty('network')
				? data.network
				: isNetwork()
			const value = network ? state.network : state.site

			let success = false

			await restPost({
				path: 'v1/settings',
				data: {
					value: value,
					network: network ? 1 : 0,
				},
			}).then((response) => {
				if (response.success) {
					// Update the store with the values from response.
					commit('updateValues', {
						network: network,
						settings: response.data,
					})

					success = true
				}
			})

			return success
		},
	},
}

export default settings
