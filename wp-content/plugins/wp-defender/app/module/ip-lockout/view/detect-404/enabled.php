<div class="sui-box">
    <form method="post" id="settings-frm" class="ip-frm">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
				<?php _e( "404 Detection", wp_defender()->domain ) ?>
            </h3>
        </div>
        <div class="sui-box-body">
            <p>
                <?php
                _e("With 404 detection enabled, Defender will keep an eye out for IP addresses that repeatedly request pages on your website that don’t exist and then temporarily block them from accessing your site.",wp_defender()->domain)
                ?>
            </p>
			<?php if ( ( $count = ( \WP_Defender\Module\IP_Lockout\Component\Login_Protection_Api::get404Lockouts( strtotime( '-24 hours', current_time( 'timestamp' ) ) ) ) ) > 0 ): ?>
                <div class="sui-notice sui-notice-error">
                    <p>
						<?php echo sprintf( __( "There have been %d lockouts in the last 24 hours. <a href=\"%s\"><strong>View log</strong></a>.", wp_defender()->domain ), $count, network_admin_url( 'admin.php?page=wdf-ip-lockout&view=logs' ) ) ?>
                    </p>
                </div>
			<?php else: ?>
                <div class="sui-notice sui-notice-info">
                    <p>
						<?php esc_html_e( "404 detection is enabled. There are no lockouts logged yet.", wp_defender()->domain ) ?>
                    </p>
                </div>
			<?php endif; ?>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Lockout threshold", wp_defender()->domain ) ?></span>
                    <span class="sui-description">
                        <?php esc_html_e( "Specify how many 404 errors within a specific time period will trigger a lockout.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <input size="8" value="<?php echo $settings->detect_404_threshold ?>" type="text"
                               class="sui-form-control sui-input-sm sui-field-has-suffix"
                               id="detect_404_threshold"
                               name="detect_404_threshold"/>
                        <span class="sui-field-suffix sui-field-prefix"><?php esc_html_e( "404 errors within", wp_defender()->domain ) ?></span>
                        <input size="8" value="<?php echo $settings->detect_404_timeframe ?>"
                               id="detect_404_timeframe"
                               name="detect_404_timeframe" type="text"
                               class="sui-form-control sui-input-sm sui-field-has-suffix">
                        <span class="sui-field-suffix"><?php esc_html_e( "seconds", wp_defender()->domain ) ?></span>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Lockout time", wp_defender()->domain ) ?></span>
                    <span class="sui-description"><?php esc_html_e( "Choose how long you’d like to ban the locked out user for.", wp_defender()->domain ) ?></span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-side-tabs sui-tabs">
                        <div data-tabs>
                            <div rel="input_value" data-target="detect_404_lockout_ban" data-value="0"
                                 class="<?php echo $settings->detect_404_lockout_ban == 0 ? 'active' : null ?>"><?php _e( "Timeframe", wp_defender()->domain ) ?></div>
                            <div rel="input_value" data-target="detect_404_lockout_ban" data-value="1"
                                 class="<?php echo $settings->detect_404_lockout_ban == 1 ? 'active' : null ?>"><?php _e( "Permanent", wp_defender()->domain ) ?></div>
                        </div>
                        <div data-panes>
                            <div class="sui-tab-boxed <?php echo $settings->detect_404_lockout_ban == 0 ? 'active' : null ?>">
                                <div class="sui-row">
                                    <div class="sui-col-md-3">
                                        <input value="<?php echo $settings->detect_404_lockout_duration ?>" size="8"
                                               name="detect_404_lockout_duration"
                                               id="detect_404_lockout_duration" type="text"
                                               class="sui-form-control"/>
                                    </div>
                                    <div class="sui-col-md-3">
                                        <select name="detect_404_lockout_duration_unit">
                                            <option <?php echo selected( 'seconds', $settings->detect_404_lockout_duration_unit ) ?>
                                                    value="seconds"><?php _e( "Seconds", wp_defender()->domain ) ?></option>
                                            <option <?php echo selected( 'minutes', $settings->detect_404_lockout_duration_unit ) ?>
                                                    value="minutes"><?php _e( "Minutes", wp_defender()->domain ) ?></option>
                                            <option <?php echo selected( 'hours', $settings->detect_404_lockout_duration_unit ) ?>
                                                    value="hours"><?php _e( "Hours", wp_defender()->domain ) ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="detect_404_lockout_ban" value="<?php echo $settings->detect_404_lockout_ban ?>"/>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Lockout message", wp_defender()->domain ) ?></span>
                    <span class="sui-description"><?php esc_html_e( "Customize the message locked out users will see.", wp_defender()->domain ) ?></span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                    <textarea name="detect_404_lockout_message" class="sui-form-control"
                              id="detect_404_lockout_message"><?php echo $settings->detect_404_lockout_message ?></textarea>
                        <span class="sui-description">
                        <?php echo sprintf( __( "This message will be displayed across your website during the lockout period. See a quick preview <a href=\"%s\">here</a>.", wp_defender()->domain ), add_query_arg( array(
	                        'def-lockout-demo' => 1,
	                        'type'             => '404'
                        ), network_site_url() ) ) ?>
                    </span>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php esc_html_e( "Whitelist", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                    <?php esc_html_e( "If you know a common file on your website is missing, you can record it here so it doesn't count towards a lockout record.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                    <textarea class="sui-form-control"
                              id="detect_404_whitelist" name="detect_404_whitelist"
                              rows="8"><?php echo $settings->detect_404_whitelist ?></textarea>
                        <span class="sui-description">
                            <?php esc_html_e( "You must list the full path beginning with a /.", wp_defender()->domain ) ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php esc_html_e( "Ignore file types", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                    <?php esc_html_e( "Choose which types of files you want to log errors for but not trigger a lockout.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                    <textarea class="sui-form-control"
                              id="detect_404_ignored_filetypes" name="detect_404_ignored_filetypes"
                              rows="8"><?php echo $settings->detect_404_ignored_filetypes ?></textarea>
                        <span class="sui-description">
                            <?php esc_html_e( "Defender will log the 404 error, but won’t lockout the user for these filetypes.", wp_defender()->domain ) ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php esc_html_e( "Exclusions", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                    <?php esc_html_e( "By default, Defender will monitor all interactions with your website but you can choose to disable 404 detection for specific areas of your site.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <input type="hidden" name="detect_404_logged" value="0"/>
                        <label class="sui-toggle">
                            <input id="detect_404_logged" <?php checked( 1, $settings->detect_404_logged ) ?>
                                   type="checkbox"
                                   name="detect_404_logged" value="1">
                            <span class="sui-toggle-slider"></span>
                        </label>
                        <label for="detect_404_logged" class="sui-toggle-label">
		                    <?php _e( "Monitor 404s from logged in users", wp_defender()->domain ) ?>
                        </label>
                    </div>
                </div>
				<?php wp_nonce_field( 'saveLockoutSettings' ) ?>
                <input type="hidden" name="action" value="saveLockoutSettings"/>
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
                    <button type="button" class="sui-button sui-button-ghost deactivate-404-lockout">
				        <?php _e( "Deactivate", wp_defender()->domain ) ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="sui-box-footer">
            <div class="sui-actions-right">
                <button type="submit" class="sui-button sui-button-blue">
                    <i class="sui-icon-save" aria-hidden="true"></i>
					<?php _e( "Save Changes", wp_defender()->domain ) ?></button>
            </div>
        </div>
    </form>
</div>
