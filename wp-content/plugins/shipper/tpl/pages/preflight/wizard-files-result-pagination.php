<?php
/**
 * Shipper templates: preflight files area, pagination template
 *
 * @package shipper
 */

?>

<div class="sui-row">
	<div class="sui-col sui-col-md-4">
		<div class="sui-form-field shipper-bulk-actions-field">
			<div class="sui-with-button">
				<select class="sui-select sui-select-sm" name="shipper-bulk-action">
					<option value=""><?php esc_html_e( 'Bulk actions', 'shipper' ); ?></option>
					<option value="include"><?php esc_html_e( 'Include', 'shipper' ); ?></option>
					<option value="exclude"><?php esc_html_e( 'Exclude', 'shipper' ); ?></option>
				</select>
				<button class="sui-button shipper-bulk-action">
					<?php esc_html_e( 'Apply', 'shipper' ); ?>
				</button>
			</div>
		</div>
	</div>
	<div class="sui-col">
		<div class="sui-pagination-wrap">
			<span class="sui-pagination-results" style="display:none">
				<span class="shipper-files-count"></span> results
			</span>
			<ul class="sui-pagination">
				<li>
					<a href="#first">
						<i class="sui-icon-chevron-left" aria-hidden="true"></i>
					</a>
				</li>

				<li>
					<a href="#last">
						<i class="sui-icon-chevron-right" aria-hidden="true"></i>
					</a>
				</li>
			</ul>
			<?php if ( ! isset( $hide_filter ) ) : ?>
				<button
					class="sui-button-icon sui-button-outlined sui-pagination-open-filter sui-tooltip"
					data-tooltip="<?php esc_attr_e( 'Filter', 'shipper' ); ?>"
					aria-label="<?php esc_attr_e( 'Filter pagination', 'shipper' ); ?>"
				>
					<i class="sui-icon-filter" aria-hidden="true"></i>
				</button>
			<?php endif; ?>
		</div>
	</div>
</div>