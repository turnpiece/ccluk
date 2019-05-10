<?php
/**
 * Uptime no membership meta box.
 *
 * @package Hummingbird
 */

?>

<img class="sui-image"
	src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@1x.png' ); ?>"
	srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@2x.png' ); ?> 2x"
	alt="<?php esc_attr_e( 'Monitor your website', 'wphb' ); ?>">

<div class="sui-message-content">
	<p>
		<?php
		esc_html_e(
			'Uptime monitors your server response time and lets you know when your website is down or
			too slow for your visitors. Get Uptime monitoring as part of a WPMU DEV membership.',
			'wphb'
		);
		?>
	</p>
</div>

<a id="wphb-upgrade-membership-modal-link" class="sui-button sui-button-green" href="<?php echo esc_url( WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_uptime_upgrade_button' ) ); ?>" target="_blank">
	<?php esc_html_e( 'Upgrade to Pro', 'wphb' ); ?>
</a>
