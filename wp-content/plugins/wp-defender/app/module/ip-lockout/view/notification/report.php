<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php esc_html_e( "Reporting", wp_defender()->domain ) ?>
        </h3>
    </div>
    <form method="post" id="settings-frm" class="ip-frm">
        <div class="sui-box-body">
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php esc_html_e( "Lockouts Report", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php esc_html_e( "Configure Defender to automatically email you a lockout report for this website.", wp_defender()->domain ) ?>
                    </span>
                </div>

                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <label class="sui-toggle">
                            <input type="hidden" name="report" value="0"/>
                            <input role="presentation" type="checkbox" name="report"
                                   class="toggle-checkbox"
                                   id="report" value="1"
								<?php checked( true, $settings->report ) ?>/>
                            <span class="sui-toggle-slider"></span>
                        </label>
                        <label for="login_lockout_notification" class="sui-toggle-label">
							<?php esc_html_e( "Send regular email report", wp_defender()->domain ) ?>
                        </label>
                        <div class="sui-border-frame sui-toggle-content">
                            <strong>
								<?php _e( "Recipients", wp_defender()->domain ) ?>
                            </strong>
							<?php $email_search->renderInput() ?>
                            <div class="sui-form-field schedule-box">
                                <strong>
									<?php _e( "Schedule", wp_defender()->domain ) ?>
                                </strong>
                                <div class="sui-row">
                                    <div class="sui-col">
                                        <label class="sui-label">
											<?php _e( "Frequency", wp_defender()->domain ) ?>
                                        </label>
                                        <select name="report_frequency">
                                            <option <?php selected( '1', $settings->report_frequency ) ?>
                                                    value="1"><?php esc_html_e( "Daily", wp_defender()->domain ) ?></option>
                                            <option <?php selected( '7', $settings->report_frequency ) ?>
                                                    value="7"><?php esc_html_e( "Weekly", wp_defender()->domain ) ?></option>
                                            <option <?php selected( '30', $settings->report_frequency ) ?>
                                                    value="30"><?php esc_html_e( "Monthly", wp_defender()->domain ) ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="sui-row">
                                    <div class="sui-col days-container">
                                        <label><?php _e( "Day of the week", wp_defender()->domain ) ?></label>
                                        <select name="report_day">
											<?php foreach ( \WP_Defender\Behavior\Utils::instance()->getDaysOfWeek() as $day ): ?>
                                                <option <?php selected( $settings->report_day, strtolower( $day ) ) ?>
                                                        value="<?php echo strtolower( $day ) ?>"><?php echo $day ?></option>
											<?php endforeach;; ?>
                                        </select>
                                    </div>
                                    <div class="sui-col">
                                        <label><?php _e( "Time of day", wp_defender()->domain ) ?></label>
                                        <select name="report_time">
											<?php foreach ( \WP_Defender\Behavior\Utils::instance()->getTimes() as $timestamp => $time ): ?>
                                                <option <?php selected( $settings->report_time, $timestamp ) ?>
                                                        value="<?php echo $timestamp ?>"><?php echo strftime( '%I:%M %p', strtotime( $time ) ) ?></option>
											<?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sui-box-footer">
			<?php wp_nonce_field( 'saveLockoutSettings' ) ?>
            <input type="hidden" name="action" value="saveLockoutSettings"/>
            <div class="sui-actions-right">
                <button type="submit" class="sui-button sui-button-blue">
                    <i class="sui-icon-save" aria-hidden="true"></i>
					<?php _e( "Save Changes", wp_defender()->domain ) ?>
                </button>
            </div>
        </div>
    </form>
</div>