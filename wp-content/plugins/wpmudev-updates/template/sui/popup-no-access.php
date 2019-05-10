<?php
/**
 * Dashboard popup template: No Access!
 *
 * This popup is displayed when a user is logged in and can view the current
 * Dashboard page, but the WPMUDEV account does not allow him to use the
 * features on the current page.
 * Usually this is displayed when a member has a single license and visits the
 * Plugins or Themes page (he cannot install new plugins or themes).
 *
 * Following variables are passed into the template:
 *   $is_logged_in
 *   $urls
 *   $username
 *   $reason
 *   $auto_show
 *
 * @since   4.0.0
 * @package WPMUDEV_Dashboard
 */

/** @var  WPMUDEV_Dashboard_Sui_Page_Urls $urls */
$url_upgrade = $urls->remote_site . 'hub/account/';
$url_logout  = $urls->dashboard_url . '&clear_key=1';
$url_refresh = wp_nonce_url( add_query_arg( 'action', 'check-updates' ), 'check-updates', 'hash' );

switch ( $reason ) {
	case 'free':
		$reason_text =
			__( "%s, to get access to all of our premium plugins, as well as 24/7 support you'll need an <strong>active membership</strong>. It's easy to do and only takes a few minutes!",
			    'wpmudev' );
		break;

	case 'single':
		$reason_text =
			__( "%s, to get access to all of our premium plugins, as well as 24/7 support you'll need to upgrade your membership from <strong>single</strong> to <strong>full</strong>. It's easy to do and only takes a few minutes!",
			    'wpmudev' );
		break;

	default:
		$reason_text = __( "%s, to get access to all of our premium plugins, as well as 24/7 support you'll need to upgrade your membership. It's easy to do and only takes a few minutes!",
		                   'wpmudev' );
		break;
}

?>
<div class="sui-dialog" tabindex="-1" aria-hidden="true" id="upgrade-membership">

	<?php if ( 'free' === $reason ) : ?>
		<div class="sui-dialog-overlay"></div>
	<?php endif; ?>
	<?php if ( 'single' === $reason ) : ?>
		<div class="sui-dialog-overlay" data-a11y-dialog-hide=""></div>
	<?php endif; ?>

	<div class="sui-dialog-content" aria-labelledby="dialogTitle" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">
			<form>

				<div class="sui-box-header">
					<h3 class="sui-box-title" id="dialogTitle"><?php esc_html_e( 'Upgrade your WPMU DEV Membership!', 'wpmudev' ); ?></h3>
					<div class="sui-actions-right">
						<a href="<?php echo esc_url( $url_upgrade ); ?>" class="sui-button sui-button-green" target="_blank">
							<?php esc_html_e( 'Upgrade Membership', 'wpmudev' ); ?>
						</a>
					</div>
				</div>

				<div class="sui-box-body">
					<p id="dialogDescription">
						<?php
						// @codingStandardsIgnoreStart: Reason contains HTML, no escaping!
						printf( $reason_text, esc_html( ucfirst( $username ) ) );
						// @codingStandardsIgnoreEnd
						?>
					</p>

					<ul>
						<li><i class="sui-icon-check" aria-hidden="true"></i> <?php esc_html_e( 'Access to 140+ Plugins', 'wpmudev' ); ?></li>
						<li><i class="sui-icon-check" aria-hidden="true"></i> <?php esc_html_e( 'Access to Security, Backups, SEO and Performance Services', 'wpmudev' ); ?></li>
						<li><i class="sui-icon-check" aria-hidden="true"></i> <?php esc_html_e( '24/7 Expert WordPress Support', 'wpmudev' ); ?></li>
					</ul>

					<div class="sui-block-content-center">
						<a href="<?php echo esc_url( $url_upgrade ); ?>" class="sui-button sui-button-green sui-button-lg" target="_blank">
							<?php esc_html_e( 'Upgrade Membership', 'wpmudev' ); ?>
						</a>
					</div>

				</div>

				<div class="sui-box-footer">
					<a class="sui-button sui-button-ghost" href="<?php echo esc_url( $url_refresh ); ?>">
						<i class="sui-icon-update" aria-hidden="true"></i>
						<?php esc_html_e( 'Refresh Status', 'wpmudev' ); ?>
					</a>
					<div class="sui-actions-right">
						<a class="sui-button" href="<?php echo esc_url( $url_logout ); ?>">
							<i class="sui-icon-power-on-off" aria-hidden="true"></i>
							<?php esc_html_e( 'Switch Account', 'wpmudev' ); ?>
						</a>
					</div>
				</div>
			</form>
		</div>

	</div>

</div>

<script type="text/javascript">
	jQuery(document).on('wpmud.ready', function () {
		if (typeof window.wpmudevDashboardAdminDialog === 'function') {
			var dialog        = document.getElementById('upgrade-membership');
			var upgradeDialog = new wpmudevDashboardAdminDialog(dialog, jQuery('.sui-wrap').get(0));
			upgradeDialog.show();

			setTimeout(function () {
				if (jQuery('#upgrade-membership').attr('aria-hidden')) {
					jQuery('#upgrade-membership').removeAttr('aria-hidden');
				}
			}, 2000);
		}
	});
</script>
