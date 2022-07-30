<?php
/**
 * Shipper templates: preflight modal, remote checks, package size report template
 *
 * @since v1.0.3
 * @package shipper
 */

$checks             = $result['checks']['remote_package'];
$has_service_errors = ! empty( $checks['errors'] );
?>

<div class="sui-accordion sui-accordion-block" data-section="files">
<?php if ( ! $has_service_errors ) { ?>

	<?php foreach ( $checks['checks'] as $check_type => $check ) { ?>
		<?php
		if ( 'is_done' === $check_type ) {
			return;
		}
		?>
		<?php
			$check_id = ! empty( $check['check_id'] )
				? $check['check_id']
				: ( ! empty( $check['title'] ) ? md5( $check['title'] ) : '' );
		?>
		<div class="sui-accordion-item" data-check_item="<?php echo esc_attr( $check_id ); ?>">
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
				<div>
					<?php
					$content = ! empty( $check['estimated_package_size'] ) ? size_format( $check['estimated_package_size'] ) : 0;
					$type    = 'ok' === $check['status'] ? 'success' : 'warning'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
					$this->render(
						'tags/status-text',
						array(
							'status' => $type,
							'text'   => $content,
						)
					);
					?>
				</div>
				<div>
					<button class="sui-button-icon sui-accordion-open-indicator">
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
					</button>
				</div>
			</div>
			<div class="sui-accordion-item-body">
			<?php if ( ! empty( $check['message'] ) ) { ?>
				<?php echo wp_kses_post( $check['message'] ); ?>
			<?php } ?>
			</div>
		</div>
		<?php
	}
} else {
	$this->render( 'msgs/wizard-rpkg-errors', array( 'result' => $result ) );
}
?>
</div>
