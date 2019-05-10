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

<?php if ( ! WP_Hummingbird_Utils::hide_wpmudev_branding() ) : ?>
	<img class="sui-image"
		 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@1x.png' ); ?>"
		 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@2x.png' ); ?> 2x"
		 alt="<?php esc_attr_e( 'Monitor your website', 'wphb' ); ?>">
<?php endif; ?>

<div class="sui-message-content">
	<p>
		<?php
		esc_html_e(
			'Uptime monitors your server response time and lets you know when your website is down or
		too slow for your visitors. Activate Uptime and make sure your website is always online.',
			'wphb'
		);
		?>
	</p>
</div>

<a href="<?php echo esc_url( $activate_url ); ?>" class="sui-button sui-button-blue" id="activate-uptime">
	<?php esc_html_e( 'Activate', 'wphb' ); ?>
</a>
