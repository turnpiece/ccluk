<?php
/**
 * Page caching meta box footer.
 *
 * @package Hummingbird
 */

?>
	<?php wp_nonce_field( 'wphb-caching' ); ?>
	<input type="hidden" name="pc-settings" value="1">
	<div class="buttons alignright">
		<input type="submit" class="button button-large" name="submit" value="<?php esc_attr_e( 'Save Settings', 'wphb' ); ?>">
	</div>
</form>