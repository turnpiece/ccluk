<?php
/**
 * Shipper preflight templates: local checks subtemplate
 *
 * @since v1.0.3
 * @package shipper
 */

$checks    = $result['checks']['local'];
$migration = new Shipper_Model_Stored_Migration();
$sorted    = Shipper_Helper_Template_Sorter::checks_by_error_status( $checks['checks'] );
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


	if (
		Shipper_Model_Stored_Migration::TYPE_EXPORT === $migration->get_type()
		&& ! empty( $result['checks']['files'] ) ) {
		$this->render(
			'modals/preflight/checks-local-files',
			array(
				'result' => $result,
			)
		);
	}
	?>
</div>