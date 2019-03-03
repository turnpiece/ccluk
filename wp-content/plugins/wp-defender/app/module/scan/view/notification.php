<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Notifications", wp_defender()->domain ) ?>
        </h3>
    </div>
    <form method="post" class="scan-frm scan-settings">
        <div class="sui-box-body">
            <p>
				<?php _e( "Get email notifications when Defender has finished manual files scans.", wp_defender()->domain ) ?>
            </p>

            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php _e( "Enable notifications", wp_defender()->domain ) ?></span>
                    <span class="sui-description">
                    <?php _e( "Enabling this option will ensure you get the results of every scan once they’re completed.", wp_defender()->domain ) ?>
                </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <input type="hidden" name="notification" value="<?php echo $setting->notification ?>"/>
                        <div class="sui-side-tabs sui-tabs">
                            <div data-tabs>
                                <div rel="input_value" data-target="notification" data-value="1"
                                     class="<?php echo $setting->notification == 1 ? 'active' : null ?>"><?php _e( "On", wp_defender()->domain ) ?></div>
                                <div rel="input_value" data-target="notification" data-value="0"
                                     class="<?php echo $setting->notification == 0 ? 'active' : null ?>"><?php _e( "Off", wp_defender()->domain ) ?></div>
                            </div>
                            <div data-panes>
                                <div class="sui-tab-boxed no-padding-bottom <?php echo $setting->notification == 1 ? 'active' : null ?>">
                                    <p class="sui-p-small">
										<?php _e( "By default, we will only notify the recipients below when there is an issue from your file scan. Enable this option to send emails even when no issues are detected. ", wp_defender()->domain ) ?>
                                    </p>
                                    <label class="sui-toggle">
                                        <input type="hidden" name="always_send" value="0"/>
                                        <input role="presentation" type="checkbox" name="alwaysSendNotification"
                                               class="toggle-checkbox"
                                               id="alwaysSendNotification" value="1"
											<?php checked( true, $setting->alwaysSendNotification ) ?>/>
                                        <span class="sui-toggle-slider"></span>
                                    </label>
                                    <label for="always_send" class="sui-toggle-label">
										<?php _e( "Also send notifications when no issues are detected.", wp_defender()->domain ) ?>
                                    </label>
                                    <div class="margin-top-30">
										<?php $email->renderInput() ?>
                                    </div>
                                    <div class="sui-field-list sui-flushed no-border">
                                        <div class="sui-field-list-header">
                                            <h3 class="sui-field-list-title"><?php _e( "Email Templates", wp_defender()->domain ) ?></h3>
                                        </div>
                                        <div class="sui-field-list-body">
                                            <div class="sui-field-list-item">
                                                <label class="sui-field-list-item-label">
                                                    <strong>
														<?php _e( "When an issue is found", wp_defender()->domain ) ?>
                                                    </strong>
                                                </label>
                                                <button data-a11y-dialog-show="issue-found" type="button"
                                                        class="sui-button-icon">
                                                    <i class="sui-icon-pencil" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <div class="sui-field-list-item">
                                                <label class="sui-field-list-item-label">
                                                    <strong>
														<?php _e( "When no issues are found", wp_defender()->domain ) ?>
                                                    </strong>
                                                </label>
                                                <button data-a11y-dialog-show="all-ok" type="button"
                                                        class="sui-button-icon">
                                                    <i class="sui-icon-pencil" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="action" value="saveScanSettings"/>
		<?php wp_nonce_field( 'saveScanSettings' ) ?>
        <div class="sui-box-footer">
            <div class="sui-actions-right">
                <button type="submit" class="sui-button sui-button-blue">
                    <i class="sui-icon-save" aria-hidden="true"></i>
					<?php _e( "Save Changes", wp_defender()->domain ) ?></button>
            </div>
        </div>
    </form>
    <div class="sui-dialog" aria-hidden="true" tabindex="-1" id="all-ok">

        <div class="sui-dialog-overlay" data-a11y-dialog-hide></div>

        <div class="sui-dialog-content" aria-labelledby="dialogTitle" aria-describedby="dialogDescription"
             role="dialog">

            <div class="sui-box" role="document">
                <form method="post" class="scan-frm scan-settings">
                    <div class="sui-box-header">
                        <h3 class="sui-box-title" id="dialogTitle">
							<?php _e( "Edit Template", wp_defender()->domain ) ?>
                        </h3>
                        <div class="sui-actions-right">
                            <button type="button" data-a11y-dialog-hide class="sui-dialog-close"
                                    aria-label="Close this dialog window"></button>
                        </div>
                    </div>

                    <div class="sui-box-body">
                        <p>
							<?php _e( "Edit the email copy for when Defender finishes a scan and sends an email summary report.", wp_defender()->domain ) ?>
                        </p>
                        <div class="sui-row">
                            <div class="sui-col">
                                <div class="sui-form-field">
                                    <textarea rows="12" class="sui-form-control"
                                              name="email_all_ok"><?php echo $setting->email_all_ok ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="sui-form-field">
                            <label for="dialog-text-5" class="sui-label">
								<?php _e( "Available variables", wp_defender()->domain ) ?>
                            </label>
                            <span class="sui-tag">{USER_NAME}</span>
                            <span class="sui-tag">{SITE_URL}</span>
                            <span class="sui-tag">{ISSUES_COUNT}</span>
                            <span class="sui-tag">{ISSUES_LIST}</span>
                        </div>
                    </div>

                    <div class="sui-box-footer">
						<?php wp_nonce_field( 'saveScanSettings' ) ?>
                        <input type="hidden" name="action" value="saveScanSettings"/>
                        <div class="sui-actions-left">
                            <button class="sui-button" type="button" data-a11y-dialog-hide="issue-found">
								<?php _e( "Cancel", wp_defender()->domain ) ?></button>
                        </div>
                        <div class="sui-actions-right">
                            <button class="sui-modal-close sui-button sui-button-blue"><i class="sui-icon-save"
                                                                                          aria-hidden="true"></i><?php _e( "Save Changes", wp_defender()->domain ) ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
    <div class="sui-dialog" aria-hidden="true" tabindex="-1" id="issue-found">

        <div class="sui-dialog-overlay" data-a11y-dialog-hide></div>

        <div class="sui-dialog-content" aria-labelledby="dialogTitle" aria-describedby="dialogDescription"
             role="dialog">

            <div class="sui-box" role="document">
                <form method="post" class="scan-frm scan-settings">
                    <div class="sui-box-header">
                        <h3 class="sui-box-title" id="dialogTitle">
							<?php _e( "Edit Template", wp_defender()->domain ) ?>
                        </h3>
                        <div class="sui-actions-right">
                            <button type="button" data-a11y-dialog-hide class="sui-dialog-close"
                                    aria-label="Close this dialog window"></button>
                        </div>
                    </div>

                    <div class="sui-box-body">
                        <p>
							<?php _e( "Edit the email copy for when Defender finishes a scan and sends an email summary report.", wp_defender()->domain ) ?>
                        </p>
                        <div class="sui-row">
                            <div class="sui-col">
                                <div class="sui-form-field">
                                    <textarea rows="12" class="sui-form-control"
                                              name="email_has_issue"><?php echo $setting->email_has_issue ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="sui-form-field">
                            <label for="dialog-text-5" class="sui-label">
								<?php _e( "Available variables", wp_defender()->domain ) ?>
                            </label>
                            <span class="sui-tag">{USER_NAME}</span>
                            <span class="sui-tag">{SITE_URL}</span>
                            <span class="sui-tag">{ISSUES_COUNT}</span>
                            <span class="sui-tag">{ISSUES_LIST}</span>
                        </div>
                    </div>

                    <div class="sui-box-footer">
						<?php wp_nonce_field( 'saveScanSettings' ) ?>
                        <input type="hidden" name="action" value="saveScanSettings"/>
                        <div class="sui-actions-left">
                            <button type="button" class="sui-button" data-a11y-dialog-hide="issue-found">
								<?php _e( "Cancel", wp_defender()->domain ) ?></button>
                        </div>
                        <div class="sui-actions-right">
                            <button class="sui-modal-close sui-button sui-button-blue"><i class="sui-icon-save"
                                                                                          aria-hidden="true"></i><?php _e( "Save Changes", wp_defender()->domain ) ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>