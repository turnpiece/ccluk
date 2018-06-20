<?php
/**
 * Advanced tools database cleanup settings meta box header.
 *
 * @package Hummingbird
 * @since 1.8
 */
?>

<h3 class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="sui-actions-right">
		<a class="sui-button sui-button-green sui-tooltip sui-tooltip-top-left"
		   data-tooltip="<?php esc_attr_e( 'Unlock automatic scheduled database cleanups with a WPMU DEV Membership', 'wphb' ); ?>"
		   href="<?php echo esc_url( WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dbcleanup_schedule_upgrade_button' ) ); ?>"
		   target="_blank"
		>
			<?php esc_html_e( 'Upgrade to Pro', 'wphb' ); ?>
		</a>
	</div>
<?php endif; ?>