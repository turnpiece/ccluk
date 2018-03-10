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
	your server – which is great feature. Hummingbird gives you control over the expiry time, or
	you can disable it all together.', 'wphb' ); ?>
</p>

<form id="rss-caching-settings">
	<div class="row settings-form with-bottom-border">
		<div class="wphb-notice wphb-notice-success">
			<p>
				<?php esc_html_e( 'RSS Feed Caching is currently active.', 'wphb' ); ?>
			</p>
		</div>
	</div>
	<div class="row settings-form with-bottom-border">
		<div class="col-third">
			<strong><?php esc_html_e( 'Expiry time', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'Choose the length of time you want WordPress to cache your RSS feed
				for. The longer you cache it for, the less load on your server.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">
			<input type="text" id="rss-expiry-time" name="rss-expiry-time" value="<?php echo absint( $duration ); ?>">
			<label for="rss-expiry-time">
				<?php esc_html_e( 'seconds', 'wphb' ); ?>
			</label>
			<span class="desc">
				<?php esc_html_e( 'Note: The default expiry is set to one hour.', 'wphb' ); ?>
			</span>
		</div>
	</div>

	<div class="row settings-form">
		<div class="col-third">
			<strong><?php esc_html_e( 'Disable caching', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( "If don't want your RSS feed cached, you can disable it here.", 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">
			<a href="<?php echo esc_url( $url ); ?>" class="button button-ghost">
				<?php esc_html_e( 'Disable Caching', 'wphb' ); ?>
			</a>
		</div><!-- end col-two-third -->
	</div>
</form>