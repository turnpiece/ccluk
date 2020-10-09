import Vue from 'vue'
import Router from 'vue-router'
import AccountBox from './tabs/account-box'
import SettingsBox from './tabs/settings-box'

Vue.use(Router)

let routes = [
	{
		path: '/',
		redirect: '/account',
	},
	{
		path: '/account',
		name: 'AccountBox',
		component: AccountBox,
	},
	{
		path: '/settings',
		name: 'SettingsBox',
		component: SettingsBox,
	},
	{
		path: '*',
		redirect: '/account',
	},
]

export default new Router({
	linkActiveClass: 'current',
	routes: routes,
})
