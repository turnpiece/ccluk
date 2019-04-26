<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Data & Settings", wp_defender()->domain ) ?>
        </h3>
    </div>
    <form method="post" id="settings" class="settings-frm">
        <div class="sui-box-body">
            <p>
				<?php _e( "Control what to do with your settings and data. Settings are each module’s configuration options, Data includes the stored information like logs, statistics other pieces of information stored over time.", wp_defender()->domain ) ?>
            </p>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php esc_html_e( "Uninstallation", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php _e( "When you uninstall this plugin, what do you want to do with your settings and stored data?", wp_defender()->domain ) ?>
                    </span>
                </div>

                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <label class="sui-label"><?php _e( "Settings", wp_defender()->domain ) ?></label>
                        <div class="sui-side-tabs sui-tabs">
                            <div data-tabs>
                                <div rel="input_value" data-target="uninstall_settings" data-value="preserve"
                                     class="<?php echo $settings->uninstall_settings == 'preserve' ? 'active' : null ?>"><?php _e( "Preserve", wp_defender()->domain ) ?></div>
                                <div rel="input_value" data-target="uninstall_settings" data-value="reset"
                                     class="<?php echo $settings->uninstall_settings == 'reset' ? 'active' : null ?>"><?php _e( "Reset", wp_defender()->domain ) ?></div>
                            </div>
                            <input type="hidden" name="uninstall_settings"
                                   value="<?php echo $settings->uninstall_settings ?>"/>
                        </div>
                        <label class="sui-label"><?php _e( "Data", wp_defender()->domain ) ?></label>
                        <div class="sui-side-tabs sui-tabs">
                            <div data-tabs>
                                <div rel="input_value" data-target="uninstall_data" data-value="keep"
                                     class="<?php echo $settings->uninstall_data == 'keep' ? 'active' : null ?>"><?php _e( "Keep", wp_defender()->domain ) ?></div>
                                <div rel="input_value" data-target="uninstall_data" data-value="remove"
                                     class="<?php echo $settings->uninstall_data == 'remove' ? 'active' : null ?>"><?php _e( "Remove", wp_defender()->domain ) ?></div>
                            </div>
                            <input type="hidden" name="uninstall_data"
                                   value="<?php echo $settings->uninstall_data ?>"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php esc_html_e( "Reset Settings", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php esc_html_e( "Needing to start fresh? Use this button to roll back to the default settings.", wp_defender()->domain ) ?>
                    </span>
                </div>

                <div class="sui-box-settings-col-2">
                    <button type="button" data-a11y-dialog-show="reset-data-confirm"
                            class="sui-button-ghost sui-button wd-reset-settings">
                        <i class="sui-icon-undo" aria-hidden="true"></i>
						<?php _e( "Reset Settings", wp_defender()->domain ) ?>
                    </button>
                    <span class="sui-description">
                        <?php _e( "Note: This will instantly revert all settings to their default states but will leave your data intact.", wp_defender()->domain ) ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="sui-box-footer">
            <input type="hidden" name="action" value="saveSettings"/>
			<?php wp_nonce_field( 'saveSettings' ) ?>
            <div class="sui-actions-right">
                <button type="submit" class="sui-button sui-button-blue">
                    <i class="sui-icon-save" aria-hidden="true"></i>
					<?php _e( "Save Changes", wp_defender()->domain ) ?></button>
            </div>
        </div>
    </form>
</div>