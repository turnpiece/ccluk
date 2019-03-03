<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Reporting", wp_defender()->domain ) ?>
        </h3>
    </div>
    <div class="sui-box-body sui-upsell-items">
        <div class="sui-box-settings-row sui-disabled no-padding-bottom no-margin-bottom">
            <div class="sui-box-settings-col-1">
                <span class="sui-settings-label"><?php _e( "Lockouts Report", wp_defender()->domain ) ?></span>
                <span class="sui-description">
                        <?php esc_html_e( "Configure Defender to automatically email you a lockout report for this website.", wp_defender()->domain ) ?>
                    </span>
            </div>
            <div class="sui-box-settings-col-2">
                <div class="sui-side-tabs sui-tabs">
                    <div data-tabs>
                        <div><?php _e( "On", wp_defender()->domain ) ?></div>
                        <div class="active"><?php _e( "Off", wp_defender()->domain ) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sui-box-settings-row sui-upsell-row">
            <img class="sui-image sui-upsell-image"
                 src="<?php echo wp_defender()->getPluginUrl() . '/assets/img/scanning-free-man.svg' ?>">
            <div class="sui-upsell-notice">
                <p>
					<?php printf( __( "Schedule daily, weekly or monthly lockout summary reports for all your websites. This feature is included in a WPMU DEV membership along with 100+ plugins & themes, 24/7 support and lots of handy site management tools  – <a href='%s'>Try it all FREE today</a>!", wp_defender()->domain ), \WP_Defender\Behavior\Utils::instance()->campaignURL( 'defender_iplockout_reports_upsell_link' ) ) ?>
                </p>
            </div>
        </div>
    </div>
</div>