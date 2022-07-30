<?php
/**
 * Shipper tags: check non-ok state template
 *
 * @since v1.0.3
 * @package shipper
 */

?>
<div class="sui-accordion-item" data-check_item="<?php echo esc_attr( $check['check_id'] ); ?>">
	<div class="sui-accordion-item-header">
		<div class="sui-accordion-item-title">
			<?php
			$this->render(
				'tags/status-icon-preflight-check',
				array( 'item' => $check )
			);
			?>
			<?php echo esc_html( $check['title'] ); ?>
		</div>
		<div class="shipper-chevron-wrapper">
			<button class="sui-button-icon sui-accordion-open-indicator">
				<i class="sui-icon-chevron-down" aria-hidden="true"></i>
			</button>
		</div>
	</div>
	<div class="sui-accordion-item-body">
		<?php echo wp_kses_post( $check['message'] ); ?>
		<?php $this->render( 'tags/reset-check', array( 'check' => $check ) ); ?>
	</div>
</div>