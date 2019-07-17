<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php esc_html_e( "404 Detection", wp_defender()->domain ) ?>
        </h3>
    </div>
    <div class="sui-message">
		<?php if ( wp_defender()->whiteLabel == 0 ): ?>
            <img
                    src="<?php echo wp_defender()->getPluginUrl() ?>assets/img/lockout-man.svg"
                    class="sui-image"/>
		<?php endif; ?>
        <div class="sui-message-content">
            <p>
				<?php esc_html_e( "With 404 detection enabled, Defender will keep an eye out for IP addresses that repeatedly request pages on your website that don’t exist and then temporarily block them from accessing your site.", wp_defender()->domain ) ?>
            </p>
            <form method="post" id="settings-frm" class="ip-frm">
				<?php wp_nonce_field( 'saveLockoutSettings' ) ?>
                <input type="hidden" name="action" value="saveLockoutSettings"/>
                <input type="hidden" name="detect_404" value="1"/>
                <button type="submit" class="sui-button sui-button-blue">
					<?php esc_html_e( "Enable", wp_defender()->domain ) ?>
                </button>
            </form>
        </div>
    </div>
</div>