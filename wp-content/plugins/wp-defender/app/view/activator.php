<div class="sui-dialog" aria-hidden="true" tabindex="-1" id="activator">

    <div class="sui-dialog-overlay" data-a11y-dialog-hide></div>

    <div class="sui-dialog-content" aria-labelledby="Quick setup" aria-describedby="" role="dialog">

        <div class="sui-box activate-picker" role="document">

            <div class="sui-box-header">
                <h3 class="sui-box-title">
					<?php _e( "Quick Setup", wp_defender()->domain ) ?></h3>
                <div class="sui-actions-right">
                    <form method="post" class="skip-activator">
                        <input type="hidden" name="action" value="skipActivator"/>
						<?php wp_nonce_field( 'skipActivator', '_wpnonce', true ) ?>
                        <button type="submit" class="sui-button sui-button-ghost">
							<?php _e( "Skip", wp_defender()->domain ) ?>
                        </button>
                    </form>
                </div>
            </div>
            <form method="post">
                <div class="sui-box-body">
                    <p><?php _e( "Welcome to Defender, the hottest security plugin for WordPress! Let’s quickly set up the basics for you, then you can fine tweak each setting as you go – our recommendations are on by default.", wp_defender()->domain ) ?></p>
                    <hr class="sui-flushed"/>
                    <input type="hidden" value="activateModule" name="action"/>
					<?php wp_nonce_field( 'activateModule' ) ?>
                    <div class="sui-row">
                        <div class="sui-col-md-10">
                        <span class="sui-settings-label">
                            <?php
                            if ( wp_defender()->isFree ) {
	                            _e( "File Scanning", wp_defender()->domain );
                            } else {
	                            _e( "Automatic File Scans & Reporting", wp_defender()->domain );
                            } ?>
                        </span>
                            <span class="sui-description">
                            <?php _e( "Scan your website for file changes, vulnerabilities and injected code and get notified about anything suspicious.", wp_defender()->domain ) ?>
                        </span>
                        </div>

                        <div class="sui-col-md-2">
                            <div class="sui-form-field tr">
                                <label class="sui-toggle">
                                    <input type="checkbox"
                                           name="activator[]" checked
                                           class="toggle-checkbox" id="active_scan"
                                           value="activate_scan"/>
                                    <span class="sui-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <hr class="sui-flushed"/>
                    <div class="sui-row">
                        <div class="sui-col-md-10">
                        <span class="sui-settings-label">
                           <?php _e( "Audit Logging", wp_defender()->domain ) ?>
                        </span>
                            <span class="sui-description">
                            <?php _e( "Track and log events when changes are made to your website giving you full visibility of what’s going on behind the scenes.", wp_defender()->domain ) ?>
                        </span>
                        </div>

                        <div class="sui-col-md-2">
                            <div class="sui-form-field tr">
                                <label class="sui-toggle">
                                    <input type="checkbox"
                                           name="activator[]" checked
                                           class="toggle-checkbox" id="active_audit" value="activate_audit"/>
                                    <span class="sui-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <hr class="sui-flushed"/>
                    <div class="sui-row">
                        <div class="sui-col-md-10">
                        <span class="sui-settings-label">
                           <?php _e( "IP Lockouts", wp_defender()->domain ) ?>
                        </span>
                            <span class="sui-description">
                            <?php _e( "Protect your login area and have Defender automatically lockout any suspicious behaviour.", wp_defender()->domain ) ?>
                        </span>
                        </div>

                        <div class="sui-col-md-2">
                            <div class="sui-form-field tr">
                                <label class="sui-toggle">
                                    <input type="checkbox" checked
                                           name="activator[]" class="toggle-checkbox" id="activate_lockout"
                                           value="activate_lockout"/>
                                    <span class="sui-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <hr class="sui-flushed"/>
                    <div class="sui-row">
                        <div class="sui-col-md-10">
                        <span class="sui-settings-label">
                           <?php _e( "Blacklist Monitor", wp_defender()->domain ) ?>
                        </span>
                            <span class="sui-description">
                            <?php _e( "Automatically check if you’re on Google’s blacklist every 6 hours. If something’s wrong, we’ll let you know via email.", wp_defender()->domain ) ?>
                        </span>
                        </div>

                        <div class="sui-col-md-2">
                            <div class="sui-form-field tr">
                                <label class="sui-toggle">
                                    <input type="checkbox" checked
                                           name="activator[]" class="toggle-checkbox" id="activate_blacklist"
                                           value="activate_blacklist"/>
                                    <span class="sui-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sui-box-footer">
                    <div class="sui-row">
                        <div class="sui-col-md-9">
                            <small><?php _e( "Note: These services will be configured with our recommended settings. You can change these at any time.", wp_defender()->domain ) ?></small>
                        </div>
                        <div class="sui-col-md-3">
                            <button type="submit" class="sui-button sui-button-blue">
								<?php _e( "Get Started", wp_defender()->domain ) ?></button>
                        </div>
                    </div>
                </div>
            </form>
            <img src="<?php echo wp_defender()->getPluginUrl() . '/assets/img/defender-activator.svg' ?>"
                 class="sui-image sui-image-center"/>
        </div>
        <div class="sui-box activate-progress wd-hide">
            <div class="sui-box-body">
                <p>
					<?php _e( "Just a moment while Defender activates those services for you...", wp_defender()->domain ) ?>
                </p>
                <div class="sui-progress-block">
                    <div class="sui-progress">
                        <div class="sui-progress-text scan-progress-text sui-icon-loader sui-loading">
                            <span>0%</span>
                        </div>
                        <div class="sui-progress-bar scan-progress-bar">
                            <span style="width: 0%"></span>
                        </div>
                    </div>
                </div>
                <div class="sui-progress-state">
                    <span class="status-text"></span>
                </div>
            </div>
        </div>
    </div>

</div>