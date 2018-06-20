<div class="dialog sui-dialog" aria-hidden="true" id="wphb-upgrade-membership-modal">

	<div class="sui-dialog-overlay" data-a11y-dialog-hide tabindex="-1"></div>

	<div class="sui-dialog-content" aria-labelledby="upgradeMembership" aria-describedby="dialogDescription"
		 role="dialog">

		<div class="sui-box sui-no-margin-bottom" role="document">

			<div class="sui-box-header">
				<h3 class="sui-box-title" id="upgradeMembership">
					<?php esc_html_e( 'Upgrade Membership', 'wphb' ); ?>
				</h3>
				<div class="sui-actions-right">
					<button data-a11y-dialog-hide class="sui-dialog-close"
							aria-label="Close this dialog window"></button>
				</div>
			</div>

			<div class="sui-box-body sui-no-padding-bottom">
				<p><?php esc_html_e( "Here's what you'll get by upgrading to Hummingbird Pro.", 'wphb' ); ?></p>

				<ul class="sui-listing wphb-listing">
					<li>
						<strong><?php esc_html_e( 'Automation', 'wphb' ); ?></strong>
						<p><?php esc_html_e( 'Schedule Hummingbird to run regular performance tests daily, weekly or monthly and get email reports delivered straight to your inbox.', 'wphb' ); ?></p>
					</li>
					<li>
						<strong><?php esc_html_e( 'Enhanced Asset Optimization', 'wphb' ); ?></strong>
						<p><?php esc_html_e( 'Compress your minified files up to 2x more than regular optimization and reduce your page load time even further.', 'wphb' ); ?></p>
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

				<p class="sui-block-content-center"><?php esc_html_e( 'Get all of this, plus heaps more as part of a WPMU DEV membership.', 'wphb' ); ?></p>

				<div class="sui-block-content-center">
					<a target="_blank"
					   href="<?php echo esc_url( WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_footer_upgrade_button' ) ); ?>"
					   class="sui-button sui-button-green sui-button-lg">
						<?php esc_html_e( 'Upgrade Membership', 'wphb' ); ?>
					</a>
				</div>

				<div class="wphb-modal-image wphb-modal-image-bottom dev-man">
					<img class="wphb-image wphb-image-center"
						 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/dev-team.png' ); ?>"
						 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/dev-team@2x.png' ); ?> 2x"
						 alt="<?php esc_attr_e( 'Hummingbird', 'wphb' ); ?>">
				</div>

			</div>

		</div>
	</div>
</div>