/*!
 * WPMU Dev UI library
 * (Philipp Stracker for WPMU Dev)
 *
 * This module provides the WpmUiAjaxData object that is used to serialize whole
 * forms and submit then via Ajax. Even file uploads are possibly with this
 * object.
 *
 * @version  1.0.0
 * @author   Philipp Stracker for WPMU Dev
 * @requires jQuery
 */
/*global jQuery:false */
/*global window:false */
/*global document:false */
/*global XMLHttpRequest:false */

(function( wpmUi ) {

	/*===============================*\
	===================================
	==                               ==
	==           AJAX-DATA           ==
	==                               ==
	===================================
	\*===============================*/


	/**
	 * Form Data object that is used to load or submit data via ajax.
	 *
	 * @type   WpmUiAjaxData
	 * @since  1.0.0
	 */
	wpmUi.WpmUiAjaxData = function( _ajaxurl, _default_action ) {

		/**
		 * Backreference to the WpmUiAjaxData object.
		 *
		 * @since  1.0.0
		 * @private
		 */
		var _me = this;

		/**
		 * An invisible iframe with name "wpmui_void", created by this object.
		 *
		 * @type   jQuery object
		 * @since  1.0.0
		 * @private
		 */
		var _void_frame = null;

		/**
		 * Data that is sent to the server.
		 *
		 * @type   Object
		 * @since  1.0.0
		 * @private
		 */
		var _data = {};

		/**
		 * Progress handler during upload/download.
		 * Signature function( progress )
		 *     - progress .. Percentage complete or "-1" for "unknown"
		 *
		 * @type  Callback function.
		 * @since  1.0.0
		 * @private
		 */
		var _onprogress = null;

		/**
		 * Receives the server response after ajax call is finished.
		 * Signature: function( response, okay, xhr )
		 *     - response .. Data received from the server.
		 *     - okay .. bool; false means an error occured.
		 *     - xhr .. XMLHttpRequest object.
		 *
		 * @type  Callback function.
		 * @since  1.0.0
		 * @private
		 */
		var _ondone = null;

		/**
		 * Feature detection: HTML5 upload/download progress events.
		 *
		 * @type  bool
		 * @since  1.0.0
		 * @private
		 */
		var _support_progress = false;

		/**
		 * Feature detection: HTML5 file API.
		 *
		 * @type  bool
		 * @since  1.0.0
		 * @private
		 */
		var _support_file_api = false;

		/**
		 * Feature detection: HTML5 FormData object.
		 *
		 * @type  bool
		 * @since  1.0.0
		 * @private
		 */
		var _support_form_data = false;


		// ==============================
		// == Public functions ==========


		/**
		 * Define the data that is sent to the server.
		 *
		 * @since  1.0.0
		 * @param  mixed Data that is sent to the server. Either:
		 *                - Normal javascript object interpreted as key/value pairs.
		 *                - A jQuery object of the whole form element
		 *                - An URL-encoded string ("key=val&key2=val2")
		 */
		this.data = function data( obj ) {
			_data = obj;
			return _me;
		};

		/**
		 * Returns an ajax-compatible version of the data object passed in.
		 * This data object can be any of the values that is recognized by the
		 * data() method above.
		 *
		 * @since  1.0.7
		 * @param  mixed obj
		 * @return Object
		 */
		this.extract_data = function extract_data( obj ) {
			_data = obj;
			return _get_data( undefined, false );
		};

		/**
		 * Define the upload/download progress callback.
		 *
		 * @since  1.0.0
		 * @param  function callback Progress handler.
		 */
		this.onprogress = function onprogress( callback ) {
			_onprogress = callback;
			return _me;
		};

		/**
		 * Callback that receives the server response of the ajax request.
		 *
		 * @since  1.0.0
		 * @param  function callback
		 */
		this.ondone = function ondone( callback ) {
			_ondone = callback;
			return _me;
		};

		/**
		 * Reset all configurations.
		 *
		 * @since  1.0.0
		 */
		this.reset = function reset() {
			_data = {};
			_onprogress = null;
			_ondone = null;
			return _me;
		};

		/**
		 * Submit the specified data to the ajaxurl and pass the response to a
		 * callback function. Server response can be any string.
		 *
		 * @since  1.0.0
		 * @param  action string The ajax action to execute.
		 */
		this.load_text = function load_text( action ) {
			action = action || _default_action;
			_load( action, 'text' );

			return _me;
		};

		/**
		 * Submit the specified data to the ajaxurl and pass the response to a
		 * callback function. Server response must be a valid JSON string!
		 *
		 * @since  1.0.0
		 * @param  action string The ajax action to execute.
		 */
		this.load_json = function load_json( action ) {
			action = action || _default_action;
			_load( action, 'json' );

			return _me;
		};

		/**
		 * Submit the specified data to the ajaxurl and let the browser process
		 * the response.
		 * Use this function for example when the server returns a file that
		 * should be downloaded.
		 *
		 * @since  1.0.0
		 * @param  string target Optional. The frame to target.
		 * @param  string action Optional. The ajax action to execute.
		 */
		this.load_http = function load_http( target, action ) {
			target = target || 'wpmui_void';
			action = action || _default_action;
			_form_submit( action, target );

			return _me;
		};


		// ==============================
		// == Private functions =========


		/**
		 * Initialize the formdata object
		 *
		 * @since  1.0.0
		 * @private
		 */
		function _init() {
			// Initialize missing Ajax-URL: Use WordPress ajaxurl if possible.
			if ( ! _ajaxurl && typeof window.ajaxurl === 'string') {
				_ajaxurl = window.ajaxurl;
			}

			// Initialize an invisible iframe for file downloads.
			_void_frame = jQuery( 'body' ).find( '#wpmui_void' );

			if ( ! _void_frame.length ) {
				/**
				 * Create the invisible iframe.
				 * Usage: <form target="wpmui_void">...</form>
				 */
				_void_frame = jQuery('<iframe></iframe>')
					.attr( 'name', 'wpmui_void' )
					.attr( 'id', 'wpmui_void' )
					.css({
						'width': 1,
						'height': 1,
						'display': 'none',
						'visibility': 'hidden',
						'position': 'absolute',
						'left': -1000,
						'top': -1000
					})
					.hide()
					.appendTo( jQuery( 'body' ) );
			}

			// Find out what HTML5 feature we can use.
			_what_is_supported();

			// Reset all configurations.
			_me.reset();
		}

		/**
		 * Feature detection
		 *
		 * @since  1.0.0
		 * @private
		 * @return bool
		 */
		function _what_is_supported() {
			var inp = document.createElement( 'INPUT' );
			var xhr = new XMLHttpRequest();

			// HTML 5 files API
			inp.type = 'file';
			_support_file_api = 'files' in inp;

			// HTML5 ajax upload "progress" events
			_support_progress = !! (xhr && ( 'upload' in xhr ) && ( 'onprogress' in xhr.upload ));

			// HTML5 FormData object
			_support_form_data = !! window.FormData;
		}

		/**
		 * Creates the XMLHttpReqest object used for the jQuery ajax calls.
		 *
		 * @since  1.0.0
		 * @private
		 * @return XMLHttpRequest
		 */
		function _create_xhr() {
			var xhr = new window.XMLHttpRequest();

			if ( _support_progress ) {
				// Upload progress
				xhr.upload.addEventListener( "progress", function( evt ) {
					if ( evt.lengthComputable ) {
						var percentComplete = evt.loaded / evt.total;
						_call_progress( percentComplete );
					} else {
						_call_progress( -1 );
					}
				}, false );

				// Download progress
				xhr.addEventListener( "progress", function( evt ) {
					if ( evt.lengthComputable ) {
						var percentComplete = evt.loaded / evt.total;
						_call_progress( percentComplete );
					} else {
						_call_progress( -1 );
					}
				}, false );
			}

			return xhr;
		}

		/**
		 * Calls the "onprogress" callback
		 *
		 * @since  1.0.0
		 * @private
		 * @param  float value Percentage complete / -1 for "unknown"
		 */
		function _call_progress( value ) {
			if ( _support_progress && typeof _onprogress === 'function' ) {
				_onprogress( value );
			}
		}

		/**
		 * Calls the "onprogress" callback
		 *
		 * @since  1.0.0
		 * @private
		 * @param  response mixed The parsed server response.
		 * @param  okay bool False means there was an error.
		 * @param  xhr XMLHttpRequest
		 */
		function _call_done( response, okay, xhr ) {
			_call_progress( 100 );
			if ( typeof _ondone === 'function' ) {
				_ondone( response, okay, xhr );
			}
		}

		/**
		 * Returns data object containing the data to submit.
		 * The data object is either a plain javascript object or a FormData
		 * object; this depends on the parameter "use_formdata" and browser-
		 * support for FormData.
		 *
		 * @since  1.0.0
		 * @private
		 * @param  string action
		 * @param  boolean use_formdata If set to true then we return FormData
		 *                when the browser supports it. If support is missing or
		 *                use_formdata is not true then the response is an object.
		 * @return Object or FormData
		 */
		function _get_data( action, use_formdata ) {
			var data = {};
			use_formdata = use_formdata && _support_form_data;

			if ( _data instanceof jQuery ) {

				// ===== CONVERT <form> to data object.

				// WP-Editor needs some special attention first:
				_data.find( '.wp-editor-area' ).each(function() {
					var id = jQuery( this ).attr( 'id' ),
						sel = '#wp-' + id + '-wrap',
						container = jQuery( sel ),
						editor = window.tinyMCE.get( id );

					if ( editor && container.hasClass( 'tmce-active' ) ) {
						editor.save(); // Update the textarea content.
					}
				});

				if ( use_formdata ) {
					data = new window.FormData( _data[0] );
				} else {
					data = {};

					// Convert a jQuery object to data object.

					// ----- Start: Convert FORM to OBJECT
					// http://stackoverflow.com/a/8407771/313501
					var push_counters = {},
						patterns = {
							"validate": /^[a-zA-Z_][a-zA-Z0-9_-]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
							"key":      /[a-zA-Z0-9_-]+|(?=\[\])/g,
							"push":     /^$/,
							"fixed":    /^\d+$/,
							"named":    /^[a-zA-Z0-9_-]+$/
						};

					var _build = function( base, key, value ) {
						base[key] = value;
						return base;
					};

					var _push_counter = function( key ) {
						if ( push_counters[key] === undefined ) {
							push_counters[key] = 0;
						}
						return push_counters[key]++;
					};

					jQuery.each( _data.serializeArray(), function() {
						// skip invalid keys
						if ( ! patterns.validate.test( this.name ) ) { return; }

						var k,
							keys = this.name.match(patterns.key),
							merge = this.value,
							reverse_key = this.name;

						while ( ( k = keys.pop() ) !== undefined ) {

							// adjust reverse_key
							reverse_key = reverse_key.replace( new RegExp( "\\[" + k + "\\]$" ), '' );

							// push
							if ( k.match( patterns.push ) ) {
								merge = _build( [], _push_counter( reverse_key ), merge );
							}

							// fixed
							else if ( k.match( patterns.fixed ) ) {
								merge = _build([], k, merge);
							}

							// named
							else if ( k.match( patterns.named ) ) {
								merge = _build( {}, k, merge );
							}
						}

						data = jQuery.extend( true, data, merge );
					});

					// ----- End: Convert FORM to OBJECT

					// Add file fields
					_data.find( 'input[type=file]' ).each( function() {
						var me = jQuery( this ),
							name = me.attr( 'name' ),
							inp = me.clone( true )[0];
						data[':files'] = data[':files'] || {};
						data[':files'][name] = inp;
					});
				}
			} else if ( typeof _data === 'string' ) {

				// ===== PARSE STRING to data object.

				var temp = _data.split( '&' ).map( function (kv) {
					return kv.split( '=', 2 );
				});

				data = ( use_formdata ? new window.FormData() : {} );
				for ( var ind in temp ) {
					var name = decodeURI( temp[ind][0] ),
						val = decodeURI( temp[ind][1] );

					if ( use_formdata ) {
						data.append( name, val );
					} else {
						if ( undefined !== data[name]  ) {
							if ( 'object' !== typeof data[name] ) {
								data[name] = [ data[name] ];
							}
							data[name].push( val );
						} else {
							data[name] = val;
						}
					}
				}
			} else if ( typeof _data === 'object' ) {

				// ===== USE OBJECT to populate data object.

				if ( use_formdata ) {
					data = new window.FormData();
					for ( var data_key in _data ) {
						if ( _data.hasOwnProperty( data_key ) ) {
							data.append( data_key, _data[data_key] );
						}
					}
				} else {
					data = jQuery.extend( {}, _data );
				}
			}

			if ( undefined !== action ) {
				if ( data instanceof window.FormData ) {
					data.append('action', action);
				} else {
					data.action = action;
				}
			}

			return data;
		}

		/**
		 * Submit the data.
		 *
		 * @since  1.0.0
		 * @private
		 * @param  string action The ajax action to execute.
		 */
		function _load( action, type ) {
			var data = _get_data( action, true ),
				ajax_args = {},
				response = null,
				okay = false;

			if ( type !== 'json' ) { type = 'text'; }

			_call_progress( -1 );

			ajax_args = {
				url: _ajaxurl,
				type: 'POST',
				dataType: 'html',
				data: data,
				xhr: _create_xhr,
				success: function( resp, status, xhr ) {
					okay = true;
					response = resp;
					if ( 'json' === type ) {
						try {
							response = jQuery.parseJSON( resp );
						} catch(ignore) {
							response = { 'status': 'ERR', 'data': resp };
						}
					}
				},
				error: function( xhr, status, error ) {
					okay = false;
					response = error;
				},
				complete: function( xhr, status ) {
					if ( response instanceof Object && 'ERR' === response.status ) {
						okay = false;
					}
					_call_done( response, okay, xhr );
				}
			};

			if ( data instanceof window.FormData ) {
				ajax_args.processData = false;  // tell jQuery not to process the data
				ajax_args.contentType = false;  // tell jQuery not to set contentType
			}

			jQuery.ajax(ajax_args);
		}

		/**
		 * Send data via a normal form submit targeted at the invisible iframe.
		 *
		 * @since  1.0.0
		 * @private
		 * @param  string action The ajax action to execute.
		 * @param  string target The frame to refresh.
		 */
		function _form_submit( action, target ) {
			var data = _get_data( action, false ),
				form = jQuery( '<form></form>' ),
				ajax_action = '';

			// Append all data fields to the form.
			for ( var name in data ) {
				if ( data.hasOwnProperty( name ) ) {
					if ( name === ':files' ) {
						for ( var file in data[name] ) {
							var inp = data[name][file];
							form.append( inp );
						}
					} else if ( name === 'action') {
						ajax_action = name + '=' + data[name].toString();
					} else {
						jQuery('<input type="hidden" />')
							.attr( 'name', name )
							.attr( 'value', data[name] )
							.appendTo( form );
					}
				}
			}

			if ( _ajaxurl.indexOf( '?' ) === -1 ) {
				ajax_action = '?' + ajax_action;
			} else {
				ajax_action = '&' + ajax_action;
			}

			// Set correct form properties.
			form.attr( 'action', _ajaxurl + ajax_action )
				.attr( 'method', 'POST' )
				.attr( 'enctype', 'multipart/form-data' )
				.attr( 'target', target )
				.hide()
				.appendTo( jQuery( 'body' ) );

			// Submit the form.
			form.submit();
		}


		// Initialize the formdata object
		_me = this;
		_init();

	}; /* ** End: WpmUiAjaxData ** */

}( window.wpmUi = window.wpmUi || {} ));