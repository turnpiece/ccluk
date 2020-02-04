<?php
/**
 * Shipper package migration modals: package-specific settings template, advanced section
 *
 * @since v1.1
 * @package shipper
 */

?>
<p class="sui-p-small"><?php esc_html_e( 'Use the following advanced options to filter the content of your package.', 'shipper' ); ?></p>

<label class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
	<input type="checkbox"
		name="spam-comments"
		value=""
	/>
	<span aria-hidden="true"></span>
	<span><?php esc_html_e( 'Exclude spam comments  ', 'shipper' ); ?></span>
</label>

<label class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
	<input type="checkbox"
		name="post-revisions"
		value=""
	/>
	<span aria-hidden="true"></span>
	<span><?php esc_html_e( 'Exclude post revisions  ', 'shipper' ); ?></span>
</label>

<?php if ( ! is_multisite() ) { ?>
<label class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
	<input type="checkbox"
		name="inactive-themes"
		value=""
	/>
	<span aria-hidden="true"></span>
	<span><?php esc_html_e( 'Exclude inactive themes', 'shipper' ); ?></span>
</label>

<label class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
	<input type="checkbox"
		name="inactive-plugins"
		value=""
	/>
	<span aria-hidden="true"></span>
	<span><?php esc_html_e( 'Exclude inactive plugins', 'shipper' ); ?></span>
</label>
<?php } ?>