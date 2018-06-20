<?php
/**
 * Rss caching met box.
 *
 * @since 1.8
 * @package Hummingbird
 *
 * @var int    $duration  Rss cache duration.
 * @var string $url       Activate/deactivate link.
 */
?>

<p>
	<?php esc_html_e( 'By default, WordPress will cache your RSS feeds to reduce the load on
	your server – which is a great feature. Hummingbird gives you control over the expiry time, or
	you can disable it all together.', 'wphb' ); ?>
</p>

<div class="sui-box-settings-row">
	<div class="sui-notice sui-notice-success">
		<p>
			<?php esc_html_e( 'RSS Feed Caching is currently active.', 'wphb' ); ?>
		</p>
	</div>
</div>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Expiry time', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Choose the length of time you want WordPress to cache your RSS feed
			for. The longer you cache it for, the less load on your server.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<input type="text" class="sui-form-control wphb-rss-expiry-time" id="rss-expiry-time" name="rss-expiry-time" value="<?php echo absint( $duration ); ?>">
		<label for="rss-expiry-time" class="wphb-rss-expiry-time-label">
			<?php esc_html_e( 'seconds', 'wphb' ); ?>
		</label>
		<span class="sui-description">
			<?php esc_html_e( 'Note: The default expiry is set to one hour.', 'wphb' ); ?>
		</span>
	</div>
</div>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Disable caching', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( "If you don't want your RSS Feed cached, you can disable it here.", 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<a href="<?php echo esc_url( $url ); ?>" class="sui-button sui-button-ghost">
			<?php esc_html_e( 'Disable Caching', 'wphb' ); ?>
		</a>
	</div><!-- end sui-box-settings-col-2 -->
</div>