<?php
/**
 * Shipper package migration templates: actual migration root template
 *
 * @since v1.1
 * @package shipper
 */

$assets       = new Shipper_Helper_Assets();
$model        = new Shipper_Model_Stored_Package();
$has_packages = $model->has_package();
?>
<div class="shipper-packages-migration <?php echo $has_packages ? 'shipper-has-packages' : ''; ?>">
	<div class="sui-box shipper-packages-migration-main">

		<div class="sui-box-header">
			<h3 class="sui-box-title">
				<?php esc_html_e( 'Package', 'shipper' ); ?>
			</h3>
		<?php if ( ! empty( $has_packages ) ) { ?>
			<div class="sui-actions-right">
				<button type="button" class="sui-button sui-button-primary shipper-new-package">
					<i class="sui-icon-plus" aria-hidden="true"></i>
					<?php esc_html_e( 'New Package', 'shipper' ); ?>
				</button>
			</div><!-- sui-actions-right -->
		<?php } ?>
		</div><!-- sui-box-header -->

		<div class="sui-box-body">
		<?php if ( ! empty( $has_packages ) ) { ?>
			<?php $this->render( 'pages/packages/migration/package' ); ?>
		<?php } else { ?>
			<?php $this->render( 'pages/packages/migration/initial' ); ?>
		<?php } ?>
		</div><!-- sui-box-body -->

		<?php if ( ! empty( $has_packages ) ) { ?>
			<?php $this->render( 'pages/packages/migration/additional' ); ?>
			<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
		<?php } ?>
	</div><!-- sui-box -->

	<?php $this->render( 'modals/packages/create', array( 'modal' => 'confirm' ) ); ?>
	<?php $this->render( 'modals/packages/create', array( 'modal' => 'meta' ) ); ?>
	<?php $this->render( 'modals/packages/create', array( 'modal' => 'settings' ) ); ?>

	<?php $this->render( 'modals/packages/preflight', array( 'modal' => 'check' ) ); ?>
	<?php $this->render( 'modals/packages/preflight', array( 'modal' => 'issues' ) ); ?>

	<?php $this->render( 'modals/packages/build', array( 'modal' => 'migration' ) ); ?>
	<?php $this->render( 'modals/packages/build', array( 'modal' => 'cancel' ) ); ?>
	<?php $this->render( 'modals/packages/build', array( 'modal' => 'fail' ) ); ?>

</div>