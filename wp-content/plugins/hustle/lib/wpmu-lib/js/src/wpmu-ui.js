/*!
 * WPMU Dev UI library
 * (Philipp Stracker for WPMU Dev)
 *
 * This library provides a Javascript API via the global wpmUi object.
 *
 * @version  1.0.0
 * @author   Philipp Stracker for WPMU Dev
 * @link     http://appendto.com/2010/10/how-good-c-habits-can-encourage-bad-javascript-habits-part-1/
 * @requires jQuery
 */
/*global jQuery:false */
/*global window:false */
/*global document:false */
/*global XMLHttpRequest:false */

(function( wpmUi ) {

	/**
	 * The document element.
	 *
	 * @type   jQuery object
	 * @since  1.0.0
	 * @private
	 */
	var _doc = null;

	/**
	 * The html element.
	 *
	 * @type   jQuery object
	 * @since  1.0.0
	 * @private
	 */
	var _html = null;

	/**
	 * The body element.
	 *
	 * @type   jQuery object
	 * @since  1.0.0
	 * @private
	 */
	var _body = null;

	/**
	 * Modal overlay, created by this object.
	 *
	 * @type   jQuery object
	 * @since  1.0.0
	 * @private
	 */
	var _modal_overlay = null;


	// ==========
	// == Public UI functions ==================================================
	// ==========


	/**
	 * Creates a new popup window.
	 *
	 * @since  1.0.0
	 * @param  string template Optional. The HTML template of the popup.
	 *         Note: The template should contain an element ".popup"
	 * @param  string css CSS styles to attach to the popup.
	 * @return WpmUiWindow A new popup window.
	 */
	wpmUi.popup = function popup( template, css ) {
		_init();
		return new wpmUi.WpmUiWindow( template, css );
	};

	/**
	 * Creates a new progress bar element.
	 *
	 * @since  2.0.2
	 * @return WpmUiProgress A new progress bar element.
	 */
	wpmUi.progressbar = function progressbar() {
		_init();
		return new wpmUi.WpmUiProgress();
	};

	/**
	 * Creates a new formdata object.
	 * With this object we can load or submit data via ajax.
	 *
	 * @since  1.0.0
	 * @param  string ajaxurl URL to the ajax handler.
	 * @param  string default_action The action to use when an ajax function
	 *                does not specify an action.
	 * @return WpmUiAjaxData A new formdata object.
	 */
	wpmUi.ajax = function ajax( ajaxurl, default_action ) {
		_init();
		return new wpmUi.WpmUiAjaxData( ajaxurl, default_action );
	};

	/**
	 * Upgrades normal multiselect fields to chosen-input fields.
	 *
	 * This function is a bottle-neck in Firefox -> el.chosen() takes quite long
	 *
	 * @since  1.0.0
	 * @param  jQuery|string base All children of this base element will be
	 *                checked. If empty then the body element is used.
	 */
	wpmUi.upgrade_multiselect = function upgrade_multiselect( base ) {
		_init();
		base = jQuery( base || _body );

		var items = base.find( 'select[multiple]' ).not( 'select[data-select-ajax]' ),
		ajax_items = base.find( 'select[data-select-ajax]' );

		// When an DOM container is *cloned* it may contain markup for a select2
		// listbox that is not attached to any event handler. Clean this up.
		var clean_ghosts = function clean_ghosts( el ) {
			var id = el.attr( 'id' ),
				s2id = '#s2id_' + id,
				ghosts = el.parent().find( s2id );

			ghosts.remove();
		};

		// Initialize normal select or multiselect list.
		var upgrade_item = function upgrade_item() {
			var el = jQuery( this ),
				options = {
					'closeOnSelect': false,
					'width': '100%'
				};

			// Prevent double initialization (i.e. conflict with other plugins)
			if ( typeof el.data( 'select2' ) === 'object' ) { return; }
			if ( typeof el.data( 'chosen' ) === 'object' ) { return; }
			if ( el.filter( '[class*=acf-]' ).length ) { return; }

			// Prevent double initialization (with other WPMU LIB plugin)
			if ( el.data( 'wpmui-select' ) === '1' ) { return; }

			// Prevent auto-initialization when manually disabled.
			if ( el.closest( '.no-auto-init', base[0] ).length ) { return; }

			el.data( 'wpmui-select', '1' );
			clean_ghosts( el );

			// Prevent lags during page load by making this asynchronous.
			if ( "function" === typeof( el.wpmuiSelect ) ) {
				window.setTimeout( function() {
					el.wpmuiSelect(options);
				}, 1);
			}
		};

		// Initialize select list with ajax source.
		var upgrade_ajax = function upgrade_ajax() {
			var format_item = function format_item( item ) {
				return item.text;
			};

			var el = jQuery( this ),
				options = {
					'closeOnSelect': false,
					'width': '100%',
					'multiple': true,
					'minimumInputLength': 1,
					'ajax': {
						url: el.attr( 'data-select-ajax' ),
						dataType: 'json',
						quietMillis: 100,
						cache: true,
						data: function(params) {
							return {
								q: params.term,
								page: params.page,
							};
						},
						processResults: function(data, params) {
							return {
								results: data.items
							};
						}
					},
					'templateResult': format_item,
					'templateSelection': format_item
				};

			// Prevent double initialization (i.e. conflict with other plugins)
			if ( typeof el.data( 'select2' ) === 'object' ) { return; }
			if ( typeof el.data( 'chosen' ) === 'object' ) { return; }
			if ( el.filter( '[class*=acf-]' ).length ) { return; }

			// Prevent double initialization (with other WPMU LIB plugin)
			if ( el.data( 'wpmui-select' ) === '1' ) { return; }

			// Prevent auto-initialization when manually disabled
			if ( el.closest( '.no-auto-init', base[0] ).length ) { return; }

			el.data( 'wpmui-select', '1' );
			clean_ghosts( el );

			// Prevent lags during page load by making this asynchronous.
			if ( "function" === typeof( el.wpmuiSelect ) ) {
				window.setTimeout( function() {
					el.wpmuiSelect(options);
				}, 1);
			}
		};

		if ( 'function' === typeof jQuery.fn.each2 ) {
			items.each2( upgrade_item );
			ajax_items.each2( upgrade_ajax );
		} else {
			items.each( upgrade_item );
			ajax_items.each( upgrade_ajax );
		}

	};

	/**
	 * Displays a WordPress-like message to the user.
	 *
	 * @since  1.0.0
	 * @param  string|object args Message options object or message-text.
	 *             args: {
	 *               'message': '...'
	 *               'type': 'ok|err'  // Style
	 *               'close': true     // Show close button?
	 *               'parent': '.wrap' // Element that displays the message
	 *               'insert_after': 'h2' // Inside the parent the message
	 *                                    // will be displayed after the
	 *                                    // first element of this type.
	 *                                    // Set to false to insert at top.
	 *                'id': 'msg-ok'   // When set to a string value then the
	 *                                 // the first call to "message()" will
	 *                                 // insert a new message and the next
	 *                                 // call will update the existing element.
	 *                'class': 'msg1'  // Additional CSS class.
	 *                'details': obj   // Details for error-type message.
	 *             }
	 */
	wpmUi.message = function message( args ) {
		var parent, msg_box, btn_close, need_insert, debug;
		_init();

		// Hides the message again, e.g. when user clicks the close icon.
		var hide_message = function hide_message( ev ) {
			ev.preventDefault();
			msg_box.remove();
			return false;
		};

		// Toggle the error-details
		var toggle_debug = function toggle_debug( ev ) {
			var me = jQuery( this ).closest( '.wpmui-msg' );
			me.find( '.debug' ).toggle();
		};

		if ( 'undefined' === typeof args ) { return false; }

		if ( 'string' === typeof args || args instanceof Array ) {
			args = { 'message': args };
		}

		if ( args['message'] instanceof Array ) {
			args['message'] = args['message'].join( '<br />' );
		}

		if ( ! args['message'] ) { return false; }

		args['type'] = undefined === args['type'] ? 'ok' : args['type'].toString().toLowerCase();
		args['close'] = undefined === args['close'] ? true : args['close'];
		args['parent'] = undefined === args['parent'] ? '.wrap' : args['parent'];
		args['insert_after'] = undefined === args['insert_after'] ? 'h2' : args['insert_after'];
		args['id'] = undefined === args['id'] ? '' : args['id'].toString().toLowerCase();
		args['class'] = undefined === args['class'] ? '' : args['class'].toString().toLowerCase();
		args['details'] = undefined === args['details'] ? false : args['details'];

		if ( args['type'] === 'error' || args['type'] === 'red' ) { args['type'] = 'err'; }
		if ( args['type'] === 'success' || args['type'] === 'green' ) { args['type'] = 'ok'; }

		parent = jQuery( args['parent'] ).first();
		if ( ! parent.length ) { return false; }

		if ( args['id'] && jQuery( '.wpmui-msg[data-id="' + args['id'] + '"]' ).length ) {
			msg_box = jQuery( '.wpmui-msg[data-id="' + args['id'] + '"]' ).first();
			need_insert = false;
		} else {
			msg_box = jQuery( '<div><p></p></div>' );
			if ( args['id'] ) { msg_box.attr( 'data-id', args['id'] ); }
			need_insert = true;
		}
		msg_box.find( 'p' ).html( args['message'] );

		if ( args['type'] === 'err' && args['details'] && window.JSON ) {
			jQuery( '<div class="debug" style="display:none"></div>' )
				.appendTo( msg_box )
				.text( JSON.stringify( args['details'] ) );
			jQuery( '<i class="dashicons dashicons-editor-help light"></i>' )
				.prependTo( msg_box.find( 'p:first' ) )
				.click( toggle_debug )
				.after( ' ' );
		}

		msg_box.removeClass().addClass( 'updated wpmui-msg ' + args['class'] );
		if ( 'err' === args['type'] ) {
			msg_box.addClass( 'error' );
		}

		if ( need_insert ) {
			if ( args['close'] ) {
				btn_close = jQuery( '<a href="#" class="notice-dismiss"></a>' );
				btn_close.prependTo( msg_box );

				btn_close.click( hide_message );
			}

			if ( args['insert_after'] && parent.find( args['insert_after'] ).length ) {
				parent = parent.find( args['insert_after'] ).first();
				parent.after( msg_box );
			} else {
				parent.prepend( msg_box );
			}
		}

		return true;
	};

	/**
	 * Displays confirmation box to the user.
	 *
	 * The layer is displayed in the upper half of the parent element and is by
	 * default modal.
	 * Note that the confirmation is asynchronous and the functions return value
	 * only indicates if the confirmation message was created, and not the users
	 * response!
	 *
	 * Also this is a "disponsable" function which does not create DOM elements
	 * that can be re-used. All elements are temporary and are removed when the
	 * confirmation is closed. Only 1 confirmation should be displayed at a time.
	 *
	 * @since  1.0.14
	 * @param  object args {
	 *     Confirmation options.
	 *
	 *     string message
	 *     bool modal
	 *     string layout 'fixed' or 'absolute'
	 *     jQuery parent A jQuery object or selector
	 *     array buttons Default is ['OK']
	 *     function(key) callback Receives array-index of the pressed button
	 * }
	 * @return bool True if the confirmation is created correctly.
	 */
	wpmUi.confirm = function confirm( args ) {
		var parent, modal, container, el_msg, el_btn, ind, item, primary_button;

		if ( ! args instanceof Object ) { return false; }
		if ( undefined === args['message'] ) { return false; }

		args['modal'] = undefined === args['modal'] ? true : args['modal'];
		args['layout'] = undefined === args['layout'] ? 'fixed' : args['layout'];
		args['parent'] = undefined === args['parent'] ? _body : args['parent'];
		args['buttons'] = undefined === args['buttons'] ? ['OK'] : args['buttons'];
		args['callback'] = undefined === args['callback'] ? false : args['callback'];

		parent = jQuery( args['parent'] );

		function handle_close() {
			var me = jQuery( this ),
				key = parseInt( me.data( 'key' ) );

			if ( args['modal'] ) {
				if ( args['layout'] === 'fixed' ) {
					wpmUi._close_modal();
				} else {
					modal.remove();
				}
			}
			container.remove();

			if ( 'function' === typeof args['callback'] ) {
				args['callback']( key );
			}
		}

		if ( args['modal'] ) {
			if ( args['layout'] === 'fixed' ) {
				wpmUi._make_modal( 'wpmui-confirm-modal' );
			} else {
				modal = jQuery( '<div class="wpmui-confirm-modal"></div>' )
					.css( { 'position': args['layout'] } )
					.appendTo( parent );
			}
		}

		container = jQuery( '<div class="wpmui-confirm-box"></div>' )
			.css( { 'position': args['layout'] } )
			.appendTo( parent );

		el_msg = jQuery( '<div class="wpmui-confirm-msg"></div>' )
			.html( args['message'] );

		el_btn = jQuery( '<div class="wpmui-confirm-btn"></div>' );
		primary_button = true;
		for ( ind = 0; ind < args['buttons'].length; ind += 1 ) {
			item = jQuery( '<button></button>' )
				.html( args['buttons'][ind] )
				.addClass( primary_button ? 'button-primary' : 'button-secondary' )
				.data( 'key', ind )
				.click( handle_close )
				.prependTo( el_btn );
			primary_button = false;
		}

		el_msg.appendTo( container );
		el_btn.appendTo( container )
			.find( '.button-primary' )
			.focus();

		return true;
	};

	/**
	 * Attaches a tooltip to the specified element.
	 *
	 * @since  1.0.0
	 * @param jQuery el The host element that receives the tooltip.
	 * @param object|string args The tooltip options. Either a string containing
	 *                the toolip message (HTML code) or an object with details:
	 *                - content
	 *                - trigger [hover|click]
	 *                - pos [top|bottom|left|right]
	 *                - class
	 */
	wpmUi.tooltip = function tooltip( el, args ) {
		var tip, parent;
		_init();

		// Positions the tooltip according to the function args.
		var position_tip = function position_tip( tip ) {
			var tip_width = tip.outerWidth(),
				tip_height = tip.outerHeight(),
				tip_padding = 5,
				el_width = el.outerWidth(),
				el_height = el.outerHeight(),
				pos = {};

			pos['left'] = (el_width - tip_width) / 2;
			pos['top'] = (el_height - tip_height) / 2;
			pos[ args['pos'] ] = 'auto';

			switch ( args['pos'] ) {
				case 'top':    pos['bottom'] = el_height + tip_padding; break;
				case 'bottom': pos['top'] = el_height + tip_padding; break;
				case 'left':   pos['right'] = el_width + tip_padding; break;
				case 'right':  pos['left'] = el_width + tip_padding; break;
			}
			tip.css(pos);
		};

		// Make the tooltip visible.
		var show_tip = function show_tip( ev ) {
			var tip = jQuery( this )
				.closest( '.wpmui-tip-box' )
				.find( '.wpmui-tip' );

			tip.addClass( 'wpmui-visible' );
			tip.show();
			position_tip( tip );
			window.setTimeout( function() { position_tip( tip ); }, 35 );
		};

		// Hide the tooltip.
		var hide_tip = function hide_tip( ev ) {
			var tip = jQuery( this )
				.closest( '.wpmui-tip-box' )
				.find( '.wpmui-tip' );

			tip.removeClass( 'wpmui-visible' );
			tip.hide();
		};

		// Toggle the tooltip state.
		var toggle_tip = function toggle_tip( ev ) {
			if ( tip.hasClass( 'wpmui-visible' ) ) {
				hide_tip.call(this, ev);
			} else {
				show_tip.call(this, ev);
			}
		};

		if ( 'string' === typeof args ) {
			args = { 'content': args };
		}
		if ( undefined === args['content'] ) {
			return false;
		}
		el = jQuery( el );
		if ( ! el.length ) {
			return false;
		}

		args['trigger'] = undefined === args['trigger'] ? 'hover' : args['trigger'].toString().toLowerCase();
		args['pos'] = undefined === args['pos'] ? 'top' : args['pos'].toString().toLowerCase();
		args['class'] = undefined === args['class'] ? '' : args['class'].toString().toLowerCase();

		parent = el.parent();
		if ( ! parent.hasClass( 'wpmui-tip-box' ) ) {
			parent = el
				.wrap( '<span class="wpmui-tip-box"></span>' )
				.parent()
				.addClass( args['class'] + '-box' );
		}

		tip = parent.find( '> .wpmui-tip' );
		el.off();

		if ( ! tip.length ) {
			tip = jQuery( '<div class="wpmui-tip"></div>' );
			tip
				.addClass( args['class'] )
				.addClass( args['pos'] )
				.appendTo( el.parent() )
				.hide();

			if ( ! isNaN( args['width'] ) ) {
				tip.width( args['width'] );
			}
		}

		if ( 'hover' === args['trigger'] ) {
			el.on( 'mouseenter', show_tip ).on( 'mouseleave', hide_tip );
		} else if ( 'click' === args['trigger'] ) {
			el.on( 'click', toggle_tip );
		}

		tip.html( args['content'] );

		return true;
	};

	/**
	 * Checks the DOM and creates tooltips for the DOM Elements that specify
	 * tooltip details.
	 *
	 * Function can be called repeatedly and will refresh the tooltip contents
	 * if they changed since last call.
	 *
	 * @since  1.0.8
	 */
	wpmUi.upgrade_tooltips = function upgrade_tooltips() {
		var el = jQuery( '[data-wpmui-tooltip]' );

		el.each(function() {
			var me = jQuery( this ),
				args = {
					'content': me.attr( 'data-wpmui-tooltip' ),
					'pos': me.attr( 'data-pos' ),
					'trigger': me.attr( 'data-trigger' ),
					'class': me.attr( 'data-class' ),
					'width': me.attr( 'data-width' )
				};

			wpmUi.tooltip( me, args );
		});
	};

	/*
	 * Converts any value to an object.
	 * Typically used to convert an array to an object.
	 *
	 * @since  1.0.6
	 * @param  mixed value This value is converted to an JS-object.
	 * @return object
	 */
	wpmUi.obj = function( value ) {
		var obj = {};

		if ( value instanceof Object ) {
			obj = value;
		}
		else if ( value instanceof Array ) {
			if ( typeof value.reduce === 'function' ) {
				obj = value.reduce(function(o, v, i) {
					o[i] = v;
					return o;
				}, {});
			} else {
				for ( var i = value.length - 1; i > 0; i -= 1 ) {
					if ( value[i] !== undefined ) {
						obj[i] = value[i];
					}
				}
			}
		}
		else if ( typeof value === 'string' ) {
			obj.scalar = value;
		}
		else if ( typeof value === 'number' ) {
			obj.scalar = value;
		}
		else if ( typeof value === 'boolean' ) {
			obj.scalar = value;
		}

		return obj;
	};


	// ==========
	// == Private helper functions =============================================
	// ==========


	/**
	 * Initialize the object
	 *
	 * @since  1.0.0
	 * @private
	 */
	function _init() {
		if ( null !== _html ) { return; }

		_doc = jQuery( document );
		_html = jQuery( 'html' );
		_body = jQuery( 'body' );

		_init_boxes();
		_init_tabs();

		if ( ! _body.hasClass( 'no-auto-init' ) ) {
			/**
			 * Do the auto-initialization stuff after a short delay, so other
			 * scripts can run first.
			 */
			window.setTimeout(function() {
				wpmUi.upgrade_multiselect();
				wpmUi.upgrade_tooltips();
			}, 20);
		}

		wpmUi.binary = new wpmUi.WpmUiBinary();
	}

	/**
	 * Returns the modal overlay object
	 *
	 * @since  1.1.0
	 * @private
	 */
	wpmUi._modal_overlay = function() {
		if ( null === _modal_overlay ) {
			_modal_overlay = jQuery( '<div></div>' )
				.addClass( 'wpmui-overlay' )
				.appendTo( _body );
		}
		return _modal_overlay;
	};

	/**
	 * Shows a modal background layer
	 *
	 * @since  1.0.0
	 * @param  string the_class CSS class added to the overlay.
	 * @param  string html_classes Additional CSS classes added to the HTML tag.
	 * @private
	 */
	wpmUi._make_modal = function( the_class, html_classes ) {
		var overlay = wpmUi._modal_overlay();

		overlay.removeClass().addClass( 'wpmui-overlay' );
		if ( the_class ) {
			overlay.addClass( the_class );
		}

		_body.addClass( 'wpmui-has-overlay' );
		_html.addClass( 'wpmui-no-scroll' );
		if ( html_classes ) {
			_html.addClass( html_classes );
		}

		return overlay;
	};

	/**
	 * Closes the modal background layer again.
	 *
	 * @since  1.0.0
	 * @param  string html_classes Additional CSS classes to remove from HTML tag.
	 * @private
	 */
	wpmUi._close_modal = function( html_classes ) {
		_body.removeClass( 'wpmui-has-overlay' );
		_html.removeClass( 'wpmui-no-scroll' );
		if ( html_classes) {
			_html.removeClass( html_classes );
		}
	};

	/**
	 * Initialize the WordPress-ish accordeon boxes:
	 * Open or close boxes when user clicks the toggle icon.
	 *
	 * @since  1.0.0
	 */
	function _init_boxes() {
		// Toggle the box state (open/closed)
		var toggle_box = function toggle_box( ev ) {
			var box = jQuery( this ).closest( '.wpmui-box' );
			ev.preventDefault();

			// Don't toggle the box if it is static.
			if ( box.hasClass( 'static' ) ) { return false; }

			box.toggleClass( 'closed' );
			return false;
		};

		_body.on( 'click', '.wpmui-box > h3', toggle_box );
		_body.on( 'click', '.wpmui-box > h3 > .toggle', toggle_box );
	}

	/**
	 * Initialize the WordPress-ish tab navigation:
	 * Change the tab on click.
	 *
	 * @since  1.0.0
	 */
	function _init_tabs() {
		// Toggle the box state (open/closed)
		var activate_tab = function activate_tab( ev ) {
			var tab = jQuery( this ),
				all_tabs = tab.closest( '.wpmui-tabs' ),
				content = all_tabs.next( '.wpmui-tab-contents' ),
				active = all_tabs.find( '.active.tab' ),
				sel_tab = tab.attr( 'href' ),
				sel_active = active.attr( 'href' ),
				content_tab = content.find( sel_tab ),
				content_active = content.find( sel_active );

			// Close previous tab.
			if ( ! tab.hasClass( 'active' ) ) {
				active.removeClass( 'active' );
				content_active.removeClass( 'active' );
			}

			// Open selected tab.
			tab.addClass( 'active' );
			content_tab.addClass( 'active' );

			ev.preventDefault();
			return false;
		};

		_body.on( 'click', '.wpmui-tabs .tab', activate_tab );
	}

	// Initialize the object.
	jQuery(function() {
		_init();
	});

}( window.wpmUi = window.wpmUi || {} ));

