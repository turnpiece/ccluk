<div class="dev-box">
    <div class="box-title">
        <h3 class="def-issues-title">
			<?php _e( "Two-Factor Authentication", wp_defender()->domain ) ?>
        </h3>
    </div>
    <div class="box-content issues-box-content">
        <form method="post" id="advanced-settings-frm" class="advanced-settings-frm">
			<?php
			$class        = 'line';
			$enabledRoles = $settings->userRoles;

			?>
            <p class="<?php echo $class ?>"><?php _e( "Configure your two-factor authentication settings. Our recommendations are enabled by default.", wp_defender()->domain ) ?></p>
			<?php if ( isset( wp_defender()->global['compatibility'] ) ): ?>
                <div class="well well-error with-cap mline">
                    <i class="def-icon icon-warning icon-yellow "></i>
					<?php echo implode( '<br/>', array_unique( wp_defender()->global['compatibility'] ) ); ?>
                </div>
			<?php endif; ?>
			<?php
			if ( count( $enabledRoles ) ):
				?>
                <div class="well well-green with-cap">
                    <i class="def-icon icon-tick"></i>
					<?php
					printf( __( "<strong>Two-factor authentication is now active.</strong> User roles with this feature enabled must visit their <a href='%s'>Profile page</a> to complete setup and sync their account with the Authenticator app.", wp_defender()->domain ),
						admin_url( 'profile.php' ) );
					?>
                </div>
			<?php else: ?>
                <div class="well well-yellow with-cap">
                    <i class="def-icon icon-warning"></i>
					<?php
					_e( "<strong>Two-factor authentication is currently inactive.</strong> Configure and save your settings to complete setup.", wp_defender()->domain )
					?>
                </div>
			<?php endif; ?>
            <div class="columns">
                <div class="column is-one-third">
                    <label><?php _e( "User Roles", wp_defender()->domain ) ?></label>
                    <span class="sub">
                        <?php _e( "Choose the user roles you want to enable two-factor authentication for. Users with those roles will then be required to use the Google Authenticator app to login.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="column">
                    <ul class="dev-list marginless">
                        <li class="list-header">
                            <div>
                                <span class="list-label"><?php _e( "User role", wp_defender()->domain ) ?></span>
                            </div>
                        </li>
						<?php
						$enabledRoles = $settings->userRoles;
						$allRoles     = get_editable_roles();
						foreach ( $allRoles as $role => $detail ):
							?>
                            <li>
                                <div>
                                    <span class="list-label">
                                        <?php echo $detail['name'] ?>
                                    </span>
                                    <div class="list-detail">
                                    <span class="toggle">
                                        <input type="checkbox" <?php echo in_array( $role, $enabledRoles ) ? 'checked="checked"' : null ?>
                                               name="userRoles[]"
                                               value="<?php echo esc_attr( $role ) ?>"
                                               class="toggle-checkbox"
                                               id="toggle_<?php echo esc_attr( $role ) ?>_role"/>
                                        <label class="toggle-label"
                                               for="toggle_<?php echo esc_attr( $role ) ?>_role"></label>
                                    </span>
                                    </div>
                                </div>
                            </li>
						<?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="columns">
                <div class="column is-one-third">
                    <label><?php _e( "Lost Phone", wp_defender()->domain ) ?></label>
                    <span class="sub">
                        <?php _e( "If a user is unable to access their phone, you can allow an option to send the one time password to their registered email.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="column">
                    <span class="toggle">
                        <input type="hidden" name="lostPhone" value="0"/>
                        <input type="checkbox" <?php checked( 1, $settings->lostPhone ) ?> name="lostPhone" value="1"
                               class="toggle-checkbox" id="toggle_lost_phone"/>
                        <label class="toggle-label" for="toggle_lost_phone"></label>
                    </span>&nbsp;
                    <span><?php _e( "Enable lost phone option", wp_defender()->domain ) ?></span>
                </div>
            </div>
            <div class="columns">
                <div class="column is-one-third">
                    <label><?php _e( "Force Authentication", wp_defender()->domain ) ?></label>
                    <span class="sub">
                        <?php _e( "By default, two-factor authentication is optional for users. This setting forces users to activate two-factor.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="column">
                    <span class="toggle">
                        <input type="hidden" name="forceAuth" value="0"/>
                        <input type="checkbox" <?php checked( 1, $settings->forceAuth ) ?> name="forceAuth" value="1"
                               class="toggle-checkbox" id="toggle_force_auth"/>
                        <label class="toggle-label" for="toggle_force_auth"></label>
                    </span>&nbsp;
                    <span><?php _e( "Force users to log in with two-factor authentication", wp_defender()->domain ) ?></span>
                    <span class="form-help"><?php _e( "Note: Users will be forced to set up two-factor when they next login.", wp_defender()->domain ) ?></span>
                    <div class="well well-white <?php echo $settings->forceAuth == false ? 'is-hidden' : null ?>">
                        <p>
                            <span class="form-help"><strong><?php _e( "Custom warning message", wp_defender()->domain ) ?></strong></span>
                        </p>
                        <textarea name="forceAuthMess"><?php echo $settings->forceAuthMess ?></textarea>
                        <p>
                            <span class="form-help"><?php _e( "Note: This is shown in the users Profile area indicating they must use two-factor authentication.", wp_defender()->domain ) ?></span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="columns">
                <div class="column is-one-third">
                    <label><?php _e( "Custom Graphic", wp_defender()->domain ) ?></label>
                    <span class="sub">
                        <?php _e( "By default, Defender’s icon appears above the login fields. You can upload your own branding, or turn this feature off.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="column">
                    <span class="toggle">
                        <input type="hidden" name="customGraphic" value="0"/>
                        <input type="checkbox" <?php checked( 1, $settings->customGraphic ) ?> name="customGraphic"
                               value="1"
                               class="toggle-checkbox" id="customGraphic"/>
                        <label class="toggle-label" for="customGraphic"></label>
                    </span>&nbsp;
                    <span><?php _e( "Enable custom graphics above login fields", wp_defender()->domain ) ?></span>
                    <span class="form-help"></span>
                    <div class="well well-white <?php echo $settings->customGraphic == false ? 'is-hidden' : null ?>">
                        <p>
                                <span class="form-help"><strong><?php _e( "Custom Graphic", wp_defender()->domain ) ?></strong>
                                - <?php _e( "For best results use a 168x168px JPG or PNG.", wp_defender()->domain ) ?></span>
                        </p>
                        <input type="hidden" id="customGraphicURL" name="customGraphicURL"
                               value="<?php echo $settings->customGraphicURL ?>"/>
                        <button type="button" class="button button-light file-picker">
                            <i class="wdv-icon wdv-icon-fw wdv-icon-plus-sign"></i>
                        </button>
                        <img id="customGraphicIMG" height="40" src="<?php echo $settings->customGraphicURL ?>">
                    </div>
                </div>
            </div>
            <div class="columns">
                <div class="column is-one-third">
                    <label><?php _e( "Emails", wp_defender()->domain ) ?></label>
                    <span class="sub">
                        <?php _e( "Customize the default copy for emails the two-factor feature sends to users.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="column">
                  <div class="well well-white">
                    <div class="box-title">
                        <strong><?php _e( 'Email', wp_defender()->domain );?></strong>
                    </div>
                    <div class="line"><?php _e( 'Lost phone one time password', wp_defender()->domain );?></div>
                    <span class="pull-right"><span class="span-icon icon-edit change-one-time-pass-email" tooltip="Edit"></span></span>
                  </div>
                </div>
            </div>
            <div class="columns">
                <div class="column is-one-third">
                    <label><?php _e( "App Download", wp_defender()->domain ) ?></label>
                    <span class="sub">
                        <?php _e( "Need the app? Here’s links to the official Google Authenticator apps.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="column">
                    <a href="https://itunes.apple.com/vn/app/google-authenticator/id388497605?mt=8">
                        <img src="<?php echo wp_defender()->getPluginUrl() . 'assets/img/ios-download.svg' ?>"/>
                    </a>
                    <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2">
                        <img src="<?php echo wp_defender()->getPluginUrl() . 'assets/img/android-download.svg' ?>"/>
                    </a>
                </div>
            </div>
            <div class="columns">
                <div class="column is-one-third">
                    <label><?php _e( "Active Users", wp_defender()->domain ) ?></label>
                    <span class="sub">
                        <?php _e( "Here’s a quick link to see which of your users have enabled two-factor verification.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="column">
					<?php printf( __( "<a href=\"%s\">View users</a> who have enabled this feature.", wp_defender()->domain ), network_admin_url( 'users.php' ) ) ?>
                </div>
            </div>
            <div class="columns mline">
                <div class="column is-one-third">
                    <label><?php _e( "Deactivate", wp_defender()->domain ) ?></label>
                    <span class="sub">
                        <?php _e( "Disable two-factor authentication on your website.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="column">
                    <button type="button" class="button button-secondary deactivate-2factor">
						<?php _e( "Deactivate", wp_defender()->domain ) ?>
                    </button>
                </div>
            </div>
            <div class="clear line"></div>
            <input type="hidden" name="action" value="saveAdvancedSettings"/>
			<?php wp_nonce_field( 'saveAdvancedSettings' ) ?>
            <button type="submit" class="button button-primary float-r">
				<?php _e( "SAVE SETTINGS", wp_defender()->domain ) ?>
            </button>
            <div class="clear"></div>
        </form>
    </div>
</div>
<?php
$view     = '2factor-otp-email-edit-from';
$settings = array( 'settings' => $settings );
$controller->renderPartial( $view, $settings );
?>