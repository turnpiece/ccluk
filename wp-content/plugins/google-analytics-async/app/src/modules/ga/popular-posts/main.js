/**
 * Frontend popular posts widget.
 *
 * Simple script which handles popular posts widget in front end
 * of the site. If loader div is found, we will load the content
 * using API.
 *
 * @since 3.2.0
 * @author Joel James <joel@incsub.com>
 */

/* global window, beehiveI18n, beehiveVars, beehiveModuleVars */

;(function ($) {
	'use strict'

	/**
	 * Beehive popular posts widget.
	 *
	 * @since 3.2.4
	 */
	let beehivePopularWidget = {
		retry: 0, // No. of retries.
		restUrl: beehiveVars.rest.base, // Rest base URL.
		restNonce: beehiveVars.rest.nonce, // Rest nonce.
		statsType: beehiveModuleVars.stats_type, // Stats type (ua ga4).
		loaderContainer: $('#beehive-popular-widget-loading'), // Content container.

		/**
		 * Check if we can get the stats.
		 *
		 * @since 3.2.4
		 *
		 * @return {*|boolean}
		 */
		canGetStats() {
			return (
				beehiveModuleVars.can_get_stats &&
				beehiveModuleVars.can_get_stats > 0
			)
		},

		/**
		 * Get the no. of retries allowed.
		 *
		 * @since 3.2.4
		 *
		 * @return {*|boolean}
		 */
		allowedRetries() {
			if (beehiveModuleVars.retries) {
				return beehiveModuleVars.retries
			} else {
				return 1
			}
		},

		/**
		 * Load the widget content using Ajax.
		 *
		 * Loading via ajax makes sure it won't affect the page
		 * load time.
		 *
		 * @since 3.2.0
		 */
		load() {
			let self = this

			// Only if loader found.
			if (this.loaderContainer.length > 0 && this.canGetStats) {
				let restBase = this.restUrl + (this.statsType === 'ua' ? 'v1/' : 'v2/')
				// Send ajax request.
				$.get(restBase + 'stats/popular').done(function (response) {
					// If response data is found.
					if (
						true === response.success &&
						response.data &&
						response.data.length > 0
					) {
						// Set to content.
						self.setupList(response.data)
					} else if (self.retry < self.allowedRetries) {
						// Retry.
						self.load()
						self.retry++
					} else {
						self.setupEmpty()
					}
				})
			}
		},

		/**
		 * Set up the posts list from API response.
		 *
		 * @since 3.2.0
		 */
		setupList(pages) {
			let count = 0
			let list = '<ul>'

			// Append each item to the list.
			pages.forEach(function (page) {
				// Only if required details are found.
				if (page.link && page.title) {
					list +=
						'<li><a href="' +
						page.link +
						'" title="' +
						page.title +
						'">' +
						page.title +
						'</a></li>'
					count++
				}
			})

			list += '</ul>'

			// Show content only if count is not zero.
			if (count > 0) {
				this.loaderContainer.html(list)
			} else {
				// In case if the count is 0, show empty notice.
				this.setupEmpty()
			}
		},

		/**
		 * Show the empty message.
		 *
		 * @since 3.2.0
		 */
		setupEmpty() {
			this.loaderContainer.html(
				'<p>' + beehiveI18n.widget.no_data + '</p>'
			)
		},
	}

	// Initialize the popular posts widget.
	$(window).on('load', () => beehivePopularWidget.load())
})(jQuery)
