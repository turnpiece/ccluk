<?php
/**
 * Asset optimization meta box footer (common for basic and advanced views).
 *
 * @package Hummingbird
 *
 * @since 1.7.1
 */

?>
<span class="status-text">
		<?php esc_html_e( 'Publishing changes will regenerate any necessary assets, this may take a few seconds to run.', 'wphb' ); ?>
	</span>
<div class="sui-actions-right">
	<input type="submit" id="wphb-publish-changes" class="sui-button sui-button-primary disabled" name="submit" value="<?php esc_attr_e( 'Publish Changes', 'wphb' ); ?>"/>
</div>