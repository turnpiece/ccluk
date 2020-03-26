<?php

/**
 * Single Topic Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

	<?php bbp_breadcrumb(); ?>

	<?php do_action( 'bbp_template_before_single_topic' ); ?>

	<?php if ( post_password_required() ) : ?>

		<?php bbp_get_template_part( 'form', 'protected' ); ?>

	<?php else : ?>
        <?php 		
            $t = array(
                'before' => '<div class="bbp-topic-tags"><span>' . esc_html__( 'Tagged', 'onesocial' ) . '</span>',
                'sep'    => '',
                'after'  => '</div>'
            );
        ?>
		<?php bbp_topic_tag_list(0,$t); ?>
       
        <span id="bbp-topic-details">
        <?php bbp_user_favorites_link(); ?>
        <?php 		
            $s = array(
                'before'      => ''
            );
        ?>
        <?php bbp_topic_subscription_link($s); ?>

        <?php buddyboss_bbp_single_topic_description(array('before'=>'<div class="bbp-forum-data">', 'after'=>'</div>')); ?>
        </span>

		<?php if ( bbp_show_lead_topic() ) : ?>

			<?php bbp_get_template_part( 'content', 'single-topic-lead' ); ?>

		<?php endif; ?>

		<?php if ( bbp_has_replies() ) : ?>

			<?php bbp_get_template_part( 'loop',       'replies' ); ?>

			<?php bbp_get_template_part( 'pagination', 'replies' ); ?>

		<?php endif; ?>

		<?php bbp_get_template_part( 'form', 'reply' ); ?>

	<?php endif; ?>

	<?php do_action( 'bbp_template_after_single_topic' ); ?>

