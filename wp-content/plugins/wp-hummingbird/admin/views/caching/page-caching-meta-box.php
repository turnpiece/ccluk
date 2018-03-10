<?php
/**
 * Page caching meta box.
 *
 * @package Hummingbird
 *
 * @var bool          $admins_can_disable  Blog admins can disable page caching.
 * @var string        $deactivate_url      Deactivate URL.
 * @var string        $download_url        Download logs URL.
 * @var bool|WP_Error $error               Error if present.
 * @var array         $pages               A list of page types.
 * @var array         $settings            Settings array.
 */

?>
<div class="row settings-form with-bottom-border">
	<p><?php esc_html_e( 'Hummingbird stores static HTML copies of your pages and posts to decrease page load time.', 'wphb' ); ?></p>

	<?php if ( is_wp_error( $error ) ) : ?>
		<div class="wphb-caching-error wphb-notice wphb-notice-error">
			<p><?php echo $error->get_error_message(); ?></p>
		</div>
	<?php else : ?>
		<div class="wphb-caching-success wphb-notice wphb-notice-success">
			<p><?php esc_html_e( 'Page caching is currently active.', 'wphb' ); ?></p>
		</div>
	<?php endif; ?>
</div><!-- end row -->

<form id="page-caching-form" method="post">
	<div class="row settings-form with-bottom-border">
		<div class="col-third">
			<strong><?php esc_html_e( 'Page Types', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'Select which page types you wish to cache. Note: You can exclude individual post/pages with URL string rules in Exclusions section below.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">

			<div class="wphb-dash-table three-columns">
				<?php foreach ( $pages as $page_type => $page_name ) : ?>
					<div class="wphb-dash-table-row">
						<div><?php echo esc_html( $page_name ); ?></div>
						<span class="sub"><?php echo esc_html( $page_type ); ?></span>
						<span class="toggle">
							<input type="checkbox" class="toggle-checkbox" name="page_types[<?php echo esc_attr( $page_type ); ?>]" id="<?php echo esc_attr( $page_type ); ?>" <?php checked( in_array( $page_type, $settings['page_types'] ) ); ?>>
							<label class="toggle-label small" for="<?php echo esc_attr( $page_type ); ?>"></label>
						</span>
					</div>
				<?php endforeach; ?>
			</div>

		</div>
	</div>

	<!--
	<div class="row settings-form with-bottom-border">
		<div class="col-third">
			<strong><?php esc_html_e( 'Compression', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'Serve compressed versions of your cached pages.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">

		</div>
	</div>

	<div class="row settings-form with-bottom-border">
		<div class="col-third">
			<strong><?php esc_html_e( 'Location', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'Change where Hummingbird stores you cached page files.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">

		</div>
	</div>

	<div class="row settings-form with-bottom-border">
		<div class="col-third">
			<strong><?php esc_html_e( 'Automatic Flush', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'This setting automatically clears your cached pages on a regular schedule.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">

		</div>
	</div>
	-->

	<div class="row settings-form with-bottom-border">
		<div class="col-third">
			<strong><?php esc_html_e( 'Settings', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'Fine tune page caching to work how you want it to.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">

			<span class="toggle">
				<input type="hidden" name="logged-in" value="0">
				<input type="checkbox" class="toggle-checkbox" name="settings[logged-in]" value="1" id="logged-in" <?php checked( $settings['settings']['logged_in'] ); ?>>
				<label class="toggle-label small" for="logged-in" aria-hidden="true"></label>
			</span>
			<label for="logged-in"><?php esc_html_e( 'Include logged in users', 'wphb' ); ?></label>
			<span class="sub">
				<?php esc_html_e( 'Caching pages for logged in users can reduce load on your server, but can cause strange behavior with some themes/plugins.', 'wphb' ); ?>
			</span>
			<div class="clear mline"></div>

			<span class="toggle">
				<input type="hidden" name="url-queries" value="0">
				<input type="checkbox" class="toggle-checkbox" name="settings[url-queries]" value="1" id="url-queries" <?php checked( $settings['settings']['url_queries'] ); ?>>
				<label class="toggle-label small" for="url-queries" aria-hidden="true"></label>
			</span>
			<label for="url-queries"><?php esc_html_e( 'Cache URL queries', 'wphb' ); ?></label>
			<span class="sub">
				<?php esc_html_e( 'You can turn on caching pages with GET parameters (?x=y at the end of a url), though generally this isn’t a good idea if those pages are dynamic.', 'wphb' ); ?>
			</span>
			<div class="clear mline"></div>

			<span class="toggle">
				<input type="hidden" name="cache-404" value="0">
				<input type="checkbox" class="toggle-checkbox" name="settings[cache-404]" value="1" id="cache-404" <?php checked( $settings['settings']['cache_404'] ); ?>>
				<label class="toggle-label small" for="cache-404" aria-hidden="true"></label>
			</span>
			<label for="cache-404"><?php esc_html_e( 'Cache 404 requests', 'wphb' ); ?></label>
			<span class="sub">
				<?php esc_html_e( 'Even though 404s are bad and you will want to avoid them with redirects, you can still choose to cache your 404 page to avoid additional load on your server.', 'wphb' ); ?>
			</span>
			<div class="clear mline"></div>

			<span class="toggle">
				<input type="hidden" name="clear-update" value="0">
				<input type="checkbox" class="toggle-checkbox" name="settings[clear-update]" value="1" id="clear-update" <?php checked( $settings['settings']['clear_update'] ); ?>>
				<label class="toggle-label small" for="clear-update" aria-hidden="true"></label>
			</span>
			<label for="clear-update"><?php esc_html_e( 'Clear full cache when post/page is updated', 'wphb' ); ?></label>
			<span class="sub">
				<?php esc_html_e( 'If one of your pages or posts gets updated, turning this setting on will also regenerate all cached archives and taxonomies for all post types.', 'wphb' ); ?>
			</span>
			<div class="clear mline"></div>

			<span class="toggle">
				<input type="hidden" name="debug-log" value="0">
				<input type="checkbox" class="toggle-checkbox" name="settings[debug-log]" value="1" id="debug-log" <?php checked( $settings['settings']['debug_log'] ); ?>>
				<label class="toggle-label small" for="debug-log" aria-hidden="true"></label>
			</span>
			<label for="debug-log"><?php esc_html_e( 'Enable debug log', 'wphb' ); ?></label>
			<span class="sub">
				<?php
				esc_html_e( 'If you’re having issues with page caching, turn on the debug log to get insight into what’s going on.', 'wphb' );
				if ( $settings['settings']['debug_log'] ) {
					if ( file_exists( WP_CONTENT_DIR . '/wphb-logs/page-caching-log.php' ) ) {
						?>
						<a href="<?php echo esc_url( $download_url ); ?>" target="_blank" class="button button-ghost" id="wphb-pc-log-button"><?php esc_html_e( 'Download Logs', 'wphb' ); ?></a>
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

		</div><!-- end col-two-third -->
	</div><!-- end row -->

	<?php if ( is_multisite() ) : ?>
		<div class="row settings-form with-bottom-border">
			<div class="col-third">
				<strong><?php esc_html_e( 'Subsites', 'wphb' ); ?></strong>
				<span class="sub">
					<?php esc_html_e( 'Choose what options admins have for subsites.', 'wphb' ); ?>
				</span>
			</div>
			<div class="col-two-third">

				<span class="toggle">
					<input type="hidden" name="admins_disable_caching" value="0">
					<input type="checkbox" class="toggle-checkbox" name="settings[admins_disable_caching]" value="1" id="admins_disable_caching" <?php checked( $admins_can_disable ); ?>>
					<label class="toggle-label small" for="admins_disable_caching" aria-hidden="true"></label>
				</span>
				<label for="admins_disable_caching"><?php esc_html_e( 'Allow admins to disable page caching', 'wphb' ); ?></label>
				<span class="sub">
					<?php esc_html_e( 'This setting adds the Page Caching tab to Hummingbird and allows a subsite admin to disable Page Caching if they wish to. Note: It does not allow them to modify your settings.', 'wphb' ); ?>
				</span>
			</div>
		</div>
	<?php endif; ?>
	<div class="row settings-form with-bottom-border">
		<div class="col-third">
			<strong><?php esc_html_e( 'Exclusions', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'Specify any particular URLs you don’t want to cache at all.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">
			<h4><?php esc_html_e( 'URL Strings', 'wphb' ); ?></h4>
			<span class="desc">
				<?php esc_html_e( 'You can tell Hummingbird not to cache specific URLs, or any URLs that contain strings. Add one entry per line.', 'wphb' ); ?>
			</span>
			<textarea name="url_strings"><?php foreach ( $settings['exclude']['url_strings'] as $url_string ) { echo $url_string . PHP_EOL; }?></textarea>
			<span class="desc with-bottom-border">
				<?php echo __( 'For example, if you want to not cache any pages that are nested under your Forums area you might add "/forums/" as a rule. When Hummingbird goes to cache pages, she will ignore any URL that contains "/forums/". To exclude a specific page you might add "/forums/thread-title". Accepts regular expression syntax, for more complex exclusions it can be helpful to test on <a href="https://regex101.com" target="_blank">regex101.com</a>.', 'wphb' ); ?>
			</span>
			<h4><?php esc_html_e( 'User agents', 'wphb' ); ?></h4>
			<span class="desc">
				<?php esc_html_e( 'Specify any user agents you don’t want to send cached pages to like bots, spiders and crawlers. We’ve added a couple of common ones for you.', 'wphb' ); ?>
			</span>
			<textarea name="user_agents"><?php foreach ( $settings['exclude']['user_agents'] as $user_agent ) { echo $user_agent . PHP_EOL; } ?></textarea>
		</div>
	</div>

	<div class="row settings-form">
		<div class="col-third">
			<strong><?php esc_html_e( 'Deactivate', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'You can deactivate page caching at any time. ', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">
			<a href="<?php echo esc_url( $deactivate_url ); ?>" class="button button-ghost">
				<?php esc_html_e( 'Deactivate', 'wphb' ); ?>
			</a>
			<span class="desc">
				<?php esc_html_e( 'Note: Deactivating won’t lose any of your website data, only the cached pages will be removed and won’t be served to your visitors any longer. Remember this may result in slower page loads unless you have another caching plugin activate.', 'wphb' ); ?>
			</span>
		</div>
	</div>