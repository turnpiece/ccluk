<?php do_action( 'bp_before_member_messages_loop' ); ?>

<?php if ( bp_has_message_threads( bp_ajax_querystring( 'messages' ) ) ) : ?>

	<?php do_action( 'bp_before_member_messages_threads' ); ?>

	<form action="<?php echo bp_loggedin_user_domain() . bp_get_messages_slug() . '/' . bp_current_action() ?>/bulk-manage/" method="post" id="messages-bulk-management">
	
		<div class="messages-options-nav">
			<?php bp_messages_bulk_management_dropdown(); ?>
		</div><!-- .messages-options-nav -->

		<?php wp_nonce_field( 'messages_bulk_nonce', 'messages_bulk_nonce' ); ?>
		
		<table id="message-threads" class="messages-table">
		
			<thead>
				<tr>
                    <th scope="col" class="thread-checkbox"><input id="select-all-messages" type="checkbox"><strong></strong></th>
					<th scope="col" class="thread-from"><?php _e( 'From', 'buddypress' ); ?></th>
					<th scope="col" class="thread-info"><?php _e( 'Subject', 'buddypress' ); ?></th>
					<th scope="col" class="thread-options"><?php _e( 'Date', 'buddypress' ); ?></th>
                    <?php

					/**
					 * Fires inside the messages box table header to add a new column.
					 *
					 * This is to primarily add a <th> cell to the messages box table header. Use
					 * the related 'bp_messages_inbox_list_item' hook to add a <td> cell.
					 *
					 * @since BuddyPress (2.3.0)
					 */
					do_action( 'bp_messages_inbox_list_header' ); ?>

					<?php if ( bp_is_active( 'messages', 'star' ) ) : ?>
						<th scope="col" class="thread-star"></th>
					<?php endif; ?>
				</tr>
			</thead>
			
			<tbody>
    
				<?php while ( bp_message_threads() ) : bp_message_thread(); ?>

					<tr id="m-<?php bp_message_thread_id(); ?>" class="<?php bp_message_css_class(); ?><?php if ( bp_message_thread_has_unread() ) : ?> unread<?php else: ?> read<?php endif; ?>">
						<td>
                            <input type="checkbox" name="message_ids[]" class="message-check" value="<?php bp_message_thread_id(); ?>" /><strong></strong>
						</td>

						<?php if ( 'sentbox' != bp_current_action() ) : ?>
							<td class="thread-from">
                                <div class="table">
                                    <div class="table-cell"><?php bp_message_thread_avatar( array( 'width' => 60, 'height' => 60 ) ); ?></div>
                                    <div class="table-cell"><?php bp_message_thread_from(); ?></div>
                                </div>
								<?php //bp_message_thread_total_and_unread_count(); ?>
							</td>
						<?php else: ?>
							<td class="thread-from">
                                <div class="table">
                                    <div class="table-cell"><?php bp_message_thread_avatar( array( 'width' => 60, 'height' => 60 ) ); ?></div>
                                    <div class="table-cell"><?php bp_message_thread_to(); ?></div>
                                </div>
								<?php //bp_message_thread_total_and_unread_count(); ?>
							</td>
						<?php endif; ?>

						<td class="thread-info">
							<p><a href="<?php bp_message_thread_view_link(); ?>" title="<?php esc_attr_e( "View Message", "onesocial" ); ?>"><?php bp_message_thread_subject(); ?></a></p>
							<p class="thread-excerpt"><?php bp_message_thread_excerpt(); ?></p>
						</td>

						<?php do_action( 'bp_messages_inbox_list_item' ); ?>

						<td class="thread-options">
                            <span class="activity"><?php  echo buddyboss_format_time(strtotime( bp_get_message_thread_last_post_date_raw() )); ?></span>
						</td>
				        <?php

						/**
						 * Fires inside the messages box table row to add a new column.
						 *
						 * This is to primarily add a <td> cell to the message box table. Use the
						 * related 'bp_messages_inbox_list_header' hook to add a <th> header cell.
						 *
						 * @since BuddyPress (1.1.0)
						 */
						do_action( 'bp_messages_inbox_list_item' ); ?>

						<?php if ( bp_is_active( 'messages', 'star' ) ) : ?>
							<td class="thread-star">
								<?php bp_the_message_star_action_link( array( 'thread_id' => bp_get_message_thread_id() ) ); ?>
							</td>
						<?php endif; ?>
					</tr>

				<?php endwhile; ?>

			</tbody>

		</table><!-- #message-threads -->

	</form>

	<?php do_action( 'bp_after_member_messages_threads' ); ?>
	
	<!--<div class="pagination no-ajax" id="user-pag">-->
	<div id="pag-bottom" class="pagination no-ajax">

		<div class="pag-count" id="messages-dir-count">
			<?php bp_messages_pagination_count(); ?>
		</div>

		<div class="pagination-links" id="messages-dir-pag">
			<?php bp_messages_pagination(); ?>
		</div>

	</div><!-- .pagination -->

	<?php do_action( 'bp_after_member_messages_pagination' ); ?>

	<?php do_action( 'bp_after_member_messages_options' ); ?>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, no messages were found.', 'onesocial' ); ?></p>
	</div>

<?php endif;?>

<?php do_action( 'bp_after_member_messages_loop' ); ?>