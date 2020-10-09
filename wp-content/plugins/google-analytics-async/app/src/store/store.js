import Vue from 'vue'
import Vuex from 'vuex'
import helpers from './modules/helpers'
import settings from './modules/settings'

Vue.use(Vuex);

export default new Vuex.Store({
	namespaced: true,
	modules: {
		helpers: helpers,
		settings: settings,
	}
});
