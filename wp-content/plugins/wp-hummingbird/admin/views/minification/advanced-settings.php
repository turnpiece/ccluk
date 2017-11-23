<div class="wphb-notice wphb-notice-success hidden" id="wphb-notice-minification-advanced-settings-updated">
	<p><?php esc_html_e( 'Settings updated', 'wphb' ); ?></p>
</div>

<div class="toggle-item">

	<div class="toggle-item-group">
		<div class="toggle-item-info">
			<strong><?php esc_html_e( 'Super-minify my files', 'wphb' ); ?></strong>
			<p class="toggle-item-description">
				<?php esc_html_e( 'Compress your files up to 2x more than regular optimization and reduce your page load speed even further.', 'wphb' ); ?>
			</p>
		</div><!-- end toggle-item-info -->

		<div class="toggle-actions">
			<?php if ( $super_minify ) : ?>
				<span class="wphb-label wphb-label-disabled"><?php esc_html_e( 'Auto Enabled', 'wphb' ); ?></span>
			<?php else : ?>
				<span class="toggle tooltip-right" tooltip="<?php esc_html_e( 'Enable Super-minify my files', 'wphb' ); ?>">
					<input type="checkbox" class="toggle-checkbox" name="super_minify_files" id="super_minify_files" <?php checked( $use_cdn ); ?> <?php disabled( $disabled ); ?>>
					<label for="super_minify_files" class="toggle-label small"></label>
				</span>
			<?php endif; ?>
		</div><!-- end toggle-actions -->
	</div><!-- end toggle-item-group -->

</div><!-- end toggle-item -->

<div class="toggle-item bordered space-top">

	<div class="toggle-item-group">
		<div class="toggle-item-info">
			<strong><?php esc_html_e( 'Store my files on the WPMU DEV CDN', 'wphb' ); ?></strong>
			<p class="toggle-item-description"><?php esc_html_e( 'By default we minify your files via our API and then send back to your server. With this setting enabled we will host your files on WPMU DEV\'s secure and hyper fast CDN which will mean less load on your server and a fast visitor experience.', 'wphb' ); ?></p>
		</div><!-- end toggle-item-info -->

		<div class="toggle-actions">
			<span class="toggle tooltip-right" tooltip="<?php esc_attr_e( 'Enable WPMU DEV CDN', 'wphb' ); ?>">
				<input type="checkbox" class="toggle-checkbox" name="use_cdn" id="use_cdn" <?php checked( $use_cdn ); ?> <?php disabled( $disabled ); ?>>
				<label for="use_cdn" class="toggle-label small"></label>
			</span>
		</div><!-- end toggle-actions -->
	</div><!-- end toggle-item-group -->

</div><!-- end toggle-item -->

<?php if ( $disabled ) : ?>
	<div class="wphb-block-entry with-bottom-border">
		<div class="wphb-block-entry-content">
			<div class="content">
				<div class="content-box content-box-two-cols-image-left">
					<div class="wphb-block-entry-image wphb-block-entry-image-bottom">
						<img class="wphb-image"
							 src="<?php echo wphb_plugin_url() . 'admin/assets/image/hummingbird-upsell-minify.png'; ?>"
							 srcset="<?php echo wphb_plugin_url() . 'admin/assets/image/hummingbird-upsell-minify@2x.png'; ?> 2x"
							 alt="<?php esc_attr_e( 'WP Smush free installed', 'wphb' ); ?>">
					</div>
					<div class="wphb-block-entry-content wphb-upsell-free-message">
						<p>
							<?php printf(
								__( "With our pro version of Hummingbird you can super-minify your files and then host them on our blazing fast CDN. You'll get Hummingbird PRO plus 100+ WPMU DEV plugins, themes & 24/7 WP support. <a href='%s' rel='dialog'>Try it free for 14 days</a>!", 'wphb' ),
								'#wphb-upgrade-membership-modal'
							); ?>
						</p>
					</div>
				</div>
			</div><!-- end content -->
		</div><!-- end wphb-block-entry-content -->
	</div><!-- end wphb-block-entry -->
	<?php wphb_membership_modal(); ?>
<?php endif; ?>

<div class="wphb-block-entry <?php echo ( ! $disabled ) ? 'bordered space-top' : ''  ?>">
	<div class="wphb-block-entry-content">
		<div class="content">
			<strong><?php esc_html_e( 'Deactivate', 'wphb' ); ?></strong>
			<p><?php esc_html_e( 'If you no longer wish to use Hummingbird’s Minification feature you can turn it off completely.', 'wphb' ); ?></p>
			<div class="buttons">
				<a href="<?php echo esc_url( add_query_arg( 'disable-minification', 'true' ) ); ?>" class="button button-ghost button-large"><?php _e( 'Deactivate', 'wphb' ); ?></a>
			</div>
		</div><!-- end content -->
	</div><!-- end wphb-block-entry-content -->
</div><!-- end wphb-block-entry -->