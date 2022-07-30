<?php
/**
 * Shipper preflight templates: system differences checks subtemplate
 *
 * @since v1.0.3
 * @package shipper
 */

$checks             = $result['checks']['sysdiff'];
$has_service_errors = ! empty( $checks['errors'] );
$sorted             = Shipper_Helper_Template_Sorter::checks_by_error_status( $checks['checks'] );
?>

<div class="sui-accordion sui-accordion-block">
	<?php
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
	?>

</div>