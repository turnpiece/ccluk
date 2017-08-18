<?php
if ( bp_is_active('notifications') ):
$notifications = bp_notifications_get_notifications_for_user( bp_loggedin_user_id(), 'object' );
$count         = ! empty( $notifications ) ? count( $notifications ) : 0;
$alert_class   = (int) $count > 0 ? 'pending-count alert' : 'count no-alert';
$menu_title    = '<span id="ab-pending-notifications" class="' . $alert_class . '">' . number_format_i18n( $count ) . '</span>';
$menu_link     = trailingslashit( bp_loggedin_user_domain() . bp_get_notifications_slug() );

if ( $menu_link && onesocial_get_option( 'notifications_button' ) ) {
	?>

	<div id="all-notificatios" class="header-notifications">

		<a class="notification-link header-button underlined" href="<?php echo $menu_link; ?>">
			<?php echo preg_replace('/>([0-9]*)<\/span/', '><b>$1</b></span', $menu_title ); ?>
		</a>

		<div class="pop">
			<ul class="bb-adminbar-notifications">
				<?php if ( ! empty( $notifications ) && is_array( $notifications ) ): ?>
				<?php foreach ( $notifications as $notification ): ?>
				<li>
					<?php echo '<a href="' . $notification->href . '"><span class="notification-icon '. $notification->component_name. ' ' .$notification->component_action .'"></span><span class="notification-content">' . $notification->content . '</span></a>'; ?>
				</li>
				<?php endforeach; ?>
				<?php else: ?>
					<?php echo '<a href="' . bp_loggedin_user_domain() . '' . BP_NOTIFICATIONS_SLUG . '/">' . __( "No new notifications", "onesocial" ) . '</a>'; ?>
				<?php endif; ?>

			</ul>
		</div>

	</div>

	<?php
}
endif;