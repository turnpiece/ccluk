<?php
/**
 * Shipper templates: package migration preflight check package size partial
 *
 * @package shipper
 */

?>
<p>
	<?php
	$this->render(
		'modals/packages/preflight/issue-package_size-summary',
		array(
			'package_size' => $package_size,
			'threshold'    => $threshold,
		)
	);
	?>
</p>
<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s: settings page link. */
			__( 'Also, please note that the package build time may vary considerably depending on factors such as the speed of your current host and your package migration settings. So, for a better migration success rate on large sites, we also suggest that you enable the <strong>Safe Mode</strong> on the <a href="%1$s" target="_blank">settings page</a>.', 'shipper' ),
			esc_url(
				network_admin_url( 'admin.php?page=shipper-packages&tool=settings' )
			)
		)
	);
	?>
</p>