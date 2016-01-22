<div id="comments">
	<?php if ( post_password_required() ) : ?>
	                                <p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'cosmica' ); ?></p>
	                        </div><!-- #comments -->
	<?php
	                /*
	                 * Stop the rest of comments.php from being processed,
	                 * but don't kill the script entirely -- we still have
	                 * to fully load the template.
	                 */
	                return;
	        endif;
	?>
	
	<?php if ( have_comments() ) : ?>
	                        <h4 id="comments-title"><?php
	                        printf( _n( 'One Response to %2$s', '%1$s Responses to %2$s', get_comments_number(), 'cosmica' ),
	                        number_format_i18n( get_comments_number() ), '<em>' . get_the_title() . '</em>' );
	                        ?></h4>
	
	<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
	                        <div class="navigation">
	                                <div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'cosmica' ) ); ?></div>
	                                <div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'cosmica' ) ); ?></div>
	                        </div> <!-- .navigation -->
	<?php endif; // check for comment navigation ?>
	
	                        <div class="commentlist">
	                                <?php
	                                        /*
	                                         * Loop through and list the comments. Tell wp_list_comments()
	                                         * to use cosmica_comment() to format the comments.
	                                         * If you want to overload this in a child theme then you can
	                                         * define cosmica_comment() and that will be used instead.
	                                         * See cosmica_comment() in cosmica/functions.php for more.
	                                         * array( 'callback' => 'cosmica_comment' )
	                                         */
	                                         wp_list_comments( array( 'style' => 'div' ) ); 
	                                ?>
	                        </div>
	
	<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
	                        <div class="navigation">
	                                <div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'cosmica' ) ); ?></div>
	                                <div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'cosmica' ) ); ?></div>
	                        </div><!-- .navigation -->
	<?php endif; // check for comment navigation ?>
	
	        <?php
	        /*
	         * If there are no comments and comments are closed, let's leave a little note, shall we?
	         * But we only want the note on posts and pages that had comments in the first place.
	         */
	        if ( ! comments_open() && get_comments_number() ) : ?>
	                <p class="nocomments"><?php _e( 'Comments are closed.' , 'cosmica' ); ?></p>
	        <?php endif;  ?>
	
	<?php endif; // end have_comments() ?>
	
	<?php comment_form(); ?>
	
	</div><!-- #comments -->