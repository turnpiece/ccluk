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
<div style="<?php echo esc_attr( $visibility ); ?>"
	class="sui-notice-top sui-notice-warning sui-can-dismiss shipper-migration-health">
	<div class="sui-notice-content">
		<p>
		<?php esc_html_e(
			'Migration may take longer than expected.',
			'shipper'
		); ?>
		<?php esc_html_e(
			'Higher traffic or a slow host can slow down the migration process.',
			'shipper'
		); ?>
		<a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/shipper/#migrationnotice" target="_blank">
			<?php esc_html_e(
				'Learn more',
				'shipper'
			); ?>
		</a>
		</p>
	</div>
	<span class="sui-notice-dismiss">
		<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
	</span>
</div>