<?php
/**
 * Shipper modal templates: site selection modals parent template
 *
 * @since v1.0.3
 * @package shipper
 */

$modal                 = ! empty( $modal ) ? $modal : 'loading';
$arguments             = ! empty( $type ) ? array( 'type' => $type ) : array();
$modal_path            = "modals/selection/{$modal}";
$modal_id              = "shipper-site_selection-{$modal}";
$arguments['modal_id'] = $modal_id;

$modal_class = sprintf(
	'%s %s',
	sanitize_html_class( 'shipper-site_selection' ),
	sanitize_html_class( $modal_id )
);

$size_class  = 'sui-modal-lg';
$modal_space = '100';

if ( 'loading' === $modal ) {
	$size_class = 'sui-modal-sm';
} elseif ( 'confirm-password' === $modal ) {
	$size_class  = 'sui-modal-md';
	$modal_space = '30';
}

$modal_class .= " {$size_class}";
?>

<div class="sui-modal <?php echo esc_attr( $size_class ); ?>" id="shipper-site_selection">
	<div
		role="dialog"
		id="<?php echo esc_attr( $modal_id ); ?>"
		class="sui-modal-content sui-content-fade-in <?php echo esc_attr( $modal_class ); ?>"
		aria-modal="true"
		aria-labelledby="<?php echo esc_attr( $modal_id ); ?>-title"
		aria-describedby="<?php echo esc_attr( $modal_id ); ?>-description"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60 sui-spacing-sides--<?php echo esc_attr( $modal_space ); ?>">
				<?php
				$this->render( $modal_path, $arguments );
					echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() );
				?>
			</div>
		</div>
	</div>