/*!
 * WPMU Dev UI library
 * (Rheinard Korf, Philipp Stracker for WPMU Dev)
 *
 * This module adds a WordPress-like hook system in javascript that makes it
 * easier to expose actions/filters to other developers.
 *
 * ----------------------------------------------------------------------------
 * @file A WordPress-like hook system for JavaScript.
 *
 * This file demonstrates a simple hook system for JavaScript based on the hook
 * system in WordPress. The purpose of this is to make your code extensible and
 * allowing other developers to hook into your code with their own callbacks.
 *
 * There are other ways to do this, but this will feel right at home for
 * WordPress developers.
 *
 * @author Rheinard Korf
 * @license GPL2 (https://www.gnu.org/licenses/gpl-2.0.html)
 *
 * @requires underscore.js (http://underscorejs.org/)
 * ----------------------------------------------------------------------------
 *
 * @version  3.0.0
 * @author   Philipp Stracker for WPMU Dev
 * @requires jQuery
 */
/*global jQuery:false */
/*global window:false */
/*global document:false */

(function( wpmUi ) {

	if (wpmUi.add_action) { return; }

	/*===========================*\
	===============================
	==                           ==
	==           HOOKS           ==
	==                           ==
	===============================
	\*===========================*/

	/**
	 * All actions/filters are stored in the filters object.
	 *
	 * In WordPress actions and filters are synonyms - only difference is, that
	 * a filter will return a value, while an action does not return a value.
	 */
	wpmUi.filters = wpmUi.filters || {};

	/**
	 * Add a new Action callback to wpmUi.filters
	 *
	 * This function is an alias to wpmUi.add_filter
	 *
	 * @param tag The tag specified by do_action()
	 * @param callback The callback function to call when do_action() is called
	 * @param priority The order in which to call the callbacks. Default: 10 (like WordPress)
	 */
	wpmUi.add_action = function( tag, callback, priority ) {
		wpmUi.add_filter( tag, callback, priority );
	};

	/**
	 * Add a new Filter callback to wpmUi.filters
	 *
	 * @param tag The tag specified by apply_filters()
	 * @param callback The callback function to call when apply_filters() is called
	 * @param priority Priority of filter to apply. Default: 10 (like WordPress)
	 */
	wpmUi.add_filter = function( tag, callback, priority ) {
		if( undefined === callback ) {
			return;
		}

		if( undefined === priority ) {
			priority = 10;
		}

		// If the tag doesn't exist, create it.
		wpmUi.filters[ tag ] = wpmUi.filters[ tag ] || [];
		wpmUi.filters[ tag ].push( { priority: priority, callback: callback } );
	};

	/**
	 * Remove an Action callback from wpmUi.filters
	 *
	 * This function is an Alias to wpmUi.remove_filter
	 *
	 * Must be the exact same callback signature.
	 * Warning: Anonymous functions can not be removed.
	 * @param tag The tag specified by do_action()
	 * @param callback The callback function to remove
	 */
	wpmUi.remove_action = function( tag, callback ) {
		wpmUi.remove_filter( tag, callback );
	};

	/**
	 * Remove a Filter callback from wpmUi.filters
	 *
	 * Must be the exact same callback signature.
	 * Warning: Anonymous functions can not be removed.
	 * @param tag The tag specified by apply_filters()
	 * @param callback The callback function to remove
	 */
	wpmUi.remove_filter = function( tag, callback ) {
		wpmUi.filters[ tag ] = wpmUi.filters[ tag ] || [];

		wpmUi.filters[ tag ].forEach( function( filter, i ) {
			if( filter.callback === callback ) {
				wpmUi.filters[ tag ].splice(i, 1);
			}
		} );
	};

	/**
	 * Remove all Action callbacks for the specified tag.
	 *
	 * This function is an Alias to wpmUi.remove_all_filters
	 *
	 * @param tag The tag specified by do_action()
	 * @param priority Only remove actions with the specified priority
	 */
	wpmUi.remove_all_actions = function( tag, priority ) {
		wpmUi.remove_all_filters( tag, priority );
	};

	/**
	 * Remove all Filter callbacks for the specified tag
	 *
	 * @param tag The tag specified by do_action()
	 * @param priority Only remove actions with the specified priority
	 */
	wpmUi.remove_all_filters = function( tag, priority ) {
		wpmUi.filters[ tag ] = wpmUi.filters[ tag ] || [];

		if ( undefined === priority ) {
			wpmUi.filters[ tag ] = [];
		} else {
			wpmUi.filters[ tag ].forEach( function( filter, i ) {
				if( filter.priority === priority ) {
					wpmUi.filters[ tag ].splice(i, 1);
				}
			} );
		}
	};

	/**
	 * Calls actions that are stored in wpmUi.actions for a specific tag or nothing
	 * if there are no actions to call.
	 *
	 * @param tag A registered tag in Hook.actions
	 * @options Optional JavaScript object to pass to the callbacks
	 */
	wpmUi.do_action = function( tag, options ) {
		var actions = [];

		if( undefined !== wpmUi.filters[ tag ] && wpmUi.filters[ tag ].length > 0 ) {

			wpmUi.filters[ tag ].forEach( function( hook ) {

				actions[ hook.priority ] = actions[ hook.priority ] || [];
				actions[ hook.priority ].push( hook.callback );

			} );

			actions.forEach( function( hooks ) {

				hooks.forEach( function( callback ) {
					callback( options );
				} );

			} );
		}
	};

	/**
	 * Calls filters that are stored in wpmUi.filters for a specific tag or return
	 * original value if no filters exist.
	 *
	 * @param tag A registered tag in Hook.filters
	 * @options Optional JavaScript object to pass to the callbacks
	 */
	wpmUi.apply_filters = function( tag, value, options ) {
		var filters = [];

		if( undefined !== wpmUi.filters[ tag ] && wpmUi.filters[ tag ].length > 0 ) {

			wpmUi.filters[ tag ].forEach( function( hook ) {

				filters[ hook.priority ] = filters[ hook.priority ] || [];
				filters[ hook.priority ].push( hook.callback );
			} );

			filters.forEach( function( hooks ) {

				hooks.forEach( function( callback ) {
					value = callback( value, options );
				} );

			} );
		}

		return value;
	};

/* ** End: Hooks integration in wpmUi ** */

}( window.wpmUi = window.wpmUi || {} ));