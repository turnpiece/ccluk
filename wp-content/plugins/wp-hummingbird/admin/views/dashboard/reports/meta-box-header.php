<?php
/**
 * Reports meta box header on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $title  Reports module title.
 */

?>
<h3><?php echo esc_html( $title ); ?></h3>
<?php if ( ! wphb_is_member() ) : ?>
	<div class="buttons">
		<a class="button button-content-cta" href="#wphb-upgrade-membership-modal" id="dash-reports-update-membership" rel="dialog">
			<?php esc_html_e( 'Upgrade to PRO', 'wphb' ); ?>
		</a>
	</div>
<?php endif; ?>