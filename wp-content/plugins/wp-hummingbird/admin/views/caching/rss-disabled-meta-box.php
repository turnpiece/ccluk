<?php
/**
 * Rss caching meta box (disabled state).
 *
 * @since 1.8
 * @package Hummingbird
 *
 * @var string $url  Activate/deactivate link.
 */
?>

<p>
	<?php esc_html_e( 'By default, WordPress will cache your RSS feeds to reduce the load on
	your server – which is great feature. Hummingbird gives you control over the expiry time, or
	you can disable it all together.', 'wphb' ); ?>
</p>

<div class="wphb-notice wphb-notice-warning">
	<p>
		<?php esc_html_e( 'RSS Caching is currently disabled.', 'wphb' ); ?>
	</p>
	<div class="buttons">
		<a href="<?php echo esc_url( $url ); ?>" class="button button-grey">
			<?php esc_html_e( 'Enable Caching', 'wphb' ); ?>
		</a>
	</div>
</div>