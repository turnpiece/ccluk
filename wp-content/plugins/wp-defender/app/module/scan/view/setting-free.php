<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Settings", wp_defender()->domain ) ?>
        </h3>
    </div>
    <form method="post" class="scan-frm scan-settings">
        <div class="sui-box-body sui-upsell-items">
            <div class="sui-box-settings-row no-border no-padding-bottom no-margin-bottom">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php _e( "Scan Types", wp_defender()->domain ) ?></span>
                    <span class="sui-description">
                        <?php _e( "Choose the scan types you would like to include in your default scan. It's recommended you enable all types.", wp_defender()->domain ) ?>
                    </span>
                </div>

                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <label class="sui-toggle">
                            <input type="hidden" name="scan_core" value="0"/>
                            <input role="presentation" type="checkbox" name="scan_core" class="toggle-checkbox"
                                   id="core-scan" value="1"
								<?php checked( true, $setting->scan_core ) ?>/>
                            <span class="sui-toggle-slider"></span>
                        </label>
                        <label for="core-scan" class="sui-toggle-label">
							<?php _e( "WordPress Core", wp_defender()->domain ) ?>
                        </label>
                        <p class="sui-description sui-toggle-content">
							<?php _e( "Defender checks for any modifications or additions to WordPress core files.", wp_defender()->domain ) ?>
                        </p>
                    </div>
                    <div class="sui-form-field">
                        <div class="relative">
                            <label class="sui-toggle">
                                <input role="presentation" disabled type="checkbox" class="toggle-checkbox" value="0"/>
                                <span class="sui-toggle-slider"></span>
                            </label>
                            <label for="scan_vuln" class="sui-toggle-label">
								<?php _e( "Plugins & Themes", wp_defender()->domain ) ?>
                            </label>
                            <span class="sui-tag sui-tag-pro"><?php _e( "Pro", wp_defender()->domain ) ?></span>
                        </div>
                        <p class="sui-description sui-toggle-content">
							<?php _e( "Defender looks for publicly reported vulnerabilities in your installed plugins and themes.", wp_defender()->domain ) ?>
                        </p>
                    </div>
                    <div class="sui-form-field">
                        <div class="relative">
                            <label class="sui-toggle">
                                <input role="presentation" disabled type="checkbox" class="toggle-checkbox" value="0"/>
                                <span class="sui-toggle-slider"></span>
                            </label>
                            <label for="scan_content" class="sui-toggle-label">
								<?php _e( "Suspicious Code", wp_defender()->domain ) ?>
                            </label>
                            <span class="sui-tag sui-tag-pro"><?php _e( "Pro", wp_defender()->domain ) ?></span>
                        </div>
                        <p class="sui-description sui-toggle-content">
							<?php _e( "Defender looks inside all of your files for suspicious and potentially harmful code.", wp_defender()->domain ) ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row sui-upsell-row">
                <img class="sui-image sui-upsell-image"
                     src="<?php echo wp_defender()->getPluginUrl() . 'assets/img/scanning-free-man.svg' ?>">
                <div class="sui-upsell-notice">
                    <p>
						<?php printf( __( "Defender Pro allows you to scan your entire file structure for malicious code, including non-WordPress files. This feature is included in a WPMU DEV monthly membership along with 24/7 support and lots of handy site management tools  – <a target='_blank' href='%s'>Try it all FREE today!</a>", wp_defender()->domain ),
							\WP_Defender\Behavior\Utils::instance()->campaignURL( 'defender_filescanning_settings_upsell_link' ) ) ?>
                    </p>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php _e( "Maximum included file size", wp_defender()->domain ) ?></span>
                    <span class="sui-description">
                    <?php _e( "Defender will skip any files larger than this size. The smaller the number, the faster Defender will scan your website.", wp_defender()->domain ) ?>
                </span>
                </div>

                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <div class="sui-form-field">
                            <input type="number" size="4" class="sui-form-control sui-input-sm sui-field-has-suffix"
                                   value="<?php echo esc_attr( $setting->max_filesize ) ?>"
                                   name="max_filesize">
                            <span class="sui-field-suffix">Mb</span>

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
</div>
<dialog id="all-ok" title="<?php esc_attr_e( 'All OK', wp_defender()->domain ) ?>">
    <div class="wp-defender">
        <form method="post" class="scan-frm scan-settings">
            <input type="hidden" name="action" value="saveScanSettings"/>
			<?php wp_nonce_field( 'saveScanSettings' ) ?>
            <textarea rows="12" name="email_all_ok"><?php echo $setting->email_all_ok ?></textarea>
            <strong class="small">
				<?php _e( "Available variables", wp_defender()->domain ) ?>
            </strong>
            <div class="clearfix"></div>
            <span class="def-tag tag-generic">{USER_NAME}</span>
            <span class="def-tag tag-generic">{SITE_URL}</span>
            <div class="clearfix mline"></div>
            <hr class="mline"/>
            <button type="button"
                    class="button button-light close"><?php _e( "Cancel", wp_defender()->domain ) ?></button>
            <button class="button float-r"><?php _e( "Save Template", wp_defender()->domain ) ?></button>
        </form>
    </div>
</dialog>