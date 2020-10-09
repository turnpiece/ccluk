import Vue from 'vue'
import Router from 'vue-router'
import Permissions from './tabs/permissions'

Vue.use(Router)

let routes = [
	{
		path: '/',
		redirect: '/permissions',
	},
	{
		path: '/permissions',
		name: 'Permissions',
		component: Permissions,
	},
	{
		path: '*',
		redirect: '/permissions',
	},
]

export default new Router({
	linkActiveClass: 'current',
	routes: routes,
})
