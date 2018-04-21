<?php
/**
 * Uptime error meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $retry_url    URL to retry.
 * @var string $support_url  URL to support forums.
 */

?>
<div class="sui-notice sui-notice-error wphb-notice-box">
	<p><?php echo esc_html( $error ); ?></p>
	<div class="sui-notice-buttons">
		<a href="<?php echo esc_url( $retry_url ); ?>" class="sui-button">
			<?php esc_html_e( 'Try again', 'wphb' ); ?>
		</a>
		<a target="_blank" href="<?php echo esc_url( $support_url ); ?>" class="sui-button">
			<?php esc_html_e( 'Support', 'wphb' ); ?>
		</a>
	</div>
</div>