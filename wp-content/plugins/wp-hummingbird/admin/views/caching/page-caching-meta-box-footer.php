<?php
/**
 * Page caching meta box footer.
 *
 * @package Hummingbird
 */

?>
<?php wp_nonce_field( 'wphb-caching' ); ?>
<input type="hidden" name="pc-settings" value="1">
<div class="sui-actions-right">
	<input type="submit" class="sui-button sui-button-primary" name="submit" value="<?php esc_attr_e( 'Save Settings', 'wphb' ); ?>">
</div>