<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Two Factor Authentication", wp_defender()->domain ) ?>
        </h3>
    </div>
    <div class="sui-message">
		<?php if ( wp_defender()->hideHeroImage == 0 ): ?>
            <img src="<?php echo wp_defender()->getPluginUrl() . 'assets/img/2factor-disabled.svg' ?>" class="sui-image"
                 aria-hidden="true">
		<?php endif; ?>

        <div class="sui-message-content">

            <p>
				<?php _e( "Beef up your website’s security with two-factor authentication. Add an extra step in the login process so that users are required to enter a password and an app-generated passcode using their phone – the best protection against brute force attacks.", wp_defender()->domain ) ?>
            </p>

            <form method="post" id="advanced-settings-frm" class="advanced-settings-frm">
                <input type="hidden" name="action" value="saveAdvancedSettings"/>
				<?php wp_nonce_field( 'saveAdvancedSettings' ) ?>
                <input type="hidden" name="enabled" value="1"/>
                <button type="submit" class="sui-button sui-button-blue">
					<?php _e( "Activate", wp_defender()->domain ) ?></button>
            </form>

        </div>

    </div>
</div>