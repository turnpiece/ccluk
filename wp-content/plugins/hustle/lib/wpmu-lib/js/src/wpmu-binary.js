/*!
 * WPMU Dev UI library
 * (Philipp Stracker for WPMU Dev)
 *
 * This module provides the WpmUiBinary object that is used to
 * serialize/deserialize data in base64.
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
	==           UTF8-DATA           ==
	==                               ==
	===================================
	\*===============================*/




	/**
	 * Handles conversions of binary <-> text.
	 *
	 * @type   WpmUiBinary
	 * @since  1.0.0
	 */
	wpmUi.WpmUiBinary = function() {
		var map = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

		wpmUi.WpmUiBinary.utf8_encode = function utf8_encode( string ) {
			if ( typeof string !== 'string' ) {
				return string;
			} else {
				string = string.replace(/\r\n/g, "\n");
			}
			var output = '', i = 0, charCode;

			for ( i; i < string.length; i++ ) {
				charCode = string.charCodeAt(i);

				if ( charCode < 128 ) {
					output += String.fromCharCode( charCode );
				} else if ( (charCode > 127) && (charCode < 2048) ) {
					output += String.fromCharCode( (charCode >> 6) | 192 );
					output += String.fromCharCode( (charCode & 63) | 128 );
				} else {
					output += String.fromCharCode( (charCode >> 12) | 224 );
					output += String.fromCharCode( ((charCode >> 6) & 63) | 128 );
					output += String.fromCharCode( (charCode & 63) | 128 );
				}
			}

			return output;
		};

		wpmUi.WpmUiBinary.utf8_decode = function utf8_decode( string ) {
			if ( typeof string !== 'string' ) {
				return string;
			}

			var output = '', i = 0, charCode = 0;

			while ( i < string.length ) {
				charCode = string.charCodeAt(i);

				if ( charCode < 128 ) {
					output += String.fromCharCode( charCode );
					i += 1;
				} else if ( (charCode > 191) && (charCode < 224) ) {
					output += String.fromCharCode(((charCode & 31) << 6) | (string.charCodeAt(i + 1) & 63));
					i += 2;
				} else {
					output += String.fromCharCode(((charCode & 15) << 12) | ((string.charCodeAt(i + 1) & 63) << 6) | (string.charCodeAt(i + 2) & 63));
					i += 3;
				}
			}

			return output;
		};

		/**
		 * Converts a utf-8 string into an base64 encoded string
		 *
		 * @since  1.0.15
		 * @param  string input A string with any encoding.
		 * @return string
		 */
		wpmUi.WpmUiBinary.base64_encode = function base64_encode( input ) {
			if ( typeof input !== 'string' ) {
				return input;
			} else {
				input = wpmUi.WpmUiBinary.utf8_encode( input );
			}
			var output = '', a, b, c, d, e, f, g, i = 0;

			while ( i < input.length ) {
				a = input.charCodeAt(i++);
				b = input.charCodeAt(i++);
				c = input.charCodeAt(i++);
				d = a >> 2;
				e = ((a & 3) << 4) | (b >> 4);
				f = ((b & 15) << 2) | (c >> 6);
				g = c & 63;

				if ( isNaN( b ) ) {
					f = g = 64;
				} else if ( isNaN( c ) ) {
					g = 64;
				}

				output += map.charAt( d ) + map.charAt( e ) + map.charAt( f ) + map.charAt( g );
			}

			return output;
		};

		/**
		 * Converts a base64 string into the original (binary) data
		 *
		 * @since  1.0.15
		 * @param  string input Base 64 encoded text
		 * @return string
		 */
		wpmUi.WpmUiBinary.base64_decode = function base64_decode( input ) {
			if ( typeof input !== 'string' ) {
				return input;
			} else {
				input.replace(/[^A-Za-z0-9\+\/\=]/g, '');
			}
			var output = '', a, b, c, d, e, f, g, i = 0;

			while ( i < input.length ) {
				d = map.indexOf( input.charAt( i++ ) );
				e = map.indexOf( input.charAt( i++ ) );
				f = map.indexOf( input.charAt( i++ ) );
				g = map.indexOf( input.charAt( i++ ) );

				a = (d << 2) | (e >> 4);
				b = ((e & 15) << 4) | (f >> 2);
				c = ((f & 3) << 6) | g;

				output += String.fromCharCode( a );
				if ( f !== 64 ) {
					output += String.fromCharCode( b );
				}
				if ( g !== 64 ) {
					output += String.fromCharCode( c );
				}
			}

			return wpmUi.WpmUiBinary.utf8_decode( output );
		};

	}; /* ** End: WpmUiBinary ** */

}( window.wpmUi = window.wpmUi || {} ));