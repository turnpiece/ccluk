import Vue from 'vue'
import Router from 'vue-router'
import Permissions from './tabs/permissions'
import DataSettings from './tabs/data-settings'
import GeneralSettings from './tabs/general-settings'
import { hasPermissionsAccess } from '@/helpers/utils'

Vue.use(Router)

let routes = [
	{
		path: '/',
		redirect: '/general',
	},
	{
		path: '/general',
		name: 'GeneralSettings',
		component: GeneralSettings,
	},
	{
		path: '/data',
		name: 'DataSettings',
		component: DataSettings,
	},
	{
		path: '*',
		redirect: '/general',
	},
]

// Permissions menu is required only when has access.
if (hasPermissionsAccess()) {
	routes.push({
		path: '/permissions',
		name: 'Permissions',
		component: Permissions,
	})
}

export default new Router({
	linkActiveClass: 'current',
	routes: routes,
})
