<?php
/**
 * Custom template tags for this theme.
 *
 * @package politics
 */

/**
* Template for comments and pingbacks.
*/
if ( ! function_exists( 'politics_comments' ) ) :
   function politics_comments( $comment, $args, $depth ) {
      $GLOBALS['comment'] = $comment;
      switch ( $comment->comment_type ) :
          case '' :
      ?>

        <li <?php comment_class('clearfix'); ?> id="li-comment-<?php comment_ID() ?>">

          <div id="comment-<?php comment_ID(); ?>" class=" clearfix">

              <div class="comment-content-wrap">

								<div class="comment-header clearfix">

								<span class="comment-date"><?php comment_date(get_option('date_format')); ?></span>

								<?php
									$avatar = get_avatar( $comment, 45 );
									echo wp_kses( $avatar,
										array(
											'img' => array(
									        'src' => array(),
									        'title' => array(),
													'srcset' => array(),
													'class' => array(),
													'height' => array(),
													'width' => array()
									    ),
											)
										);
									?>

									<cite class="comment-author"><?php echo get_comment_author_link() ?></cite>

								</div><!-- .comment-header -->

                <?php if ($comment->comment_approved == '0') : ?><p class="moderated"><?php _e('Your comment is awaiting moderation.','politics'); ?></p><?php endif; ?>

                  <div class="comment_content">
                    <?php comment_text() ?>
                	</div><!-- .comment_content -->

								<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>

              </div><!-- .comment-content-wrap -->

            </div><!-- .comment-<?php comment_ID(); ?> -->

       	<?php
          break;
          case 'pingback'  :
          case 'trackback' :
        ?>
          <li <?php comment_class('clearfix'); ?> id="li-comment-<?php comment_ID() ?>">
          <div id="comment-<?php comment_ID(); ?>" class="clearfix">
            <?php echo "<div class='author'><em>" . __('Trackback:','politics') . "</em> ".get_comment_author_link()."</div>"; ?>
            <?php echo strip_tags(substr(get_comment_text(),0, 110)) . "..."; ?>
            <?php comment_author_url_link('', '<small>', '</small>'); ?>
          </div><!-- #comment-## -->
      	<?php
        	break;
      	endswitch;
  }
endif;

if ( ! function_exists( 'politics_entry_footer' ) ) :
/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function politics_entry_footer() {
	// Hide category and tag text for pages.
	if ( 'post' === get_post_type() ) {
		/* translators: used between list items, there is a space after the comma */
		$categories_list = get_the_category_list( esc_html__( ', ', 'politics' ) );
		if ( $categories_list && politics_categorized_blog() ) {
			printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'politics' ) . '</span>', $categories_list ); // WPCS: XSS OK.
		}

		/* translators: used between list items, there is a space after the comma */
		$tags_list = get_the_tag_list( '', esc_html__( ', ', 'politics' ) );
		if ( $tags_list ) {
			printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'politics' ) . '</span>', $tags_list ); // WPCS: XSS OK.
		}
	}

	if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		comments_popup_link( esc_html__( 'Leave a comment', 'politics' ), esc_html__( '1 Comment', 'politics' ), esc_html__( '% Comments', 'politics' ) );
		echo '</span>';
	}

	edit_post_link( esc_html__( 'Edit', 'politics' ), '<span class="edit-link">', '</span>' );
}
endif;

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function politics_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'politics_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,

			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'politics_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so politics_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so politics_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in politics_categorized_blog.
 */
function politics_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'politics_categories' );
}
add_action( 'edit_category', 'politics_category_transient_flusher' );
add_action( 'save_post',     'politics_category_transient_flusher' );
