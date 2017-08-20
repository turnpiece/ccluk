<?php
/**
 * @package WordPress
 * @subpackage BuddyPress User Blog
 *
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

global $paged, $wp_query;

$paged	 = bp_action_variable( 1 );
$paged	 = $paged ? $paged : 1;

$sort = (isset( $_GET[ 'sort' ] )) ? $_GET[ 'sort' ] : 'latest';

if ( 'drafts' == bp_current_action() ) {
    $post_status = 'draft';
}
elseif ( 'pending' == bp_current_action() ) {
    $post_status = 'pending';
} else {
    $post_status = 'publish';
}

$query_args = array(
    'author'            => bp_displayed_user_id(),
    'post_type'         => 'post',
    'post_status'       => $post_status,
    'posts_per_page'    => 20,
    'paged'             => $paged
);

if ( $sort === 'recommended' ) {
    $query_args = array(
        'meta_key'      => '_post_like_count',
        'orderby'       => 'meta_value_num',
        'order'         => 'DESC',
        'paged'         => $paged,
    );
}

query_posts( $query_args );

$create_new_post_page = buddyboss_sap()->option( 'create-new-post' );

if ( have_posts() ):
	?>
	<div id="item-posts" class="sap-blog-post-wrapper">

		<div class="wrap">

			<div class="inner sap-post-container">
				<?php
				while ( have_posts() ): the_post();
					sap_load_template_multiple_times( 'profile-blog-list' );
				endwhile;
				?>

				<div class="pagination-below sap-pagination">
					<?php
					$max_page = 0;

					if ( !$max_page ) {
						$max_page = $wp_query->max_num_pages;
					}

					$nextpage	 = intval( $paged ) + 1;
					$label		 = __( 'Load More', 'bp-user-blog' );

					if ( $nextpage <= $max_page ) {
						$attr = 'data-paged=' . $nextpage . ' data-sort=' . $sort . ' data-max=' . $max_page;
						echo '<a class="sap-load-more-posts button" href="' . next_posts( $max_page, false ) . "\" $attr>" . preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) . '</a>';
					}
					?>
				</div>
			</div>
		</div>
	</div>

<?php else: ?>
    <?php if( bp_is_my_profile() ): ?>
        <?php 
        $create_new = '';
        if( $create_new_post_page ){
            $create_new = '<a class="sap-new-post-btn-inline" href="' . get_permalink( $create_new_post_page ) . '">' . __( 'Add your first.', 'bp-user-blog' ) . '</a>';
        }
        if ( 'drafts' == bp_current_action() ) {
            _e( 'You have no drafts.', 'bp-user-blog' );
        } elseif ( 'pending' == bp_current_action() ) {
            _e( 'You have no posts in review.', 'bp-user-blog' );
        } elseif( 'blog' == bp_current_action() && isset( $_GET['sort'] ) && 'recommended' == $_GET['sort'] ) {
			_e( 'There are no recommended posts for you at the moment. Please check back later!', 'bp-user-blog' );
        } else {
			printf( __( 'You have not created any posts yet. %s', 'bp-user-blog' ), $create_new );
		}
        ?>
    <?php else: ?>
        <p><?php _e( 'There are no posts by this user at the moment. Please check back later!', 'bp-user-blog' ); ?></p>
    <?php endif; ?>
<?php endif; ?>

<?php
wp_reset_postdata();
wp_reset_query();