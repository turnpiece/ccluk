<?php
if ( !is_user_logged_in() ) { ?>

	<a href="#" class="login header-button animatedClick" data-target="LoginBox" title="<?php _e( 'Login', 'onesocial' ); ?>"><?php _e( 'Login', 'onesocial' ) ?></a><?php
	//get_template_part( 'template-parts/site-login' );

	if ( buddyboss_is_bp_active() && bp_get_signup_allowed() ) {
		?>

		<a href="<?php echo bp_get_signup_page(); ?>" class="header-button animatedClick" title="<?php _e( 'Join', 'onesocial' ); ?>"><?php _e( 'Join', 'onesocial' ); ?></a><?php
		//get_template_part( 'template-parts/site-register' );
	}


} else {

	$user_link = function_exists( 'bp_core_get_user_domain' ) ? bp_core_get_user_domain( get_current_user_id() ) : '#';

    if(function_exists( 'is_buddypress' )) {
	?>
	<div class="header-account-login header-button">

		<a class="user-link" href="<?php echo $user_link; ?>">
			<?php echo get_avatar( get_current_user_id(), 100 ); ?>
		</a>

		<div class="pop">
			<?php
			$class = function_exists( 'is_buddypress' ) ? 'bp-active' : 'bp-inactive';
			?>

			<div id="adminbar-links" class="bp_components adminbar-links <?php echo $class; ?>">
				<?php buddyboss_adminbar_myaccount(); ?>
			</div>

			<?php wp_nav_menu( array( 'theme_location' => 'header-my-account', 'fallback_cb' => '', 'menu_class' => 'links' ) ); ?>

			<a class="boss-logout" href="<?php echo wp_logout_url(); ?>"><?php _e( 'Logout', 'onesocial' ); ?></a>
		</div>

	</div>

	<?php
    }
}
