import Vue from 'vue'
import { isNetwork } from '@/helpers/utils'
import { restGet, restGetStats } from '@/helpers/api'

let vars = window.beehiveVars
let moduleVars = window.beehiveModuleVars

/**
 * Centralized management of plugin helpers.
 *
 * We use Vuex keep the common flags so it will be available
 * all across the application.
 *
 * @since 3.2.4
 */
const helpers = {
	namespaced: true,
	state: {
		google: vars.google,
		googleApi: {
			status: true,
			error: '',
			message: '',
		},
		permissions: moduleVars.stats_permissions || {},
		users: moduleVars.users || {},
	},

	getters: {
		/**
		 * Get a single user object.
		 *
		 * If user is already available in Vuex, it will
		 * return the video object. If not, empty object.
		 *
		 * @param {object} state Current state.
		 *
		 * @return {object}
		 */
		user: (state) => (id) => {
			if (state.users[id]) {
				return state.users[id]
			} else {
				return {}
			}
		},
	},

	mutations: {
		/**
		 * Update the google login status.
		 *
		 * This will only update the value in store.
		 * To update in db, you need to use settings vuex.
		 * Use this after updating settings.
		 *
		 * @param {object} state State of the module.
		 * @param {object} status Status of login.
		 */
		setGoogleLogin: (state, status) => {
			state.google.logged_in = status
		},

		/**
		 * Update the google profiles state.
		 *
		 * This will only update the value in store.
		 *
		 * @param {object} state State of the module.
		 * @param {object} profiles Google profiles.
		 */
		setGoogleProfiles: (state, profiles) => {
			state.google.profiles = profiles
		},

		/**
		 * Update the GA4 streams state.
		 *
		 * This will only update the value in store.
		 *
		 * @param {object} state State of the module.
		 * @param {object} streams GA4 streams.
		 */
		setGoogleStreams: (state, streams) => {
			state.google.streams = streams
		},

		/**
		 * Update the google API status.
		 *
		 * This will only update the value in store.
		 *
		 * @param {object} state State of the module.
		 * @param {object} status Google profiles.
		 */
		setGoogleApiStatus: (state, status) => {
			state.googleApi = status
		},

		/**
		 * Update a single user data in state.
		 *
		 * If user exist, it will replace the user object,
		 * if not, it will add the user object.
		 *
		 * @param {object} state Current state.
		 * @param {object} user User data.
		 */
		storeUser: (state, user) => {
			if (user.ID && user.ID > 0) {
				Vue.set(state.users, user.ID, user)
			}
		},
	},

	actions: {
		/**
		 * Action to change the login status in Vuex.
		 *
		 * Use this from any component.
		 *
		 * @param {object} Commit and State.
		 * @param {object} data Status and other options.
		 *
		 * @return {Promise<void>}
		 */
		updateGoogleLogin: ({ commit, dispatch }, data) => {
			if (data.reInit) {
				dispatch('settings/reInit', {}, { root: true })
			}
			commit('setGoogleLogin', data.status)
			// Update global vars.
			vars.google.logged_in = data.status
			window.beehiveVars.google.logged_in = data.status
		},

		/**
		 * Action to update the Google profiles.
		 *
		 * Use this from any component.
		 *
		 * @param {object} Commit and State.
		 * @param {object} data Status and other options.
		 *
		 * @return {Promise<void>}
		 */
		updateGoogleProfiles: async ({ commit, dispatch }, data) => {
			restGet({
				path: 'v1/data/analytics-profiles',
				params: {
					network: isNetwork() ? 1 : 0,
				},
			}).then((response) => {
				if (response.success && response.data) {
					commit('setGoogleProfiles', response.data)
					if (data.reInit) {
						dispatch('settings/reInit', {}, { root: true })
					}
					if (data.callback) {
						data.callback()
					}
				}
			})
		},

		/**
		 * Action to update the Google Analytics 4 streams.
		 *
		 * Use this from any component.
		 *
		 * @param {object} Commit and State.
		 * @param {object} data Status and other options.
		 *
		 * @return {Promise<void>}
		 */
		updateGoogleStreams: async ({ commit, dispatch }, data) => {
			restGet({
				path: 'v2/data/streams',
				params: {
					network: isNetwork() ? 1 : 0,
				},
			}).then((response) => {
				if (response.success && response.data) {
					commit('setGoogleStreams', response.data)
					if (data.reInit) {
						dispatch('settings/reInit', {}, { root: true })
					}
					if (data.callback) {
						data.callback()
					}
				}
			})
		},

		/**
		 * Action to update the Google API status.
		 *
		 * Use this from any component.
		 *
		 * @param {object} Commit and State.
		 *
		 * @return {Promise<void>}
		 */
		updateGoogleApi: async ({ commit }) => {
			restGetStats({
				path: 'stats/api-status',
				params: {
					network: isNetwork() ? 1 : 0,
				},
			}).then((response) => {
				if (response.data) {
					commit('setGoogleApiStatus', response.data)
				}
			})
		},

		/**
		 * Set a user value in state.
		 *
		 * @param {object} Commit and State.
		 * @param {object} video User data.
		 *
		 * @return {Promise<void>}
		 */
		setUser: ({ commit }, user) => {
			if (user.ID && user.ID > 0) {
				// Update the user data.
				commit('storeUser', user)
			}
		},
	},
}

export default helpers
