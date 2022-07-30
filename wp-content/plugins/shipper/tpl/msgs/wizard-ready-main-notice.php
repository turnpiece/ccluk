<?php
/**
 * Shipper messages: wizard ready to sail main notice template
 *
 * @package shipper
 */

$display_warnings = empty( $has_errors ) && ! empty( $has_issues )
	? 'block'
	: 'none';
$display_success  = empty( $has_errors ) && empty( $has_issues )
	? 'block'
	: 'none';

if ( $has_errors ) {
	$this->render( 'msgs/wizard-ready-has-errors', array( 'result' => $result ) );
} else { ?>
	<div
		class="shipper-preflight-result-overall shipper-has-warnings"
		style="display:<?php echo esc_attr( $display_warnings ); ?>"
	>
		<?php $this->render( 'msgs/wizard-ready-has-warnings' ); ?>
	</div>
	<div
		class="shipper-preflight-result-overall shipper-no-warnings"
		style="display:<?php echo esc_attr( $display_success ); ?>"
	>
		<?php $this->render( 'msgs/wizard-ready-all-good' ); ?>
	</div>
	<?php
}