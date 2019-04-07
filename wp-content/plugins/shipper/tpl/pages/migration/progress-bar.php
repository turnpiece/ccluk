<?php
/**
 * Shipper migrate page templates: migration progress bar subpage
 *
 * @package shipper
 */

$progress = ! empty( $progress )
	? (int) $progress
	: 0
;
$migration = new Shipper_Model_Stored_Migration;
?>

<?php
	$health = new Shipper_Model_Stored_Healthcheck;
	$this->render( 'msgs/migration-slow-notice', array(
		'visible' => $health->is_slow_migration(),
	) );
?>

<div class="shipper-migration-content shipper-migration-progress-content">
	<div class="shipper-page-header">
		<i class="sui-icon-shipper-anchor" aria-hidden="true"></i>
		<h2>
		<?php if ( 'export' === $type ) { ?>
			<?php esc_html_e( 'Migration in progress', 'shipper' ); ?>
		<?php } else { ?>
			<?php esc_html_e( 'Import in progress', 'shipper' ); ?>
		<?php } ?>
		</h2>
	</div>

	<div class="sui-progress-block sui-progress-can-close">
		<div class="sui-progress">
			<span class="sui-progress-icon" aria-hidden="true">
				<i class="sui-icon-loader sui-loading"></i>
			</span>
			<span class="sui-progress-text">
				<span><?php echo (int) $progress; ?>%</span>
			</span>
			<div class="sui-progress-bar">
				<span style="width: <?php echo (int) $progress; ?>%"></span>
			</div>
		</div>
		<button class="sui-progress-close sui-button-icon sui-tooltip" type="button" data-tooltip="<?php esc_attr_e( 'Cancel Migration', 'shipper' ); ?>">
			<i class="sui-icon-close"></i>
		</button>
	</div>

	<div class="sui-progress-state">
		<span
			class="sui-progress-state-text"
			data-progress_stalled="<?php esc_attr_e( 'Waiting for fresh update...', 'shipper' ); ?>"
		><?php esc_html_e( 'Checking progress state...', 'shipper' ); ?></span>
	</div>

	<p class="shipper-note">
		<?php echo esc_html(
			sprintf(
				__( '%s, your migration is underway!', 'shipper' ),
				shipper_get_user_name()
			)
		); ?>
		<?php
		if ( Shipper_Model_Stored_Migration::TYPE_EXPORT === $migration->get_type() ) {
			echo Shipper_Model_Stored_Estimate::get_estimated_migration_time_msg();
		} else {
			esc_html_e( 'It can take a while since we are migrating one-file-at-a-time via a super secure API to ensure 100% effective, secure, safe and foolproof migrations.', 'shipper' );
		}
		?>
		<?php echo wp_kses_post(
			__( 'It\'s a one-click migration with no flaws, except it can take a long time to complete. It’s worth the wait and, as long as your site isn’t a local installation, you can close this tab and <b>we’ll email you when it’s all done.</b>', 'shipper' )
		); ?>
	</p>
</div> <?php // .shipper-migration-progress-content ?>