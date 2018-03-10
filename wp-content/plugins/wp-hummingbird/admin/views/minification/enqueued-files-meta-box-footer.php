<?php
/**
 * Asset optimization meta box footer (common for basic and advanced views).
 *
 * @package Hummingbird
 *
 * @since 1.7.1
 */

?>
<div class="buttons buttons-on-right">
	<span class="status-text alignleft">
		<?php esc_html_e( 'Publishing changes will regenerate any necessary assets, this may take a few seconds to run.', 'wphb' ); ?>
	</span>
	<input type="submit" class="button disabled" name="submit" value="<?php esc_attr_e( 'Publish Changes', 'wphb' ); ?>"/>
</div>