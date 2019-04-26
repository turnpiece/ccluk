<div class="sui-box">
    <form method="post" id="settings-frm" class="ip-frm">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
				<?php _e( "IP Banning", wp_defender()->domain ) ?>
            </h3>
        </div>
        <div class="sui-box-body">
            <p>
				<?php _e( "Choose which IP addresses you wish to permanently ban from accessing your website.", wp_defender()->domain ) ?>
            </p>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "IP Addresses", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                    <?php _e( "Add IP addresses you want to permanently ban from, or always allow access to your website.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <strong><?php _e( "Blacklist", wp_defender()->domain ) ?></strong>
                    <p class="sui-description">
						<?php _e( "Any IPs addresses you list here will be completely blocked from accessing your website, including admins.", wp_defender()->domain ) ?>
                    </p>
                    <div class="sui-border-frame">
                        <label class="sui-label"><?php _e( "Banned IPs", wp_defender()->domain ) ?></label>
                        <textarea class="sui-form-control"
                                  id="ip_blacklist" name="ip_blacklist"
                                  placeholder="<?php esc_attr_e( "Add blacklisted IPs here, one per line", wp_defender()->domain ) ?>"
                                  rows="8"><?php echo $settings->ip_blacklist ?></textarea>
                        <span class="sui-description">
                            <?php _e( "Both IPv4 and IPv6 are supported. IP ranges are also accepted in format xxx.xxx.xxx.xxx-xxx.xxx.xxx.xxx.", wp_defender()->domain ) ?>
                        </span>
                    </div>
                    <strong><?php _e( "Whitelist", wp_defender()->domain ) ?></strong>
                    <div class="sui-border-frame">
                        <label class="sui-label"><?php _e( "Allowed IPs", wp_defender()->domain ) ?></label>
                        <textarea class="sui-form-control"
                                  id="ip_whitelist" name="ip_whitelist"
                                  placeholder="<?php esc_attr_e( "Add whitelisted IPs here, one per line", wp_defender()->domain ) ?>"
                                  rows="8"><?php echo $settings->ip_whitelist ?></textarea>
                        <span class="sui-description">
                            <?php _e( "One IP address per line. Both IPv4 and IPv6 are supported. IP ranges are also accepted in format xxx.xxx.xxx.xxx-xxx.xxx.xxx.xxx.", wp_defender()->domain ) ?>
                        </span>
                    </div>
                    <div class="sui-notice">
                        <p>
							<?php printf( __( "We recommend you add your own IP to avoid getting locked out accidentally! Your current IP is <span class='admin-ip'>%s</span>.", wp_defender()->domain ), \WP_Defender\Behavior\Utils::instance()->getUserIp() ) ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Locations", wp_defender()->domain ) ?></span>
                    <span class="sui-description"><?php esc_html_e( "Use this feature to ban any countries you don’t expect/want traffic from to protect your site entirely from unwanted hackers and bots.", wp_defender()->domain ) ?></span>
                </div>
                <div class="sui-box-settings-col-2 geo-ip-block">
					<?php if ( version_compare( phpversion(), '5.4', '<' ) ): ?>
                        <div class="sui-notice sui-notice-warning">
                            <p>
								<?php printf( __( "This feature requires PHP 5.4 or newer. Please upgrade your PHP version if you wish to use location banning.", wp_defender()->domain ), admin_url( 'admin.php?page=wdf-ip-lockout&view=blacklist' ) ) ?>
                            </p>
                        </div>
					<?php else: ?>
						<?php $country = \WP_Defender\Module\IP_Lockout\Component\IP_API::getCurrentCountry(); ?>
						<?php if ( $settings->isGeoDBDownloaded() == false ): ?>
                            <div class="sui-notice sui-notice-info">
                                <p>
									<?php _e( "To use this feature you must first download the latest Geo IP Database.", wp_defender()->domain ) ?>
                                </p>
                                <div class="sui-notice-buttons">
                                    <button type="button" class="sui-button sui-button-ghost download-geo-ip"
                                            data-nonce="<?php echo wp_create_nonce( 'downloadGeoIPDB' ) ?>">
                                        <span class="sui-loading-text"><?php _e( "Download", wp_defender()->domain ) ?></span>
                                        <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
						<?php elseif ( ! $country ): ?>
                            <div class="sui-notice sui-notice-warning">
                                <p>
									<?php printf( __( "Can't detect current country, it seem your site setup in localhost environment", wp_defender()->domain ), admin_url( 'admin.php?page=wdf-ip-lockout&view=blacklist' ) ) ?>
                                </p>
                            </div>
						<?php else: ?>
                            <strong><?php _e( "Blacklist", wp_defender()->domain ) ?></strong>
                            <p class="sui-description no-margin-bottom">
								<?php _e( "Any countries you select will not being able to access any area of your website.", wp_defender()->domain ) ?>
                            </p>
                            <div class="sui-border-frame">
                                <div class="sui-control-with-icon">
                                    <input type="hidden" name="country_blacklist[]" value=""/>
                                    <select class="sui-select sui-select sui-form-control" name="country_blacklist[]"
                                            placeholder="<?php esc_attr_e( "Type country name", wp_defender()->domain ) ?>"
                                            multiple>
                                        <option value="all" <?php selected( true, in_array( 'all', $settings->getCountryBlacklist() ) ) ?>><?php _e( "Block all", wp_defender()->domain ) ?></option>
										<?php foreach ( \WP_Defender\Behavior\Utils::instance()->countriesList() as $code => $country ): ?>
                                            <option value="<?php echo $code ?>" <?php selected( true, in_array( $code, $settings->getCountryBlacklist() ) ) ?>><?php echo $country ?></option>
										<?php endforeach; ?>
                                    </select>
                                    <i class="sui-icon-web-globe-world" aria-hidden="true"></i>
                                </div>
                            </div>
                            <strong><?php _e( "Whitelist", wp_defender()->domain ) ?></strong>
                            <p class="sui-description no-margin-bottom">
								<?php _e( "Any countries you select will always be able to view your website. Note: We’ve added your default country by default.", wp_defender()->domain ) ?>
                            </p>
                            <div class="sui-border-frame">
                                <div class="sui-control-with-icon">
                                    <input type="hidden" name="country_whitelist[]" value=""/>
                                    <select class="sui-select sui-select sui-form-control" name="country_whitelist[]"
                                            placeholder="<?php esc_attr_e( "Type country name", wp_defender()->domain ) ?>"
                                            multiple>
										<?php foreach ( \WP_Defender\Behavior\Utils::instance()->countriesList() as $code => $country ): ?>
                                            <option value="<?php echo $code ?>" <?php selected( true, in_array( $code, $settings->getCountryWhitelist() ) ) ?>><?php echo $country ?></option>
										<?php endforeach; ?>
                                    </select>
                                    <i class="sui-icon-web-globe-world" aria-hidden="true"></i>
                                </div>
                                <p class="sui-description">
									<?php _e( "Note: your whitelist will override any country ban, but will still follow your 404 and login lockout rules.", wp_defender()->domain ) ?>
                                </p>
                            </div>
                            <p class="sui-description">
                                This product includes GeoLite2 data created by MaxMind, available from
                                <a href="https://www.maxmind.com">https://www.maxmind.com</a>.
                            </p>
						<?php endif; ?>
					<?php endif; ?>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Message", wp_defender()->domain ) ?></span>
                    <span class="sui-description"><?php esc_html_e( "Customize the message locked out users will see.", wp_defender()->domain ) ?></span>
                </div>
                <div class="sui-box-settings-col-2">
                    <label class="sui-label">
						<?php _e( "Custom message", wp_defender()->domain ) ?>
                    </label>
                    <div class="sui-form-field">
                    <textarea name="ip_lockout_message" class="sui-form-control"
                              placeholder="<?php esc_attr_e( "The administrator has blocked your IP from accessing this website.", wp_defender()->domain ) ?>"
                              id="ip_lockout_message"><?php echo $settings->ip_lockout_message ?></textarea>
                        <span class="sui-description">
                        <?php echo sprintf( __( "This message will be displayed across your website during the lockout period. See a quick preview <a href=\"%s\">here</a>.", wp_defender()->domain ), add_query_arg( array(
	                        'def-lockout-demo' => 1,
	                        'type'             => 'blacklist'
                        ), network_site_url() ) ) ?>
                    </span>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Import", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                    <?php _e( "Use this tool to import both your blacklist and whitelist from another website.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <span><?php _e( "Upload your exported blacklist.", wp_defender()->domain ) ?></span>
                        <div class="upload-input sui-upload">
                            <div class="sui-upload-file">

                                <span></span>

                                <button aria-label="Remove file" class="file-picker-remove">
                                    <i class="sui-icon-close" aria-hidden="true"></i>
                                </button>

                            </div>
                            <button type="button" class="sui-upload-button file-picker">
                                <i class="sui-icon-upload-cloud" aria-hidden="true"></i> Upload file
                            </button>
                            <input type="hidden" name="file_import" id="file_import">
                        </div>
                        <div class="clear margin-top-10"></div>
                        <button type="button" class="sui-button sui-button-ghost btn-import-ip">
                            <i class="sui-icon-download-cloud" aria-hidden="true"></i>
							<?php _e( "Import", wp_defender()->domain ) ?>
                        </button>
                        <span class="sui-description">
                            <?php _e( "Note: Existing IPs will not be removed - only new IPs added.", wp_defender()->domain ) ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Export", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                    <?php _e( "Export both your blacklist and whitelist to use on another website.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <a href="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=export&_wpnonce=' . wp_create_nonce( 'defipexport' ) ) ?>"
                       class="sui-button sui-button-outlined export">
                        <i class="sui-icon-upload-cloud" aria-hidden="true"></i>
						<?php _e( "Export", wp_defender()->domain ) ?>
                    </a>
                    <span class="sui-description">
                        <?php _e( "The export will include both the blacklist and whitelist.", wp_defender()->domain ) ?>
                    </span>
                </div>
            </div>
        </div>
		<?php wp_nonce_field( 'saveLockoutSettings' ) ?>
        <input type="hidden" name="action" value="saveLockoutSettings"/>
        <div class="sui-box-footer">
            <div class="sui-actions-right">
                <button type="submit" class="sui-button sui-button-blue">
                    <i class="sui-icon-save" aria-hidden="true"></i>
					<?php _e( "Save Changes", wp_defender()->domain ) ?></button>
            </div>
        </div>
    </form>
</div>