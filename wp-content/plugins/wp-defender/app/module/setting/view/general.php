<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "General", wp_defender()->domain ) ?>
        </h3>
    </div>
    <form method="post" id="settings" class="settings-frm">
        <div class="sui-box-body">
            <p>
				<?php _e( "Configure general settings for this plugin.", wp_defender()->domain ) ?>
            </p>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php esc_html_e( "Translations", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php printf( __( "By default, Defender will use the language you’d set in your <a href=\"%s\">WordPress Admin Settings</a> if a matching translation is available.", wp_defender()->domain ),
	                        network_admin_url( 'options-general.php' ) ) ?>
                    </span>
                </div>

                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <label class="sui-label"><?php _e( "Active translation", wp_defender()->domain ) ?></label>
                        <input type="text" value="<?php echo $settings->translate ?>" disabled
                               class="sui-form-control">
                        <p class="sui-description">
							<?php _e( "Not using your language, or have improvements? Help us improve translations by providing your own improvements here.", wp_defender()->domain ) ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php esc_html_e( "Usage Tracking", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                        <?php esc_html_e( "Help make Defender better by letting our designers learn how you’re using the plugin.", wp_defender()->domain ) ?>
                    </span>
                </div>

                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <label class="sui-toggle">
                            <input type="hidden" name="usage_tracking" value="0"/>
                            <input role="presentation" type="checkbox" name="usage_tracking" class="toggle-checkbox"
                                   id="usage_tracking" <?php checked( 1, $settings->usage_tracking ) ?> value="1"
                            />
                            <span class="sui-toggle-slider"></span>
                        </label>
                        <label for="usage_tracking" class="sui-toggle-label">
							<?php _e( "Allow usage tracking", wp_defender()->domain ) ?>
                        </label>
                        <p class="sui-description sui-toggle-content">
							<?php _e( "Note: Usage tracking is completely anonymous. We are only tracking what features you are/aren’t using to make our feature decisions more informed.", wp_defender()->domain ) ?>
                        </p>
                    </div>
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