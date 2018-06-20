<?php
/**
 * Dashboard page
 *
 * @package Hummingbird
 */

$this->do_meta_boxes( 'main' ); ?>

<div class="sui-row">
	<div class="sui-col-lg-6"><?php $this->do_meta_boxes( 'box-dashboard-left' ); ?></div>
	<div class="sui-col-lg-6"><?php $this->do_meta_boxes( 'box-dashboard-right' ); ?></div>
</div>

<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="sui-row" id="sui-cross-sell-footer">
		<div><span class="sui-icon-plugin-2"></span></div>
		<h3><?php esc_html_e( 'Check out our other free wordpress.org plugins!', 'wphb' ); ?></h3>
	</div>
	<div class="sui-row sui-cross-sell-modules">
		<div class="sui-col-md-4">
			<div class="wphb-cross-smush"><span></span></div>
			<div class="sui-box">
				<div class="sui-box-body">
					<h3><?php esc_html_e( 'Smush Image Compression and Optimization', 'wphb' ); ?></h3>
					<p><?php esc_html_e( 'Resize, optimize and compress all of your images with the incredibly powerful and award-winning, 100% free WordPress image optimizer.', 'wphb' ); ?></p>
					<a href="https://wordpress.org/plugins/wp-smushit/" class="sui-button sui-button-ghost" target="_blank">
						<?php esc_html_e( 'View features', 'wphb' ); ?>
					</a>
				</div>
			</div>
		</div>

		<div class="sui-col-md-4">
			<div class="wphb-cross-defender"><span></span></div>
			<div class="sui-box">
				<div class="sui-box-body">
					<h3><?php esc_html_e( 'Defender Security, Monitoring, and Hack Protection', 'wphb' ); ?></h3>
					<p><?php esc_html_e( 'Security Tweaks & Recommendations, File & Malware Scanning, Login & 404 Lockout Protection, Two-Factor Authentication & more.', 'wphb' ); ?></p>
					<a href="https://wordpress.org/plugins/defender-security/" class="sui-button sui-button-ghost" target="_blank">
						<?php esc_html_e( 'View features', 'wphb' ); ?>
					</a>
				</div>
			</div>
		</div>

		<div class="sui-col-md-4">
			<div class="wphb-cross-crawl"><span></span></div>
			<div class="sui-box">
				<div class="sui-box-body">
					<h3><?php esc_html_e( 'SmartCrawl Search Engine Optimization', 'wphb' ); ?></h3>
					<p><?php esc_html_e( 'Customize Titles & Meta Data, OpenGraph, Twitter & Pinterest Support, Auto-Keyword Linking, SEO & Readability Analysis, Sitemaps, URL Crawler & more.', 'wphb' ); ?></p>
					<a href="https://wordpress.org/plugins/smartcrawl-seo/" class="sui-button sui-button-ghost" target="_blank">
						<?php esc_html_e( 'View features', 'wphb' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
	<div class="sui-cross-sell-bottom">
		<h3><?php esc_html_e( 'WPMU DEV - Your WordPress Toolkit', 'wphb' ); ?></h3>
		<p><?php esc_html_e( 'Pretty much everything you need for developing and managing WordPress based websites, and then some.', 'wphb' ); ?></p>

		<a class="sui-button sui-button-green" data-a11y-dialog-show="wphb-upgrade-membership-modal" id="dash-uptime-update-membership">
			<?php esc_html_e( 'Learn more', 'wphb' ); ?>
		</a>

		<img class="wphb-image"
			 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/dev-team.png' ); ?>"
			 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/dev-team@2x.png' ); ?> 2x"
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