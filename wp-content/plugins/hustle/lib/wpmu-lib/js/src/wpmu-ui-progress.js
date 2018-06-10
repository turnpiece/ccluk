/*!
 * WPMU Dev UI library
 * (Philipp Stracker for WPMU Dev)
 *
 * This module provides the WpmUiProgress object which is a smart and easy to use
 * Pop-up.
 *
 * @version  2.0.2
 * @author   Philipp Stracker for WPMU Dev
 * @requires jQuery
 */
/*global jQuery:false */
/*global window:false */
/*global document:false */
/*global XMLHttpRequest:false */

(function( wpmUi ) {

	/*==============================*\
	==================================
	==                              ==
	==           PROGRESS           ==
	==                              ==
	==================================
	\*==============================*/

	/**
	 * The progress bar element.
	 *
	 * @type   WpmUiProgress
	 * @since  2.0.2
	 */
	wpmUi.WpmUiProgress = function() {

		/**
		 * Backreference to the WpmUiWindow object.
		 *
		 * @since  2.0.2
		 * @internal
		 */
		var _me = this;

		/**
		 * Current value of the progress bar.
		 *
		 * @since  2.0.2
		 * @internal
		 */
		var _current = 0;

		/**
		 * Max value of the progress bar.
		 *
		 * @since  2.0.2
		 * @internal
		 */
		var _max = 100;

		/**
		 * The label text
		 *
		 * @since  2.0.2
		 * @internal
		 */
		var _label = '';

		/**
		 * The wrapper around the progress bar elements.
		 *
		 * @since  2.0.2
		 * @internal
		 */
		var _el = null;

		/**
		 * The progress bar.
		 *
		 * @since  2.0.2
		 * @internal
		 */
		var _el_bar = null;

		/**
		 * The progress bar full width indicator.
		 *
		 * @since  2.0.2
		 * @internal
		 */
		var _el_full = null;

		/**
		 * The progress bar title.
		 *
		 * @since  2.0.2
		 * @internal
		 */
		var _el_label = null;

		/**
		 * Label that displays the current progress percent value.
		 *
		 * @since  2.0.2
		 * @internal
		 */
		var _el_percent = null;

		/**
		 * Change the value of the progress bar.
		 *
		 * @since  2.0.2
		 * @api
		 */
		this.value = function value( val ) {
			if ( ! isNaN( val ) ) {
				_current = parseInt( val );
				_update();
			}
			return _me;
		};

		/**
		 * Set the max value of the progess bar.
		 *
		 * @since  2.0.2
		 * @api
		 */
		this.max = function max( val ) {
			if ( ! isNaN( val ) ) {
				_max = parseInt( val );
				_update();
			}
			return _me;
		};

		/**
		 * Set the contents of the label.
		 *
		 * @since  2.0.2
		 * @api
		 */
		this.label = function label( val ) {
			_label = val;
			_update();
			return _me;
		};

		/**
		 * Adds an event handler to the element.
		 *
		 * @since  2.0.1
		 */
		this.on = function on( event, selector, callback ) {
			_el.on( event, selector, callback );
			return _me;
		};

		/**
		 * Removes an event handler from the element.
		 *
		 * @since  2.0.1
		 */
		this.off = function off( event, selector, callback ) {
			_el.off( event, selector, callback );
			return _me;
		};

		/**
		 * Returns the jQuery object of the main element
		 *
		 * @since  1.0.0
		 */
		this.$ = function $() {
			return _el;
		};

		// ==============================
		// == Private functions =========


		/**
		 * Create the DOM elements for the window.
		 *
		 * @since  2.0.2
		 * @internal
		 */
		function _init() {
			_max = 100;
			_current = 0;

			_el = jQuery( '<div class="wpmui-progress-wrap"></div>' );
			_el_full = jQuery( '<div class="wpmui-progress-full"></div>' );
			_el_bar = jQuery( '<div class="wpmui-progress"></div>' );
			_el_label = jQuery( '<div class="wpmui-progress-label"></div>' );
			_el_percent = jQuery( '<div class="wpmui-progress-percent"></div>' );

			// Attach the window to the current page.
			_el_bar.appendTo( _el_full );
			_el_percent.appendTo( _el_full );
			_el_full.appendTo( _el );
			_el_label.appendTo( _el );

			_update();
		}

		/**
		 * Updates the progress bar
		 *
		 * @since  2.0.2
		 */
		function _update() {
			var percent = _current / _max * 100;
			if ( percent < 0 ) { percent = 0; }
			if ( percent > 100 ) { percent = 100; }

			_el_bar.width( percent + '%' );
			_el_percent.text( parseInt( percent ) + ' %' );

			if ( _label && _label.length ) {
				_el_label.html( _label );
				_el_label.show();
			} else {
				_el_label.hide();
			}
		}

		// Initialize the progress bar.
		_me = this;
		_init();

	}; /* ** End: WpmUiProgress ** */

}( window.wpmUi = window.wpmUi || {} ));