<?php
/**
 * Advanced tools: general meta box.
 *
 * @var bool   $query_stings  URL Query Strings enabled or disabled.
 * @var bool   $emoji         Remove Emojis file enabled or disabled.
 * @var string $prefetch      Prefetch dns urls.
 */
?>

<div class="row settings-form with-bottom-border">
	<p>
		<?php esc_html_e( 'Here are a few additional tweaks you can make to further reduce your page load times.', 'wphb' ); ?>
	</p>
</div>

<form id="advanced-general-settings">
	<div class="row settings-form with-bottom-border">
		<div class="col-third">
			<strong><?php esc_html_e( 'URL Query Strings', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'Some proxy caching servers and even some CDNs cannot cache static assets with query strings, resulting in a large missed opportunity for increased speeds.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">
			<span class="toggle tooltip-right" tooltip="<?php esc_attr_e( 'Remove query strings from my assets', 'wphb' ); ?>">
				<input type="checkbox" class="toggle-checkbox" name="query_strings" id="query_strings" <?php checked( $query_stings ); ?>>
				<label for="query_strings" class="toggle-label small"></label>
			</span>
			<label for="query_strings"><?php esc_html_e( 'Remove query strings from my assets', 'wphb' ); ?></label>
		</div>
	</div>

	<div class="row settings-form with-bottom-border">
		<div class="col-third">
			<strong><?php esc_html_e( 'Emojis', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'WordPress adds Javascript and CSS files to convert common symbols like “:)” to visual emojis. If you don’t need emojis this will remove two unnecessary assets.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">
			<span class="toggle tooltip-right" tooltip="<?php esc_attr_e( 'Remove the default Emoji JS & CSS files', 'wphb' ); ?>">
				<input type="checkbox" class="toggle-checkbox" name="emojis" id="emojis" <?php checked( $emoji ); ?>>
				<label for="emojis" class="toggle-label small"></label>
			</span>
			<label for="emojis"><?php esc_html_e( 'Remove the default Emoji JS & CSS files', 'wphb' ); ?></label>
		</div>
	</div>

	<div class="row settings-form">
		<div class="col-third">
			<strong><?php esc_html_e( 'Prefetch DNS Requests', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'Speeds up web pages by pre-resolving DNS. In essence it tells a browser it should resolve the DNS of a specific domain prior to it being explicitly called – very useful if you use third party services.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">
			<textarea name="url_strings"><?php echo esc_html( $prefetch ); ?></textarea>
			<span class="desc">
				<?php esc_html_e( 'Add one host entry per line replacing the http:// or https:// with // e.g. //fonts.googleapis.com. We’ve added a few common DNS requests to get you started.', 'wphb' ); ?>
			</span>
		</div>
	</div>
</form>