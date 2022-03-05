<?php
/*
 * Mobile Logo Option
 */

$logo_id = 366;
$logo	 = $logo_id ? wp_get_attachment_image( $logo_id, 'medium', '', array( 'class' => 'boss-mobile-logo' ) ) : get_bloginfo( 'name' );
?>

<div id="mobile-header">

    <div class="mobile-header-inner">

        <!-- Left button -->
		<?php if ( is_user_logged_in() || (!is_user_logged_in() && buddyboss_is_bp_active() && !bp_hide_loggedout_adminbar( false ) ) ) : ?>
			<?php if ( !is_user_logged_in() ) { ?>
				<a href="<?php echo wp_login_url(); ?>" class="login header-button bb-user-login-link"><?php _e( 'Login', 'onesocial' ); ?></a><?php
			}
			?>
			<a href="#" id="user-nav" class="left-btn onesocial-mobile-button" data-position="left">
                <?php echo get_avatar( get_current_user_id(), 55 ); ?>
            </a>
		<?php endif; ?>

        <!-- Right button -->
        <a href="#" id="main-nav" class="right-btn onesocial-mobile-button" data-position="right">Menu</a>
    </div>

	<div id="mobile-logo">
		<h1 class="site-title">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php echo $logo; ?>
			</a>
		</h1>
	</div>

</div><!-- #mobile-header -->
