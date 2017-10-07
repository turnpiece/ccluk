<div class="dev-box">
    <div class="box-title">
        <h3 class="def-issues-title">
			<?php _e( "2 Factor Authentication", wp_defender()->domain ) ?>
        </h3>
    </div>
    <div class="box-content issues-box-content">
        <form method="post" id="advanced-settings-frm" class="advanced-settings-frm">
            <p class="<?php echo isset( wp_defender()->global['compatibility'] ) ? 'line' : null ?>"><?php _e( "Configure your 2 factor authentication settings, our recommendations are on by default.", wp_defender()->domain ) ?></p>
			<?php if ( isset( wp_defender()->global['compatibility'] ) ): ?>
                <div class="well well-error with-cap">
                    <i class="def-icon icon-warning icon-yellow "></i>
					<?php echo implode( '<br/>', wp_defender()->global['compatibility'] ); ?>
                </div>
			<?php endif; ?>
            <div class="columns">
                <div class="column is-one-third">
                    <label><?php _e( "User Roles", wp_defender()->domain ) ?></label>
                    <span class="sub">
                        <?php _e( "Choose what user roles you want to enable 2 factor authentication for. They must then use the Google Authenticator app to login.", wp_defender()->domain ) ?>
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
                        <input type="checkbox" checked="checked" name="lostPhone" value="1"
                               class="toggle-checkbox" id="toggle_lost_phone"/>
                        <label class="toggle-label" for="toggle_lost_phone"></label>
                    </span>&nbsp;
                    <span><?php _e( "Enable lost phone option", wp_defender()->domain ) ?></span>
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
                        <?php _e( "Here’s a quick link to see which of your users have enabled 2 step verification.", wp_defender()->domain ) ?>
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
                        <?php _e( "Turn off the 2 factor authentication feature completely.", wp_defender()->domain ) ?>
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