<?php
/**
 * Shipper migrate pages: preflight check page partial
 *
 * @package shipper
 */

$ctrl = Shipper_Controller_Runner_Preflight::get();
$done = $ctrl->is_done() ? ' shipper-select-check-done' : '';
?>
<div class="sui-box shipper-select-check">
	<div class="sui-box-body">
		<div>
			<a href="<?php echo esc_url( remove_query_arg( 'site' ) ); ?>" class="shipper-button-back">
				<i class="sui-icon-arrow-left" aria-hidden="true"></i>
				<span><?php esc_html_e( 'Go back', 'shipper' ); ?></span>
			</a>
		</div>
		<div class="shipper-content <?php echo esc_attr( $done ); ?>">

			<?php
			$this->render(
				'modals/preflight',
				array( 'modal' => 'loading' )
			);
			$this->render(
				'modals/preflight',
				array( 'modal' => 'results' )
			);
			?>

		</div><?php // .shipper-content ?>
	</div><?php // .sui-box-body ?>
</div><?php // .sui-box ?>