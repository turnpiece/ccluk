<?php
$wrapper_class	 = 'modal-without-social-login';
$class			 = 'full-width-col';

global $WORDPRESS_SOCIAL_LOGIN_VERSION;

if ( $WORDPRESS_SOCIAL_LOGIN_VERSION ) {
	$class			 = 'col';
	$wrapper_class	 = 'modal-with-social-login';
}
?>

<div id="siteRegisterBox" class="mfp-hide boss-modal-form popup-content <?php echo $wrapper_class; ?>">

	<div class="registerfields">

		<div class="animated fadeInDownShort RegisterBox slow">
			<?php
			$title	 = onesocial_get_option( 'register_form_title' );
			$desc	 = onesocial_get_option( 'register_form_description' );

			if ( $title ) {
				echo '<h4 class="popup_title">' . $title . '</h4>';
			}

			if ( $desc ) {
				echo '<div class="description">' . $desc . '</div>';
			}
			?>

			<div id="ajax_register_messages" class="messages-output"></div>

			<div class="row">

				<div class="<?php echo $class; ?> with-email">
                    <form method="POST" action="<?php echo admin_url( 'admin-ajax.php' );?>" id="frm_siteRegisterBox">
						<input type="hidden" name="action" value="os_ajax_register">

						<h5><?php _e( 'Fill the form', 'onesocial' ); ?></h5>

						<p class="email-wrap">
							<input type="email" id="register_email" name="register_email" placeholder="Email" class="input" />
						</p>

						<p class="username-wrap">
							<!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
							<input style="display:none" type="password" name="fakepasswordremembered"/>
							<input type="text" id="register_username" name="register_username" placeholder="<?php _e( 'Username', 'onesocial' ) ?>" class="input" />
						</p>

						<p class="password-wrap">
							<input type="password" id="register_password" name="register_password" placeholder="<?php _e( 'Password', 'onesocial' ) ?>" class="input" />
						</p>

						<?php

						/**
						 * Fires and displays any extra member registration details fields.
						 *
						 * @since 1.2
						 */
						do_action( 'bp_account_details_fields' ); ?>

						<?php

						/**
						 * Fires after the display of member registration account details fields.
						 *
						 * @since 1.1.0
						 */
						do_action( 'bp_after_account_details_fields' ); ?>

						<?php /***** Extra Profile Details ******/ ?>

						<br/>

						<?php do_action( 'onesocial_registration_fields' ); ?>

						<?php if ( bp_is_active( 'xprofile' ) ) : if ( bp_has_profile( array( 'profile_group_id' => 1, 'fetch_field_data' => false ) ) ) : while ( bp_profile_groups() ) : bp_the_profile_group(); ?>

						<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>

							<div<?php bp_field_css_class( 'editfield' ); ?>>

								<?php
								$field_type = bp_xprofile_create_field_type( bp_get_the_profile_field_type() );
								$field_type->edit_field_html();

								/**
								 * Fires after the display of the visibility options for xprofile fields.
								 *
								 * @since 1.1.0
								 */
								do_action( 'bp_custom_profile_edit_fields' ); ?>

								<p class="description"><?php bp_the_profile_field_description(); ?></p>

							</div>

						<?php endwhile; ?>

						<input type="hidden" name="signup_profile_field_ids" id="signup_profile_field_ids" value="<?php bp_the_profile_field_ids(); ?>" />

						<?php endwhile; endif; endif; ?>

	                    <?php

	                    /**
	                     * Fires and displays any extra member registration xprofile fields & Memeber type fields.
	                     */
	                    do_action( 'bp_signup_profile_fields' ); ?>

                        <?php remove_filter( 'bp_xprofile_is_richtext_enabled_for_field', 'onesocial_disable_richtext_for_fields', 90 );?>

						<?php if ( bp_get_blog_signup_allowed() ) : ?>

							<?php

							/**
							 * Fires before the display of member registration blog details fields.
							 *
							 * @since 1.1.0
							 */
							do_action( 'bp_before_blog_details_fields' ); ?>

							<?php /***** Blog Creation Details ******/ ?>

							<div class="register-section" id="blog-details-section">

								<h2><?php _e( 'Blog Details', 'buddypress' ); ?></h2>

								<p><label for="signup_with_blog"><input type="checkbox" name="signup_with_blog" id="signup_with_blog" value="1"<?php if ( (int) bp_get_signup_with_blog_value() ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'Yes, I\'d like to create a new site', 'buddypress' ); ?></label></p>

								<div id="blog-details"<?php if ( (int) bp_get_signup_with_blog_value() ) : ?>class="show"<?php endif; ?>>

									<label for="signup_blog_url"><?php _e( 'Blog URL', 'buddypress' ); ?> <?php _e( '(required)', 'buddypress' ); ?></label>
									<?php

									/**
									 * Fires and displays any member registration blog URL errors.
									 *
									 * @since 1.1.0
									 */
									do_action( 'bp_signup_blog_url_errors' ); ?>

									<?php if ( is_subdomain_install() ) : ?>
										http:// <input type="text" name="signup_blog_url" id="signup_blog_url" value="<?php bp_signup_blog_url_value(); ?>" /> .<?php bp_signup_subdomain_base(); ?>
									<?php else : ?>
										<?php echo home_url( '/' ); ?> <input type="text" name="signup_blog_url" id="signup_blog_url" value="<?php bp_signup_blog_url_value(); ?>" />
									<?php endif; ?>

									<label for="signup_blog_title"><?php _e( 'Site Title', 'buddypress' ); ?> <?php _e( '(required)', 'buddypress' ); ?></label>
									<?php

									/**
									 * Fires and displays any member registration blog title errors.
									 *
									 * @since 1.1.0
									 */
									do_action( 'bp_signup_blog_title_errors' ); ?>
									<input type="text" name="signup_blog_title" id="signup_blog_title" value="<?php bp_signup_blog_title_value(); ?>" />

									<fieldset class="register-site">
										<legend class="label"><?php _e( 'Privacy: I would like my site to appear in search engines, and in public listings around this network.', 'buddypress' ); ?></legend>
										<?php

										/**
										 * Fires and displays any member registration blog privacy errors.
										 *
										 * @since 1.1.0
										 */
										do_action( 'bp_signup_blog_privacy_errors' ); ?>

										<label for="signup_blog_privacy_public"><input type="radio" name="signup_blog_privacy" id="signup_blog_privacy_public" value="public"<?php if ( 'public' == bp_get_signup_blog_privacy_value() || !bp_get_signup_blog_privacy_value() ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'Yes', 'buddypress' ); ?></label>
										<label for="signup_blog_privacy_private"><input type="radio" name="signup_blog_privacy" id="signup_blog_privacy_private" value="private"<?php if ( 'private' == bp_get_signup_blog_privacy_value() ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'No', 'buddypress' ); ?></label>
									</fieldset>

									<?php

									/**
									 * Fires and displays any extra member registration blog details fields.
									 *
									 * @since 1.9.0
									 */
									do_action( 'bp_blog_details_fields' ); ?>

								</div>

							</div><!-- #blog-details-section -->

							<?php
							/**
							 * Fires after the display of member registration blog details fields.
							 *
							 * @since 1.1.0
							 */
							do_action( 'bp_after_blog_details_fields' ); ?>

						<?php endif; ?>

						<p>
							<button id="register_button" class="button" type="submit"><i class="fa fa-spinner fa-spin" style="display: none"></i> <?php _e( 'Register Now', 'onesocial' ); ?></button>
						</p>

						<?php wp_nonce_field( 'ajax-register-security', 'ajax-register-security' ); ?>

						<h6><?php _e( 'Already a member?', 'onesocial' ); ?> <a href="#" class="siginbutton"><?php _e( 'Sign In', 'onesocial' ); ?></a>.</h6>

                    </form><!-- #frm_siteRegisterBox -->
				</div>

				<div class="<?php echo $class; ?> with-plugin">

					<?php do_action( 'login_form' ); ?>

					<?php
					$login_message = onesocial_get_option( 'boss_login_message' );

					if ( !empty( $WORDPRESS_SOCIAL_LOGIN_VERSION ) && !empty( $login_message ) ) {
						?>
						<p class="login-message"><?php echo $login_message; ?></p>
					<?php } ?>

				</div>

			</div>

		</div>

	</div>

	<div class="joined" style="display:none">
		<h4 class="popup_title"><?php  printf( __( 'Welcome to %s', 'onesocial' ), get_bloginfo( 'name' ) ) ?></h4>

		<p class="express"><?php _e( 'To finish activating your account, check your inbox for our Welcome message and confirm your email address.', 'onesocial' ); ?></p>

		<button id="register_okay" class="button"><i class="fa fa-spinner fa-spin" style="display: none"></i> <?php _e( 'Okay', 'onesocial' ); ?></button>

	</div>

</div>

<a href="#siteRegisterBox" class="onesocial-register-popup-link mfp-hide"><?php _e( 'Register', 'onesocial' ); ?></a>
