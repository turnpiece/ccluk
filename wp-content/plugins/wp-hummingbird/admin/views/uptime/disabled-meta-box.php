<?php
/**
 * Uptime disabled meta box.
 *
 * @package Hummingbird
 *
 * @var string      $activate_url    Activate Uptime URL.
 * @var bool|string $user            False if no user, or users name.
 */

?>
<div class="wphb-block-entry">
	<div class="wphb-block-entry-content wphb-block-content-center">
		<img class="wphb-image wphb-image-center wphb-image-icon-content-top"
		     src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@1x.png'; ?>"
		     srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@2x.png'; ?> 2x"
		     alt="<?php esc_attr_e( 'Monitor your website', 'wphb' ); ?>">

		<div class="content">
			<p><?php esc_html_e( 'Uptime monitors your server response time and lets you know when your website is down or too slow for your visitors. Activate Uptime and make sure your website is always online.', 'wphb' ); ?></p>
		</div><!-- end content -->
		<div class="buttons">
			<a href="<?php echo esc_url( $activate_url ); ?>" class="button" id="activate-uptime">
				<?php esc_html_e( 'Activate', 'wphb' ); ?>
			</a>
		</div>
	</div><!-- end wphb-block-entry-content -->
</div><!-- end wphb-block-entry -->