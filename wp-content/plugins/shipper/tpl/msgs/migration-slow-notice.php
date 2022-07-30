<?php
/**
 * Shipper message templates: migration slow notice
 *
 * @package shipper
 */

$visibility = 'display:none';
if ( ! empty( $visible ) ) {
	$visibility = '';
}
?>
<div id="migration-slow-notice-inline-dismiss" style="<?php echo esc_attr( $visibility ); ?>"
	class="sui-notice-top sui-notice-warning sui-can-dismiss shipper-migration-health">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<p>
			<?php esc_html_e( 'Migration may take longer than expected.', 'shipper' ); ?>
			<?php esc_html_e( 'Higher traffic or a slow host can slow down the migration process.', 'shipper' ); ?>
	<?php if ( Shipper_Helper_Assets::has_docs_links() ) { ?>
			<a href="https://wpmudev.com/docs/wpmu-dev-plugins/shipper/#migrationnotice" target="_blank">
				<?php esc_html_e( 'Learn more', 'shipper' ); ?>
			</a>
	<?php } ?>
			</p>
		</div>
	</div>
	<div class="sui-notice-actions">
		<button class="sui-button-icon" data-notice-close="migration-slow-notice-inline-dismiss">
			<i class="sui-icon-check" aria-hidden="true"></i>
			<span class="sui-screen-reader-text"><?php esc_attr_e( 'Close this notice', 'shipper' ); ?></span>
		</button>
	</div>
</div>