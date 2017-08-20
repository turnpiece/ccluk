;if ( typeof jq == "undefined" ) {
    var jq = jQuery;
}
jQuery( document ).ready( function ( jq ) {
    
    jq( '.bookmark-it' ).on( 'click', function (e) {
        e.preventDefault();
        
        if( jq( this ).hasClass('loading') ) {
            return false;
        }
        
        jq(this).addClass('loading');
        jq(this).find('.fa').addClass('fa-spinner fa-spin');

        var post_id = jq( this ).attr( 'data-post-id' ),
            user_id = jq( this ).attr( 'data-user-id' ),
            user_action = jq( this ).attr( 'data-action' );

        jq.ajax( {
            url: bookmark_it_vars.ajaxurl,
            type: 'post',
            data: {
                action: 'bookmark_it',
                item_id: post_id,
                user_id: user_id,
                user_action: user_action,
                bookmark_it_nonce: bookmark_it_vars.nonce
            },
            success: function ( html ) {
                jq( '.bookmark-it' ).removeClass('loading').find('.fa').removeClass('fa-spinner fa-spin');;

                if( 'add-bookmark' === user_action ) {
                    jq( '.bookmark-it' ).attr('data-action', 'remove-bookmark').addClass('bookmarked').find('.fa').toggleClass('fa-bookmark-o fa-bookmark');
                } else {
                    jq( '.bookmark-it' ).attr('data-action', 'add-bookmark').removeClass('bookmarked').find('.fa').toggleClass('fa-bookmark fa-bookmark-o');
                }
            }
        } );
    } );
} );