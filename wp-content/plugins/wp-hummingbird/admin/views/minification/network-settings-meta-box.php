<?php
/**
 * Network settings meta box.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var string      $download_url  Link to download log files.
 * @var bool|string $enabled       Status of Asset Optimization module.
 * @var bool        $is_member     Is user a PRO user.
 * @var bool        $log_enabled   Logging is enabled.
 * @var bool|string $type          Permissions type: 'super-admins' or true.
 * @var bool        $use_cdn       CDN status.
 */

?>

<div class="sui-box-settings-row">

	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Subsites', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Choose whether you want to enable the asset optimization in your subsites and further configure it as per your liking.', 'wphb' ); ?>
		</span>
	</div>

	<div class="sui-box-settings-col-2">
		<form id="ao-network-settings-form">
			<label class="sui-toggle">
				<input type="checkbox" name="network" id="wphb-network-ao" <?php checked( $enabled ); ?>>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="toggle-with-label">
				<?php esc_html_e( 'Enable Asset Optimization module for your subsites', 'wphb' ); ?>
			</label>

			<div class="sui-border-frame <?php echo $enabled ? '' : 'sui-hidden'; ?>">
				<div>
					<span class="sui-settings-label"><?php esc_html_e( 'Minimum user role', 'wphb' ); ?></span>
					<span class="sui-description">
						<?php esc_html_e( 'Choose the minimum user role required to configure the Asset Optimization module in your subsites.', 'wphb' ); ?>
					</span>

					<div class="sui-side-tabs">
						<div class="sui-tabs-menu">
							<label for="enabled-true" class="sui-tab-item <?php echo 'super-admins' === $type ? 'active' : ''; ?>">
								<input type="radio" name="enabled" value="super-admins" id="enabled-true" <?php checked( $type, 'super-admins' ); ?>>
								<?php esc_html_e( 'Super Admin', 'wphb' ); ?>
							</label>

							<label for="enabled-false" class="sui-tab-item <?php echo true === $type ? 'active' : ''; ?>">
								<input type="radio" name="enabled" value="1" id="enabled-false" <?php checked( $type, true ); ?>>
								<?php esc_html_e( 'Subsite Admin', 'wphb' ); ?>
							</label>
						</div>
					</div>
				</div>

				<div <?php echo $is_member ? '' : 'class="sui-disabled"'; ?>>
					<span class="sui-settings-label"><?php esc_html_e( 'Host files on WPMU DEV CDN', 'wphb' ); ?></span>
					<span class="sui-description">
						<?php esc_html_e( 'Enabling this setting will serve your CSS, JS and other compatible files from our external CDN, effectively taking the load off your server so that pages load faster for your visitors.', 'wphb' ); ?>
					</span>

					<div class="sui-side-tabs">
						<div class="sui-tabs-menu">
							<label for="use_cdn-true" class="sui-tab-item <?php echo $use_cdn ? 'active' : ''; ?>">
								<input type="radio" name="use_cdn" value="1" id="use_cdn-true" <?php checked( $use_cdn ); ?> <?php disabled( ! $is_member ); ?>>
								<?php esc_html_e( 'Enable', 'wphb' ); ?>
							</label>

							<label for="use_cdn-false" class="sui-tab-item <?php echo $use_cdn ? '' : 'active'; ?>">
								<input type="radio" name="use_cdn" value="0" id="use_cdn-false" <?php checked( $use_cdn, false ); ?> <?php disabled( ! $is_member ); ?>>
								<?php esc_html_e( 'Disable', 'wphb' ); ?>
							</label>
						</div>
					</div>
				</div>

				<div>
					<span class="sui-settings-label"><?php esc_html_e( 'Debug logs', 'wphb' ); ?></span>
					<span class="sui-description">
						<?php esc_html_e( 'Enable the debug log to get insight into any issues you’re having.', 'wphb' ); ?>
					</span>

					<div class="sui-side-tabs">
						<div class="sui-tabs-menu">
							<label for="log-true" class="sui-tab-item <?php echo $log_enabled ? 'active' : ''; ?>">
								<input type="radio" name="log" value="1" id="log-true" <?php checked( $log_enabled ); ?>>
								<?php esc_html_e( 'Enable', 'wphb' ); ?>
							</label>

							<label for="log-false" class="sui-tab-item <?php echo $log_enabled ? '' : 'active'; ?>">
								<input type="radio" name="log" value="0" id="log-false" <?php checked( $log_enabled, false ); ?>>
								<?php esc_html_e( 'Disable', 'wphb' ); ?>
							</label>
						</div>
					</div>

					<div class="sui-border-frame wphb-logs-frame <?php echo ! $log_enabled ? 'sui-hidden' : ''; ?>">
						<div class="sui-notice sui-notice-info">
							<p><?php esc_html_e( "Debug logging is active. Logs are stored for 30 days. You can download each subsite's log file via their Asset Optimization / Settings tabs.", 'wphb' ); ?></p>
						</div>
					</div>
				</div>

				<?php if ( ! $is_member ) : ?>
					<div class="sui-upsell-row">
						<img class="sui-image sui-upsell-image"
							src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-upsell-minify.png' ); ?>"
							srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-upsell-minify@2x.png' ); ?> 2x"
							alt="<?php esc_attr_e( 'WP Smush free installed', 'wphb' ); ?>">
						<div class="sui-upsell-notice">
							<p>
								<?php
								printf(
									/* translators: %1$s: upsell modal href link, %2$s: closing a tag */
									esc_html__( "With pro version of Hummingbird you can host your compressed files on our blazing fast CDN. You'll get Hummingbird Pro plus 100+ WPMU DEV plugins, themes & 24/7 WP support. %1\$sTry Pro for FREE today!%2\$s", 'wphb' ),
									'<a href="' . esc_html( WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_test_multisite_cdn_upsell_link' ) ) . '" target="_blank">',
									'</a>'
								);
								?>
							</p>
						</div>
					</div>
				<?php endif; ?>
			</div><!-- end sui-border-frame -->
		</form>
	</div><!-- end sui-box-settings-col-2 -->

</div><!-- end sui-box-settings-row -->
