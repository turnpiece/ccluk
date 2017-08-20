<?php
/**
 * BuddyPress User Blog - Post Create
 *
 * @package WordPress
 * @subpackage BuddyPress User Blog
 */

$theme		 = wp_get_theme(); // gets the current theme
$theme_name	 = $theme->template;

$container		 = '';
$container_class = '';
$content_class	 = '';
$sidebar_class	 = '';

if ( 'kleo' == $theme_name ) {
	$container		 = ' kleo-sap-wrapper';
}

if ( 'boss' == $theme_name || 'social-portfolio' == $theme_name ) {
	$container		 = ' boss-sap-wrapper';
}

$old_post   = '';
$content    = isset( $_POST[ 'content' ] ) ? $_POST[ 'content' ] : '';
$title      = isset( $_POST[ 'title' ] ) ? $_POST[ 'title' ] : '';

if ( isset($_GET['post']) && !empty($_GET['post']) ) {
    $pid = $_GET['post'];
    $post_data = get_post($pid);
}

if ( !empty( $post_data ) && 'trash' != $post_data->post_status && get_current_user_id() == $post_data->post_author ) {
    $old_post = 'true';
}

if ( $old_post == 'true' ) {
    $edit_status    = 'true';
    $draft_id       = $pid;
    $post_status    = $post_data->post_status;
    $post_status_content = ucfirst($post_data->post_status);
    $old_title = $post_data->post_title;
    $old_content    = apply_filters( 'bp-user-blog_editable_content', $post_data->post_content );
    $post_category  = wp_get_post_categories($pid);
    $permalink      = get_the_permalink($pid);
    $class_to_apply   = '';
    $featured_image_id_src = wp_get_attachment_image_src( get_post_thumbnail_id( $pid ) ,'medium' );
    $draft_btn_txt = __('Revert to draft','bp-user-blog');
    
    if ( 'publish' == $post_status ) {
        $post_status_content = __('Published','bp-user-blog');
        $view_btn_txt = __('View','bp-user-blog');
    } elseif( 'pending' == $post_status ) {
        $view_btn_txt = __('Preview','bp-user-blog');
    } else {
        $view_btn_txt = __('Preview','bp-user-blog');
        $draft_btn_txt = __('Save','bp-user-blog');
    }
    
} else {
    $pid            = '';
    $post_category  = '';
    $edit_status    = '';
    $draft_id       = '';
    $post_status    = '';
    $old_title    = '';
    $old_content    = '';
    $permalink      = '';
    $class_to_apply = 'sap-disabled';
    $post_status_content = __('Draft','bp-user-blog');
    $featured_image_id_src = '';
    $draft_btn_txt = __('Save','bp-user-blog');
    $view_btn_txt = __('Preview','bp-user-blog');
}

?>

<div class="sap-container-wrapper<?php echo $container; ?>">

	<div class="sap-container<?php echo $container_class; ?>">

		<div class="sap-editor-wrap<?php echo $content_class; ?>">
			<div class="sap-post-author-wrap">

				<?php
				global $current_user;

				$current_user_id = get_current_user_id();
				$publish_post = buddyboss_sap()->option( 'publish_post' );
                                
                                $displayed_user_id = bp_displayed_user_id();
                                $user_domain = (!empty($displayed_user_id) ) ? bp_displayed_user_domain() : bp_loggedin_user_domain();

                                $blog_link = trailingslashit($user_domain . __('blog', 'bp-user-blog'));

				if ( $publish_post ) {
					$button_text = __( 'Publish', 'bp-user-blog' );
				} else {
					$button_text = __( 'Submit for Review', 'bp-user-blog' );
				} ?>

                                <a href="<?php echo $blog_link; ?>"><?php echo get_avatar( get_current_user_id(), 100 ); ?></a>
                                
				<div class="sap-author-info">
                                    <a class="sap-author-name" href="<?php echo $blog_link; ?>"><?php echo esc_html( $current_user->display_name ); ?></a>
                                    <p class="sap-post-status"><?php echo $post_status_content; ?></p>
				</div>
			</div>
           
           <div class="side-panel">
                <div class="sap-editor-toolbar">
                    
                <a href="#" class="toggle-sap-widgets" title="<?php _e( 'More Actions', 'bp-user-blog' ); ?>">
                    <svg class="write-story-icon" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30">
                    <path id="write_a_story" data-name="write a story" class="cls-1" d="M4.2,12.214h11.5v0.873l-0.279.279H4.2V12.214Zm0,3.455h8.925l-1.151,1.152H4.2V15.67Zm6.9,8.062H4.2V22.58h6.9v1.152ZM4.2,19.125H9.938L9.563,20.25H9.213L9.24,20.277H4.2V19.125ZM19.158,21.8v3.088a2.3,2.3,0,0,1-2.3,2.3H3.051a2.3,2.3,0,0,1-2.3-2.3V5.517A2.5,2.5,0,0,1,3.051,3H15.526l3.632,3.636v3l-1.151,1.152V8.758h-3.26a1.343,1.343,0,0,1-1.342-1.344V4.151H3.051A1.354,1.354,0,0,0,1.9,5.518V24.883a1.151,1.151,0,0,0,1.15,1.152H16.857a1.151,1.151,0,0,0,1.151-1.152V22.947Zm-4.6-15.052a0.863,0.863,0,0,0,.863.864h1.726a0.863,0.863,0,0,0,.863-0.864L15.418,4.151a0.863,0.863,0,0,0-.863.864V6.743Z"/>
                    <path id="write_a_post_icon_copy_2" data-name="write a post icon copy 2" class="cls-2" d="M29.188,9.323L15.9,22.626l-3.976,1.106-0.057-.057a0.59,0.59,0,0,1-.707-0.707L11.1,22.911l1.1-3.98L25.5,5.628a0.85,0.85,0,0,1,1.2.029L29.159,8.12A0.852,0.852,0,0,1,29.188,9.323ZM13.959,19.118a0.385,0.385,0,0,0-.353-0.354l-0.788.789-0.784,2.474a0.614,0.614,0,0,0,.773.774l2.471-.785,0.788-.789-0.057-.057c-0.345.084-.41-0.41-0.41-0.41a0.584,0.584,0,0,0-.158-0.549,1.22,1.22,0,0,0-.662-0.272,0.583,0.583,0,0,1-.549-0.158A1.222,1.222,0,0,1,13.959,19.118ZM23.572,8.787l-9.38,9.39a0.461,0.461,0,0,1,.353.354,1.222,1.222,0,0,0,.272.663,0.582,0.582,0,0,0,.549.158,1.221,1.221,0,0,1,.662.272,0.584,0.584,0,0,1,.158.549L16.3,20.287c-0.091.376,0.353,0.354,0.353,0.354l9.38-9.39Zm3.146-2.328a0.64,0.64,0,0,0-.8-0.019L24.158,8.2l2.461,2.464L28.378,8.9a0.626,0.626,0,0,0-.019-0.8Z"/>
                    </svg>

                    <svg class="remove-icon" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                     width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve">
                    <path fill-rule="evenodd" clip-rule="evenodd" fill="none" d="M1.588,0.174l18.231,18.23l-1.415,1.414L0.174,1.588L1.588,0.174z"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" fill="none" d="M0.193,18.392L18.404,0.181l1.414,1.415L1.607,19.806L0.193,18.392z"/>
                    </svg>
                    </a>

                    <div class="sap-editor-publish-wrapper clearfix">

                        <div class="sap-publish-popup">
                            <?php
                            if ( $publish_post ) {
                                   if ( $old_post == 'true' && $post_status != 'draft' ) { ?>
                                        <a class="sap-story-update sap-action-button sap-story-update-btn button button-primary" href="#" title="<?php _e('Update','bp-user-blog'); ?>" ><?php _e('Update','bp-user-blog'); ?></a><?php
                                   } else { ?>
                                        <a class="sap-story-publish sap-action-button sap-story-publish-btn button button-primary" href="#" title="<?php _e('Publish post','bp-user-blog'); ?>" ><?php echo $button_text ?></a><?php
                                   }
                            } else {
                                   if ( $old_post == 'true' && $post_status == 'pending' ) { ?>
                                        <a style="display:none;" class="sap-story-review sap-action-button sap-story-review-btn button button-primary" href="#" title="<?php _e('Submit post for review','bp-user-blog'); ?>" ><?php echo $button_text ?></a>
                                        <a class="sap-pending-preview sap-pending-preview-btn button button-secondary sap-disabled" ><?php _e('In Review','bp-user-blog'); ?></a><?php
                                   } else { ?>
                                        <a class="sap-story-review sap-action-button sap-story-review-btn button button-primary" href="#" title="<?php _e('Submit post for review','bp-user-blog'); ?>" ><?php echo $button_text ?></a>
                                        <a style="display:none;" class="sap-pending-preview sap-pending-preview-btn button button-secondary sap-disabled" ><?php _e('In Review','bp-user-blog'); ?></a><?php
                                   }
                            }
                            ?>
                            <a class="sap-story-draft sap-story-draft-btn button button-secondary" href="#" ><?php echo $draft_btn_txt; ?></a>
                                                    <a class="sap-story-preview sap-story-preview-btn button button-secondary <?php echo $class_to_apply; ?>" target="_blank" href="<?php echo $permalink; ?>" title="<?php _e('Show preview in new window','bp-user-blog'); ?>" ><?php echo $view_btn_txt; ?></a>
                        </div>

                    </div>

                </div>

                <div class="sap-widget-container" id="sap-widget-container">
                    <?php sap_post_category_tags_widget($pid, $post_category); ?>
                    <?php sap_post_featured_img_widget(); ?>

                    <?php if ( $old_post == 'true') { ?>
                        <a class="sap-story-delete sap-story-delete-btn" href="#"><?php _e('Delete','bp-user-blog'); ?></a><?php
                    } else { ?>
                        <a style="display:none;" class="sap-story-delete sap-story-delete-btn" href="#"><?php _e('Delete','bp-user-blog'); ?></a><?php
                    } ?>
                </div>
            </div>

			<div class="sap-editor-area-wrapper">
				<textarea class="sap-editable-title" data-disable-toolbar="true" ><?php echo $old_title; ?></textarea>
				<textarea class="sap-editable-area"><?php echo $old_content; ?></textarea>
			</div>

			<input type="hidden" class="sap-editor-nonce" name="sap_editor_nonce" value="<?php echo wp_create_nonce( 'sap-editor-nonce' ); ?>" />
			<input type="hidden" id="sap-draft-pid" name="draft_pid" value="<?php echo $draft_id; ?>" /> 
			<input type="hidden" id="sap-draft-status" name="draft_status" value="<?php echo $post_status; ?>" />
			<input type="hidden" id="sap-edit-status" name="edit_status" value="<?php echo $edit_status; ?>" />

		</div>

	</div>

</div>

<script>
    var content = '<?php echo $content; ?>',
        title = '<?php echo $title; ?>';
    if ( title ) {
        jQuery( '.sap-editable-title' ).html( title );
    }
    if ( content ) {
        jQuery( '.sap-editable-area' ).html( content );
    }
    <?php if ( !empty($featured_image_id_src) ) { ?>
        jQuery('.featured-img-preview').attr('src','<?php echo $featured_image_id_src['0']; ?>' ).show();
        jQuery('#featured-img-placeholder').hide();
        jQuery('#featured-img-placeholder-id').val(); 
        jQuery('.sap-preview-close').show(<?php echo get_post_thumbnail_id( $pid ); ?>); 
    <?php } ?>
</script>

<?php

