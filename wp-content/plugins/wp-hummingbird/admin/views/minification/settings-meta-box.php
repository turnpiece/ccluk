<?php
/**
 * Asset optimization settings meta box.
 *
 * @package Hummingbird
 *
 * @var bool   $cdn_status  CDN status.
 * @var string $file_path   Path to store files.
 * @var bool   $is_member   Member status.
 * @var bool   $logging     Logging status.
 */

?>

<?php if ( ! $cdn_status ) : ?>
<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'File Location', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Choose where Hummingbird should store your modified assets.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<label for="file_path">
			<input type="text" class="sui-form-control" name="file_path" id="file_path" placeholder="/wp-content/uploads/hummingbird-assets/" value="<?php echo esc_attr( $file_path ); ?>">
		</label>
		<span class="sui-description">
			<?php esc_html_e( 'Leave this blank to use the default folder, or define your own as a relative path.
			Note: changing the directory will clear out al the generated assets.', 'wphb' ); ?>
		</span>
	</div>
</div>
<?php endif; ?>

<div class="sui-box-settings-row <?php echo ( ! $is_member ) ? ' sui-disabled' : ''; ?>">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Super-compress my files', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Compress your files up to 2x more with our enhanced optimization engine.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<?php if ( $is_member ) : ?>
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
	<div class="sui-box-settings-row <?php echo ( ! $is_member ) ? ' sui-disabled' : ''; ?>">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Enable WPMU DEV CDN', 'wphb' ); ?></span>
			<span class="sui-description">
				<?php esc_html_e( 'Host your files on WPMU DEV’s secure and hyper fast CDN.', 'wphb' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<label class="sui-toggle sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_html_e( 'Enable WPMU DEV CDN', 'wphb' ); ?>">
				<input type="checkbox" name="use_cdn" id="use_cdn" <?php checked( $cdn_status && $is_member ); ?> <?php disabled( ! $is_member ); ?>>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="use_cdn"><?php esc_html_e( 'Host my files on the WPMU DEV CDN', 'wphb' ); ?></label>
			<span class="sui-description sui-toggle-description">
				<?php esc_html_e( 'Enabling this setting will serve your CSS, JS and other compatible files from
				our external CDN, effectively taking the load off your server so that pages load faster for
				your visitors.', 'wphb' ); ?>
			</span>
		</div>
	</div>
<?php endif; ?>

<?php if ( $cdn_status ) : ?>
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'File Location', 'wphb' ); ?></span>
			<span class="sui-description">
			<?php esc_html_e( 'Choose where Hummingbird should store your modified assets.', 'wphb' ); ?>
		</span>
		</div>
		<div class="sui-box-settings-col-2">
			<div class="sui-notice sui-notice-warning">
				<p>
					<?php esc_html_e( 'This feature is inactive when you’re using the WPMU DEV CDN.', 'wphb' ); ?>
				</p>
			</div>
			<label for="file_path">
				<input type="text" class="sui-form-control" name="file_path" id="file_path" placeholder="/wp-content/uploads/hummingbird-assets/" disabled>
			</label>
			<span class="sui-description">
			<?php esc_html_e( 'Leave this blank to use the default folder, or define your own as a relative path.', 'wphb' ); ?>
		</span>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! $is_member ) : ?>
	<div class="sui-box-settings-row sui-upsell-row">
		<img class="sui-image sui-upsell-image"
			 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-upsell-minify.png' ); ?>"
			 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-upsell-minify@2x.png' ); ?> 2x"
			 alt="<?php esc_attr_e( 'WP Smush free installed', 'wphb' ); ?>">
		<div class="sui-upsell-notice">
			<p>
				<?php printf(
					/* translators: %s: upsell modal href link */
					__( "With our pro version of Hummingbird you can super-compress your files and then host them on our blazing fast CDN. You'll get Hummingbird Pro plus 100+ WPMU DEV plugins & 24/7 WP support.  <a href='%s' target='_blank'>Try Pro for FREE today!</a>", 'wphb' ),
					WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_assetoptimization_settings_upsell_link' )
				); ?>
			</p>
		</div>
	</div><!-- end sui-upsell-row -->
<?php
endif;

if ( ! is_multisite() ) :
?>
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Debug', 'wphb' ); ?></span>
			<span class="sui-description">
				<?php esc_html_e( 'Turn on the debug log to get insight into any issues you’re having.', 'wphb' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<label class="sui-toggle sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_html_e( 'Enable debug log', 'wphb' ); ?>">
				<input type="checkbox" name="debug_log" id="debug_log" <?php checked( $logging ); ?>>
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
		<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'reset', 'true' ), 'wphb-reset-minification' ) ); ?>" class="sui-button sui-button-ghost">
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
		<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'disable', 'true' ), 'wphb-disable-minification' ) ); ?>" class="sui-button sui-button-ghost">
			<?php esc_html_e( 'Deactivate', 'wphb' ); ?>
		</a>
		<span class="sui-description"><?php esc_html_e( 'Note: This will not remove any files, they will just go back to their original, unoptimized state.', 'wphb' ); ?></span>
	</div>
</div>