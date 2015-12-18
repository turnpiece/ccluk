/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

/*--------------------------------------------------------------
>>> TABLE OF CONTENTS:
----------------------------------------------------------------
1.0 Header
2.0 Home
	2.1 Home posts
	2.2 Secondary Content
	2.3 Paralax
	2.4 Slider
3.0 Footer
4.0 Blog
5.0 Colors Sitewide
--------------------------------------------------------------*/

(function ($) {

/*--------------------------------------------------------------
1.0 Header
--------------------------------------------------------------*/
	// Site title
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	} );

	// Site description
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
		} );
	} );

	// Phone number
	wp.customize( 'politics-header-phone', function( value ) {
		value.bind( function( to ) {
			$( '.header-contact .header-phone a span' ).text( to );
		} );
	} );

	// Location
	wp.customize( 'politics-header-address', function( value ) {
		value.bind( function( to ) {
			$( '.header-contact .header-address a span' ).text( to );
		} );
	} );

/*--------------------------------------------------------------
2.0 Home
--------------------------------------------------------------*/
	// Home: Featured Posts Title
	wp.customize( 'home_posts_section_title', function( value ) {
		value.bind( function( to ) {
			$( '.home_posts_titles h2' ).text( to );
		} );
	} );

	// Home: Featured Posts Subtitle
	wp.customize( 'home_posts_section_subtitle', function( value ) {
		value.bind( function( to ) {
			$( '.home_posts_titles p' ).text( to );
		} );
	} );

	// Home: Secondary Content Title
	wp.customize( 'home_secondary_content_title', function( value ) {
		value.bind( function( to ) {
			$( '.home_secondary_content_header h2' ).text( to );
		} );
	} );

	// Home: Secondary Content Subtitle
	wp.customize( 'home_secondary_content_subtitle', function( value ) {
		value.bind( function( to ) {
			$( '.home_secondary_content_header p' ).text( to );
		} );
	} );

/*--------------------------------------------------------------
3.0 Footer
--------------------------------------------------------------*/
	// Footer Copyright
	wp.customize( 'footer_copyright', function( value ) {
		value.bind( function( to ) {
			$( '.copyright-info' ).text( to );
		} );
	} );

/*--------------------------------------------------------------
4.0 Blog
--------------------------------------------------------------*/
	// Blog page title
	wp.customize( 'politics_blog_title', function( value ) {
		value.bind( function( to ) {
			$( 'h1.page-header-title' ).text( to );
		} );
	} );

	// Blog page subtitle
	wp.customize( 'politics_blog_subtitle', function( value ) {
		value.bind( function( to ) {
			$( 'h2.page-header-subtitle' ).text( to );
		} );
	} );

/*--------------------------------------------------------------
5.0 Colors Sitewide
--------------------------------------------------------------*/
	wp.customize( 'color-headers', function( value ) {
		value.bind( function( to ) {
			$( '.content-area h1, .content-area h2, .content-area h3, .content-area h4, .content-area h5, .content-area h6, .page-header h1' ).css( 'color', to );
		} );
	} );

	wp.customize( 'color-content', function( value ) {
		value.bind( function( to ) {
			$( '.entry-content p, .comment_content p' ).css( 'color', to );
		} );
	} );

	wp.customize( 'color-content-link', function( value ) {
		value.bind( function( to ) {
			$( '#content a, .hero-widgets-wrap a' ).css( 'color', to );
		} );
	} );

	wp.customize( 'color-content-top-bar', function( value ) {
		value.bind( function( to ) {
			$( '.mini-header' ).css( 'background-color', to );
		} );
	} );

	wp.customize( 'color-content-body-bg', function( value ) {
		value.bind( function( to ) {
			$( 'body, .home .content-area' ).css( 'background-color', to );
		} );
	} );


} )( jQuery );
