<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Two-Factor Authentication", wp_defender()->domain ) ?>
        </h3>
    </div>
    <form method="post" id="advanced-settings-frm" class="advanced-settings-frm">
        <div class="sui-box-body sui-upsell-items">
            <div class="padding-30 no-padding-bottom">
                <p>
					<?php _e( "Configure your two-factor authentication settings. Our recommendations are enabled by default.", wp_defender()->domain ) ?>
                </p>
				<?php
				$enabledRoles = $settings->userRoles;
				$allRoles     = get_editable_roles();
				?>
				<?php if ( isset( wp_defender()->global['compatibility'] ) ): ?>
                    <div class="sui-notice sui-notice-error">
                        <p>
							<?php echo implode( '<br/>', array_unique( wp_defender()->global['compatibility'] ) ); ?>
                        </p>
                    </div>
				<?php endif; ?>
				<?php
				if ( count( $enabledRoles ) ):
					?>
                    <div class="sui-notice sui-notice-info no-margin-bottom">
                        <p>
							<?php
							printf( __( "<strong>Two-factor authentication is now active.</strong> User roles with this feature enabled must visit their <a href='%s'>Profile page</a> to complete setup and sync their account with the Authenticator app.", wp_defender()->domain ),
								admin_url( 'profile.php' ) );
							?>
                        </p>
                    </div>
				<?php else: ?>
                    <div class="sui-notice sui-notice-warning no-margin-bottom">
                        <p>
							<?php
							_e( "<strong>Two-factor authentication is currently inactive.</strong> Configure and save your settings to complete setup.", wp_defender()->domain )
							?>
                        </p>
                    </div>
				<?php endif; ?>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php esc_html_e( "User Roles", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php esc_html_e( "Choose the user roles you want to enable two-factor authentication for. Users with those roles will then be required to use the Google Authenticator app to login.", wp_defender()->domain ) ?>
                    </span>
                </div>

                <div class="sui-box-settings-col-2">
                    <div class="sui-field-list">
                        <div class="sui-field-list-header">
                            <h3 class="sui-field-list-title"><?php _e( "User role", wp_defender()->domain ) ?></h3>
                        </div>
                        <div class="sui-field-list-body">
							<?php
							foreach ( $allRoles as $role => $detail ):
								?>
                                <div class="sui-field-list-item">
                                    <label class="sui-field-list-item-label"
                                           for="toggle_<?php echo esc_attr( $role ) ?>_role">
										<?php echo $detail['name'] ?>
                                    </label>
                                    <label class="sui-toggle">
                                        <input type="checkbox" <?php echo in_array( $role, $enabledRoles ) ? 'checked="checked"' : null ?>
                                               name="userRoles[]" value="<?php echo esc_attr( $role ) ?>"
                                               id="toggle_<?php echo esc_attr( $role ) ?>_role"/>
                                        <span class="sui-toggle-slider"></span>
                                    </label>
                                </div>
							<?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Lost Phone", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php _e( "If a user is unable to access their phone, you can allow an option to send the one time password to their registered email.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <label class="sui-toggle">
                            <input type="hidden" name="lostPhone" value="0"/>
                            <input role="presentation" type="checkbox" name="lostPhone" class="toggle-checkbox"
                                   id="lostPhone" value="1"
								<?php checked( true, $settings->lostPhone ) ?>/>
                            <span class="sui-toggle-slider"></span>
                        </label>
                        <label for="lostPhone" class="sui-toggle-label">
							<?php _e( "Enable lost phone option", wp_defender()->domain ) ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Force Authentication", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php _e( "By default, two-factor authentication is optional for users. This setting forces users to activate two-factor.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <label class="sui-toggle">
                            <input type="hidden" name="forceAuth" value="0"/>
                            <input role="presentation" type="checkbox" name="forceAuth" class="toggle-checkbox"
                                   id="forceAuth" value="1"
								<?php checked( true, $settings->forceAuth ) ?>/>
                            <span class="sui-toggle-slider"></span>
                        </label>
                        <label for="forceAuth" class="sui-toggle-label">
							<?php _e( "Force users to log in with two-factor authentication", wp_defender()->domain ) ?>
                        </label>
                        <span class="sui-description sui-toggle-content">
                            <?php _e( "Note: Users will be forced to set up two-factor when they next login.", wp_defender()->domain ) ?>
                        </span>
                        <div id="forceAuthRoles" class="sui-border-frame sui-toggle-content"
                             aria-hidden="<?php echo ! $settings->forceAuth ?>">
                            <strong><?php _e( "User Roles", wp_defender()->domain ) ?></strong>
                            <ul>
								<?php
								$forceAuthRoles = $settings->forceAuthRoles;
								foreach ( $allRoles as $role => $detail ):
									?>
                                    <li>
                                        <label for="forceAuth<?php echo esc_attr( $role ) ?>" class="sui-checkbox">
                                            <input id="forceAuth<?php echo esc_attr( $role ) ?>" type="checkbox"
                                                   name="forceAuthRoles[]"
                                                   value="<?php echo esc_attr( $role ) ?>" <?php echo in_array( $role, $forceAuthRoles ) ? 'checked="checked"' : null ?> />
                                            <span aria-hidden="true"></span>
                                            <span><?php echo $detail['name'] ?></span>
                                        </label>
                                    </li>
								<?php endforeach; ?>
                            </ul>
                            <strong><?php _e( "Custom warning message", wp_defender()->domain ) ?></strong>
                            <textarea class="sui-form-control"
                                      name="forceAuthMess"><?php echo $settings->forceAuthMess ?></textarea>
                            <span class="sui-description">
                            <?php _e( "Note: This is shown in the users Profile area indicating they must use two-factor authentication.", wp_defender()->domain ) ?>
                        </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row sui-disabled">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Custom Graphic", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php _e( "By default, Defender’s icon appears above the login fields. You can upload your own branding, or turn this feature off.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <label class="sui-toggle">
                        <input role="presentation" type="checkbox" name="customGraphic" class="toggle-checkbox"
                               id="customGraphic" value="0"/>
                        <span class="sui-toggle-slider"></span>
                    </label>
                    <label for="customGraphic" class="sui-toggle-label">
						<?php _e( "Enable custom graphics above login fields", wp_defender()->domain ) ?>
                    </label>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Emails", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php _e( 'Customize the default copy for emails the two-factor feature sends to users.', wp_defender()->domain ); ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-field-list">
                        <div class="sui-field-list-header">
                            <h3 class="sui-field-list-title"><?php _e( "Email", wp_defender()->domain ) ?></h3>
                        </div>
                        <div class="sui-field-list-body">
                            <div class="sui-field-list-item">
                                <label class="sui-field-list-item-label" for="demo-table-2-toggle-5">
									<?php _e( "Lost phone one time password", wp_defender()->domain ) ?>
                                </label>
                                <button type="button" class="sui-button-icon"
                                        data-a11y-dialog-show="edit-one-time-password-email">
                                    <i class="sui-icon-pencil" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "App Download", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php _e( 'Need the app? Here’s links to the official Google Authenticator apps.', wp_defender()->domain ); ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <a href="https://itunes.apple.com/vn/app/google-authenticator/id388497605?mt=8">
                        <img src="<?php echo wp_defender()->getPluginUrl() . 'assets/img/ios-download.svg' ?>"/>
                    </a>
                    <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2">
                        <img src="<?php echo wp_defender()->getPluginUrl() . 'assets/img/android-download.svg' ?>"/>
                    </a>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Active Users", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php _e( "Here’s a quick link to see which of your users have enabled two-factor verification.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
					<?php printf( __( "<a href=\"%s\">View users</a> who have enabled this feature.", wp_defender()->domain ), network_admin_url( 'users.php' ) ) ?>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Deactivate", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php _e( "Disable two-factor authentication on your website.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <button type="button" class="sui-button sui-button-ghost deactivate-2factor">
						<?php _e( "Deactivate", wp_defender()->domain ) ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="sui-box-footer">
            <input type="hidden" name="action" value="saveAdvancedSettings"/>
			<?php wp_nonce_field( 'saveAdvancedSettings' ) ?>
            <div class="sui-actions-right">
                <button type="submit" class="sui-button sui-button-blue">
                    <i class="sui-icon-save" aria-hidden="true"></i>
					<?php _e( "Save Changes", wp_defender()->domain ) ?></button>
            </div>
        </div>
    </form>
	<?php
	$view     = '2factor-otp-email-edit-from';
	$settings = array( 'settings' => $settings );
	$controller->renderPartial( $view, $settings );
	?>
</div>
