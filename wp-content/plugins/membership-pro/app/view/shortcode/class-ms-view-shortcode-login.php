<?php
class MS_View_Shortcode_Login extends MS_View {

	/**
	 * Returns the HTML code.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function to_html() {
		$res_html = '';
		$res_form = '';
		$html = '';

		$valid_forms = array(
			'login',
			'logout',
			'reset',
			'lost',
		);

		extract( $this->data );

		if ( ! isset( $form ) || ! in_array( $form, $valid_forms ) ) {
			if ( MS_Model_Member::is_logged_in() ) {
				$form = 'logout';
			} elseif ( isset( $action ) && 'resetpass' === $action ) {
				$form = 'reset';
			} elseif ( isset( $_GET['show'] ) && 'lostpass' == $_GET['show'] ) {
				$form = 'lost';
			} else {
				$form = 'login';
			}

			$this->data['form'] = $form;
		}

		/**
		 * Provide a customized login form.
		 *
		 * Possible filters to provide a customized login form:
		 * - 'ms_shortcode_custom_form-login'
		 * - 'ms_shortcode_custom_form-logout'
		 * - 'ms_shortcode_custom_form-reset'
		 * - 'ms_shortcode_custom_form-lost'
		 *
		 * @since  1.0.0
		 */
		$html = apply_filters(
			'ms_shortcode_custom_form-' . $form,
			'',
			$this->data
		);

		if ( ! empty( $html ) ) {
			return $html;
		} else {
			$html = '';
		}

		if ( 'logout' === $form ) {
			return $this->logout_form();
		} elseif ( 'reset' === $form ) {
			return $this->reset_form();
		} else {
			if ( empty( $redirect_login ) ) {
				$redirect_login = MS_Helper_Utility::get_current_url();
			}

			//Filter to use ajax login
			$ajax_login = apply_filters( 'ms_shortcode_ajax_login', true );

			// Build the Login Form.
			//$res_form .= $prefix;
			if ( $ajax_login ) {
				$res_form .= $this->login_form( $redirect_login );
				$res_form .= $this->lostpass_form();
			} else {
				$res_form .= wp_login_form( array( 'echo' => false, 'redirect' => $redirect_login ) );
				$res_form .= '<br/><a href="'.wp_lostpassword_url( get_bloginfo('url') ).'" title="'.__( 'Lost Password', 'membership2' ).'">'.__( 'Lost Password', 'membership2' ).'</a>';
			}

			// Wrap form in optional wrappers.
			if ( ! empty( $wrapwith ) ) {
				$res_form .= sprintf( '<%s class="%s">', esc_attr( $wrapwith ), esc_attr( $wrapwithclass ) );
				$res_form = sprintf(
					'<%1$s class="%2$s">%3$s</%1$s>',
					esc_attr( $wrapwith ),
					esc_attr( $wrapwithclass ),
					$res_form
				);
			}
			if ( ! empty( $item ) ) {
				$res_form = sprintf(
					'<%1$s class="%2$s">%3$s</%1$s>',
					esc_attr( $item ),
					esc_attr( $itemclass ),
					$res_form
				);
			}
			if ( ! empty( $holder ) ) {
				$res_form = sprintf(
					'<%1$s class="%2$s">%3$s</%1$s>',
					esc_attr( $holder ),
					esc_attr( $holderclass ),
					$res_form
				);
			}

			// Complete the HTML output.
			if ( $header ) {
				$html .= $this->login_header_html();
			}
			$html .= $res_form;

			if ( $register && ! MS_Model_Member::is_logged_in() ) {
				if ( MS_Model_Member::can_register() ) {
					$link = sprintf(
						'<a href="%1$s">%2$s</a>',
						MS_Controller_Frontend::get_registration_url( 'register' ),
						__( 'Register', 'membership2' )
					);

					/**
					 * Filter documented in wp-includes/general-template.php
					 */
					$html .= apply_filters( 'register', $link );
				}
			}
		}
		// Remove linebreaks to bypass the "wpautop" filter.
		$html = str_replace( array( "\r\n", "\r", "\n" ), '', $html );

		$html = '<div class="ms-membership-form-wrapper">' . $html . '</div>';
		$html = apply_filters( 'ms_compact_code', $html );

		/*
		 * Possible filters to provide a customized login form:
		 * - 'ms_shortcode_form-login'
		 * - 'ms_shortcode_form-logout'
		 * - 'ms_shortcode_form-reset'
		 * - 'ms_shortcode_form-lost'
		 */
		return apply_filters(
			'ms_shortcode_form-' . $form,
			$html,
			$this->data
		);
	}

	/**
	 * Returns HTML partial with the header of the login form.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	private function login_header_html() {
		extract( $this->data );

		ob_start();
		?>
		<legend><?php echo esc_html( $title ); ?></legend>
		<?php if ( $show_note ) : ?>
		<div class="ms-alert-box ms-alert-error ms-auth-header">
			<?php _e( 'Please log in to access this page.', 'membership2' ); ?>
		</div>
		<?php endif;

		return ob_get_clean();
	}

	/**
	 * Returns HTML partial with the actual login form.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $redirect_to URL to redirect to right after login.
	 * @return string
	 */
	private function login_form( $redirect_to = null ) {
		if ( empty( $redirect_to ) ) {
			// Default redirect is back to the current page
			$redirect_to = mslib3()->net->current_url();
		}

		$defaults = array(
			'redirect_login' 	=> $redirect_to,
			'label_username' 	=> __( 'Username', 'membership2' ),
			'label_password' 	=> __( 'Password', 'membership2' ),
			'label_remember' 	=> __( 'Remember Me', 'membership2' ),
			'label_log_in' 		=> __( 'Log In', 'membership2' ),
			'id_login_form' 	=> 'loginform',
			'id_username'		=> 'user_login',
			'id_password' 		=> 'user_pass',
			'id_remember' 		=> 'rememberme',
			'id_login' 			=> 'wp-submit',
			'show_remember' 	=> true,
			'value_username' 	=> '',
			'value_remember' 	=> false, // Set this to true to default the "Remember me" checkbox to checked
			'error_message'		=> '',
			'nav_pos'			=> 'top'
		);

		/**
		 * Filter the default login form output arguments.
		 *
		 * @since  1.0.0
		 *
		 * @see wp_login_form()
		 *
		 * @param array $defaults An array of default login form arguments.
		 */
		$args = wp_parse_args(
			$this->data,
			apply_filters( 'login_form_defaults', $defaults )
		);

		extract( $args );

		$show_form = 'login' === $form ? '' : 'display:none';
		$form_class = 'ms-form ms-form-login';
		if ( $show_labels ) {
			$form_class .= ' ms-has-labels';
		} else {
			$form_class .= ' ms-no-labels';
		}
		if ( $autofocus ) {
			$form_class .= ' autofocus';
		}

		ob_start();
		?>
		<form
			name="<?php esc_attr_e( $id_login_form ); ?>"
			id="<?php esc_attr_e( $id_login_form ); ?>"
			action="login" method="post"
			class="<?php esc_attr_e( $form_class ); ?>"
			style="<?php esc_attr_e( $show_form ); ?>">

			<div class="form">
				<?php wp_nonce_field( 'ms-ajax-login', '_membership_auth_nonce' ); ?>
				<?php echo apply_filters( 'login_form_top', '', $args ); ?>
				<?php if ( 'top' === $nav_pos ) : ?>
					<?php
						if ( !empty( $error_message ) ) {
							?>
							<div class="status"><?php echo $error_message; ?></div>
							<?php
						} else {
							?>
							<div class="status" style="display:none"></div>
							<?php
						}
					?>

				<?php endif; ?>
				<p class="login-username ms-field">
					<?php if ( $show_labels ) : ?>
						<label for="<?php esc_attr_e( $id_username ); ?>">
						<?php echo esc_html( $label_username ); ?>
						</label>
					<?php endif; ?>
					<input
						type="text"
						name="log"
						id="<?php esc_attr_e( $id_username ); ?>"
						class="input focus"
						value="<?php esc_attr_e( $value_username ); ?>"
						size="20"
						placeholder="<?php echo esc_html( $label_username ); ?>">
				</p>
				<p class="login-password ms-field">
					<?php if ( $show_labels ) : ?>
						<label for="<?php esc_attr_e( $id_password ); ?>">
						<?php echo esc_html( $label_password ); ?>
						</label>
					<?php endif; ?>
					<input
						type="password"
						name="pwd"
						id="<?php esc_attr_e( $id_password ); ?>"
						class="input"
						value=""
						size="20"
						placeholder="<?php echo esc_html( $label_password ); ?>">
				</p>
				<?php echo apply_filters( 'login_form_middle', '', $args ); ?>
				<?php if ( 'top' === $nav_pos ) : ?>
					<div class="nav">
						<p><a class="lost" href="#lostpassword">
							<?php _e( 'Lost your password?', 'membership2' ); ?>
						</a></p>
					</div>
				<?php endif; ?>
				<?php if ( $show_remember ) : ?>
				<p class="login-remember ms-field">
					<input
						name="rememberme"
						type="checkbox"
						id="<?php esc_attr_e( $id_remember ); ?>"
						value="forever"
						<?php checked( $value_remember ); ?> />
					<label for="<?php esc_attr_e( $id_remember ); ?>">
						<?php echo esc_html( $label_remember ); ?>
					</label>
				</p>
				<?php endif; ?>
				<?php do_action( 'login_form' );?>
				<p class="login-submit">
					<input
						type="submit"
						name="wp-submit"
						id="<?php esc_attr_e( $id_login ); ?>"
						class="button-primary"
						value="<?php esc_attr_e( $label_log_in ); ?>" />
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_login ); ?>" />
				</p>
				<?php echo apply_filters( 'login_form_bottom', '', $args ); ?>
			<?php if ( 'bottom' === $nav_pos ) : ?>
				<?php
					if ( !empty( $error_message ) ) {
						?>
						<div class="status"><?php echo $error_message; ?></div>
						<?php
					} else {
						?>
						<div class="status" style="display:none"></div>
						<?php
					}
				?>
			</div>
			<div class="nav">
				<p><a class="lost" href="#lostpassword"><?php _e( 'Lost your password?', 'membership2' ); ?></a></p>
			<?php endif; ?>
			</div>
		</form>
		<?php
		$html = ob_get_clean();
		/**
		 * Footer actions
		 */
		$this->run_action( 'wp_footer', 'login_footer' );
		$html = apply_filters( 'ms_compact_code', $html );
		return $html;
	}

	/**
	 * Returns the HTML partial of the lost-password form
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	private function lostpass_form() {
		$defaults = array(
			'label_lost_username' 	=> __( 'Username or E-mail', 'membership2' ),
			'label_lostpass' 		=> __( 'Reset Password', 'membership2' ),
			'id_lost_form' 			=> 'lostpasswordform',
			'id_lost_username' 		=> 'user_login',
			'id_lostpass' 			=> 'wp-submit',
			'value_username' 		=> '',
		);

		/**
		 * Filter the default login form output arguments.
		 *
		 * @since  1.0.0
		 *
		 * @see wp_login_form()
		 *
		 * @param array $defaults An array of default login form arguments.
		 */
		$args = wp_parse_args( $this->data, apply_filters( 'login_form_defaults', $defaults ) );

		extract( $args );

		$show_form = 'lost' === $form ? '' : 'display:none';
		$form_class = 'ms-form ms-form-lost';
		if ( $show_labels ) {
			$form_class .= ' ms-has-labels';
		} else {
			$form_class .= ' ms-no-labels';
		}

		ob_start();
		do_action( 'lost_password' );
		?>
		<form
			name="<?php esc_attr_e( $id_lost_form ); ?>"
			id="<?php esc_attr_e( $id_lost_form ); ?>"
			action="lostpassword"
			method="post"
			class="<?php esc_attr_e( $form_class ); ?>"
			style="<?php esc_attr_e( $show_form ); ?>">
			<div class="form">
				<?php wp_nonce_field( 'ms-ajax-lostpass', '_membership_auth_lostpass_nonce' ); ?>
				<?php echo apply_filters( 'lostpass_form_top', '', $args ); ?>
				<?php if ( 'top' === $nav_pos ) : ?>
					<div class="status" style="display:none"></div>
				<?php endif; ?>
				<p class="lostpassword-username ms-field">
					<?php if ( $show_labels ) : ?>
						<label for="<?php esc_attr_e( $id_lost_username ); ?>" >
						<?php echo esc_html( $label_lost_username ); ?>
						</label>
					<?php endif; ?>
					<input
						type="text"
						name="user_login"
						id="<?php esc_attr_e( $id_lost_username ); ?>"
						class="input focus"
						value="<?php esc_attr_e( $value_username ); ?>"
						size="20"
						placeholder="<?php echo esc_html( $label_lost_username ); ?>" />
				</p>
				<?php echo apply_filters( 'lostpass_form_middle', '', $args ); ?>
				<?php if ( 'top' === $nav_pos ) : ?>
					<div class="nav">
						<p><a class="login" href="#login"><?php _e( 'Log in', 'membership2' ); ?></a></p>
					</div>
				<?php endif; ?>
				<?php
				/**
				 * Fires inside the lostpassword <form> tags, before the hidden fields.
				 *
				 * @since  1.0.0
				 */
				do_action( 'lostpassword_form' ); ?>
				<p class="submit">
					<input
						type="submit"
						name="wp-submit"
						id="<?php esc_attr_e( $id_lostpass ); ?>"
						class="button-primary"
						value="<?php esc_attr_e( $label_lostpass ); ?>" />
				</p>
				<?php echo apply_filters( 'lostpass_form_bottom', '', $args ); ?>
			<?php if ( 'bottom' === $nav_pos ) : ?>
				<div class="status" style="display:none"></div>
			</div>
			<div class="nav">
				<p><a class="login" href="#login"><?php _e( 'Log in', 'membership2' ); ?></a></p>
			<?php endif; ?>
			</div>
		</form>
		<?php
		$html = ob_get_clean();
		$html = apply_filters( 'ms_compact_code', $html );

		return $html;
	}

	/**
	 * Returns HTML partial that contains the logout form
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	private function logout_form() {
		if ( ! MS_Model_Member::is_logged_in() ) { return ''; }
		$member = MS_Model_Member::get_current_member();

		extract( $this->data );

		if ( empty( $redirect_logout ) ) {
			$redirect_logout = MS_Helper_Utility::home_url( '/' );
		}

		$yourname = sprintf(
			__( 'You are logged in as %s.', 'membership2' ),
			ucfirst( $member->name )
		);

		$yourname = apply_filters(
			'ms_shortcode_logout_message',
			$yourname,
			$member
		);

		$logout_text = apply_filters(
			'ms_shortcode_logout_link_text',
			__( 'Logout', 'membership2' ),
			$member
		);

		$redirect_logout = apply_filters(
			'ms_shortcode_logout_redirect',
			$redirect_logout,
			$member
		);

		$html = sprintf(
			'%1$s <a class="login_button" href="%2$s">%3$s</a>',
			$yourname,
			wp_logout_url( $redirect_logout ),
			$logout_text
		);

		if ( ! empty( $holder ) ) {
			$html = sprintf(
				'<%1$s class="%2$s">%3$s</%1$s>',
				esc_attr( $holder ),
				esc_attr( $holderclass ),
				$html
			);
		}

		return $html;
	}

	/**
	 * Returns HTML partial that contains password-reset form.
	 * Based on WordPress core code from wp-login.php
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	private function reset_form() {
		static $Reset_Result = null;

		if ( null === $Reset_Result ) {
			mslib3()->array->equip_get( 'login', 'key' );
			mslib3()->array->equip_post( 'pass1', 'pass2' );
			$rp_login = wp_unslash( $_GET['login'] );
			$rp_key = wp_unslash( $_GET['key'] );
			$err_msg = new WP_Error();
			$fatal_error = false;

			mslib3()->array->strip_slashes( $_POST, 'pass1', 'pass2' );
			$pass1 = $_POST['pass1'];
			$pass2 = $_POST['pass2'];

			// Get the user object and validate the key.
			if ( $rp_login && $rp_key ) {
				$user = check_password_reset_key( $rp_key, $rp_login );
			} else {
				$user = false;
			}

			if ( ! $user || is_wp_error( $user ) ) {
				// If the user was not found then show an error message.
				if ( $user && 'expired_key' == $user->get_error_code() ) {
					$fatal_error = true;
					$err_msg->add(
						'password_expired_key',
						__( 'Sorry, this reset-key is not valid anymore. Please request a new reset email and try again.', 'membership2' )
					);
				} else {
					$fatal_error = true;
					$err_msg->add(
						'password_invalid_key',
						__( 'Sorry, we did not find a valid reset-key. Please request a new reset email and try again.', 'membership2' )
					);
				}
			} else {
				// If the user provided a new password, then check it now.
				if ( $pass1 && $pass1 != $pass2 ) {
					$pass1 = false;
					$err_msg->add(
						'password_reset_mismatch',
						__( 'The passwords do not match, try again.', 'membership2' )
					);
				}
			}

			if ( $fatal_error && count( $err_msg->errors ) ) {
				$url = esc_url_raw(
					add_query_arg(
						array( 'show' => 'lostpass' ),
						remove_query_arg( array( 'action', 'key', 'login' ) )
					)
				);

				$url = apply_filters(
					'ms_sc_login_pw_reset_url',
					$url
				);

				$Reset_Result = sprintf(
					'[ms-note type="warning"]%s[/ms-note]<a href="%s">%s</a>',
					$err_msg->get_error_message(),
					$url,
					__( 'Request a new password-reset key', 'membership2' )
				);
			} elseif ( $pass1 ) {
				// This action is documented in wp-login.php
				do_action( 'validate_password_reset', $err_msg, $user );

				reset_password( $user, $_POST['pass1'] );

				// All done! Show success message and link to login form
				$url = esc_url_raw(
					remove_query_arg( array( 'action', 'key', 'login' ) )
				);

				$url = apply_filters(
					'ms_sc_login_new_login_url',
					$url
				);

				$Reset_Result = sprintf(
					'[ms-note type="info"]%s[/ms-note]<a href="%s">%s</a>',
					__( 'Your Password has been reset.', 'membership2' ),
					$url,
					__( 'Login with your new password', 'membership2' )
				);
			} else {
				// This action is documented in wp-login.php
				do_action( 'validate_password_reset', $err_msg, $user );

				wp_enqueue_script( 'utils' );
				wp_enqueue_script( 'user-profile' );

				ob_start();
				if ( count( $err_msg->errors ) ) {
					printf(
						'[ms-note type="warning"]%s[/ms-note]',
						implode( '<br>', $err_msg->get_error_messages() )
					);
				}
				?>
				<form name="resetpassform" id="resetpassform"
					action="" method="post" autocomplete="off" class="ms-form">
					<input type="hidden" id="user_login"
						value="<?php esc_attr_e( $rp_login ); ?>" autocomplete="off"/>

					<p class="user-pass1-wrap">
						<label for="pass1"><?php _e( 'New password' ) ?></label><br />
						<div class="wp-pwd">
							<span class="password-input-wrapper">
								<input type="password" data-reveal="1" data-pw="<?php esc_attr_e( wp_generate_password( 16 ) ); ?>" name="pass1" id="pass1" class="input" size="20" value="" autocomplete="off" aria-describedby="pass-strength-result" />
							</span>
							<div id="pass-strength-result" class="hide-if-no-js" aria-live="polite"><?php _e( 'Strength indicator' ); ?></div>
						</div>
					</p>
					<p class="user-pass2-wrap">
						<label for="pass2"><?php _e( 'Confirm new password' ) ?></label><br />
						<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" />
					</p>

					<p class="description indicator-hint"><?php echo wp_get_password_hint(); ?></p>

					<br class="clear"/>

					<?php
					// This action is documented in wp-login.php
					do_action( 'resetpass_form', $user );
					?>
					<p class="submit">
						<input type="hidden" name="rp_key" value="<?php esc_attr_e( $rp_key ); ?>" />

						<button type="submit" name="wp-submit" id="wp-submit"
						class="button button-primary button-large">
						<?php _e( 'Reset Password', 'membership2' ); ?>
						</button>
					</p>
				</form>
				<?php
				$html = ob_get_clean();
				$Reset_Result = apply_filters( 'ms_compact_code', $html );
			}

			$Reset_Result = do_shortcode( $Reset_Result );
		}

		return $Reset_Result;
	}

	public function enqueue_scripts() {
		// Load the ajax script that handles the Ajax login functions.
		wp_enqueue_script( 'ms-ajax-login' );

		mslib3()->ui->data(
			'ms_ajax_login',
			array(
				'loginmessage' 		=> __( 'Please log in to access this page.', 'membership2' ),
				'resetmessage' 		=> __( 'Please enter your details to reset your password', 'membership2' ),
				'ajaxurl' 			=> admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
				'loadingmessage' 	=> __( 'Please wait...', 'membership2' ),
				'errormessage' 		=> __( 'Request failed, please try again.', 'membership2' ),
			)
		);
	}

	/**
	 * Login footer actions
	 *
	 * @since 1.1.3
	 */
	public function login_footer() {
		/**
		 * Fire the login-footer action, which is usually done in the page footer
		 * of the wp-login.php page. This hook is used by other plugins to include
		 * custom javascript or CSS on the login page.
		 *
		 * We need it specifically for our Domain Mapping plugin.
		 *
		 * @since  1.0.3.2
		 */
		do_action( 'login_footer' );
	}
}