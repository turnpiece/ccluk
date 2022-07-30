import Vue from 'vue'
import App from './app'
import store from '@/store/store'
import Fragment from 'vue-fragment'
import { sprintf } from 'sprintf-js'
import {
	getOption,
	setOption,
	saveOptions,
	isNetwork,
	isMultisite,
	isSubsite,
	isNetworkWide,
} from '@/helpers/utils'

Vue.config.productionTip = false

// Global functions.
Vue.mixin({
	methods: {
		sprintf,
		getOption,
		setOption,
		saveOptions,
		isNetwork,
		isMultisite,
		isSubsite,
		isNetworkWide,
	},
})

// Global vars.
Vue.prototype.$i18n = window.beehiveI18n
Vue.prototype.$vars = window.beehiveVars
Vue.prototype.$moduleVars = window.beehiveModuleVars

Vue.use(Fragment.Plugin)

new Vue({
	render: (h) => h(App),
	store,
}).$mount('#beehive-dashboard-app')
