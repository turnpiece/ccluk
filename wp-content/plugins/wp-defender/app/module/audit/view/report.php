<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Notification", wp_defender()->domain ) ?>
        </h3>
    </div>
    <form method="post" class="audit-frm audit-settings">
        <div class="sui-box-body">
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Scheduled Reports", wp_defender()->domain ) ?></span>
                    <span class="sui-description"><?php esc_html_e( "Schedule Defender to automatically email you a summary of all your website events.", wp_defender()->domain ) ?></span>
                </div>
                <div class="sui-box-settings-col-2">
                    <input type="hidden" name="notification" value="0"/>
                    <label class="sui-toggle">
                        <input type="checkbox" <?php checked( 1, $setting->notification ) ?> name="notification"
                               value="1" id="toggle_notification"/>
                        <span class="sui-toggle-slider"></span>
                    </label>
                    <label for="toggle_notification" class="sui-toggle-label">
						<?php _e( "Send regular email report", wp_defender()->domain ) ?></label>
                    <div class="sui-border-frame sui-toggle-content">
						<?php $email->renderInput() ?>
                        <div class="sui-form-field margin-top-30 schedule-box">
                            <label class="sui-label"><?php _e( "Frequency", wp_defender()->domain ) ?></label>
                            <div class="sui-row">
                                <div class="sui-col">
                                    <select name="frequency">
                                        <option <?php selected( 1, $setting->frequency ) ?>
                                                value="1"><?php _e( "Daily", wp_defender()->domain ) ?></option>
                                        <option <?php selected( 7, $setting->frequency ) ?>
                                                value="7"><?php _e( "Weekly", wp_defender()->domain ) ?></option>
                                        <option <?php selected( 30, $setting->frequency ) ?>
                                                value="30"><?php _e( "Monthly", wp_defender()->domain ) ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="sui-row">
                                <div class="sui-col days-container">
                                    <label class="sui-label"><?php _e( "Day of the week", wp_defender()->domain ) ?></label>
                                    <select name="day">
										<?php foreach ( \WP_Defender\Behavior\Utils::instance()->getDaysOfWeek() as $day ): ?>
                                            <option <?php selected( $day, $setting->day ) ?>
                                                    value="<?php echo $day ?>"><?php echo ucfirst( $day ) ?></option>
										<?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="sui-col">
                                    <label class="sui-label"><?php _e( "Time of day", wp_defender()->domain ) ?></label>
                                    <select name="time">
		                                <?php foreach ( \WP_Defender\Behavior\Utils::instance()->getTimes() as $time ): ?>
                                            <option <?php selected( $time, $setting->time ) ?>
                                                    value="<?php echo $time ?>"><?php echo strftime( '%I:%M %p', strtotime( $time ) ) ?></option>
		                                <?php endforeach;; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	    <?php wp_nonce_field( 'saveAuditSettings' ) ?>
        <input type="hidden" name="action" value="saveAuditSettings"/>
        <div class="sui-box-footer">
            <div class="sui-actions-right">
                <button type="submit" class="sui-button sui-button-blue">
                    <i class="sui-icon-save" aria-hidden="true"></i>
				    <?php _e( "Save Changes", wp_defender()->domain ) ?></button>
            </div>
        </div>
    </form>
</div>