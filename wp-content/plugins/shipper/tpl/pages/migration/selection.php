<?php
/**
 * Shipper migrate page templates: selection dispatch hub
 *
 * @package shipper
 */

?>
<div class="<?php echo esc_attr( Shipper_Helper_Assets::get_page_class( 'migrate' ) ); ?>">

	<?php $this->render( 'pages/header' ); ?>

	<div class="sui-header">
		<h1 class="sui-header-title"><?php esc_html_e( 'Migrate', 'shipper' ); ?></h1>
		<?php $this->render( 'pages/migration/view-docs-destinations' ); ?>
	</div>

	<?php if ( ! empty( $errors ) ) { ?>
		<div class="sui-box">
			<div class="sui-box-body">
				<?php foreach ( $errors as $error ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable ?>
					<div class="sui-notice sui-notice-error">
						<p><?php echo wp_kses_post( $error->get_error_message() ); ?></p>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>

	<?php $this->render( 'modals/destination' ); ?>

	<?php
	if ( empty( $type ) ) {
		// Render migration type selection.
		$this->render(
			'pages/migration/selection-type',
			array(
				'destinations' => $destinations,
			)
		);
	} elseif ( 'export' === $type && is_multisite() && empty( $network ) ) {
		$this->render(
			'modals/network-type',
			array(
				'type'  => 'export',
				'sites' => Shipper_Helper_MS::get_all_sites(),
			)
		);
	} elseif ( empty( $site ) ) {
		// We have the migration type - render site selection.
		$this->render(
			'pages/migration/selection-site',
			array(
				'destinations' => $destinations,
				'type'         => $type,
			)
		);
	} elseif ( 'import' === $type && Shipper_Helper_MS::can_ms_subsite_import() && ! empty( $site ) && empty( $network ) ) {
		$task = new Shipper_Task_Api_Info_Get();
		$data = $task->apply();
		$this->render(
			'modals/network-type',
			array(
				'type'  => 'import',
				'sites' => $data['wordpress'][ Shipper_Model_System_Wp::MS_SUBSITES ],
			)
		);
	} elseif ( empty( $is_excludes_picked ) ) {
		$this->render( 'modals/migration-exclusion' );
	} elseif ( empty( $check ) ) {
		// We have the migration type - render preflight check.
		$this->render(
			'pages/migration/selection-check',
			array(
				'destinations' => $destinations,
				'type'         => $type,
				'site'         => $site,
			)
		);
	} elseif ( empty( $db_prefix_check ) ) {
		$this->render( 'modals/db-prefix' );
	} else {
		// We have done or skipped preflight check - render begin.
		$this->render(
			'pages/migration/ready',
			array(
				'destinations' => $destinations,
				'type'         => $type,
				'site'         => $site,
				'size'         => $size,
				'time'         => $time,
				'time_unit'    => $time_unit,
			)
		);
	}
	?>

	<?php $this->render( 'pages/footer' ); ?>
</div>
