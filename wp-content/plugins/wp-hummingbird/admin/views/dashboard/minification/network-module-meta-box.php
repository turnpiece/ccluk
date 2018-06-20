<?php
/**
 * Asset optimization network meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var bool $enabled           Asset optimization status.
 * @var bool $use_cdn           CDN status.
 * @var bool $log               Debug log status.
 * @var bool $use_cdn_disabled  Can use CDN?
 */

?>
<p><?php esc_html_e( 'Compress, combine and position your assets to dramatically improve your pageload speed. Choose which user roles can configure Asset Optimization.', 'wphb' ); ?></p>

<?php
if ( $enabled ) :
	$msg = __( 'Asset Optimization is active and configurable by Blog Admins only.', 'wphb' );
	if ( 'super-admins' === $enabled ) {
		$msg = __( 'Asset Optimization is active and configurable by Super Admins only.', 'wphb' );
	}
	?>
	<div class="sui-notice sui-notice-success">
		<p><?php echo esc_html( $msg ); ?></p>
	</div>
<?php endif; ?>

<div class="sui-notice sui-notice-success sui-hidden" id="wphb-notice-minification-settings-updated">
	<p><?php esc_html_e( 'Asset optimization settings updated', 'wphb' ); ?></p>
</div>

<?php if ( isset( $_GET['minify-instructions'] ) ) : // Input var ok. ?>
	<div class="sui-notice sui-notice-warning">
		<p><?php esc_html_e( 'Please, activate minification first. A new menu will appear in every site on your Network.', 'wphb' ); ?></p>
	</div>
<?php endif; ?>

<div class="sui-form-field">
	<label for="wphb-activate-minification" class="sui-label">
		<?php esc_html_e( 'User Role Access', 'wphb' ); ?>
	</label>
	<select name="wphb-activate-minification" id="wphb-activate-minification">
		<option value="false" <?php selected( $enabled, false ); ?>><?php esc_html_e( 'Deactivate completely', 'wphb' ); ?></option>
		<option value="true" <?php selected( $enabled, true ); ?>><?php esc_html_e( 'Blog Admins can configure Asset Optimization', 'wphb' ); ?></option>
		<option value="super-admins" <?php selected( $enabled, 'super-admins' ); ?>><?php esc_html_e( 'Only Super Admins can configure Asset Optimization', 'wphb' ); ?></option>
	</select>
</div>

<?php
$tooltip_msg_enabled  = __( 'Enable WPMU DEV CDN', 'wphb' );
$tooltip_msg_disabled = __( 'Enable minification to use the WPMU DEV CDN', 'wphb' );
$tooltip_msg = $use_cdn_disabled ? $tooltip_msg_disabled : $tooltip_msg_enabled;
?>
<input type="hidden" id="cdn_enabled_tooltip" value="<?php echo esc_attr( $tooltip_msg_enabled ); ?>">
<input type="hidden" id="cdn_disabled_tooltip" value="<?php echo esc_attr( $tooltip_msg_disabled ); ?>">

<?php if ( WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="sui-form-field">
		<label class="sui-toggle sui-tooltip sui-tooltip-top-right" data-tooltip="<?php echo esc_attr( $tooltip_msg ); ?>">
			<input type="checkbox" name="use_cdn" id="use_cdn" <?php checked( $use_cdn ); ?> <?php disabled( $use_cdn_disabled ); ?>>
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="use_cdn"><?php esc_html_e( 'Host my files on the WPMU DEV CDN', 'wphb' ); ?></label>
	</div>
<?php else : ?>
	<div class="wphb-dash-ao-upsell">
		<span><?php esc_html_e( 'Host my files on the WPMU DEV CDN', 'wphb' ); ?></span>
		<a class="sui-button sui-button-ghost sui-button-green" href="<?php echo WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_summary_pro_tag' ); ?>" target="_blank">
			<?php esc_html_e( 'Pro Feature', 'wphb' ); ?>
		</a>
	</div>
<?php endif; ?>

<?php if ( ! $use_cdn_disabled ) : ?>
	<div class="sui-form-field sui-no-margin-bottom">
		<label class="sui-toggle sui-tooltip sui-tooltip-top-right"
			   data-tooltip="<?php esc_html_e( 'Turn on the debug log to get insight into any issues you’re having across your subsites.', 'wphb' ); ?>">
			<input type="checkbox" name="debug_log" id="debug_log" <?php checked( $log ); ?> <?php disabled( $use_cdn_disabled ); ?>>
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="debug_log"><?php esc_html_e( 'Enable debug log', 'wphb' ); ?></label>

		<span id="wphb-minification-debug-log" class="sui-description sui-toggle-description <?php echo ! $log ? 'sui-hidden' : ''; ?>">
		<?php
		printf(
		/* translators: %s: Logs location */
			esc_html__( 'Location: %s', 'wphb' ),
			esc_url( get_home_url() . '/wp-content/wphb-logs/' )
		); ?>
	</span>
	</div>
<?php endif; ?>