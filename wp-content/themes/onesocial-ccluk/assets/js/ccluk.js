/**
 * Main BuddyBoss Class
 *
 * Opens and closes search form etc.
 *
 * ====================================================================
 * @return {class}
 */

(function($) {

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
    $(App.domReady);
    };

    // When the DOM is ready (page laoded)
    App.domReady = function () {
        Responsive.domReady();
    }

    /** --------------------------------------------------------------- */

    /**
     * BuddyPress Responsive Help
     */
    Responsive.domReady = function () {

        var $window = $( window );
        var $document = $( document );
        var $inner = $( '#inner-wrap' );

        var is_mobile = false;
        var mobile_modified = false;

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

            if ( viewport().width <= 1024 ) {
                $( 'body' ).removeClass( 'is-desktop' ).addClass( 'is-mobile' );
            } else {
                $( 'body' ).removeClass( 'is-mobile' ).addClass( 'is-desktop' );
            }

            is_mobile = $( 'body' ).hasClass( 'is-mobile' );
        }

        function render_layout() {

            // If on small screens make sure the main page elements are
            // full width vertically
            if ( is_mobile && ( $inner.height() < $window.height() ) ) {
                $( '#page' ).css( 'min-height', $window.height() - ( $( '#mobile-header' ).height() + $( '#colophon' ).height() ) );
            }
            
            // Runs once, first time we experience a mobile resolution
            if ( is_mobile && !mobile_modified ) {
                mobile_modified = true;
            }
            // Resized to non-mobile resolution
            else if ( !is_mobile && mobile_modified ) {
                //$mobile_nav_wrap.css( { display: 'none' } );
                $document.trigger( 'menu-close.buddyboss' );
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
        $window.on( 'load', function () {
            do_render();
        } );

        // Re-render layout on resize
        var throttle;
        $window.on( 'resize', function () {
            clearTimeout( throttle );
            throttle = setTimeout( do_render, 150 );
        } );

        $window.on( 'load', function () {
            $( 'body' ).addClass( 'boss-page-loaded' );
        } );

        /*--------------------------------------------------------------------------------------------------------
        3.9 - Search
        --------------------------------------------------------------------------------------------------------*/

        var $search_form = $( '#header-search' ).find( 'form' );

        $( '#search-open' ).on( 'click', function ( e ) {
            e.preventDefault();
            $search_form.fadeIn();
            setTimeout( function () {
                $search_form.find( '#s' ).trigger('focus');
            }, 301 );
        } );

        $( '#search-close' ).on( 'click', function ( e ) {
            e.preventDefault();
            $search_form.fadeOut();
        } );

        $document.on( 'click', function ( e )
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

            $search_form.width( $( '.header-wrapper' ).width() - 320 - buttons_width );
        }

        search_width();
        $window.on( 'resize', function () {
            search_width();
        } );


        /*--------------------------------------------------------------------------------------------------------
        3.14 - To Top Button
        --------------------------------------------------------------------------------------------------------*/
        //Scroll Effect
        $( '.to-top' ).on( 'click', function ( event ) {

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
    }


    /** --------------------------------------------------------------- */

    // Boot 'er up
    App.init();
  

    var isTouch = !!( 'ontouchstart' in window ),
        TorC = isTouch ? 'touchstart' : 'click';

    $( '#main-nav' ).on( TorC, function ( e ) {
        e.preventDefault();

        var $body = $( 'body' ),
            $page = $( '#main-wrap' ),
            transitionEndNav = 'transitionend webkitTransitionEnd otransitionend MSTransitionEnd';

        $( '#mobile-right-panel' ).css( { 'opacity': 1 } );

        $page.on( transitionEndNav, function () {
            if ( !$( 'body' ).hasClass( 'menu-visible-right' ) ) {
                $( '#mobile-right-panel' ).removeAttr( 'style' );
                $page.off( transitionEndNav );
            }
        } );

        /* When the toggle menu link is clicked, animation starts */
        $body.toggleClass( 'menu-visible-right' );
    } );

    $( document ).on( 'ready', function () {
        $( '.bb-overlay' ).on( 'click', function () {
            if ( $( 'body' ).hasClass( 'menu-visible-right' ) ) {
                $( '#main-nav' ).trigger( TorC );
            }
        } );

    } );

} )( jQuery );