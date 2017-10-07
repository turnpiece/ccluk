<?php
/**
 * Minification network meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var bool $use_cdn           CDN status.
 * @var bool $use_cdn_disabled  Can use CDN?
 */

?>
<div class="content">
	<p><?php esc_html_e( 'Minification can be a bit daunting to configure for beginners, and has the potential to break the frontend of a site. You can choose here who can configure Minification options on subsites in your Multisite install.', 'wphb' ); ?></p>

	<div class="wphb-notice wphb-notice-success hidden" id="wphb-notice-minification-settings-updated">
		<p><?php esc_html_e( 'Minification settings updated', 'wphb' ); ?></p>
	</div>

	<?php if ( isset( $_GET['minify-instructions'] ) ): ?>
		<div class="wphb-notice wphb-notice-warning">
			<p><?php esc_html_e( 'Please, activate minification first. A new menu will appear in every site on your Network.', 'wphb' ); ?></p>
		</div>
	<?php endif; ?>

	<label for="wphb-activate-minification"><span class="screen-reader-text"><?php esc_html_e( 'Select users that can minify in this network', 'wphb' ); ?></span></label>
	<select name="wphb-activate-minification" id="wphb-activate-minification">
		<option value="false" <?php selected( wphb_get_setting( 'minify' ), false ); ?>><?php esc_html_e( 'Deactivate completely', 'wphb' ); ?></option>
		<option value="true" <?php selected( wphb_get_setting( 'minify' ), true ); ?>><?php esc_html_e( 'Blog Admins can minify', 'wphb' ); ?></option>
		<option value="super-admins" <?php selected( wphb_get_setting( 'minify' ), 'super-admins' ); ?>><?php esc_html_e( 'Only Super Admins can minify', 'wphb' ); ?></option>
	</select>
	<div class="toggle-item space-top-small">
		<div class="toggle-item-group">
			<?php
			$tooltip_msg = __( 'Enable WPMU DEV CDN', 'wphb' );
			if ( $use_cdn_disabled ) {
				$tooltip_msg = __( 'Enable minification to use the WPMU DEV CDN', 'wphb' );
			}
			?>
			<label for="use_cdn"><?php esc_html_e( 'Store my files on the WPMU DEV CDN', 'wphb' ); ?></label>
			<div class="toggle-actions">
				<span class="toggle tooltip-right" tooltip="<?php echo esc_attr( $tooltip_msg ); ?>">
					<input type="checkbox" class="toggle-checkbox" name="use_cdn" id="use_cdn" <?php checked( $use_cdn ); ?> <?php disabled( $use_cdn_disabled ); ?>>
					<label for="use_cdn" class="toggle-label"></label>
				</span>
			</div><!-- end toggle-actions -->
		</div>
	</div>
</div><!-- end content -->