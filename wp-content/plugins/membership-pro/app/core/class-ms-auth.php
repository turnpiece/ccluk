<?php
/**
 * Membership Auth Class
 *
 * Handle the ajax login
 *
 * @since  1.1
 */
class MS_Auth {

    /**
     * Handle Ajax Login requests
	 *
	 * @since 1.1
     */
    public static function check_ms_ajax() {

        if ( isset( $_REQUEST['ms_ajax'] ) ) {
            if ( 1 == $_REQUEST['ms_ajax'] ) {
                add_action( 'wp_ajax_ms_login', 'ms_ajax_login' );
                add_action( 'wp_ajax_nopriv_ms_login', 'ms_ajax_login' );

                function ms_ajax_login() {
                    $resp = array();
                    check_ajax_referer( 'ms-ajax-login', '_membership_auth_nonce' );

                    if ( empty( $_POST['username'] ) && ! empty( $_POST['log'] ) ) {
                        $_POST['username'] = $_POST['log'];
                    }
                    if ( empty( $_POST['password'] ) && ! empty( $_POST['pwd'] ) ) {
                        $_POST['password'] = $_POST['pwd'];
                    }
                    if ( empty( $_POST['remember'] ) && ! empty( $_POST['rememberme'] ) ) {
                        $_POST['remember'] = $_POST['rememberme'];
                    }

                    // Nonce is checked, get the POST data and sign user on
                    $info = array(
                        'user_login' 	=> $_POST['username'],
                        'user_password' => $_POST['password'],
                        'remember' 		=> isset( $_POST['remember'] ),
					);

					$can_login = apply_filters( 'ms_auth_ajax_login_can_login', true, $info );

					if ( $can_login ) {

						$user_signon = wp_signon( $info, false );

						if ( is_wp_error( $user_signon ) ) {
							$resp['error'] 		= __( 'Wrong username or password', 'membership2' );
						} else {
							$settings = MS_Factory::load( 'MS_Model_Settings' );
							if ( $settings->force_registration_verification ) {
								$user_activation_status = get_user_meta( $user_signon->ID, '_ms_user_activation_status', true );
								$user_activation_status = empty( $user_activation_status ) ? 0 : $user_activation_status;
								$redirect_to 			= MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_ACCOUNT );
								$resp['redirect'] 		= $redirect_to;
							} else {
								$user_activation_status = 1;
								update_user_meta( $user_signon->ID, '_ms_user_activation_status', $user_activation_status ); //Setting disabled so update
							}
							if ( MS_Model_Member::is_admin_user( $user_signon->ID ) ) {
								//Admin always active
								$user_activation_status = 1;
								update_user_meta( $user_signon->ID, '_ms_user_activation_status', $user_activation_status );
							}

							if ( $user_activation_status != 1 ) {

								wp_destroy_current_session();
								wp_clear_auth_cookie();

								$resp['error'] 	= __( 'Account not verified. Please check your email for a verification link', 'membership2' );

							} else {

								$resp['loggedin'] 	= true;
								$resp['success'] 	= __( 'Logging in...', 'membership2' );

								/**
								* Allows a custom redirection after login.
								* Empty value will use the default redirect option of the login form.
								*/

								// TODO: These filters are never called!
								//       This code is too early to allow any other plugin to register a filter handler...
								$enforce = false;
								if ( isset( $_POST['redirect_to'] ) ) {
									$resp['redirect'] = apply_filters(
										'ms-ajax-login-redirect',
										$_POST['redirect_to'],
										$user_signon->ID
									);
								}

								/**
								 * Login filter for redirect
								 *
								 * @since 1.1.2
								 */
								$resp['redirect'] = apply_filters( 'ms_auth_ajax_login_redirect_url', $resp['redirect'], $user_signon->ID );

								/**
								 * After login success action
								 *
								 * @since 1.0.4
								 */
								do_action( 'ms_ajax_after_login_success', $user_signon );

								//checking domains
								if ( is_plugin_active_for_network( 'domain-mapping/domain-mapping.php' ) ) {
									$url1 = parse_url( home_url() );
									$url2 = parse_url( $resp['redirect'] );
									if ( strpos( $url2['host'], $url1['host'] ) === false ) {
										//add 'auth' param for set cookie when mapped domains
										$resp['redirect'] = add_query_arg( array('auth' => wp_generate_auth_cookie( $user_signon->ID, time() + MINUTE_IN_SECONDS ) ), $resp['redirect'] );
									}
								}
							}
						}
					} else {
						$resp['error'] 	= __( 'Wrong username or password', 'membership2' );
					}

                    echo json_encode( $resp );
                    exit();
                }
            }
        }
    }
}
?>