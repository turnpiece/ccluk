<?php
/**
 * Shipper templates: preflight wizard files result wrapper template
 *
 * @package shipper
 */

?>

<?php
if ( 'package_size' === $check_type ) {
	echo $html;
	return;
}
?>

<p>
	<?php if ( 'file_sizes' === $check_type ) { ?>
		<?php esc_html_e(
			'Some of your files are very large in size which can cause issues with migrating.',
			'shipper'
		); ?>
	<?php } else if ( 'file_names' === $check_type ) { ?>
		<?php esc_html_e(
			'Some of your files have very long paths which can cause issues with migrating.',
			'shipper'
		); ?>
	<?php } ?>
	<?php esc_html_e(
		'We recommend excluding them and uploading them via FTP if you run into issues.',
		'shipper'
	); ?>
</p>

<hr />
<?php $this->render( 'pages/preflight/wizard-files-result-pagination' ); ?>

<div class="shipper-filter-area sui-box">
	<div class="sui-box-body">
		<div class="sui-row">
			<div class="sui-col" data-filter-field="path">
				<label class="sui-label">
					<span><?php esc_html_e( 'File path has keyword', 'shipper' ); ?></span>
				</label>
				<input type="text"
					placeholder="<?php esc_attr_e( 'E.g. Plugin Name', 'shipper' ); ?>"
					name="shipper-filter-path" class="sui-form-control" />
				<i class="sui-icon-magnifying-glass-search" aria-hidden="true"></i>
			</div>
			<div class="sui-col" data-filter-field="type">
				<label class="sui-label">
					<span><?php esc_html_e( 'Type', 'shipper' ); ?></span>
				</label>
				<select name="shipper-filter-extension">
					<option value=""><?php esc_html_e( 'Any', 'shipper' ); ?></option>
					<option value="zip"><?php esc_html_e( 'Archive', 'shipper' ); ?></option>
				</select>
			</div>
		</div>
		<div class="sui-row">
			<div class="sui-col" data-filter-field="size">
				<div class="sui-row">
					<div class="sui-col">
						<label class="sui-label">
							<span><?php esc_html_e( 'Size', 'shipper' ); ?></span>
						</label>
						<input type="number"
							placeholder="<?php esc_attr_e( 'E.g. 150', 'shipper' ); ?>"
							name="shipper-filter-size" class="sui-form-control" />
					</div>
					<div class="sui-col">
						<label class="sui-label"><span>&nbsp;</span></label>
						<select>
							<option value=""><?php esc_html_e( 'Mb', 'shipper' ); ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="sui-col">
			</div>
		</div>
	</div>
	<div class="sui-box-footer">
		<div class="sui-row">
			<div class="sui-col">
				<button class="sui-button sui-button-ghost shipper-filter-reset">
					<?php esc_html_e( 'Reset', 'shipper' ); ?>
				</button>
			</div>
			<div class="sui-col">
				<button class="sui-button shipper-filter-apply">
					<?php esc_html_e( 'Apply', 'shipper' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<div class="sui-pagination-active-filters shipper-active-filters">
	<span class="sui-active-filter shipper-filter" data-filter-type="path">
		<?php esc_html_e( 'Path:', 'shipper' ); ?>
		<span class="shipper-filter-target"></span>
		<span class="sui-active-filter-remove"></span>
	</span>
	<span class="sui-active-filter shipper-filter" data-filter-type="type">
		<?php esc_html_e( 'Type:', 'shipper' ); ?>
		<span class="shipper-filter-target"></span>
		<span class="sui-active-filter-remove"></span>
	</span>
	<span class="sui-active-filter shipper-filter" data-filter-type="size">
		<?php esc_html_e( 'Size:', 'shipper' ); ?>
		<span class="shipper-filter-target"></span>Mb
		<span class="sui-active-filter-remove"></span>
	</span>
</div>


<?php echo $html; ?>


<?php $this->render( 'pages/preflight/wizard-files-result-pagination' ); ?>