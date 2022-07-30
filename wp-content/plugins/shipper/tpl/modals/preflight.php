<?php
/**
 * Shipper modal templates: preflight modals parent template
 *
 * @since v1.0.3
 * @package shipper
 */

$modal                   = ! empty( $modal ) ? $modal : 'loading';
$arguments               = ! empty( $arguments ) ? $arguments : array();
$arguments['is_recheck'] = ! empty( $is_recheck );

$modal_path  = "modals/preflight/{$modal}";
$modal_id    = "shipper-preflight-{$modal}";
$modal_class = sprintf(
	'%s %s',
	sanitize_html_class( 'shipper-preflight' ),
	sanitize_html_class( $modal_id )
);

$arguments['modal_id'] = $modal_id;
$size_class            = 'results' === $modal ? 'sui-modal-xl' : 'sui-modal-md';
$modal_class          .= " {$size_class}";
?>

<div class="sui-modal <?php echo esc_attr( $modal_class ); ?>">

	<div
		role="dialog"
		id="<?php echo esc_attr( $modal_id ); ?>"
		class="sui-modal-content sui-fade-in"
	>
		<div class="sui-box" role="document">
			<?php $this->render( $modal_path, $arguments ); ?>
			<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
		</div>
	</div>

</div>