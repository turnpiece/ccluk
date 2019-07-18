<?php
/**
 * Settings meta box.
 *
 * @package Hummingbird
 */
?>
<form method="post" class="settings-frm">
	<p>
		<?php esc_html_e( 'Enable support for any accessibility enhancements available in the plugin interface.', 'wphb' ); ?>
	</p>
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Color Accessibility', 'wphb' ); ?></span>
			<span class="sui-description">
				<?php esc_html_e( 'Increase the visibility and accessibility of elements and components to meet WCAG AAA requirements.', 'wphb' ); ?>
			</span>
		</div><!-- end col-third -->
		<div class="sui-box-settings-col-2">
			<input type="hidden" name="accessible_colors" value="0" />
			<label class="sui-toggle">
				<input type="checkbox" name="accessible_colors" value="1"
					   id="color_accessible" <?php checked( 1, $settings['accessible_colors'] ); ?> />
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="color_accessible"><?php esc_html_e( 'Enable high contrast mode', 'wphb' ); ?></label>
		</div>
	</div>
</form>
