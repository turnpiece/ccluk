<?php
/**
 * Shipper migrate page templates: migration progress bar subpage
 *
 * @package shipper
 */

$progress = ! empty( $progress )
	? (int) $progress
	: 0;
?>

<div class="shipper-migration-content shipper-migration-progress-content">
	<div class="shipper-page-header">
		<h2>
		<?php esc_html_e( 'Pre-flight Check', 'shipper' ); ?>
		</h2>
	</div>

	<?php
	$this->render(
		'pages/migration/sourcedest-tag',
		array(
			'destinations' => $destinations,
			'site'         => $site,
		)
	);
	?>

	<p class="shipper-note">
		<?php esc_html_e( 'We\'re just running a quick pre-flight check to make sure the migration will run smoothly.', 'shipper' ); ?>
		<?php esc_html_e( 'Please be patient as it can take anywhere from a few seconds to a couple of minutes depending on the size of your website.', 'shipper' ); ?>
	</p>
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
		<button class="sui-progress-close sui-button-icon sui-tooltip" type="button" data-tooltip="<?php esc_attr_e( 'Cancel Pre-flight Check', 'shipper' ); ?>">
			<i class="sui-icon-close"></i>
		</button>
	</div>

	<div class="sui-progress-state">
		<span
			class="sui-progress-state-text"
			data-progress_stalled="<?php esc_attr_e( 'Waiting for fresh update...', 'shipper' ); ?>"
		><?php esc_html_e( 'Preparing for the pre-flight check...', 'shipper' ); ?></span>
		<span class="sui-progress-state-subtext"></span>
	</div>
</div> <?php // .shipper-migration-progress-content ?>

<?php $this->render( 'modals/preflight-cancel' ); ?>