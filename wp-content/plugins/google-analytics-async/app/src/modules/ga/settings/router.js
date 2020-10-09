import Vue from 'vue'
import Router from 'vue-router'
import Account from './tabs/account'
import Settings from './tabs/settings'

Vue.use(Router)

let routes = [
	{
		path: '/',
		redirect: '/account',
	},
	{
		path: '/account',
		name: 'Account',
		component: Account,
	},
	{
		path: '/settings',
		name: 'Settings',
		component: Settings,
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
