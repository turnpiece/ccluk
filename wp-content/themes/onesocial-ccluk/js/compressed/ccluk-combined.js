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

      /*------------------------------------------------------------------------------------------------------
       1.1 - Startup (Binds Events + Conditionals)
       --------------------------------------------------------------------------------------------------------*/
      /*
      // Re-render layout on resize
      var throttle;
      $window.on( 'resize', function () {
          clearTimeout( throttle );
          throttle = setTimeout( do_render, 150 );
      } );
      */
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

          $search_form.width( $( '.header-wrapper' ).width() - 180 - buttons_width );
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

      //$( '.sticky-header #masthead' ).sticky( { topSpacing: topSpace } );

  }


  /** --------------------------------------------------------------- */

  // Boot 'er up
  App.init();
  
} )( jQuery );