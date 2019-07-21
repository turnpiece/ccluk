<?php
/**
 * Shipper message templates: migration destination delete success
 *
 * @package shipper
 */

?>
<div style="display:none"
	class="sui-notice-top sui-notice-success sui-can-dismiss shipper-destdelete-success">
	<div class="sui-notice-content">
		<p>
		<?php printf(
			__(
				'%s has been successfully removed from your destinations.',
				'shipper'
			),
			'<b class="shipper-destdelete-target"></b>'
		); ?>
		</p>
	</div>
	<span class="sui-notice-dismiss">
		<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
	</span>
</div>
