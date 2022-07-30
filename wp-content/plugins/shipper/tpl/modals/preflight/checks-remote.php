<?php
/**
 * Shipper preflight templates: remote checks subtemplate
 *
 * @since v1.0.3
 * @package shipper
 */

$checks             = $result['checks']['remote'];
$migration          = new Shipper_Model_Stored_Migration();
$has_service_errors = ! empty( $checks['errors'] );
$sorted             = Shipper_Helper_Template_Sorter::checks_by_error_status( $checks['checks'] );
?>

<div class="sui-accordion sui-accordion-block">
<?php
if ( ! $has_service_errors ) {
	foreach ( $sorted as $check ) {
		if ( 'ok' === $check['status'] ) {
			if ( ! empty( $is_recheck ) ) {
				$this->render(
					'tags/check-success-tag',
					array(
						'check'      => $check,
						'is_recheck' => ! empty( $is_recheck ),
					)
				);
			}
		} else {
			$this->render(
				'tags/check-failure-tag',
				array(
					'check'      => $check,
					'is_recheck' => ! empty( $is_recheck ),
				)
			);
		}
	}
}

if (
	Shipper_Model_Stored_Migration::TYPE_IMPORT === $migration->get_type()
	&& ! empty( $result['checks']['remote_package'] ) ) {
		$this->render(
			'modals/preflight/checks-remote-rpkg',
			array(
				'result' => $result,
			)
		);
} else {
	$this->render( 'msgs/wizard-destination-errors', array( 'result' => $result ) );
}
?>
</div>