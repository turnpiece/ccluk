<?php
/**
 * Settings meta box header.
 *
 * @var string $title
 */
?>

<h3><?php echo esc_html( $title ); ?></h3>

<?php if ( ! wphb_is_member() ) : ?>
	<div class="buttons">
		<a class="button button-content-cta" href="#wphb-upgrade-membership-modal" id="dash-uptime-update-membership" rel="dialog">
			<?php _e( 'Upgrade to PRO', 'wphb' ); ?>
		</a>
	</div>
<?php endif; ?>