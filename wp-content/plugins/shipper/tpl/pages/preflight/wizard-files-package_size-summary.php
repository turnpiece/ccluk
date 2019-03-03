<?php
/**
 * Shipper templates: preflight check package size summary partial
 *
 * @package shipper
 */

echo Shipper_Model_Stored_Estimate::get_estimated_migration_time_msg();
if ( $package_size > $threshold ) {
	echo '&nbsp;';
	esc_html_e(
		'You can try excluding the large files to reduce the overall package size for a faster migration.',
		'shipper'
	);
}