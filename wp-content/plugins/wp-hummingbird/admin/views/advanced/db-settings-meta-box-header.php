<?php
/**
 * Advanced tools database cleanup settings meta box header.
 *
 * @package Hummingbird
 * @since 1.8
 */
?>

<h3><?php echo esc_html( $title ); ?></h3>
<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="buttons">
		<a class="button button-content-cta tooltip-right tooltip-l" tooltip="<?php esc_attr_e( 'Unlock automatic scheduled database cleanups with a WPMU DEV Membership', 'wphb' ); ?>" href="<?php echo WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dbcleanup_schedule_upgrade_button' ); ?>" target="_blank">
			<?php _e( 'Upgrade to Pro', 'wphb' ); ?>
		</a>
	</div>
<?php endif; ?>