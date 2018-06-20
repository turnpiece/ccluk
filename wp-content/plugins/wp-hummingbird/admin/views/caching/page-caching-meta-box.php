<?php
/**
 * Page caching meta box.
 *
 * @package Hummingbird
 *
 * @var bool          $admins_can_disable  Blog admins can disable page caching.
 * @var bool          $blog_is_frontpage   Is the Blog set as the Frontpage.
 * @var string        $deactivate_url      Deactivate URL.
 * @var string        $download_url        Download logs URL.
 * @var bool|WP_Error $error               Error if present.
 * @var array         $pages               A list of page types.
 * @var array         $settings            Settings array.
 */

?>
<div class="sui-box-settings-row">
	<p><?php esc_html_e( 'Hummingbird stores static HTML copies of your pages and posts to decrease page load time.', 'wphb' ); ?></p>

	<?php if ( is_wp_error( $error ) ) : ?>
		<div class="wphb-caching-error sui-notice sui-notice-error">
			<p><?php echo $error->get_error_message(); ?></p>
		</div>
	<?php else : ?>
		<div class="wphb-caching-success sui-notice sui-notice-success">
			<p><?php esc_html_e( 'Page caching is currently active.', 'wphb' ); ?></p>
		</div>
	<?php endif; ?>
</div><!-- end row -->

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Page Types', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Select which page types you wish to cache.', 'wphb' ); ?>
			<?php ( ! is_multisite() ) ? esc_html_e( ' Select which page types you wish to cache.', 'wphb' ) : false; ?>
		</span>
		<?php if ( is_multisite() ) : ?>
			<span class="sui-description">
				<?php esc_html_e( 'Subsites will inherit the settings you use here, except any additional custom post types or taxonomies will be cached by default.', 'wphb' ); ?>
			</span>
			<span class="sui-description">
				<?php esc_html_e( 'Your subsite admins can use the DONOTCACHEPAGE constant to prevent caching on their custom post types.', 'wphb' ); ?>
			</span>
		<?php endif; ?>
	</div>
	<div class="sui-box-settings-col-2">

		<div class="wphb-dash-table three-columns">
			<?php foreach ( $pages as $page_type => $page_name ) : ?>
				<div class="wphb-dash-table-row">
					<div><?php echo esc_html( $page_name ); ?></div>
					<?php if ( 'home' === $page_type && $blog_is_frontpage ) : ?>
						<span class="sui-tag sui-tag-inactive"><?php esc_html_e( 'Your blog is your frontpage', 'wphb' ); ?></span>
					<?php else : ?>
						<span class="sub"><?php echo esc_html( $page_type ); ?></span>
						<label class="sui-toggle">
							<input type="checkbox" name="page_types[<?php echo esc_attr( $page_type ); ?>]" id="<?php echo esc_attr( $page_type ); ?>" <?php checked( in_array( $page_type, $settings['page_types'],  true ) ); ?>>
							<span class="sui-toggle-slider"></span>
						</label>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<?php foreach ( $custom_post_types  as $post_type ) : ?>
				<div class="wphb-dash-table-row">
					<div><?php echo esc_html( $post_type->label ); ?></div>
					<span class="sub"><?php echo esc_html( $post_type->name ); ?></span>
					<label class="sui-toggle">
						<input type="hidden" name="custom_post_types[<?php echo esc_attr( $post_type->name ); ?>]" value="1">
						<input type="checkbox" name="custom_post_types[<?php echo esc_attr( $post_type->name ); ?>]" id="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( ! in_array( $post_type->name, $settings['custom_post_types'], true ) ); ?> value="0">
						<span class="sui-toggle-slider"></span>
					</label>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="sui-notice sui-notice-sm">
			<p>
				<?php
				/* translators: %s: code snippet. */
				printf(
					__( 'You can use the <code>%s</code> constant to instruct Hummingbird not to cache specific pages or templates.', 'wphb' ),
					esc_attr( 'define(\'DONOTCACHEPAGE\', true);', 'wphb' )
				);
				?>
			</p>
		</div>
	</div>
</div>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Settings', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Fine tune page caching to work how you want it to.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<label class="sui-toggle">
			<input type="hidden" name="logged-in" value="0">
			<input type="checkbox" name="settings[logged-in]" value="1" id="logged-in" <?php checked( $settings['settings']['logged_in'] ); ?>>
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="logged-in"><?php esc_html_e( 'Include logged in users', 'wphb' ); ?></label>
		<span class="sui-description sui-toggle-description">
			<?php esc_html_e( 'Caching pages for logged in users can reduce load on your server, but can cause strange behavior with some themes/plugins.', 'wphb' ); ?>
		</span>
		<div class="clear mline"></div>

		<label class="sui-toggle">
			<input type="hidden" name="url-queries" value="0">
			<input type="checkbox" name="settings[url-queries]" value="1" id="url-queries" <?php checked( $settings['settings']['url_queries'] ); ?>>
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="url-queries"><?php esc_html_e( 'Cache URL queries', 'wphb' ); ?></label>
		<span class="sui-description sui-toggle-description">
			<?php esc_html_e( 'You can turn on caching pages with GET parameters (?x=y at the end of a url), though generally this isn’t a good idea if those pages are dynamic.', 'wphb' ); ?>
		</span>
		<div class="clear mline"></div>

		<label class="sui-toggle">
			<input type="hidden" name="cache-404" value="0">
			<input type="checkbox" class="toggle-checkbox" name="settings[cache-404]" value="1" id="cache-404" <?php checked( $settings['settings']['cache_404'] ); ?>>
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="cache-404"><?php esc_html_e( 'Cache 404 requests', 'wphb' ); ?></label>
		<span class="sui-description sui-toggle-description">
			<?php esc_html_e( 'Even though 404s are bad and you will want to avoid them with redirects, you can still choose to cache your 404 page to avoid additional load on your server.', 'wphb' ); ?>
		</span>
		<div class="clear mline"></div>

		<label class="sui-toggle">
			<input type="hidden" name="clear-update" value="0">
			<input type="checkbox" name="settings[clear-update]" value="1" id="clear-update" <?php checked( $settings['settings']['clear_update'] ); ?>>
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="clear-update"><?php esc_html_e( 'Clear full cache when post/page is updated', 'wphb' ); ?></label>
		<span class="sui-description sui-toggle-description">
			<?php esc_html_e( 'If one of your pages or posts gets updated, turning this setting on will also regenerate all cached archives and taxonomies for all post types.', 'wphb' ); ?>
		</span>
		<div class="clear mline"></div>

		<label class="sui-toggle">
			<input type="hidden" name="debug-log" value="0">
			<input type="checkbox" name="settings[debug-log]" value="1" id="debug-log" <?php checked( $settings['settings']['debug_log'] ); ?>>
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="debug-log"><?php esc_html_e( 'Enable debug log', 'wphb' ); ?></label>
		<span class="sui-description sui-toggle-description">
			<?php
			esc_html_e( 'If you’re having issues with page caching, turn on the debug log to get insight into what’s going on.', 'wphb' );
			if ( $settings['settings']['debug_log'] ) {
				if ( file_exists( WP_CONTENT_DIR . '/wphb-logs/page-caching-log.php' ) ) {
					?>
					<div class="clear"></div>
					<a href="<?php echo esc_url( $download_url ); ?>" class="sui-button sui-button-ghost" id="wphb-pc-log-button"><?php esc_html_e( 'Download Logs', 'wphb' ); ?></a>
					<div class="clear"></div>

					<?php
					printf(
						/* translators: %s: File location */
						esc_html__( 'Location: %s', 'wphb' ),
						esc_url( get_home_url() . '/wp-content/wphb-logs/page-caching-log.php' )
					);
				}
			}
			?>
	</span>

	</div><!-- end sui-box-settings-col-2 -->
</div><!-- end row -->

<?php if ( is_multisite() ) : ?>
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Subsites', 'wphb' ); ?></span>
		</div>
		<div class="sui-box-settings-col-2">
			<label class="sui-toggle">
				<input type="hidden" name="admins_disable_caching" value="0">
				<input type="checkbox" class="toggle-checkbox" name="settings[admins_disable_caching]" value="1" id="admins_disable_caching" <?php checked( $admins_can_disable ); ?>>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="admins_disable_caching"><?php esc_html_e( 'Allow subsites to disable page caching', 'wphb' ); ?></label>
			<span class="sui-description sui-toggle-description">
				<?php esc_html_e( 'This setting adds the Page Caching tab to Hummingbird and allows a network or subsite admin to disable Page Caching if they wish to. Note: It does not allow them to modify your network settings.', 'wphb' ); ?>
			</span>
		</div>
	</div>
<?php endif; ?>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Exclusions', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Specify any particular URLs you don’t want to cache at all.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<span class="sui-settings-label"><?php esc_html_e( 'URL Strings', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'You can tell Hummingbird not to cache specific URLs, or any URLs that contain strings. Add one entry per line.', 'wphb' ); ?>
		</span>
		<div class="sui-form-field">
			<textarea class="sui-form-control" name="url_strings"><?php foreach ( $settings['exclude']['url_strings'] as $url_string ) { echo $url_string . PHP_EOL; }?></textarea>
		</div>
		<span class="sui-description sui-with-bottom-border">
			<?php echo __( 'For example, if you want to not cache any pages that are nested under your Forums
				area you might add "/forums/" as a rule. When Hummingbird goes to cache pages, she will ignore any
				URL that contains "/forums/". To exclude a specific page you might add "/forums/thread-title". Accepts
				regular expression syntax, for more complex exclusions it can be helpful to test
				on <a href="https://regex101.com" target="_blank">regex101.com</a>. Note: Hummingbird will auto convert
				your input into valid regex syntax.', 'wphb' ); ?>
		</span>
		<span class="sui-settings-label"><?php esc_html_e( 'User agents', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Specify any user agents you don’t want to send cached pages to like bots, spiders and crawlers. We’ve added a couple of common ones for you.', 'wphb' ); ?>
		</span>
		<div class="sui-form-field">
			<textarea class="sui-form-control"  name="user_agents"><?php foreach ( $settings['exclude']['user_agents'] as $user_agent ) { echo $user_agent . PHP_EOL; } ?></textarea>
		</div>
	</div>
</div>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<strong><?php esc_html_e( 'Deactivate', 'wphb' ); ?></strong>
		<span class="sui-description">
			<?php esc_html_e( 'You can deactivate page caching at any time. ', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2 wphb-deactivate-pc">
		<a href="<?php echo esc_url( $deactivate_url ); ?>" class="sui-button sui-button-ghost">
			<?php esc_html_e( 'Deactivate', 'wphb' ); ?>
		</a>
		<span class="sui-description">
			<?php esc_html_e( 'Note: Deactivating won’t lose any of your website data, only the cached pages will be removed and won’t be served to your visitors any longer. Remember this may result in slower page loads unless you have another caching plugin activate.', 'wphb' ); ?>
		</span>
	</div>
</div>