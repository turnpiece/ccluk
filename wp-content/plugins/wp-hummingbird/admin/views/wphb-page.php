<div class="row">
	<?php $this->do_meta_boxes( 'main' ); ?>
</div>

<div class="row">
	<div class="col-half"><?php $this->do_meta_boxes( 'box-dashboard-left' ); ?></div>
	<div class="col-half"><?php $this->do_meta_boxes( 'box-dashboard-right' ); ?></div>
</div>

<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="row" id="wphb-cross-sell-footer">
		<div><span class="wphb-icon hb-fi-plugin-2"></span></div>
		<h3><?php esc_html_e( 'Check out our other free wordpress.org plugins!', 'wphb' ); ?></h3>
	</div>

	<div class="row wphb-cross-sell-modules">
		<div class="col-third">
			<div class="wphb-cross-smush"><span></span></div>
			<div class="wphb-content">
				<h3><?php esc_html_e( 'Smush Image Compression and Optimization', 'wphb' ); ?></h3>
				<p><?php esc_html_e( 'Resize, optimize and compress all of your images with the incredibly powerful and award-winning, 100% free WordPress image optimizer.', 'wphb' ); ?></p>
				<a href="https://wordpress.org/plugins/wp-smushit/" class="button button-ghost" target="_blank">
					<?php esc_html_e( 'View features', 'wphb' ); ?>
				</a>
			</div>
		</div>

		<div class="col-third">
			<div class="wphb-cross-defender"><span></span></div>
			<div class="wphb-content">
				<h3><?php esc_html_e( 'Defender Security, Monitoring, and Hack Protection', 'wphb' ); ?></h3>
				<p><?php esc_html_e( 'Security Tweaks & Recommendations, File & Malware Scanning, Login & 404 Lockout Protection, Two-Factor Authentication & more.', 'wphb' ); ?></p>
				<a href="https://wordpress.org/plugins/defender-security/" class="button button-ghost" target="_blank">
					<?php esc_html_e( 'View features', 'wphb' ); ?>
				</a>
			</div>
		</div>

		<div class="col-third">
			<div class="wphb-cross-crawl"><span></span></div>
			<div class="wphb-content">
				<h3><?php esc_html_e( 'SmartCrawl Search Engine Optimization', 'wphb' ); ?></h3>
				<p><?php esc_html_e( 'Customize Titles & Meta Data, OpenGraph, Twitter & Pinterest Support, Auto-Keyword Linking, SEO & Readability Analysis, Sitemaps, URL Crawler & more.', 'wphb' ); ?></p>
				<a href="https://wordpress.org/plugins/smartcrawl-seo/" class="button button-ghost" target="_blank">
					<?php esc_html_e( 'View features', 'wphb' ); ?>
				</a>
			</div>
		</div>
	</div>

	<div class="row wphb-cross-sell-bottom">
		<h3><?php esc_html_e( 'WPMU DEV - Your WordPress Toolkit', 'wphb' ); ?></h3>
		<p><?php esc_html_e( 'Pretty much everything you need for developing and managing WordPress based websites, and then some.', 'wphb' ); ?></p>

		<a class="button button-content-cta" href="#wphb-upgrade-membership-modal" id="dash-uptime-update-membership" rel="dialog">
			<?php esc_html_e( 'Learn more', 'wphb' ); ?>
		</a>

		<img class="wphb-image"
			 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/dev-team.png'; ?>"
			 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/dev-team@2x.png'; ?> 2x"
			 alt="<?php esc_attr_e( 'Try pro features for free!', 'wphb' ); ?>">
	</div>
<?php endif;

WP_Hummingbird_Utils::get_modal( 'membership' );
?>

<script>
	jQuery( document).ready( function () {
		window.WPHB_Admin.getModule( 'dashboard' );
	});
</script>