<?php
/**
 * Shipper migrate page templates: selection dispatch hub
 *
 * @package shipper
 */

?>
<div class="<?php echo esc_attr( Shipper_Helper_Assets::get_page_class( 'migrate' ) ); ?>" >
	<div class="sui-header">
		<h1 class="sui-header-title"><?php esc_html_e( 'Migrate', 'shipper' ); ?></h1>
		<?php $this->render( 'pages/migration/view-docs-destinations' ); ?>
	</div>

	<?php if ( ! empty( $errors ) ) { ?>
	<div class="sui-box">
		<div class="sui-box-body">
		<?php foreach ( $errors as $error ) { ?>
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
		$this->render('pages/migration/selection-type', array(
			'destinations' => $destinations,
		));
	} elseif ( empty( $site ) ) {
		// We have the migration type - render site selection.
		$this->render('pages/migration/selection-site', array(
			'destinations' => $destinations,
			'type' => $type,
		));
	} elseif ( empty( $check ) ) {
		// We have the migration type - render preflight check.
		$this->render('pages/migration/selection-check', array(
			'destinations' => $destinations,
			'type' => $type,
			'site' => $site,
		));
	} else {
		// We have done or skipped preflight check - render begin.
		$this->render('pages/migration/ready', array(
			'destinations' => $destinations,
			'type' => $type,
			'site' => $site,
		));
	}
	?>

	<?php $this->render('pages/footer'); ?>
</div> <?php // .sui-wrap ?>

