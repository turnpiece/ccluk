<?php
/**
 * Shipper migrate page templates: migration progress page
 *
 * @package shipper
 */

$target = $destinations->get_by_site_id( $site );
?>
<div class="<?php echo esc_attr( Shipper_Helper_Assets::get_page_class( 'migrate' ) ); ?>" >
	<div class="sui-header">
		<h1 class="sui-header-title"><?php esc_html_e( 'Migrate', 'shipper' ); ?></h1>
		<?php $this->render( 'pages/migration/view-docs' ); ?>
	</div>

	<div class="sui-box shipper-page-migrate-progress">
		<div class="sui-box-body">

		<div class="shipper-actions">
			<div class="shipper-actions-left">
				<a href="<?php echo esc_url( remove_query_arg( array( 'begin', 'check', 'site', 'type' ) ) ); ?>" class="shipper-button-back shipper-migration-cancel">
					<i class="sui-icon-arrow-left" aria-hidden="true"></i>
					<span><?php esc_html_e( 'Go back', 'shipper' ); ?></span>
				</a>
			</div>
			<div class="shipper-actions-right">
				<a href="<?php echo esc_url( remove_query_arg( array( 'begin', 'site', 'type', 'check' ) ) ); ?>" class="shipper-button-back shipper-migration-cancel">
					<i class="sui-icon-close" aria-hidden="true"></i>
					<span><?php esc_html_e( 'Go back', 'shipper' ); ?></span>
				</a>
			</div>
		</div>

			<div class="shipper-content">

				<?php
					$this->render(
						'pages/migration/progress-bar',
						array(
							'type' => $type,
							'site' => $site,
							'progress' => $progress,
						)
					);
				?>

				<?php
					$this->render(
						'pages/migration/progress-done',
						array(
							'type' => $type,
							'destinations' => $destinations,
							'site' => $site,
						)
					);
				?>

				<?php echo Shipper_Helper_Assets::get_custom_hero_image_markup(); ?>
			</div><?php // .shipper-content ?>

		<?php $this->render( 'modals/migration-cancel' ); ?>

		</div>
	</div>

</div> <?php // .sui-wrap ?>
