<?php
/**
 * Shipper templates: preflight check package size summary partial
 *
 * @package shipper
 */

$conservative_estimate = $package_size * 0.66;
$optimistic_estimate   = $package_size * 0.125;
?>
<span class="shipper-package-size-summary">
<?php
echo wp_kses_post(
	sprintf(
		/* translators: %1$s %2$s %3$s: package size, estimated time and conservative estimate time. */
		__( 'Your website is %1$s in size, and <strong>the final archive could be somewhere between %2$s to %3$s</strong> depending upon the type of files on your site and the compression rate possible with them. If you haven’t already, you can try excluding larger files to speed up the package build.', 'shipper' ),
		size_format( $package_size ),
		size_format( $optimistic_estimate ),
		size_format( $conservative_estimate )
	)
);
?>
</span>