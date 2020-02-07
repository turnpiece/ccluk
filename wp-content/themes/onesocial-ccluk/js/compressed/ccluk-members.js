( function ( $ ) {
    // Get Follow lists

    function ajaxCall( $link, $container, group ) {
        var sort = $link.attr( 'href' );
        $container.empty();
        $container.append( '<div class="fa fa-spin fa-spinner"></div>' );
        $.ajax( {
            url: ajaxfollow.ajaxurl,
            type: 'post',
            data: {
                action: 'buddyboss_get_follow',
                sort: sort,
                group: group,
                followNonce: ajaxfollow.followNonce
            },
            success: function ( html ) {
                $container.empty();
                $container.append( html );
                $container.find( 'ul' ).fadeIn();
                $link.addClass( 'selected' );
            }
        } );
    }

    $( document ).on( 'click', '#following-filter a', function ( event ) {
        event.preventDefault();
        $( '#following-filter a' ).removeClass( 'selected' );
        ajaxCall( $( this ), $( '#following-results' ), 'following' );
    } );

    $( document ).on( 'click', '#followers-filter a', function ( event ) {
        event.preventDefault();
        $( '#followers-filter a' ).removeClass( 'selected' );
        ajaxCall( $( this ), $( '#followers-results' ), 'followers' );
    } );

    // Search results page
    $( 'body' ).on( 'click', '.search_filters .groups a, .search_filters > ul li:first-child a', function () {
        setTimeout( function () {
            groupMembersResult();
        }, 900 );
    } );

    function groupMembersResult() {
        var $containers = $( '.group-members-results' );

        $containers.each( function () {
            var $container = $( this );
            $container.empty();
            $container.append( '<div class="spin">Loading...</div>' );
            $.ajax( {
                url: ajaxmembers.ajaxurl,
                type: 'post',
                data: {
                    action: 'buddyboss_get_group_members',
                    sort: 'recently_active',
                    page: 'dir',
                    id: $container.data( 'group-id' ),
                    count: 4,
                    membersNonce: ajaxmembers.membersNonce
                },
                success: function ( html ) {
                    $container.empty();
                    $container.append( html );
                    $container.find( 'ul' ).fadeIn();
                }
            } );
        } );
    }

} )( jQuery );