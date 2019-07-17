<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php esc_html_e( "Login Protection", wp_defender()->domain ) ?>
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
				<?php esc_html_e( "Put a stop to hackers trying to randomly guess your login credentials. Defender will lock out users after a set number of failed login attempts.", wp_defender()->domain ) ?>
            </p>
            <form method="post" id="settings-frm" class="ip-frm">
				<?php wp_nonce_field( 'saveLockoutSettings' ) ?>
                <input type="hidden" name="action" value="saveLockoutSettings"/>
                <input type="hidden" name="login_protection" value="1"/>
                <button type="submit" class="sui-button sui-button-blue">
					<?php esc_html_e( "Active", wp_defender()->domain ) ?>
                </button>
            </form>
        </div>
    </div>
</div>