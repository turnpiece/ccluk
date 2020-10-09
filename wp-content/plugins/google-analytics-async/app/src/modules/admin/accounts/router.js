import Vue from 'vue'
import Router from 'vue-router'
import GoogleAccount from './tabs/google-account'

Vue.use(Router)

let routes = [
	{
		path: '/',
		redirect: '/google',
	},
	{
		path: '/google',
		name: 'GoogleAccount',
		component: GoogleAccount,
	},
	{
		path: '*',
		redirect: '/google',
	},
]

export default new Router({
	linkActiveClass: 'current',
	routes: routes,
})
