<?php
/**
 * Shipper templates: preflight check package size partial
 *
 * @package shipper
 */

?>
<p>
	<?php
		$this->render('pages/preflight/wizard-files-package_size-summary', array(
			'package_size' => $package_size,
			'threshold' => $threshold,
		));
	?>
</p>
<p>
	<?php esc_html_e(
		'Note: The actual migration time may vary depending on a lot of other factors.',
		'shipper'
	); ?>
</p>