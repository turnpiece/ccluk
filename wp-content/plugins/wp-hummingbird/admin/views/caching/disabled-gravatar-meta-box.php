<?php
/**
 * Disabled Gravatar caching meta box.
 *
 * @package Hummingbird
 *
 * @var string $activate_url  Activation URL.
 */

?>
<div class="wphb-block-entry">

	<div class="wphb-block-entry-content wphb-block-content-center">

		<img class="wphb-image wphb-image-center wphb-image-icon-content-top"
			 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-gravatarcaching-disabled.png'; ?>"
			 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-gravatarcaching-disabled@2x.png'; ?> 2x"
			 alt="<?php esc_attr_e( 'Gravatar Caching', 'wphb' ); ?>">

		<div class="content">
			<p><?php esc_html_e( 'Gravatar Caching stores local copies of avatars used in comments and in your theme. You can control how often you want the cache purged depending on how your website is set up.', 'wphb' ); ?></p>
		</div><!-- end content -->

		<div class="buttons">
			<a href="<?php echo esc_url( $activate_url ); ?>" class="button button-large" id="activate-page-caching">
				<?php esc_html_e( 'Activate', 'wphb' ); ?>
			</a>
		</div>

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->