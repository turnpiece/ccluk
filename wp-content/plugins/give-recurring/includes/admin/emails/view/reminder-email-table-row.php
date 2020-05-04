<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php ob_start(); ?>
<tr>
	<th class="check-column">
		<span class="give-email-notification-status give-email-notification-%1$s" data-id="%2$s" data-status="%3$s" data-edit="1"><i class="dashicons dashicons-%4$s"></i></span>
		<span class="spinner"></span>
		<!-- Email Status -->
	</th>
	<td>
		<div>%5$s</div> <!-- Email Subject Title -->
		<div class="action-buttons"><a href="%7$s">%8$s</a> | <a href="%9$s">%10$s</a> | <a target="_blank" href="%11$s">%12$s</a></div> <!-- Row Actions -->
	</td>
	<td>%6$s</td> <!-- Send Period -->
	<td>%14$s</td> <!-- Email Content Type -->
	<td>%13$s</td> <!-- Gateways -->
</tr>
<?php return ob_get_clean(); ?>