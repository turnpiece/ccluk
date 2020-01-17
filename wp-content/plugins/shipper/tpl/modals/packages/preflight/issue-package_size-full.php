<?php
/**
 * Shipper templates: package migration preflight check package size partial
 *
 * @package shipper
 */

?>
<p>
	<?php
		$this->render('modals//packages/preflight/issue-package_size-summary', array(
			'package_size' => $package_size,
			'threshold' => $threshold,
		));
	?>
</p>
<p>
	<?php esc_html_e(
		'Also, please note that the package build time may vary considerably depending on factors such as the speed of your current host and your package migration settings.',
		'shipper'
	); ?>
</p>