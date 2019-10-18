<?php
/**
 * Notice template for admin notifications.
 *
 * @var string $content Notice content.
 * @var string $type    Notice type.
 * @var bool   $top     Is this top notice?.
 * @var bool   $dismiss Is this top notice dismissble?.
 */

defined( 'WPINC' ) || die();

?>

<?php if ( $top ) : // If top notification. ?>
	<div class="sui-notice-top sui-notice-<?php echo esc_attr( $type ); ?> <?php echo $dismiss ? 'sui-can-dismiss' : ''; ?>">
		<div class="sui-notice-content">
			<p><?php echo $content; // Notice content. ?></p>
		</div>
		<?php if ( $dismiss ) : ?>
			<span class="sui-notice-dismiss">
				<a role="button" href="#" aria-label="<?php esc_html_e( 'Dismiss', 'ga_trans' ); ?>" class="sui-icon-check"></a>
			</span>
		<?php endif; ?>
	</div>
<?php else : ?>
	<div class="beehive-notice sui-notice sui-notice-<?php echo esc_attr( $type ); ?>">
		<p><?php echo $content; // Notice content. ?></p>
	</div>
<?php endif; ?>