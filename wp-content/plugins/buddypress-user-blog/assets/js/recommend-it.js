;( function ( jq ) {

    'use strict';

    jq( document ).on( 'click', '.sl-button', function () {
        var button = jq( this );
        var post_id = button.attr( 'data-post-id' );
        var security = button.attr( 'data-nonce' );
        var iscomment = button.attr( 'data-iscomment' );
        var allbuttons;

        if ( iscomment === '1' ) { /* Comments can have same id */
            allbuttons = jq( '.sl-comment-button-' + post_id );
        } else {
            allbuttons = jq( '.sl-button-' + post_id );
        }

        // When the overlay login is disabele, redirect user to the login page
        if ( !recommendPost.is_user_logged_in ) {
            window.location.href = button.attr('href');
        }

        if ( post_id !== '' ) {

            jq.ajax( {
                type: 'POST',
                url: recommendPost.ajaxurl,
                data: {
                    action: 'process_simple_like',
                    post_id: post_id,
                    nonce: security,
                    is_comment: iscomment
                },
                beforeSend: function () {
                    button.find( 'i' ).addClass( 'fa-spinner fa-spin' );
                },
                success: function ( response ) {
                    //console.log( response );
                    var icon = response.icon;
                    var count = response.count;
                    allbuttons.html( icon + count );

                    if ( response.status === 'unliked' ) {
                        var like_text = recommendPost.like;
                        allbuttons.prop( 'title', like_text );
                        allbuttons.removeClass( 'liked' );
                    } else {
                        var unlike_text = recommendPost.unlike;
                        allbuttons.prop( 'title', unlike_text );
                        allbuttons.addClass( 'liked' );
                    }

                    button.find( 'i' ).removeClass( 'fa-spinner fa-spin' );
                }
            } );

        }

        return false;
    } );

} )( jQuery );