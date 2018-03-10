<dialog id="wphb-upgrade-membership-modal" class="small wphb-modal" title="<?php esc_attr_e( 'Upgrade Membership', 'wphb' ); ?>">
	<div class="wphb-dialog-content dialog-upgrade">

		<p><?php esc_html_e( "Here's what you'll get by upgrading to Hummingbird Pro.", 'wphb' ); ?></p>

		<ul class="listing wphb-listing">
			<li>
				<strong><?php esc_html_e( 'Automation', 'wphb' ); ?></strong>
				<p><?php esc_html_e( 'Schedule Hummingbird to run regular performance tests daily, weekly or monthly and get email reports delivered straight to your inbox.', 'wphb' ); ?></p>
			</li>
			<li>
				<strong><?php esc_html_e( 'Enhanced Asset Optimization', 'wphb' ); ?></strong>
				<p><?php esc_html_e( 'Compress your minified files up to 2x more than regular optimization and reduce your page load speed even further.', 'wphb' ); ?></p>
			</li>
			<li>
				<strong><?php esc_html_e( 'WPMU DEV CDN', 'wphb' ); ?></strong>
				<p><?php esc_html_e( 'By default we minify your files via our API and send them back to your server. Pro users can host their files on WPMU DEV’s secure and hyper fast CDN, which will mean less load on your server and a fast visitor experience.', 'wphb' ); ?></p>
			</li>
			<li>
				<strong><?php esc_html_e( 'Smush Pro', 'wphb' ); ?></strong>
				<p><?php esc_html_e( 'A membership for Hummingbird Pro also gets you the award winning Smush Pro with unlimited advanced lossy compression that’ll give image heavy websites a speed boost.', 'wphb' ); ?></p>
			</li>
		</ul>

		<p class="wphb-block-content-center"><?php esc_html_e( 'Get all of this, plus heaps more as part of a WPMU DEV membership.', 'wphb' ); ?></p>

		<div class="wphb-block-content-center">
			<a target="_blank" href="<?php echo WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_footer_upgrade_button' ); ?>" class="button button-content-cta button-large">
				<?php esc_html_e( 'Upgrade Membership', 'wphb' ); ?>
			</a>
		</div>

		<div class="wphb-modal-image wphb-modal-image-bottom dev-man">
			<img class="wphb-image wphb-image-center"
				 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/dev-team.png'; ?>"
				 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/dev-team@2x.png'; ?> 2x"
				 alt="<?php esc_attr_e( 'Hummingbird','wphb' ); ?>">
		</div>

	</div>
</dialog><!-- end wphb-upgrade-membership-modal -->