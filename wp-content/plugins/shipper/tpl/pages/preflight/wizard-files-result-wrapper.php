<?php
/**
 * Shipper templates: preflight wizard files result wrapper template
 *
 * @package shipper
 */

?>

<?php
if ( 'package_size' === $check_type ) {
	echo $html; // phpcs:ignore
	return;
}
?>

<p>
	<?php
	if ( 'file_sizes' === $check_type ) {
		esc_html_e( 'Files over 8MB are listed below. Large files such as media files or backups can cause timeout issues on some budget hosts during the migration. We recommend excluding them from the migration and uploading them via FTP to your destination.', 'shipper' );
	} elseif ( 'file_names' === $check_type ) {
		esc_html_e( 'Files with names longer than 256 characters are listed below. Files with large names can cause issues on some hosts. We recommend excluding them from the migration and uploading them via FTP to your destination.', 'shipper' );
	}
	?>
</p>

<hr/>
<?php $this->render( 'pages/preflight/wizard-files-result-pagination' ); ?>

<div class="shipper-filter-area sui-box">
	<div class="sui-box-body">
		<div class="sui-row">
			<div class="sui-col" data-filter-field="path">
				<label class="sui-label">
					<span><?php esc_html_e( 'File path has keyword', 'shipper' ); ?></span>
				</label>
				<input
					type="text"
					placeholder="<?php esc_attr_e( 'E.g. Plugin Name', 'shipper' ); ?>"
					name="shipper-filter-path" class="sui-form-control"
				>
				<i class="sui-icon-magnifying-glass-search" aria-hidden="true"></i>
			</div>
			<div class="sui-col" data-filter-field="type">
				<label class="sui-label">
					<span><?php esc_html_e( 'Type', 'shipper' ); ?></span>
				</label>
				<select name="shipper-filter-extension" class="sui-select">
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
						<input
							type="number"
							placeholder="<?php esc_attr_e( 'E.g. 150', 'shipper' ); ?>"
							name="shipper-filter-size" class="sui-form-control"
						>
					</div>
					<div class="sui-col">
						<label class="sui-label"><span>&nbsp;</span></label>
						<select class="sui-select">
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

<?php
echo $html; // phpcs:ignore

$this->render(
	'pages/preflight/wizard-files-result-pagination',
	array(
		'hide_filter' => true,
	)
);