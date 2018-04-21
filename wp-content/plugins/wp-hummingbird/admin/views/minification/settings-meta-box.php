<?php
/* @var WP_Hummingbird_Module_Minify $minify */
$minify = WP_Hummingbird_Utils::get_module( 'minify' );
?>
<div class="sui-box-settings-row <?php echo ( ! WP_Hummingbird_Utils::is_member() ) ? ' sui-disabled' : ''; ?>">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Super-compress my files', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Compress your files up to 2x more with our enhanced optimization engine.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<?php if ( WP_Hummingbird_Utils::is_member() ) : ?>
			<span class="sui-tag sui-tag-disabled"><?php esc_html_e( 'Auto-enabled', 'wphb' ); ?></span>
		<?php else : ?>
			<label class="sui-toggle sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_html_e( 'Enable Super-minify my files', 'wphb' ); ?>">
				<input type="checkbox" name="super_minify_files" id="super_minify_files" disabled>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="super_minify_files"><?php esc_html_e( 'Enable super-compression', 'wphb' ); ?></label>
		<?php endif; ?>
	</div>
</div>

<?php if ( ! is_multisite() ) : ?>
	<div class="sui-box-settings-row <?php echo ( ! WP_Hummingbird_Utils::is_member() ) ? ' sui-disabled' : ''; ?>">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Enable WPMU DEV CDN', 'wphb' ); ?></span>
			<span class="sui-description">
				<?php esc_html_e( 'Host your files on WPMU DEV’s secure and hyper fast CDN.', 'wphb' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<label class="sui-toggle sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_html_e( 'Enable WPMU DEV CDN', 'wphb' ); ?>">
				<input type="checkbox" name="use_cdn" id="use_cdn" <?php checked( $minify->get_cdn_status() && WP_Hummingbird_Utils::is_member() ); ?> <?php disabled( ! WP_Hummingbird_Utils::is_member() ); ?>>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="use_cdn"><?php esc_html_e( 'Host my files on the WPMU DEV CDN', 'wphb' ); ?></label>
			<span class="sui-description sui-toggle-description">
				<?php esc_html_e( 'Enabling this setting will serve your CSS, JS and other compatible files from our external CDN, effectively taking the load off your server so that pages load faster for your visitors.', 'wphb' ); ?>
			</span>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="sui-box-settings-row">
		<div class="content-box content-box-two-cols-image-left">
			<div class="wphb-block-entry-image wphb-block-entry-image-bottom">
				<img class="wphb-image"
					 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hummingbird-upsell-minify.png'; ?>"
					 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hummingbird-upsell-minify@2x.png'; ?> 2x"
					 alt="<?php esc_attr_e( 'WP Smush free installed', 'wphb' ); ?>">
			</div>
			<div class="wphb-block-entry-content wphb-upsell-free-message">
				<p>
					<?php printf(
						/* translators: %s: upsell modal href link */
						__( "With our pro version of Hummingbird you can super-compress your files and then host them on our blazing fast CDN. You'll get Hummingbird Pro plus 100+ WPMU DEV plugins, themes & 24/7 WP support.  <a href='%s' target='_blank'>Try Pro for FREE today!</a>", 'wphb' ),
						WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_assetoptimization_settings_upsell_link' )
					); ?>
				</p>
			</div>
		</div><!-- end content-box -->
	</div><!-- end settings-form -->
<?php endif;

$options = $minify->get_options();
if ( ! is_multisite() || is_main_site() ) : ?>
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Debug', 'wphb' ); ?></span>
			<span class="sui-description">
				<?php esc_html_e( 'Turn on the debug log to get insight into any issues you’re having.', 'wphb' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<label class="sui-toggle sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_html_e( 'Enable debug log', 'wphb' ); ?>">
				<input type="checkbox" name="debug_log" id="debug_log" <?php checked( $options['log'] ); ?>>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="debug_log"><?php esc_html_e( 'Enable debug log', 'wphb' ); ?></label>
		</div>
	</div>
<?php endif; ?>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Reset to defaults', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Use this button to wipe any existing settings and return to defaults.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<a href="<?php echo esc_url( add_query_arg( 'reset-minification', 'true' ) ); ?>" class="sui-button sui-button-ghost">
			<?php esc_html_e( 'Reset', 'wphb' ); ?>
		</a>
		<span class="sui-description"><?php esc_html_e( 'Note: This will clear all your settings and run a new file check.', 'wphb' ); ?></span>
	</div>
</div>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Deactivate', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'No longer need Asset Optimization? This will completely deactivate this feature.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<a href="<?php echo esc_url( add_query_arg( 'disable-minification', 'true' ) ); ?>" class="sui-button sui-button-ghost">
			<?php esc_html_e( 'Deactivate', 'wphb' ); ?>
		</a>
		<span class="sui-description"><?php esc_html_e( 'Note: This will not remove any files, they will just go back to their original, unoptimized state.', 'wphb' ); ?></span>
	</div>
</div>