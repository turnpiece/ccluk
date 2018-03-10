<?php
/**
 * Page caching meta box footer.
 *
 * @package Hummingbird
 */

?>
	<?php wp_nonce_field( 'wphb-caching' ); ?>
	<input type="hidden" name="pc-settings" value="1">
	<div class="buttons buttons-on-right">
		<input type="submit" class="button" name="submit" value="<?php esc_attr_e( 'Save Settings', 'wphb' ); ?>">
	</div>
</form>