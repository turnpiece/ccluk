/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2006, 2014 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// CommonJS
		factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (arguments.length > 1 && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setTime(+t + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {};

		// To prevent the for loop in the first place assign an empty array
		// in case there are no cookies at all. Also prevents odd result when
		// calling $.cookie().
		var cookies = document.cookie ? document.cookie.split('; ') : [];

		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = parts.join('=');

			if (key && key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) === undefined) {
			return false;
		}

		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

}));


( function ( $ ) {

    $( document ).on( 'click', '.button-load-more-posts', function ( event ) {
        event.preventDefault();

        var self = $( this );

        var page = self.data( 'page' ),
            template = self.data( 'template' ),
            href = self.attr( 'href' );

        self.addClass( 'loading' );

        $.get( href, function ( response ) {
            $( '.pagination-below' ).remove();

            if ( template === 'home' ) {
                $( response ).find( '.article-outher' ).each( function () {
                    $( '#content' ).append( $( this ) );
                } );
            } else if ( template === 'search' ) {
                $( response ).find( 'article.hentry' ).each( function () {
                    $( '.search-content-inner' ).append( $( this ) );
                } );
            } else {
                $( response ).find( '.article-outher' ).each( function () {
                    $( '#content' ).append( $( this ) );
                } );
            }

            $( '#content' ).append( $( response ).find( '.pagination-below' ) );
        } );
    } );

    $( document ).on( 'scroll', function () {

        var load_more_posts = $( '.post-infinite-scroll' );

        if ( load_more_posts.length ) {

            var pos = load_more_posts.offset();

            if ( $( window ).scrollTop() + $( window ).height() > pos.top ) {

                if ( !load_more_posts.hasClass( 'loading' ) ) {
                    load_more_posts.trigger( 'click' );
                }
            }
        }

    } );

} )( jQuery );


/**
 * BuddyBoss JavaScript functionality
 *
 * @since    3.0
 * @package  buddyboss
 *
 * ====================================================================
 *
 * 1. jQuery Global
 * 2. Main BuddyBoss Class
 * 3. Inline Plugins
 */


/**
 * 1. jQuery Global
 * ====================================================================
 */
var jq = $ = jQuery;

/**
 * 2. Main BuddyBoss Class
 *
 * This class takes care of BuddyPress additional functionality and
 * provides a global name space for BuddyBoss plugins to communicate
 * through.
 *
 * Event name spacing:
 * $(document).on( "buddyboss:*module*:*event*", myCallBackFunction );
 * $(document).trigger( "buddyboss:*module*:*event*", [a,b,c]/{k:v} );
 * ====================================================================
 * @return {class}
 */
var BuddyBossMain = ( function ( $, window, undefined ) {

    /**
     * Globals/Options
     */
    var _l = {
        $document: $( document ),
        $window: $( window )
    };

    // Controller
    var App = { };

    // Responsive
    var Responsive = { };


    /** --------------------------------------------------------------- */

    /**
     * Application
     */

    // Initialize, runs when script is processed/loaded
    App.init = function () {
        _l.$document.ready( App.domReady );
    }

    // When the DOM is ready (page laoded)
    App.domReady = function () {
        _l.body = $( 'body' );
        Responsive.domReady();
    }

    /** --------------------------------------------------------------- */

    /**
     * BuddyPress Responsive Help
     */
    Responsive.domReady = function () {

        // GLOBALS *
        // ---------
        window.BuddyBoss = window.BuddyBoss || { };

        window.BuddyBoss.is_mobile = null;

        var
            $document = $( document ),
            $window = $( window ),
            is_mobile = false,
            mobile_modified = false,
            $inner = $( '#inner-wrap' ),
            Panels = { },
            inputsEnabled = $( 'body' ).data( 'inputs' ),
            $mobile_nav_wrap,
            $mobile_item_wrap,
            $mobile_item_nav;

        // Detect android stock browser
        // http://stackoverflow.com/a/17961266
        var isAndroid = navigator.userAgent.indexOf( 'Android' ) >= 0;
        var webkitVer = parseInt( ( /WebKit\/([0-9]+)/.exec( navigator.appVersion ) || 0 )[1], 10 ) || void 0; // also match AppleWebKit

        /*------------------------------------------------------------------------------------------------------
         1.0 - Core Functions
         --------------------------------------------------------------------------------------------------------*/

        // get viewport size
        function viewport() {
            var e = window, a = 'inner';
            if ( !( 'innerWidth' in window ) ) {
                a = 'client';
                e = document.documentElement || document.body;
            }
            return { width: e[ a + 'Width' ], height: e[ a + 'Height' ] };
        }
        /**
         * Checks for supported mobile resolutions via media query and
         * maximum window width.
         *
         * @return {boolean} True when screen size is mobile focused
         */
        function check_is_mobile() {
            // The $mobile_check element refers to an empty div#mobile-check we
            // hide or show with media queries. We use this to determine if we're
            // on mobile resolution

//                mobile_css = window.document.getElementById('boss-main-mobile-css'),
//                $mobile_css = $(mobile_css);

            if ( $.cookie( 'switch_mode' ) != 'mobile' ) {
//                    if(($mobile_css.attr('media') != 'all')) {
                if ( ( !translation.only_mobile ) ) {
                    if ( viewport().width <= 1024 ) {
                        $( 'body' ).removeClass( 'is-desktop' ).addClass( 'is-mobile' );
                    } else {
                        $( 'body' ).removeClass( 'is-mobile' ).addClass( 'is-desktop' );
                    }
                }
            }

            is_mobile = BuddyBoss.is_mobile = $( 'body' ).hasClass( 'is-mobile' );

            return is_mobile;
        }

        function render_layout() {

            // If on small screens make sure the main page elements are
            // full width vertically
            if ( is_mobile && ( $inner.height() < $window.height() ) ) {
                $( '#page' ).css( 'min-height', $window.height() - ( $( '#mobile-header' ).height() + $( '#colophon' ).height() ) );
            }

            // Swipe/panel shut area
            if ( is_mobile && $( '#buddyboss-swipe-area' ).length && Panels.state ) {
                $( '#buddyboss-swipe-area' ).css( {
                    left: Panels.state === 'left' ? 240 : 'auto',
                    right: Panels.state === 'right' ? 240 : 'auto',
                    width: $( window ).width() - 240,
                    height: $( window ).outerHeight( true ) + 200
                } );
            }

            // Log out link in left panel
            var $left_logout_link = $( '#wp-admin-bar-logout' ),
                $left_account_panel = $( '#wp-admin-bar-user-actions' ),
                $left_settings_menu = $( '#wp-admin-bar-my-account-settings .ab-submenu' ).first();

            if ( $left_logout_link.length && $left_account_panel.length && $left_settings_menu.length ) {
                // On mobile user's accidentally click the link when it's up
                // top so we move it into the setting menu
                if ( is_mobile ) {
                    $left_logout_link.appendTo( $left_settings_menu );
                }
                // On desktop we move it back to it's original place
                else {
                    $left_logout_link.appendTo( $left_account_panel );
                }
            }
            
            // Runs once, first time we experience a mobile resolution
            if ( is_mobile && !mobile_modified ) {
                mobile_modified = true;
                $mobile_nav_wrap = $( '<div id="mobile-item-nav-wrap" class="mobile-item-nav-container mobile-item-nav-scroll-container">' );
                $mobile_item_wrap = $( '<div class="mobile-item-nav-wrapper">' ).appendTo( $mobile_nav_wrap );
                $mobile_item_nav = $( '<div id="mobile-item-nav" class="mobile-item-nav">' ).appendTo( $mobile_item_wrap );
                $mobile_item_nav.append( $item_nav.html() );

                //$mobile_item_nav.css( 'width', ( $item_nav.find( 'li' ).length * 94 ) );
                $mobile_nav_wrap.insertBefore( $item_nav ).show();
                $( '#mobile-item-nav-wrap, .mobile-item-nav-scroll-container, .mobile-item-nav-container' ).addClass( 'fixed' );
                $item_nav.css( { display: 'none' } );
            }
            // Resized to non-mobile resolution
            else if ( !is_mobile && mobile_modified ) {
                $mobile_nav_wrap.css( { display: 'none' } );
                $item_nav.css( { display: 'block' } );
                $document.trigger( 'menu-close.buddyboss' );
            }
            // Resized back to mobile resolution
            else if ( is_mobile && mobile_modified ) {
                $mobile_nav_wrap.css( {
                    display: 'block'
                } );

                $item_nav.css( { display: 'none' } );
            }


            // Update select drop-downs
            if ( typeof Selects !== 'undefined' ) {
                if ( $.isFunction( Selects.populate_select_label ) ) {
                    Selects.populate_select_label( is_mobile );
                }
            }
        }

        /**
         * Renders the layout, called when the page is loaded and on resize
         *
         * @return {void}
         */
        function do_render()
        {
            check_is_mobile();
            render_layout();
        }

        /*------------------------------------------------------------------------------------------------------
         1.1 - Startup (Binds Events + Conditionals)
         --------------------------------------------------------------------------------------------------------*/

        // Render layout
        do_render();

        // Re-render layout after everything's loaded
        $window.bind( 'load', function () {
            do_render();
        } );

        // Re-render layout on resize
        var throttle;
        $window.resize( function () {
            clearTimeout( throttle );
            throttle = setTimeout( do_render, 150 );
        } );

        function elementInViewport( el ) {
            var top = el.offsetTop;
            var left = el.offsetLeft;
            var width = el.offsetWidth;
            var height = el.offsetHeight;

            while ( el.offsetParent ) {
                el = el.offsetParent;
                top += el.offsetTop;
                left += el.offsetLeft;
            }

            return (
                top < ( window.pageYOffset + window.innerHeight ) &&
                left < ( window.pageXOffset + window.innerWidth ) &&
                ( top + height ) > window.pageYOffset &&
                ( left + width ) > window.pageXOffset
                );
        }

        var $images_out_of_viewport =
            $( '#main-wrap img, .svg-graphic' ).filter( function ( index ) {
            return !elementInViewport( this );
        } );

        $images_out_of_viewport.each( function () {
            this.classList.add( "not-loaded" );
        } );





        /*------------------------------------------------------------------------------------------------------
         2.2 - Responsive Dropdowns
         -------------------------------------------------------------------------------------------------------*/
        if ( typeof Selects !== 'undefined' ) {
            if ( $.isFunction( Selects.init_select ) ) {
                Selects.init_select( is_mobile, inputsEnabled );
            }
        }

        $( window ).on( 'load', function () {
            $( 'body' ).addClass( 'boss-page-loaded' );
        } );

        $( '#mobile-right-panel .menu-item-has-children' ).each( function () {
            $( this ).prepend( '<i class="fa submenu-btn fa-angle-down"></i>' );
        } );

        $( '#mobile-right-panel .submenu-btn' ).on( 'click', function ( e ) {
            e.preventDefault();
            $( this ).toggleClass( 'fa-angle-left fa-angle-down' ).parent().find( '.sub-menu' ).slideToggle();
        } );



        /*------------------------------------------------------------------------------------------------------
         3.0 - Content
         --------------------------------------------------------------------------------------------------------*/

        $( '.comment-form-comment .boss-insert-buttons-show' ).on( 'click', function ( e ) {
            e.preventDefault();

            if ( $( this ).parents( '.comment-form' ).hasClass( 'boss-show-buttons' ) ) {
                setTimeout( function () {
                    $( '#comment' ).css( 'visibility', 'visible' ).attr( 'placeholder', $( '.comment-form-comment .boss-insert-text' ).data( 'placeholder' ) ).focus();
                }, 150 );
            } else {
                $( '#comment' ).removeAttr( 'placeholder' ).css( 'visibility', 'hidden' );
                ;
            }

            $( this ).parents( '.comment-form' ).toggleClass( 'boss-show-buttons' );
        } );

        $( '.comment-form-comment .boss-insert-image' ).on( 'click', function ( e ) {
            e.preventDefault();
            $( '#attachment' ).trigger( 'click' );
        } );

        $( '#attachment' ).change( function () {
            String.prototype.filename = function ( extension ) {
                var s = this.replace( /\\/g, '/' );
                s = s.substring( s.lastIndexOf( '/' ) + 1 );
                // With extension
                return s;
                // Without extension
                // return extension ? s.replace( /[?#].+$/, '' ) : s.split( '.' )[0];
            };

            var fileName = $( this ).val().filename();
            $( '#comments' ).find( '.attached-image' ).remove();
            $( '#comment' ).after( '<p class="attached-image"><i class="fa fa-picture-o" aria-hidden="true"></i>' + fileName + '</p>' );
        } );

        // Turning On HTML5 Native Form Validation For WordPress Comments.
        $( '#commentform, #attachmentForm' ).removeAttr( 'novalidate' );

        $( '.comment-form-comment .boss-insert-text' ).on( 'click', function ( e ) {
            e.preventDefault();
            self = $( this );
            $( '#attachmentForm' ).removeClass( 'boss-show-buttons' );

            setTimeout( function () {
                $( '#comment' ).css( 'visibility', 'visible' ).attr( 'placeholder', self.data( 'placeholder' ) ).focus();
            }, 150 );
        } );

        /*------------------------------------------------------------------------------------------------------
         3.2 - Search Input Field
         --------------------------------------------------------------------------------------------------------*/
        $( 'div.bbp-search-form form, form#bbp-search-form' ).append( '<a href="#" id="clear-input"> </a>' );
        $( 'a#clear-input' ).on( 'click', function () {
            jQuery( "div.bbp-search-form form input[type=text], form#bbp-search-form input[type=text]" ).val( "" );
        } );

        function searchWidthMobile() {
            if ( is_mobile ) {
                var $mobile_search = $( '#mobile-header .searchform' );
                if ( $mobile_search.length ) {
                    $mobile_search.focusin( function () {
                        $mobile_search.addClass( 'animate-form' );
                    } );

                    $mobile_search.focusout( function () {
                        $mobile_search.removeClass( 'animate-form' );
                    } );
                }
            }
        }

        searchWidthMobile();


        /*------------------------------------------------------------------------------------------------------
         3.5 - Spinners
         --------------------------------------------------------------------------------------------------------*/
        function initSpinner() {
            $( '.generic-button:not(.pending)' ).on( 'click', function () {
                $link = $( this ).find( 'a' );
                if ( !$link.find( 'i' ).length && !$link.hasClass( 'pending' ) ) {
                    $link.append( '<i class="fa fa-spinner fa-spin"></i>' );
                }
            } );
        }

        initSpinner();

        $( document ).ajaxComplete( function () {
            setTimeout( function () {
                initSpinner();
            }, 500 );

            setTimeout( function () {
                initSpinner();
            }, 1500 );
        } );


        /*--------------------------------------------------------------------------------------------------------
         3.7 - Infinite Scroll
         --------------------------------------------------------------------------------------------------------*/

        if ( $( '#masthead' ).data( 'infinite' ) == 'on' ) {
            var is_activity_loading = false;//We'll use this variable to make sure we don't send the request again and again.

            jq( document ).on( 'scroll', function () {
                //Find the visible "load more" button.
                //since BP does not remove the "load more" button, we need to find the last one that is visible.
                var load_more_btn = jq( ".load-more:visible" );
                //If there is no visible "load more" button, we've reached the last page of the activity stream.
                if ( !load_more_btn.get( 0 ) )
                    return;

                //Find the offset of the button.
                var pos = load_more_btn.offset();

                //If the window height+scrollTop is greater than the top offset of the "load more" button, we have scrolled to the button's position. Let us load more activity.
                //            console.log(jq(window).scrollTop() + '  '+ jq(window).height() + ' '+ pos.top);

                if ( jq( window ).scrollTop() + jq( window ).height() > pos.top ) {

                    load_more_activity();
                }

            } );

            /**
             * This routine loads more activity.
             * We call it whenever we reach the bottom of the activity listing.
             *
             */
            function load_more_activity() {

                //Check if activity is loading, which means another request is already doing this.
                //If yes, just return and let the other request handle it.
                if ( is_activity_loading )
                    return false;

                //So, it is a new request, let us set the var to true.
                is_activity_loading = true;

                //Add loading class to "load more" button.
                //Theme authors may need to change the selector if their theme uses a different id for the content container.
                //This is designed to work with the structure of bp-default/derivative themes.
                //Change #content to whatever you have named the content container in your theme.
                jq( "#content li.load-more" ).addClass( 'loading' );


                if ( null == jq.cookie( 'bp-activity-oldestpage' ) )
                    jq.cookie( 'bp-activity-oldestpage', 1, {
                        path: '/'
                    } );

                var oldest_page = ( jq.cookie( 'bp-activity-oldestpage' ) * 1 ) + 1;

                //Send the ajax request.
                jq.post( ajaxurl, {
                    action: 'activity_get_older_updates',
                    'cookie': encodeURIComponent( document.cookie ),
                    'page': oldest_page
                },
                function ( response )
                {
                    jq( ".load-more" ).hide();//Hide any "load more" button.
                    jq( "#content li.load-more" ).removeClass( 'loading' );//Theme authors, you may need to change #content to the id of your container here, too.

                    //Update cookie...
                    jq.cookie( 'bp-activity-oldestpage', oldest_page, {
                        path: '/'
                    } );

                    //and append the response.
                    jq( "#content ul.activity-list" ).append( response.contents );

                    //Since the request is complete, let us reset is_activity_loading to false, so we'll be ready to run the routine again.

                    is_activity_loading = false;
                }, 'json' );

                return false;

            }
        }

        /*--------------------------------------------------------------------------------------------------------
         3.9 - Search
         --------------------------------------------------------------------------------------------------------*/

        var $search_form = $( '#header-search' ).find( 'form' );

        $( '#search-open' ).on( 'click', function ( e ) {
            e.preventDefault();
            $search_form.fadeIn();
            setTimeout( function () {
                $search_form.find( '#s' ).focus();
            }, 301 );
        } );

        $( '#search-close' ).on( 'click', function ( e ) {
            e.preventDefault();
            $search_form.fadeOut();
        } );

        $( document ).on( 'click', function ( e )
        {
            var container = $( "#header-search" );

            if ( !container.is( e.target ) // if the target of the click isn't the container...
                && container.has( e.target ).length === 0 ) // ... nor a descendant of the container
            {
                $search_form.fadeOut();
            }
        } );

        function search_width() {
            var buttons_width = 0;

            $( '#header-search' ).nextAll( 'div, a' ).each( function () {
                buttons_width = buttons_width + $( this ).width();
            } );

            $search_form.width( $( '.header-wrapper' ).width() - 180 - buttons_width );
        }

        search_width();
        $window.resize( function () {
//            setTimeout(function(){
            search_width();
//            }, 10);
        } );


        /*--------------------------------------------------------------------------------------------------------
         3.9 - Sidebar
         --------------------------------------------------------------------------------------------------------*/
        if ( !is_mobile ) {
            $( '#trigger-sidebar' ).on( 'click', function ( e ) {
                e.preventDefault();
                $( 'body' ).toggleClass( 'bb-sidebar-on' );
            } );
        }

        /*--------------------------------------------------------------------------------------------------------
         3.14 - To Top Button
         --------------------------------------------------------------------------------------------------------*/
        //Scroll Effect
        $( '.to-top' ).bind( 'click', function ( event ) {

            event.preventDefault();

            //, 'easeInOutExpo'
            $( 'html, body' ).stop().animate( {
                scrollTop: "0px"
            }, 500 );

        } );

        // Sticky Header
        var topSpace = 0;
        if ( $( '#wpadminbar' ).is( ':visible' ) ) {
            topSpace = 32;
        }

        $( '.sticky-header #masthead' ).sticky( { topSpacing: topSpace } );

        /*--------------------------------------------------------------------------------------------------------
         3.15 - Friends Lists
         --------------------------------------------------------------------------------------------------------*/
        $document.ajaxComplete( function () {
            $( 'ul.horiz-gallery h5, ul#group-admins h5, ul#group-mods h5' ).each( function () {
                $( this ).css( {
                    left: -$( this ).width() / 2 + 25
                } );
            } );
        } );

        $( '.trigger-filter' ).on( 'click', function () {
            $( this ).next().fadeToggle( 200 );
            $( this ).toggleClass( 'notactive active' );
        } );



        /*--------------------------------------------------------------------------------------------------------
         3.25 - Better Radios and Checkboxes Styling
         --------------------------------------------------------------------------------------------------------*/
        function initCheckboxes() {
            if ( !inputsEnabled ) {
                //only few buddypress and bbpress related fields
                $( '#frm_buddyboss-media-tag-friends input[type="checkbox"], #buddypress table.notifications input, #send_message_form input[type="checkbox"], #profile-edit-form input[type="checkbox"],  #profile-edit-form input[type="radio"], #message-threads input, #settings-form input[type="radio"], #create-group-form input[type="radio"], #create-group-form input[type="checkbox"], #invite-list input[type="checkbox"], #group-settings-form input[type="radio"], #group-settings-form input[type="checkbox"], #new-post input[type="checkbox"], .bbp-form input[type="checkbox"], .bbp-form .input[type="radio"], .register-section .input[type="radio"], .register-section input[type="checkbox"], .message-check, #select-all-messages, #siteLoginBox input[type="checkbox"], #siteRegisterBox input[type="checkbox"]' ).each( function () {
                    var $this = $( this );
                    $this.addClass( 'styled' );
                    if ( $this.next( "label" ).length == 0 && $this.next( "strong" ).length == 0 ) {
                        $this.after( '<strong></strong>' );
                    }
                } );
            } else {
                //all fields
                $( 'input[type="checkbox"], input[type="radio"]' ).each( function () {
                    var $this = $( this );
                    if ( $this.val() == 'gf_other_choice' ) {
                        $this.addClass( 'styled' );
                        $this.next().wrap( '<strong class="other-option"></strong>' );
                    } else {
                        if ( !$this.parents( '#bp-group-documents-form' ).length ) {
                            $this.addClass( 'styled' );
                            if ( $this.next( "label" ).length == 0 && $this.next( "strong" ).length == 0 ) {
                                $this.after( '<strong></strong>' );
                            }
                        }
                    }
                } );
            }
        }

        initCheckboxes();

        $( document ).ajaxSuccess( function () {
            initCheckboxes();
        });


    }


    /** --------------------------------------------------------------- */



    // Boot er' up
    jQuery( document ).ready( function () {
        App.init();
    } );

}( jQuery, window ) );