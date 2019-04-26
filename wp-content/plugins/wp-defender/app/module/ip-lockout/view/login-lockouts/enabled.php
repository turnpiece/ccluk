<div class="sui-box">
    <form method="post" id="settings-frm" class="ip-frm">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
				<?php _e( "Login Protection", wp_defender()->domain ) ?>
            </h3>
        </div>
        <div class="sui-box-body">
            <p>
				<?php _e( "Put a stop to hackers trying to randomly guess your login credentials. Defender will lock out users after a set number of failed login attempts.", wp_defender()->domain ) ?>
            </p>
			<?php if ( ( $count = ( \WP_Defender\Module\IP_Lockout\Component\Login_Protection_Api::getLoginLockouts( strtotime( '-24 hours', current_time( 'timestamp' ) ) ) ) ) > 0 ): ?>
                <div class="sui-notice sui-notice-error">
                    <p>
						<?php echo sprintf( __( "There have been %d lockouts in the last 24 hours. <a href=\"%s\"><strong>View log</strong></a>.", wp_defender()->domain ), $count, network_admin_url( 'admin.php?page=wdf-ip-lockout&view=logs' ) ) ?>
                    </p>
                </div>
			<?php else: ?>
                <div class="sui-notice sui-notice-info">
                    <p>
						<?php esc_html_e( "Login protection is enabled. There are no lockouts logged yet.", wp_defender()->domain ) ?>
                    </p>
                </div>
			<?php endif; ?>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Threshold", wp_defender()->domain ) ?></span>
                    <span class="sui-description"><?php esc_html_e( "Specify how many failed login attempts within a specific time period will trigger a lockout.", wp_defender()->domain ) ?></span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <div class="sui-row">
                            <div class="sui-col-md-2">
                                <label class="sui-label"><?php _e( "Failed logins", wp_defender()->domain ) ?></label>
                                <input size="8" value="<?php echo $settings->login_protection_login_attempt ?>"
                                       type="text"
                                       class="sui-form-control sui-input-sm sui-field-has-suffix"
                                       id="login_protection_login_attempt"
                                       name="login_protection_login_attempt"/>
                            </div>
                            <div class="sui-col-md-3">
                                <label class="sui-label">
									<?php _e( "Timeframe", wp_defender()->domain ) ?>
                                </label>
                                <input size="8" value="<?php echo $settings->login_protection_lockout_timeframe ?>"
                                       id="login_lockout_timeframe"
                                       name="login_protection_lockout_timeframe" type="text"
                                       class="sui-form-control sui-input-sm sui-field-has-suffix">
                                <span class="sui-field-suffix"><?php esc_html_e( "seconds", wp_defender()->domain ) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Duration", wp_defender()->domain ) ?></span>
                    <span class="sui-description"><?php esc_html_e( "Choose how long you’d like to ban the locked out user for.", wp_defender()->domain ) ?></span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-side-tabs sui-tabs">
                        <div data-tabs>
                            <div rel="input_value" data-target="login_protection_lockout_ban" data-value="0"
                                 class="<?php echo $settings->login_protection_lockout_ban == 0 ? 'active' : null ?>"><?php _e( "Timeframe", wp_defender()->domain ) ?></div>
                            <div rel="input_value" data-target="login_protection_lockout_ban" data-value="1"
                                 class="<?php echo $settings->login_protection_lockout_ban == 1 ? 'active' : null ?>"><?php _e( "Permanent", wp_defender()->domain ) ?></div>
                        </div>
                        <div data-panes>
                            <div class="sui-tab-boxed <?php echo $settings->login_protection_lockout_ban == 0 ? 'active' : null ?>">
                                <p class="sui-description">
									<?php _e( "Choose a timeframe to temporarily lock out blocked the IP for.", wp_defender()->domain ) ?>
                                </p>
                                <div class="sui-row">
                                    <div class="sui-col-md-3">
                                        <input value="<?php echo $settings->login_protection_lockout_duration ?>"
                                               size="4"
                                               name="login_protection_lockout_duration"
                                               id="login_protection_lockout_duration" type="text"
                                               class="sui-form-control"/>
                                    </div>
                                    <div class="sui-col-md-4">
                                        <select name="login_protection_lockout_duration_unit">
                                            <option <?php echo selected( 'seconds', $settings->login_protection_lockout_duration_unit ) ?>
                                                    value="seconds"><?php _e( "Seconds", wp_defender()->domain ) ?></option>
                                            <option <?php echo selected( 'minutes', $settings->login_protection_lockout_duration_unit ) ?>
                                                    value="minutes"><?php _e( "Minutes", wp_defender()->domain ) ?></option>
                                            <option <?php echo selected( 'hours', $settings->login_protection_lockout_duration_unit ) ?>
                                                    value="hours"><?php _e( "Hours", wp_defender()->domain ) ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="login_protection_lockout_ban"
                               value="<?php echo $settings->login_protection_lockout_ban ?>"/>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Message", wp_defender()->domain ) ?></span>
                    <span class="sui-description"><?php esc_html_e( "Customize the message locked out users will see.", wp_defender()->domain ) ?></span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <label class="sui-label"><?php _e( "Custom message", wp_defender()->domain ) ?></label>
                        <textarea name="login_protection_lockout_message" class="sui-form-control"
                                  id="login_protection_lockout_message"><?php echo $settings->login_protection_lockout_message ?></textarea>
                        <span class="sui-description">
                        <?php echo sprintf( __( "This message will be displayed across your website during the lockout period. See a quick preview <a href=\"%s\">here</a>.", wp_defender()->domain ), add_query_arg( array(
	                        'def-lockout-demo' => 1,
	                        'type'             => 'login'
                        ), network_site_url() ) ) ?>
                    </span>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Banned usernames", wp_defender()->domain ) ?></span>
                    <span class="sui-description">
                    <?php esc_html_e( "It is highly recommended you avoid using the default username ‘admin’. Use this tool to automatically lockout and ban users who try to login with common usernames.", wp_defender()->domain ) ?>
                </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <label class="sui-label"><?php _e( "Banned usernames", wp_defender()->domain ) ?></label>
                        <textarea class="sui-form-control"
                                  placeholder="<?php esc_attr_e( "Type usernames, one per line", wp_defender()->domain ) ?>"
                                  id="username_blacklist" name="username_blacklist"
                                  rows="8"><?php echo $settings->username_blacklist ?></textarea>
                        <span class="sui-description">
                        <?php
                        $host = parse_url( get_site_url(), PHP_URL_HOST );
                        $host = str_replace( 'www.', '', $host );
                        $host = explode( '.', $host );
                        if ( is_array( $host ) ) {
	                        $host = array_shift( $host );
                        } else {
	                        $host = null;
                        }
                        printf( __( "We recommend adding the usernames <strong>admin</strong>, <strong>administrator</strong> and your hostname <strong>%s</strong> as these are common for bots to try logging in with. One username per line", wp_defender()->domain ), $host ) ?>
                    </span>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Deactivate", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                       <?php esc_html_e( "If you no longer want to use this feature you can turn it off at any time.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <button type="button" class="sui-button sui-button-ghost deactivate-login-lockout">
						<?php _e( "Deactivate", wp_defender()->domain ) ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="sui-box-footer">
			<?php wp_nonce_field( 'saveLockoutSettings' ) ?>
            <input type="hidden" name="action" value="saveLockoutSettings"/>
            <div class="sui-actions-right">
                <button type="submit" class="sui-button sui-button-blue">
                    <i class="sui-icon-save" aria-hidden="true"></i>
					<?php _e( "Save Changes", wp_defender()->domain ) ?></button>
            </div>
        </div>
    </form>
</div>