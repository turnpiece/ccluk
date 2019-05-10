<?php
/**
 * Gravatar caching meta box.
 *
 * @package Hummingbird
 *
 * @var string        $deactivate_url    Deactivation URL.
 * @var bool|WP_Error $error             Error if present.
 */

?>
<p><?php esc_html_e( 'Gravatar Caching stores local copies of avatars used in comments and in your theme. You can control how often you want the cache purged depending on how your website is set up.', 'wphb' ); ?></p>
<div class="sui-box-settings-row">
	<?php if ( is_wp_error( $error ) ) : ?>
		<div class="wphb-caching-error sui-notice sui-notice-error">
			<p><?php echo esc_html( $error->get_error_message() ); ?></p>
		</div>
	<?php else : ?>
		<div class="wphb-caching-success sui-notice sui-notice-success">
			<p><?php esc_html_e( 'Gravatar Caching is currently active.', 'wphb' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<div class="sui-box-settings-row">

	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Deactivate', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'This will deactivate Gravatar Caching and clear your local avatar storage.', 'wphb' ); ?>
		</span>
	</div><!-- end sui-box-settings-col-1 -->
	<div class="sui-box-settings-col-2">
		<a href="<?php echo esc_url( $deactivate_url ); ?>" class="sui-button sui-button-ghost sui-button-icon-left">
			<i class="sui-icon-power-on-off" aria-hidden="true"></i>
			<?php esc_html_e( 'Deactivate', 'wphb' ); ?>
		</a>
	</div><!-- end sui-box-settings-col-2 -->
</div><!-- end row -->
