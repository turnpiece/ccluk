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
		'Also, please note that the time your site takes to migrate may vary considerably depending on many other factors (such as the speed of your current host!).',
		'shipper'
	); ?>
</p>
