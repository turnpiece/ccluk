<?php
/**
 * Advanced tools: general meta box.
 *
 * @var bool   $query_stings  URL Query Strings enabled or disabled.
 * @var bool   $emoji         Remove Emojis file enabled or disabled.
 * @var string $prefetch      Prefetch dns urls.
 */
?>

<div class="sui-margin-bottom">
	<p>
		<?php esc_html_e( 'Here are a few additional tweaks you can make to further reduce your page load times.', 'wphb' ); ?>
	</p>
</div>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'URL Query Strings', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Some proxy caching servers and even some CDNs cannot cache static assets with query strings, resulting in a large missed opportunity for increased speeds.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<label class="sui-toggle sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_attr_e( 'Remove query strings from my assets', 'wphb' ); ?>">
			<input type="checkbox" name="query_strings" id="query_strings" <?php checked( $query_stings ); ?>>
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="query_strings"><?php esc_html_e( 'Remove query strings from my assets', 'wphb' ); ?></label>
	</div>
</div>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Emojis', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'WordPress adds Javascript and CSS files to convert common symbols like “:)” to visual emojis. If you don’t need emojis this will remove two unnecessary assets.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<label class="sui-toggle sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_attr_e( 'Remove the default Emoji JS & CSS files', 'wphb' ); ?>">
			<input type="checkbox" name="emojis" id="emojis" <?php checked( $emoji ); ?>>
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="emojis"><?php esc_html_e( 'Remove the default Emoji JS & CSS files', 'wphb' ); ?></label>
	</div>
</div>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Prefetch DNS Requests', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Speeds up web pages by pre-resolving DNS. In essence it tells a browser it should resolve the DNS of a specific domain prior to it being explicitly called – very useful if you use third party services.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<textarea class="sui-form-control" name="url_strings" placeholder="//fonts.googleapis.com
//fonts.gstatic.com
//ajax.googleapis.com
//apis.google.com
//google-analytics.com
//www.google-analytics.com
//ssl.google-analytics.com
//youtube.com
//s.gravatar.com"><?php echo esc_html( $prefetch ); ?></textarea>
		<span class="sui-description">
			<?php esc_html_e( 'Add one host entry per line replacing the http:// or https:// with // e.g. //fonts.googleapis.com. We’ve added a few common DNS requests to get you started.', 'wphb' ); ?>
			<?php printf(
				'<a href="#" id="wphb-adv-paste-value">%s</a>',
				esc_html__( 'Paste in recommended defaults.', 'wphb' )
			); ?>
		</span>
	</div>
</div>