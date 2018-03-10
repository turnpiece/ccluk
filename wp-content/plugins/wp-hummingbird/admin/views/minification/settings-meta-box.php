<?php
/* @var WP_Hummingbird_Module_Minify $minify */
$minify = WP_Hummingbird_Utils::get_module( 'minify' );
?>
<div class="row settings-form with-bottom-border with-padding <?php echo ( ! WP_Hummingbird_Utils::is_member() ) ? 'disabled' : ''; ?>">
	<div class="col-third">
		<strong><?php esc_html_e( 'Super-compress my files', 'wphb' ); ?></strong>
		<span class="sub">
			<?php esc_html_e( 'Compress your files up to 2x more than regular optimization and reduce your page load speed even further.', 'wphb' ); ?>
		</span>
	</div>
	<div class="col-two-third">
		<?php if ( WP_Hummingbird_Utils::is_member() ) : ?>
			<span class="wphb-label wphb-label-disabled"><?php esc_html_e( 'Auto Enabled', 'wphb' ); ?></span>
		<?php else : ?>
			<span class="toggle tooltip-right" tooltip="<?php esc_html_e( 'Enable Super-minify my files', 'wphb' ); ?>">
				<input type="checkbox" class="toggle-checkbox" name="super_minify_files" id="super_minify_files" disabled>
				<label for="super_minify_files" class="toggle-label small"></label>
			</span>
			<label for="super_minify_files"><?php esc_html_e( 'Enable super-compression', 'wphb' ); ?></label>
		<?php endif; ?>
	</div>
</div>

<?php if ( ! is_multisite() ) : ?>
	<div class="row settings-form with-bottom-border with-padding <?php echo ( ! WP_Hummingbird_Utils::is_member() ) ? 'disabled' : ''; ?>">
		<div class="col-third">
			<strong><?php esc_html_e( 'Enable WPMU DEV CDN', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'By default your files are hosted on your own server. With this setting enabled we will host your files on WPMU DEV’s secure and hyper fast CDN.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">
			<span class="toggle tooltip-right" tooltip="<?php esc_attr_e( 'Enable WPMU DEV CDN', 'wphb' ); ?>">
				<input type="checkbox" class="toggle-checkbox" name="use_cdn" id="use_cdn" <?php checked( $minify->get_cdn_status() && WP_Hummingbird_Utils::is_member() ); ?> <?php disabled( ! WP_Hummingbird_Utils::is_member() ); ?>>
				<label for="use_cdn" class="toggle-label small"></label>
			</span>
			<label for="use_cdn"><?php esc_html_e( 'Host my files on the WPMU DEV CDN', 'wphb' ); ?></label>
			<span class="sub">
				<?php esc_html_e( 'Hosting your files externally means less load on your server and a super-fast visitor experience.', 'wphb' ); ?>
			</span>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="row settings-form with-bottom-border with-padding">
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
	<div class="row settings-form with-bottom-border with-padding">
		<div class="col-third">
			<strong><?php esc_html_e( 'Enable debug log', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'If you’re having issues with minification, turn on the debug log to get insight into what’s going on.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">
			<span class="toggle tooltip-right" tooltip="<?php esc_attr_e( 'Enable debug log', 'wphb' ); ?>">
				<input type="checkbox" class="toggle-checkbox" name="debug_log" id="debug_log" <?php checked( $options['log'] ); ?>>
				<label for="debug_log" class="toggle-label small"></label>
			</span>
			<label for="debug_log"><?php esc_html_e( 'Enable debug log', 'wphb' ); ?></label>
		</div>
	</div>
<?php endif; ?>

<div class="row settings-form with-bottom-border with-padding">
	<div class="col-third">
		<strong><?php esc_html_e( 'Reset to defaults', 'wphb' ); ?></strong>
		<span class="sub">
			<?php esc_html_e( 'If your frontend has fallen apart or you just want to go back to the default settings you can use this button to do so. It will clear all your settings and run a new file check.', 'wphb' ); ?>
		</span>
	</div>
	<div class="col-two-third">
		<a href="<?php echo esc_url( add_query_arg( 'reset-minification', 'true' ) ); ?>" class="button button-ghost">
			<?php esc_html_e( 'Reset', 'wphb' ); ?>
		</a>
	</div>
</div>

<div class="row settings-form with-padding">
	<div class="col-third">
		<strong><?php esc_html_e( 'Deactivate', 'wphb' ); ?></strong>
		<span class="sub">
			<?php esc_html_e( 'If you no longer wish to use Hummingbird’s asset optimization feature you can turn it off completely', 'wphb' ); ?>
		</span>
	</div>
	<div class="col-two-third">
		<a href="<?php echo esc_url( add_query_arg( 'disable-minification', 'true' ) ); ?>" class="button button-ghost">
			<?php esc_html_e( 'Deactivate', 'wphb' ); ?>
		</a>
	</div>
</div>