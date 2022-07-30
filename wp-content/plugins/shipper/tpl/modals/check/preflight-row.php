<?php
/**
 * Shipper checks: preflight response row template
 *
 * @package shipper
 */

$labels = array(
	'local'  => array(
		'icon'  => 'sitemap',
		'label' => __( 'Local server', 'shipper' ),
	),
	'remote' => array(
		'icon'  => 'cloud',
		'label' => __( 'Remote server', 'shipper' ),
	),
	'files'  => array(
		'icon'  => 'page',
		'label' => __( 'Files', 'shipper' ),
	),
);

$type       = ! empty( $type ) && in_array( $type, array_keys( $labels ), true ) ? $type : 'local'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
$label      = isset( $labels[ $type ]['label'] ) ? $labels[ $type ]['label'] : '';
$icon       = isset( $labels[ $type ]['icon'] ) ? $labels[ $type ]['icon'] : '';
$panel_type = $type;

$success_status_class = '';
if ( ! empty( $is_done ) ) {
	$success_status_class = ! empty( $errors_count )
		? 'sui-warning'
		: 'sui-success';
}
?>

<div class="sui-box shipper-check shipper-check-<?php echo esc_attr( $type ); ?> <?php echo sanitize_html_class( $success_status_class ); ?>">

<?php
/*
 * Checks status title
 */
?>
	<div class="sui-box-header">
		<h3 class="sui-box-title shipper-check-title">
			<i class="sui-icon-<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></i>
			<?php echo esc_html( $label ); ?>
	<?php if ( ! empty( $is_done ) ) { ?>
		<?php if ( ! empty( $errors_count ) ) { ?>
			<span class="sui-tag sui-tag-warning"><?php echo (int) $errors_count; ?></span>
		<?php } else { ?>
			<i class="sui-icon sui-icon-check-tick sui-success shipper-check-status"></i>
		<?php } // errors count ?>
	<?php } else { // is done. ?>
		<i class="sui-icon-loader sui-loading shipper-check-status" aria-hidden="true"></i>
	<?php } // is done ?>
		</h3>
	</div>

<?php
/*
 * Checks status output
 */
?>
	<div class="sui-box-body shipper-check-output">
	<?php if ( empty( $checks ) && empty( $is_done ) ) { ?>
		<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
		<?php esc_html_e( 'Checking...', 'shipper' ); ?>
	<?php } elseif ( ! empty( $errors_count ) && empty( $checks ) ) { ?>
		<?php
			// Service errors.
			// phpcs:disable -- this is not WordPress global variable.
		?>
		<?php foreach ( $errors as $error ) { ?>
			<div class="sui-notice sui-notice-error">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
						<p><?php echo esc_html( $error ); ?></p>
					</div>
				</div>
			</div>
		<?php } ?>
	<?php } elseif ( ! empty( $errors_count ) ) { ?>

		<?php
			// Actual issues list.
			// phpcs:enable
		?>
		<div class="shipper-check-results">
		<?php foreach ( $checks as $check ) { ?>

			<?php
			$not_errors = array(
				Shipper_Model_Check::STATUS_PENDING,
				Shipper_Model_Check::STATUS_OK,
			);

			$type      = ! in_array( $check['status'], $not_errors, true ) // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
				? ( Shipper_Model_Check::STATUS_ERROR === $check['status'] ? 'error' : 'warning' )
				: 'success';
			$indicator = 'success' === $type ? 'check-tick' : 'warning-alert';
			$title     = $check['title']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
			$message   = $check['message'];
			?>

			<div class="shipper-check-result shipper-<?php echo esc_attr( $type ); ?>">
				<div class="shipper-check-result-head">
					<div class="shipper-check-result-head-title">
						<i class="sui-icon-<?php echo sanitize_html_class( $indicator ); ?> sui-<?php echo sanitize_html_class( $type ); ?>"></i>
						<b><?php echo esc_html( $title ); ?></b>
						<?php if ( empty( $is_done ) ) { ?>
							<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
						<?php } ?>
					</div><?php // head-title. ?>
					<div class="shipper-check-result-head-state">
							<i class="sui-icon-chevron-down"></i>
					</div> <?php // head-state. ?>
				</div> <?php // head. ?>

				<div class="shipper-check-result-body">
				<?php if ( 'success' !== $type ) { ?>
					<?php echo $message; // @codingStandardsIgnoreLine Message is to be HTML. ?>
				<?php } ?>
				</div> <?php // body. ?>
			</div><?php // result. ?>

		<?php } ?>
		</div>
		<?php // End actual issues list. ?>

	<?php } // !checks && !done. ?>
	</div>
</div>
