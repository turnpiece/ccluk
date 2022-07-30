<?php
/**
 * Shipper migrations page templates: Source > destination tag partial
 *
 * @package shipper
 */

$source      = Shipper_Model_Stored_Destinations::get_current_domain();
$destination = $destinations->get_by_site_id( $site );

$default_type = Shipper_Model_Stored_Migration::TYPE_EXPORT;
$model        = new Shipper_Model_Stored_Migration();
$type         = $model->get_type(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable

if ( empty( $type ) ) {
	$type = $default_type; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
}
?>
<div class="shipper-sourcedest-tag">
	<span class="shipper-type-<?php echo esc_attr( $type ); ?> shipper-source-part">
		<?php echo esc_html( $source ); ?>
	</span>
	<span class="shipper-destination-part">
		<?php echo esc_html( $destination['domain'] ); ?>
	</span>
</div>