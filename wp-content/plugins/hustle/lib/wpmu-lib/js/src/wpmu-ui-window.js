/*!
 * WPMU Dev UI library
 * (Philipp Stracker for WPMU Dev)
 *
 * This module provides the WpmUiWindow object which is a smart and easy to use
 * Pop-up.
 *
 * @version  3.0.0
 * @author   Philipp Stracker for WPMU Dev
 * @requires jQuery
 */
/*global jQuery:false */
/*global window:false */

(function( wpmUi ) {

	/*============================*\
	================================
	==                            ==
	==           WINDOW           ==
	==                            ==
	================================
	\*============================*/

	/**
	 * The next popup ID to use
	 *
	 * @type int
	 * @since  1.1.0
	 * @internal
	 */
	var _next_id = 1;

	/**
	 * A list of all popups
	 *
	 * @type array
	 * @since  1.1.0
	 * @internal
	 */
	var _all_popups = {};

	/**
	 * Returns a list with all currently open popups.
	 *
	 * When a popup is created it is added to the list.
	 * When it is closed (not hidden!) it is removed.
	 *
	 * @since  1.1.0
	 * @return WpmUiWindow[]
	 */
	wpmUi.popups = function() {
		return _all_popups;
	};

	/**
	 * Popup window.
	 *
	 * @type   WpmUiWindow
	 * @since  1.0.0
	 */
	wpmUi.WpmUiWindow = function( _template, _css ) {

		/**
		 * Backreference to the WpmUiWindow object.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		var _me = this;

		/**
		 * Stores the state of the window.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		var _visible = false;

		/**
		 * Defines if a modal background should be visible.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		var _modal = false;

		/**
		 * Defines if the dialog title should contain a close button.
		 *
		 * @since  1.1.2
		 * @internal
		 */
		var _title_close = true;

		/**
		 * Defines if clicking in the modal background closes the dialog.
		 *
		 * @since  1.1.2
		 * @internal
		 */
		var _background_close = true;

		/**
		 * Size of the window.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		var _width = 740;

		/**
		 * Size of the window.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		var _height = 400;

		/**
		 * Title of the window.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		var _title = 'Window';

		/**
		 * Content of the window. Either a jQuery selector/object or HTML code.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		var _content = '';

		/**
		 * Class names to add to the popup window
		 *
		 * @since  1.0.14
		 * @internal
		 */
		var _classes = '';

		/**
		 * Opening animation - triggered when .show() is called.
		 *
		 * @since  3.0.0
		 * @internal
		 */
		var _animation_in = '';

		/**
		 * Closing animation - triggered when .hide() or .close() is called.
		 *
		 * @since  3.0.0
		 * @internal
		 */
		var _animation_out = '';

		/**
		 * Is set to true when new content is assigned to the window.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		var _content_changed = false;

		/**
		 * Flag is set to true when the window size was changed.
		 * After the window was updated we will additionally check if it is
		 * visible in the current viewport.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		var _need_check_size = false;

		/**
		 * Position of the popup, can contain one or more of these flags:
		 * 'none', 'left', 'right', 'top', 'bottom'
		 *
		 * @since  2.0.0
		 * @internal
		 */
		var _snap = { top: false, left: false, right: false, bottom: false };

		/**
		 * Define closing-behavior of the popup to be a slide-in:
		 * 'none', 'up', 'down'
		 *
		 * @since  2.0.0
		 * @internal
		 */
		var _slidein = 'none';

		/**
		 * Called after the window is made visible.
		 *
		 * @type  Callback function.
		 * @since  1.0.0
		 * @internal
		 */
		var _onshow = null;

		/**
		 * Called after the window was hidden.
		 *
		 * @type  Callback function.
		 * @since  1.0.0
		 * @internal
		 */
		var _onhide = null;

		/**
		 * Called after the window was hidden + destroyed.
		 *
		 * @type  Callback function.
		 * @since  1.0.0
		 * @internal
		 */
		var _onclose = null;

		/**
		 * Custom resize handler.
		 *
		 * @type  Callback function.
		 * @since  1.0.0
		 * @internal
		 */
		var _onresize = null;

		/**
		 * The popup container element. This is the outermost DOM element of the
		 * popup. The _wnd element might contain additional data, such as a
		 * CSS <style> tag that belongs to the popup.
		 *
		 * The _wnd element
		 * - is attached/detached from the DOM on show/hide
		 * - is positioned during resize
		 * - is positioned during open/close of slide-in
		 * - can contain <style> tag or hidden .buttons element
		 *
		 * @type  jQuery object.
		 * @since  1.0.0
		 * @internal
		 */
		var _wnd = null;

		/**
		 * The popup window element.
		 * By default this is identical to _wnd, but might be different when
		 * using a custom template. This is the element with class .popup
		 *
		 * The _popup element
		 * - is displayed/hidden during show/hide
		 * - is animated during show/hide
		 * - contains the loading-animation via .loading(true)
		 * - all dynamic classes are added to this element
		 *
		 * @type  jQuery object.
		 * @since  3.0.0
		 * @internal
		 */
		var _popup = null;

		/**
		 * Window status: visible, hidden, closing
		 *
		 * @type   string
		 * @since  1.0.14
		 * @internal
		 */
		var _status = 'hidden';


		/**
		 * Slide-in status: collapsed, collapsing, expanded, expaning
		 *
		 * @type   string
		 * @since  2.0.0
		 * @internal
		 */
		var _slidein_status = 'none';

		/**
		 * Slide-in Icon for collapsed state
		 *
		 * @type   string
		 * @since  2.0.0
		 * @internal
		 */
		var _icon_collapse = '';

		/**
		 * Slide-in Icon for expanded state
		 *
		 * @type   string
		 * @since  2.0.0
		 * @internal
		 */
		var _icon_expand = '';

		/**
		 * Slide-in option that defines the speed to expand/collapse the popup.
		 *
		 * @type   number
		 * @since  2.0.0
		 * @internal
		 */
		var _slidein_speed = 400;


		// ==============================
		// == Public functions ==========

		/**
		 * The official popup ID
		 *
		 * @since 1.1.0
		 * @type  int
		 */
		this.id = 0;

		/**
		 * Returns the modal property.
		 *
		 * @since  2.0.0
		 */
		this.is_modal = function is_modal() {
			return _modal;
		};

		/**
		 * Returns the visible-state property.
		 *
		 * @since  2.0.0
		 */
		this.is_visible = function is_visible() {
			return _visible;
		};

		/**
		 * Returns the slidein property.
		 *
		 * @since  2.0.0
		 */
		this.is_slidein = function is_slidein() {
			return _slidein;
		};

		/**
		 * Returns the _snap property.
		 *
		 * @since  2.0.0
		 */
		this.get_snap = function get_snap() {
			return _snap;
		};

		/**
		 * Sets the modal property.
		 *
		 * @since  1.0.0
		 */
		this.modal = function modal( state, background_close ) {
			if ( undefined === background_close ) {
				background_close = true;
			}

			_modal = ( state ? true : false );
			_background_close = ( background_close ? true : false );

			_update_window();
			return _me;
		};

		/**
		 * Sets the window size.
		 *
		 * @since  1.0.0
		 */
		this.size = function size( width, height ) {
			var new_width = parseFloat( width ),
				new_height = parseFloat( height );

			if ( isNaN( new_width ) ) { new_width = 0; }
			if ( isNaN( new_height ) ) { new_height = 0; }
			if ( new_width >= 0 ) { _width = new_width; }
			if ( new_height >= 0 ) { _height = new_height; }

			_need_check_size = true;
			_update_window();
			return _me;
		};

		/**
		 * Sets the snap-constraints of the popup.
		 *
		 * @since  2.0.0
		 */
		this.snap = function snap() {
			var is_middle = false;
			_snap = { top: false, left: false, right: false, bottom: false };

			for ( var i = 0; i < arguments.length && ! is_middle; i += 1 ) {
				var snap_to = arguments[i].toLowerCase();

				switch(snap_to) {
					case 'top':
					case 'left':
					case 'right':
					case 'bottom':
						_snap[snap_to] = true;
						break;

					case 'none':
					case 'center':
						is_middle = true;
						break;
				}
			}

			if ( is_middle ) {
				_snap = { top: false, left: false, right: false, bottom: false };
			}

			_need_check_size = true;
			_update_window();
			return _me;
		};

		/**
		 * Enables or disables the slide-in function of the popup.
		 *
		 * @since  2.0.0
		 */
		this.slidein = function slidein( option, duration ) {
			option = option.toLowerCase();
			_slidein = 'none';

			switch ( option ) {
				case 'down':
					_slidein = 'down';
					_icon_collapse = 'dashicons-arrow-down-alt2';
					_icon_expand = 'dashicons-arrow-up-alt2';
					break;

				case 'up':
					_slidein = 'up';
					_icon_collapse = 'dashicons-arrow-up-alt2';
					_icon_expand = 'dashicons-arrow-down-alt2';
					break;
			}

			if ( ! isNaN( duration ) && duration >= 0 ) {
				_slidein_speed = duration;
			}

			_need_check_size = true;
			_update_window();
			return _me;
		};

		/**
		 * Define the opening and closing animation for the popup.
		 *
		 * @since  3.0.0
		 */
		this.animate = function animate( anim_in, anim_out ) {
			var can_animate = false,
				domPrefixes = 'Webkit Moz O ms Khtml'.split( ' ' );

			if ( _popup[0].style.animationName !== undefined ) { can_animate = true; }

			if ( can_animate === false ) {
				for ( var i = 0; i < domPrefixes.length; i++ ) {
					if ( _popup[0].style[ domPrefixes[i] + 'AnimationName' ] !== undefined ) {
						can_animate = true;
						break;
					}
				}
			}

			if ( ! can_animate ) {
				// Sorry guys, CSS animations are not supported...
				anim_in = '';
				anim_out = '';
			}

			_animation_in = anim_in;
			_animation_out = anim_out;

			return _me;
		};

		/**
		 * Sets optional classes for the main window element.
		 *
		 * @since  1.0.14
		 */
		this.set_class = function set_class( class_names ) {
			_classes = class_names;
			_content_changed = true;

			_update_window();
			return _me;
		};

		/**
		 * Define a callback that is executed when the popup needs to be moved
		 * or resized.
		 *
		 * @since  3.0.0
		 */
		this.onresize = function onresize( callback ) {
			_onresize = callback;
			return _me;
		};

		/**
		 * Define a callback that is executed after popup is made visible.
		 *
		 * @since  1.0.0
		 */
		this.onshow = function onshow( callback ) {
			_onshow = callback;
			return _me;
		};

		/**
		 * Define a callback that is executed after popup is hidden.
		 *
		 * @since  1.0.0
		 */
		this.onhide = function onhide( callback ) {
			_onhide = callback;
			return _me;
		};

		/**
		 * Define a callback that is executed after popup was destroyed.
		 *
		 * @since  1.0.0
		 */
		this.onclose = function onclose( callback ) {
			_onclose = callback;
			return _me;
		};

		/**
		 * Add a loading-overlay to the popup or remove the overlay again.
		 *
		 * @since  1.0.0
		 * @param  bool state True will add the overlay, false removes it.
		 */
		this.loading = function loading( state ) {
			if ( state ) {
				_popup.addClass( 'wpmui-loading' );
			} else {
				_popup.removeClass( 'wpmui-loading' );
			}
			return _me;
		};

		/**
		 * Shows a confirmation box inside the popup
		 *
		 * @since  1.0.14
		 * @param  object args Message options
		 */
		this.confirm = function confirm( args ) {
			if ( _status !== 'visible' ) { return _me; }
			if ( ! args instanceof Object ) { return _me; }

			args['layout'] = 'absolute';
			args['parent'] = _popup;

			wpmUi.confirm( args );

			return _me;
		};

		/**
		 * Sets the window title.
		 *
		 * @since  1.0.0
		 */
		this.title = function title( new_title, can_close ) {
			if ( undefined === can_close ) {
				can_close = true;
			}

			_title = new_title;
			_title_close = ( can_close ? true : false );

			_update_window();
			return _me;
		};

		/**
		 * Sets the window content.
		 *
		 * @since  1.0.0
		 */
		this.content = function content( data, move ) {
			if ( data instanceof jQuery ) {
				if ( move ) {
					// Move the object into the popup.
					_content = data;
				} else {
					// Create a copy of the object inside the popup.
					_content = data.html();
				}
			} else {
				// Content is text, will always be a copy.
				_content = data;
			}

			_need_check_size = true;
			_content_changed = true;

			_update_window();
			return _me;
		};

		/**
		 * Show the popup window.
		 *
		 * @since  1.0.0
		 */
		this.show = function show() {
			// Add the DOM elements to the document body and add event handlers.
			_wnd.appendTo( jQuery( 'body' ) );
			_popup.hide();
			_hook();

			_visible = true;
			_need_check_size = true;
			_status = 'visible';

			_update_window();

			// Fix issue where Buttons are not available in Chrome
			// https://app.asana.com/0/11388810124414/18688920614102
			_popup.hide();
			window.setTimeout(function() {
				// The timeout is so short that the element will *not* be
				// hidden but webkit will still redraw the element.
				_popup.show();
			}, 2);

			if ( 'none' === _slidein && _animation_in ) {
				_popup.addClass( _animation_in + ' animated' );
				_popup.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
					_popup.removeClass( 'animated' );
					_popup.removeClass( _animation_in );
				});
			}

			if ( typeof _onshow === 'function' ) {
				_onshow.apply( _me, [ _me.$() ] );
			}
			return _me;
		};

		/**
		 * Hide the popup window.
		 *
		 * @since  1.0.0
		 */
		this.hide = function hide() {
			function hide_popup() {
				if ( 'none' === _slidein ) {
					// Remove the popup from the DOM (but keep it in memory)
					_wnd.detach();
					_unhook();
				}

				_visible = false;
				_status = 'hidden';
				_update_window();

				if ( typeof _onhide === 'function' ) {
					_onhide.apply( _me, [ _me.$() ] );
				}
			}

			if ( 'none' === _slidein && _animation_out ) {
				_popup.addClass( _animation_out + ' animated' );
				_popup.one(
					'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend',
					function() {
						_popup.removeClass( 'animated' );
						_popup.removeClass( _animation_out );
						hide_popup();
					}
				);
			} else {
				hide_popup();
			}

			return _me;
		};

		/**
		 * Completely removes the popup window.
		 * The popup object cannot be re-used after calling this function.
		 *
		 * @since  1.0.0
		 */
		this.destroy = function destroy() {
			var orig_onhide = _onhide;

			// Prevent infinite loop when calling .destroy inside onclose handler.
			if ( _status === 'closing' ) { return; }

			_onhide = function() {
				if ( typeof orig_onhide === 'function' ) {
					orig_onhide.apply( _me, [ _me.$() ] );
				}

				_status = 'closing';

				if ( typeof _onclose === 'function' ) {
					_onclose.apply( _me, [ _me.$() ] );
				}

				// Completely remove the popup from the memory.
				_wnd.remove();
				_wnd = null;
				_popup = null;

				delete _all_popups[_me.id];

				_me = null;
			};

			_me.hide();
		};

		/**
		 * Adds an event handler to the dialog.
		 *
		 * @since  2.0.1
		 */
		this.on = function on( event, selector, callback ) {
			_wnd.on( event, selector, callback );

			if ( _wnd.filter( selector ).length ) {
				_wnd.on( event, callback );
			}

			return _me;
		};

		/**
		 * Removes an event handler from the dialog.
		 *
		 * @since  2.0.1
		 */
		this.off = function off( event, selector, callback ) {
			_wnd.off( event, selector, callback );

			if ( _wnd.filter( selector ).length ) {
				_wnd.off( event, callback );
			}

			return _me;
		};

		/**
		 * Returns the jQuery object of the window
		 *
		 * @since  1.0.0
		 */
		this.$ = function $( selector ) {
			if ( selector ) {
				return _wnd.find( selector );
			} else {
				return _wnd;
			}
		};


		// ==============================
		// == Private functions =========


		/**
		 * Create the DOM elements for the window.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		function _init() {
			_me.id = _next_id;
			_next_id += 1;
			_all_popups[_me.id] = _me;

			if ( ! _template ) {
				// Defines the default popup template.
				_template = '<div class="wpmui-popup">' +
					'<div class="popup-title">' +
						'<span class="the-title"></span>' +
						'<span class="popup-close"><i class="dashicons dashicons-no-alt"></i></span>' +
					'</div>' +
					'<div class="popup-content"></div>' +
					'</div>';
			}

			// Create the DOM elements.
			_wnd = jQuery( _template );

			// Add custom CSS.
			if ( _css ) {
				jQuery( '<style>' + _css + '</style>' ).prependTo( _wnd );
			}

			// Add default selector class to the base element if the class is missing.
			if ( ! _wnd.filter( '.popup' ).length && ! _wnd.find( '.popup' ).length ) {
				_wnd.addClass( 'popup' );
			}

			// See comments in top section for difference between _wnd and _popup.
			if ( _wnd.hasClass( 'popup' ) ) {
				_popup = _wnd;
			} else {
				_popup = _wnd.find( '.popup' ).first();
			}

			// Add supported content modification methods.
			if ( ! _popup.find( '.popup-title' ).length ) {
				_me.title = function() { return _me; };
			}

			if ( ! _popup.find( '.popup-content' ).length ) {
				_me.content = function() { return _me; };
			}

			if ( ! _popup.find( '.slidein-toggle' ).length ) {
				if (  _popup.find( '.popup-title .popup-close' ).length ) {
					_popup.find( '.popup-title .popup-close' ).addClass( 'slidein-toggle' );
				} else if ( _popup.find( '.popup-title' ).length ) {
					_popup.find( '.popup-title' ).addClass( 'slidein-toggle' );
				} else {
					_popup.prepend( '<span class="slidein-toggle only-slidein"><i class="dashicons"></i></span>' );
				}
			}

			_visible = false;
		}

		/**
		 * Add event listeners.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		function _hook() {
			if ( _popup && ! _popup.data( 'hooked' ) ) {
				_popup.data( 'hooked', true );
				_popup.on( 'click', '.popup-close', _click_close );
				_popup.on( 'click', '.popup-title', _click_title );
				_popup.on( 'click', '.close', _me.hide );
				_popup.on( 'click', '.destroy', _me.destroy );
				_popup.on( 'click', 'thead .check-column :checkbox', _toggle_checkboxes );
				_popup.on( 'click', 'tfoot .check-column :checkbox', _toggle_checkboxes );
				_popup.on( 'click', 'tbody .check-column :checkbox', _check_checkboxes );
				jQuery( window ).on( 'resize', _resize_and_move );

				if ( jQuery().draggable !== undefined ) {
					_popup.draggable({
						containment: jQuery( 'body' ),
						scroll: false,
						handle: '.popup-title'
					});
				}
			}
		}

		/**
		 * Remove all event listeners.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		function _unhook() {
			if ( _popup && _popup.data( 'hooked' ) ) {
				_popup.data( 'hooked', false );
				_popup.off( 'click', '.popup-close', _click_close );
				_popup.off( 'click', '.popup-title', _click_title );
				_popup.off( 'click', '.close', _me.hide );
				_popup.off( 'click', '.check-column :checkbox', _toggle_checkboxes );
				jQuery( window ).off( 'resize', _resize_and_move );
			}
		}

		/**
		 * Updates the size and position of the window.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		function _update_window() {
			if ( ! _wnd ) { return false; }
			if ( ! _popup ) { return false; }

			var _overlay = wpmUi._modal_overlay(),
				_el_title = _popup.find( '.popup-title' ),
				_el_content = _popup.find( '.popup-content' ),
				_title_span = _el_title.find( '.the-title' );

			// Window title.
			if ( _template && ! _title_span.length ) {
				_title_span = _el_title;
			}
			_title_span.html( _title );

			if ( _title_close ) {
				_popup.removeClass( 'no-close' );
			} else {
				_popup.addClass( 'no-close' );
			}

			// Display a copy of the specified content.
			if ( _content_changed ) {
				// Remove the current button bar.
				_wnd.find( '.buttons' ).remove();
				_popup.addClass( 'no-buttons' );

				// Update the content.
				if ( _content instanceof jQuery ) {
					// _content is a jQuery element.
					_el_content.empty().append( _content );
				} else {
					// _content is a HTML string.
					_el_content.html( _content );
				}

				// Move the buttons out of the content area.
				var buttons = _el_content.find( '.buttons' );
				if ( buttons.length ) {
					buttons.appendTo( _popup );
					_popup.removeClass( 'no-buttons' );
				}

				// Add custom class to the popup.
				_popup.addClass( _classes );

				_content_changed = false;
			}

			if ( _overlay instanceof jQuery ) {
				_overlay.off( 'click', _modal_close );
			}

			// Show or hide the window and modal background.
			if ( _visible ) {
				_show_the_popup();

				if ( _modal ) {
					wpmUi._make_modal( '', 'has-popup' );
				}

				if ( _background_close ) {
					_overlay.on( 'click', _modal_close );
				}

				if ( _need_check_size ) {
					_need_check_size = false;
					_resize_and_move();
				}

				// Allow the browser to display + render the title first.
				window.setTimeout(function() {
					if ( 'down' === _slidein ) {
						_el_content.css({ bottom: _el_title.height() + 1 });
					} else {
						_el_content.css({ top: _el_title.height() + 1 });
					}
					if ( ! _height ) {
						window.setTimeout(_resize_and_move, 5);
					}
				}, 5);
			} else {
				_hide_the_popup();

				var wnd, remove_modal = true;
				for ( wnd in _all_popups ) {
					if ( _all_popups[wnd] === _me ) { continue; }
					if ( ! _all_popups[wnd].is_visible() ) { continue; }
					if ( _all_popups[wnd].is_modal() ) {
						remove_modal = false;
						break;
					}
				}

				if ( remove_modal ) {
					wpmUi._close_modal( 'has-popup no-scroll can-scroll' );
				}
			}

			// Adjust the close-icon according to slide-in state.
			var icon = _popup.find('.popup-close .dashicons');
			if ( icon.length ) {
				if ( 'none' === _slidein ) {
					icon.removeClass().addClass('dashicons dashicons-no-alt');
				} else {
					if ( 'collapsed' === _slidein_status ) {
						icon.removeClass().addClass('dashicons').addClass(_icon_collapse);
					} else if ( 'expanded' === _slidein_status ) {
						icon.removeClass().addClass('dashicons').addClass(_icon_expand);
					}
				}
			}

			// Remove all "slidein-..." classes from the popup.
			_popup[0].className = _popup[0].className.replace(/\sslidein-.+?\b/g, '');

			if ( 'none' === _slidein ) {
				_popup.removeClass( 'slidein' );
				_popup.removeClass( 'wdev-slidein' );
				_popup.addClass( 'wdev-window' );
			} else {
				_popup.addClass( 'slidein' );
				_popup.addClass( 'slidein-' + _slidein );
				_popup.addClass( 'slidein-' + _slidein_status );
				_popup.addClass( 'wdev-slidein' );
				_popup.removeClass( 'wdev-window' );
			}
			if ( _snap.top ) { _popup.addClass('snap-top'); }
			if ( _snap.left ) { _popup.addClass('snap-left'); }
			if ( _snap.right ) { _popup.addClass('snap-right'); }
			if ( _snap.bottom ) { _popup.addClass('snap-bottom'); }
		}

		/**
		 * Displays the popup while considering the slidein option
		 *
		 * @since  2.0.0
		 */
		function _show_the_popup() {
			_popup.show();

			// We have a collapsed slide-in. Animate it.
			var have_slidein = 'none' !== _slidein,
				can_expand = ('collapsed' === _slidein_status);

			if ( have_slidein ) {
				// First time the slide in is opened? Animate it.
				if ( ! can_expand && 'none' === _slidein_status ) {
					var styles = {};
					_slidein_status = 'collapsed';
					styles = _get_popup_size( styles );
					styles = _get_popup_pos( styles );
					_popup.css(styles);

					can_expand = true;
				}

				if ( can_expand ) {
					_slidein_status = 'expanding';
					_resize_and_move( _slidein_speed );
					_need_check_size = false;

					window.setTimeout(function() {
						_slidein_status = 'expanded';
						_update_window();
						window.setTimeout( _resize_and_move, 10 );
					}, _slidein_speed);
				}
			}
		}

		/**
		 * Hides the popup while considering the slidein option to either
		 * completely hide the popup or to keep the title visible.
		 *
		 * @since  2.0.0
		 */
		function _hide_the_popup() {
			switch ( _slidein ) {
				case 'up':
				case 'down':
					var can_collapse = ('expanded' === _slidein_status);

					if ( can_collapse ) {
						var wnd = jQuery( window ),
							window_height = wnd.innerHeight(),
							popup_pos = _popup.position(),
							styles = {};

						// First position the popup using the `top` property only.
						styles['margin-top'] = 0;
						styles['margin-bottom'] = 0;
						styles['bottom'] = 'auto';
						styles['top'] = popup_pos.top;
						_popup.css( styles );

						// Calculate the destination position of the popup and animate.
						_slidein_status = 'collapsing';
						styles = _get_popup_pos();
						_popup.animate(styles, _slidein_speed, function() {
							_slidein_status = 'collapsed';
							_update_window();
							window.setTimeout( _resize_and_move, 10 );
						});
					}
					break;

				default:
					_popup.hide();
					break;
			}
		}

		/**
		 * When the popup has slide-in behavior then the close button acts as
		 * a toggle-visiblity button.
		 *
		 * @since  1.0.0
		 */
		function _click_close( ev ) {
			if ( 'none' === _slidein ) {
				_me.hide();
			} else {
				if ( _visible ) {
					_me.hide();
				} else {
					_me.show();
				}
			}
			ev.stopPropagation();
		}

		/**
		 * Slide-ins also react when the user clicks the title.
		 *
		 * @since  1.0.0
		 */
		function _click_title( ev ) {
			if ( 'none' !== _slidein ) {
				if ( _visible ) {
					_me.hide();
				} else {
					_me.show();
				}
				ev.stopPropagation();
			}
		}

		/**
		 * Closes the window when user clicks on the modal overlay
		 *
		 * @since  1.0.0
		 * @internal
		 */
		function _modal_close() {
			var _overlay = wpmUi._modal_overlay();
			if ( ! _wnd ) { return false; }
			if ( ! _overlay instanceof jQuery ) { return false; }

			_overlay.off( 'click', _modal_close );
			_me.hide();
		}

		/**
		 * Makes sure that the popup window is not bigger than the viewport.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		function _resize_and_move(duration) {
			if ( ! _popup ) { return false; }

			if ( typeof _onresize === 'function' ) {
				_onresize.apply( _me, [ _me.$() ] );
			} else {
				var styles = {};

				styles = _get_popup_size( styles );
				styles = _get_popup_pos( styles );

				// Size and position.
				if ( ! isNaN( duration ) && duration > 0 ) {
					_popup.animate(styles, duration);
				} else {
					_popup.css(styles);
				}
			}
		}

		/**
		 * A helper function for the resize/slidein functions that returns the
		 * actual size (width and height) of the popup.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		function _get_popup_size( size ) {
			var wnd = jQuery( window ),
				window_width = wnd.innerWidth(),
				window_height = wnd.innerHeight(),
				border_x = parseInt( _popup.css('border-left-width') ) +
					parseInt( _popup.css('border-right-width') ),
				border_y = parseInt( _popup.css('border-top-width') ) +
					parseInt( _popup.css('border-bottom-width') ),
				real_width = _width + border_x,
				real_height = _height + border_y;

			if ( 'object' !== typeof size ) { size = {}; }

			// Calculate the width and height ------------------------------

			if ( ! _height || ! _width ) {
				var get_width = ! _width,
					get_height = ! _height,
					new_width = 0, new_height = 0;

				_popup.find('*').each(function() {
					var el = jQuery( this ),
						pos = el.position(),
						el_width = el.outerWidth() + pos.left,
						el_height = el.outerHeight() + pos.top;

					if ( get_width && new_width < el_width ) {
						new_width = el_width;
					}
					if ( get_height && new_height < el_height ) {
						new_height = el_height;
					}
				});

				if ( get_width ) { real_width = new_width + border_x; }
				if ( get_height ) { real_height = new_height + border_y; }
			}

			if ( _snap.left && _snap.right ) {
				// Snap to 2 sides: full width.
				size['width'] = window_width - border_x;
			} else {
				if ( window_width < real_width ) {
					real_width = window_width;
				}
				size['width'] = real_width - border_x;
			}

			if ( _snap.top && _snap.bottom ) {
				// Snap to 2 sides: full height.
				size['height'] = window_height - border_y;
			} else {
				if ( window_height < real_height ) {
					real_height = window_height;
				}
				size['height'] = real_height - border_y;
			}

			return size;
		}

		/**
		 * Helper function used for positioning the popup, it will return the
		 * x/y positioning styles.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		function _get_popup_pos( styles ) {
			var wnd = jQuery( window ),
				el_toggle = _popup.find( '.slidein-toggle' ),
				window_width = wnd.innerWidth(),
				window_height = wnd.innerHeight(),
				border_x = parseInt( _popup.css('border-left-width') ) +
					parseInt( _popup.css('border-right-width') ),
				border_y = parseInt( _popup.css('border-top-width') ) +
					parseInt( _popup.css('border-bottom-width') );

			if ( 'object' !== typeof styles ) { styles = {}; }
			if ( undefined === styles['width'] || undefined === styles['height'] ) {
				styles = _get_popup_size( styles );
			}

			// Position X: (empty) / left / right / left + right
			if ( ! _snap.left && ! _snap.right ) {
				// Center X.
				styles['left'] = (window_width - styles['width']) / 2;
			} else if ( _snap.left && _snap.right ) {
				// Snap to 2 sides.
				styles['left'] = 0;
			} else {
				// Snap to one side.
				if ( _snap.left ) {
					styles['left'] = 0;
				}
				if ( _snap.right ) {
					styles['left'] = window_width - styles['width'] - border_x;
				}
			}

			if ( 'none' !== _slidein && ('collapsed' === _slidein_status || 'collapsing' === _slidein_status) ) {
				// We have a collapsed slide-in. Y-position is fixed.
				if ( 'down' === _slidein ) {
					styles['top'] = el_toggle.outerHeight() - styles['height'];
				} else {
					styles['top'] = window_height - el_toggle.outerHeight();
				}
			} else {
				// Position Y: (empty) / top / bottom / top + bottom
				if ( ! _snap.top && ! _snap.bottom ) {
					// Center Y.
					styles['top'] = (window_height - styles['height']) / 2;
				} else if ( _snap.top && _snap.bottom ) {
					// Snap to 2 sides.
					styles['top'] = 0;
				} else {
					// Snap to one side.
					if ( _snap.top ) {
						styles['top'] = 0;
					}
					if ( _snap.bottom ) {
						styles['top'] = window_height - styles['height'] - border_y;
					}
				}
			}

			styles['margin-top'] = 0;
			styles['margin-bottom'] = 0;
			styles['bottom'] = 'auto';
			styles['right'] = 'auto';

			if ( undefined === styles['top'] ) { styles['top'] = 'auto'; }
			if ( undefined === styles['left'] ) { styles['left'] = 'auto'; }

			return styles;
		}

		/**
		 * Toggle all checkboxes in a WordPress-ish table when the user clicks
		 * the check-all checkbox in the header or footer.
		 *
		 * @since  1.0.0
		 * @internal
		 */
		function _toggle_checkboxes( ev ) {
			var chk = jQuery( this ),
				c = chk.prop( 'checked' ),
				toggle = (ev.shiftKey);

			// Toggle checkboxes inside the table body
			chk
				.closest( 'table' )
				.children( 'tbody, thead, tfoot' )
				.filter( ':visible' )
				.children()
				.children( '.check-column' )
				.find( ':checkbox' )
				.prop( 'checked', c);
		}

		/**
		 * Toggle the check-all checkexbox in the header/footer in a
		 * WordPress-ish table when a single checkbox in the body is changed.
		 *
		 * @since  1.0.0
		 */
		function _check_checkboxes( ev ) {
			var chk = jQuery( this ),
				unchecked = chk
					.closest( 'tbody' )
					.find( ':checkbox' )
					.filter( ':visible' )
					.not( ':checked' );

			chk
				.closest( 'table' )
				.children( 'thead, tfoot' )
				.find( ':checkbox' )
				.prop( 'checked',  ( 0 === unchecked.length ) );

			return true;
		}

		// Initialize the popup window.
		_me = this;
		_init();

	}; /* ** End: WpmUiWindow ** */

}( window.wpmUi = window.wpmUi || {} ));