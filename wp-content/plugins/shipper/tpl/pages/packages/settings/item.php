<?php
/**
 * Shipper package settings templates: settings item root template
 *
 * @since v1.1
 * @package shipper
 */

$item     = ! empty( $item ) ? $item : false;
$item_tpl = "pages/packages/settings/item-{$item}";
?>
<div class="sui-box-settings-row shipper-settings-item shipper-item-<?php echo esc_attr( $item ); ?>">

	<div class="sui-box-settings-col-1 shipper-settings-item-summary">
		<?php $this->render( "{$item_tpl}-title" ); ?>
	</div>

	<div class="sui-box-settings-col-2 shipper-settings-item-body">
		<?php $this->render( "{$item_tpl}-body" ); ?>
	</div>

</div>