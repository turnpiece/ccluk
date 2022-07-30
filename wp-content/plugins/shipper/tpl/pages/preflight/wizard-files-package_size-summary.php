<?php
/**
 * Shipper templates: preflight check package size summary partial
 *
 * @package shipper
 */

echo '<span class="shipper-package-size-summary">';
echo wp_kses_post( Shipper_Model_Stored_Estimate::get_estimated_migration_time_msg() );

if ( $package_size > $threshold ) {
	echo '&nbsp;';
	esc_html_e(
		'If you haven’t already, you can try excluding larger files to speed up migration.',
		'shipper'
	);
}
echo '</span>';