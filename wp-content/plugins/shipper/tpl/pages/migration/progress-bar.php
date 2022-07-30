<?php
/**
 * Shipper migrate page templates: migration progress bar subpage
 *
 * @package shipper
 */

$progress  = ! empty( $progress )
	? (int) $progress
	: 0;
$migration = new Shipper_Model_Stored_Migration();

$health = new Shipper_Model_Stored_Healthcheck();
$this->render(
	'msgs/migration-slow-notice',
	array(
		'visible' => $health->is_slow_migration(),
	)
);
?>

<div class="shipper-migration-content shipper-migration-progress-content shipper-migration-ready">
	<div class="shipper-page-header">
		<i class="sui-icon-shipper-anchor" aria-hidden="true"></i>
		<h2>
		<?php if ( 'export' === $type ) { ?>
			<?php esc_html_e( 'Migration in progress', 'shipper' ); ?>
		<?php } else { ?>
			<?php esc_html_e( 'Import in progress', 'shipper' ); ?>
		<?php } ?>
		</h2>
		<?php
			$this->render( 'tags/domains-tag' );
		?>
	</div>

	<table class="sui-table shipper-migration-ready-table">
		<tbody>
		<tr>
			<td class="sui-table-item-title">
				<?php esc_html_e( 'Package Size', 'shipper' ); ?>
			</td>
			<td class="shipper-align-right">
				<?php echo esc_html( $size ); ?>
			</td>
		</tr>

		<tr>
			<td class="sui-table-item-title">
				<?php esc_html_e( 'Migration ETA', 'shipper' ); ?>
			</td>
			<td class="shipper-align-right">
				<?php
				/* translators: %s: migratoin eta time. */
				echo esc_html( sprintf( __( 'Up to %s', 'shipper' ), $time ) );

				if ( 'hours' === $time_unit ) {
					?>
					<span
						class="sui-tooltip sui-tooltip-constrained"
						style="--tooltip-width: 340px;"
						data-tooltip="<?php esc_html_e( 'Looks like a long time? That’s because the API method causes no load to your server and is super reliable. If you’d like a much quicker migration, use the Package Migration method – you can upload a package of your site onto any server (local or live) and be migrated in a matter of minutes.', 'shipper' ); ?>"
					>
							<i class="sui-icon-info" aria-hidden="true"></i>
						</span>
				<?php } ?>
			</td>
		</tr>
		</tbody>
	</table>

	<div class="sui-progress-block sui-progress-can-close">
		<div class="sui-progress shipper-migration-progress">
			<span class="sui-progress-icon" aria-hidden="true">
				<i class="sui-icon-loader sui-loading"></i>
			</span>
			<span class="sui-progress-text">
				<span><?php echo (int) $progress; ?>%</span>
			</span>
			<div class="sui-progress-bar">
				<span style="width: <?php echo (int) $progress; ?>%"></span>
			</div>
			<span class="sui-progress-close">
				<?php esc_html_e( 'CANCEL', 'shipper' ); ?>
			</span>
		</div>

	</div>

	<div class="sui-progress-state">
		<span
			class="sui-progress-state-text"
			data-progress_stalled="<?php esc_attr_e( 'Waiting for fresh update...', 'shipper' ); ?>"
		><?php esc_html_e( 'Checking progress state...', 'shipper' ); ?></span>
	</div>

	<p class="shipper-note">
		<?php
		if ( Shipper_Model_Stored_Migration::TYPE_EXPORT === $migration->get_type() ) {
			echo wp_kses( 'As long as your site isn’t a local installation, you can close this tab and <strong>we’ll email you when it’s all done.</strong>', 'shipper' );
		} else {
			esc_html_e( 'It can take a while since we are migrating one-file-at-a-time via a super secure API to ensure 100% effective, secure, safe and foolproof migrations.', 'shipper' );
		}
		?>
	</p>
</div> <?php // .shipper-migration-progress-content ?>