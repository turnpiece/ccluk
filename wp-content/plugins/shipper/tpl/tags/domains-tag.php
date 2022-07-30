<?php
/**
 * Shipper tag templates: migration domains tag
 *
 * @since v1.0.3
 * @package shipper
 */

$migration   = new Shipper_Model_Stored_Migration();
$source      = preg_replace( '/^https?:\/\//', '', esc_url( $migration->get_source( true ) ) );
$destination = preg_replace( '/^https?:\/\//', '', esc_url( $migration->get_destination( true ) ) );
$is_import   = Shipper_Model_Stored_Migration::TYPE_IMPORT === $migration->get_type();
?>

<span class="sui-tag shipper-domains">
	<span class="shipper-destination-local"><?php echo esc_html( $source ); ?></span>
	<span class="shipper-migration-direction">
		<?php if ( $is_import ) { ?>
			<i class="sui-icon-chevron-left" aria-hidden="true"></i>
		<?php } else { ?>
			<i class="sui-icon-chevron-right" aria-hidden="true"></i>
		<?php } ?>
	</span>
	<span class="shipper-destination-remote"><?php echo esc_html( $destination ); ?></span>
</span>