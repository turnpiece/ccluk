<?php
/**
 * Shipper package settings templates: settings root template
 *
 * @since v1.1
 * @package shipper
 */

?>
<div class="shipper-packages-settings">
<form method="POST">
	<input type="hidden"
		name="_wpnonce"
		value="<?php echo esc_attr( wp_create_nonce( 'shipper-settings' ) ); ?>" />
	<div class="sui-box shipper-packages-settings-main">

		<div class="sui-box-header">
			<h3 class="sui-box-title">
				<?php esc_html_e( 'Settings', 'shipper' ); ?>
			</h3>
		</div><!-- sui-box-header -->

		<div class="sui-box-body">
			<?php
				$this->render( 'pages/packages/settings/item', array( 'item' => 'database' ) );
				$this->render( 'pages/packages/settings/item', array( 'item' => 'archive' ) );
				$this->render( 'pages/packages/settings/item', array( 'item' => 'safe-mode' ) );
			?>
		</div><!-- sui-box-body -->

		<div class="sui-box-footer">
			<div class="sui-actions-right">
				<button type="submit" class="sui-button sui-button-primary shipper-save">
					<i class="sui-icon-save" aria-hidden="true"></i>
					<?php esc_attr_e( 'Save Changes', 'shipper' ); ?>
				</button>
			</div>
		</div><!-- sui-box-footer -->

	</div><!-- sui-box -->
</form>
</div>


<style>
.shipper-settings-item-body label.sui-form-field {
	display: block;
}
.shipper-settings-item-body label.sui-form-field > span.sui-label {
	color: #666666;
	font-size: 15px;
	font-weight: 500;
	line-height: 22px;
}
.shipper-settings-item-body label.sui-form-field span.sui-description {
	color: #888888;
	font-size: 13px;
	line-height: 22px;
	margin-bottom: 10px;
}
</style>