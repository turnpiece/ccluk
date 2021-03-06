<?php
/**
 * Shipper modal templates: preflight modals parent template
 *
 * @since v1.0.3
 * @package shipper
 */

$modal = ! empty( $modal ) ? $modal : 'loading';
$arguments = ! empty( $arguments ) ? $arguments : array();
$arguments['is_recheck'] = ! empty( $is_recheck );

$modal_path = "modals/preflight/{$modal}";

$modal_id = "shipper-preflight-{$modal}";
$modal_class = sprintf(
	'%s %s',
	sanitize_html_class( 'shipper-preflight' ),
	sanitize_html_class( $modal_id )
);
$arguments['modal_id'] = $modal_id;

$size_class = 'results' === $modal
	? 'sui-dialog-lg'
	: 'sui-dialog-reduced';
$modal_class .= " {$size_class}";
?>

<div
	class="sui-dialog sui-dialog-alt sui-fade-in <?php echo esc_attr( $modal_class ); ?>"
	tabindex="-1" id="<?php echo esc_attr( $modal_id ); ?>" aria-hidden="true">
	<div class="sui-dialog-overlay" data-a11y-dialog-hide="<?php echo esc_attr( $modal_id ); ?>"></div>

	<div class="sui-dialog-content sui-fade-in" role="dialog">
		<div class="sui-box" role="document">
			<?php $this->render( $modal_path, $arguments ); ?>
			<?php echo Shipper_Helper_Assets::get_custom_hero_image_markup(); ?>
		</div>
	</div>

</div>