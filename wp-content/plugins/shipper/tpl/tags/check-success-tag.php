<?php
/**
 * Shipper tags: check success template
 *
 * @since v1.0.3
 * @package shipper
 */

$recheck_class = ! empty( $is_recheck )
	? 'shipper-rechecked-success'
	: '';
?>
<div class="sui-accordion-item <?php echo esc_attr( $recheck_class ); ?>"
	data-check_item="<?php echo esc_attr( $check['check_id'] ); ?>">
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
		<!-- no chevron div -->
	</div>
	<!-- no body -->
</div>