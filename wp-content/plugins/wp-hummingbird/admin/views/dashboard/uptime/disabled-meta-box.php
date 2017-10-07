<?php
/**
 * Uptime disabled meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $enable_url  URL to enable uptime module.
 */

?>
<div class="content">
	<p><?php esc_html_e( 'Monitor your website and get notified if/when it’s inaccessible. We’ll also watch your server response time.', 'wphb' ); ?></p>
</div>

<div class="buttons">
	<a class="button button-cta-green" href="<?php echo esc_url( $enable_url ); ?>" id="enable-uptime">
		<?php esc_html_e( 'Activate', 'wphb' ); ?>
	</a>
</div>