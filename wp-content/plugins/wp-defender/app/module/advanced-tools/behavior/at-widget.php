<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Module\Advanced_Tools\Model\Auth_Settings;
use WP_Defender\Module\Advanced_Tools\Model\Mask_Settings;

class AT_Widget extends Behavior {
	public function renderATWidget() {
		?>
        <div class="sui-box advanced-tools">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    <i class="sui-icon-wand-magic" aria-hidden="true"></i>
					<?php _e( "Advanced Tools", wp_defender()->domain ) ?>
                </h3>
            </div>
            <div class="sui-box-body">
                <p class="margin-bottom-30">
					<?php _e( "Enable advanced tools for enhanced protection against even the most aggressive of hackers and bots.", wp_defender()->domain ) ?>
                </p>
                <hr class="sui-flushed margin-bottom-20"/>
                <small><strong><?php _e( "Two-Factor Authentication", wp_defender()->domain ) ?></strong></small>
                <div class="clearfix"></div>
                <p class="sui-p-small">
		            <?php _e( "Add an extra layer of security to your WordPress account to ensure that you’re the only person who can log in, even if someone else knows your password.", wp_defender()->domain ) ?>
                </p>
				<?php
				$settings = Auth_Settings::instance();
				if ( $settings->enabled ):
					$enabledRoles = $settings->userRoles;
					if ( count( $enabledRoles ) ):
						?>
                        <div class="sui-notice sui-notice-success margin-bottom-10 margin-top-10">
                            <p>
								<?php _e( "Two-factor authentication is now active. User roles with this feature enabled must visit their Profile page to complete setup and sync their account with the Authenticator app.", wp_defender()->domain ) ?>
                            </p>
                        </div>
                        <p class="sui-p-small">
                            <?php _e( "Note: Each user on your website must individually enable two-factor authentication via their user profile in order to enable and use this security feature.", wp_defender()->domain ) ?>
                        </p>
					<?php else: ?>
                        <div class="sui-notice sui-notice-warning margin-top-10">
                            <p class="margin-bottom-10">
								<?php _e( "Two-factor authentication is currently inactive. Configure and save your settings to finish setup. ", wp_defender()->domain ) ?>
                            </p>
                            <a class="sui-button"
                               href="<?php echo network_admin_url( 'admin.php?page=wdf-advanced-tools' ) ?>">
								<?php _e( "Finish Setup", wp_defender()->domain ) ?>
                            </a>
                        </div>
					<?php endif; ?>
				<?php else: ?>
                    <form method="post" id="advanced-settings-frm" class="advanced-settings-frm margin-top-10">
                        <input type="hidden" name="action" value="saveAdvancedSettings"/>
						<?php wp_nonce_field( 'saveAdvancedSettings' ) ?>
                        <input type="hidden" name="enabled" value="1"/>
                        <button type="submit" class="sui-button sui-button-blue">
							<?php _e( "Activate", wp_defender()->domain ) ?>
                        </button>
                    </form>
                    <div class="margin-bottom-20"></div>
				<?php endif; ?>
                <hr class="sui-flushed margin-bottom-20"/>
                <small><strong><?php _e( "Mask Login Area", wp_defender()->domain ) ?></strong></small>
                <div class="clearfix"></div>
                <p class="sui-p-small margin-bottom-10">
		            <?php _e( "Change the location of WordPress’s default login area.", wp_defender()->domain ) ?>
                </p>
				<?php
				$settings = Mask_Settings::instance();
				if ( $settings->enabled ):?>
					<?php if ( $settings->isEnabled() == false ): ?>
                        <div class="sui-notice sui-notice-warning margin-bottom-10 margin-top-10">
                            <p class="margin-bottom-10">
								<?php _e( "Masking is currently inactive. Choose your URL and save your settings to finish setup. ", wp_defender()->domain ) ?>
                            </p>
                            <a class="sui-button"
                               href="<?php echo network_admin_url( 'admin.php?page=wdf-advanced-tools&view=mask-login' ) ?>">
								<?php _e( "Finish Setup", wp_defender()->domain ) ?>
                            </a>
                        </div>
					<?php else: ?>
                        <div class="sui-notice sui-notice-success margin-bottom-10 margin-top-10">
                            <p>
								<?php printf( __( "Masking is currently active at <strong>%s</strong>", wp_defender()->domain ), \WP_Defender\Module\Advanced_Tools\Component\Mask_Api::getNewLoginUrl() ) ?>
                            </p>
                        </div>
					<?php endif; ?>
				<?php else: ?>
                    <form method="post" id="advanced-settings-frm" class="advanced-settings-frm margin-top-10">
                        <input type="hidden" name="action" value="saveATMaskLoginSettings"/>
						<?php wp_nonce_field( 'saveATMaskLoginSettings' ) ?>
                        <input type="hidden" name="enabled" value="1"/>
                        <button type="submit" class="sui-button sui-button-blue">
							<?php _e( "Activate", wp_defender()->domain ) ?>
                        </button>
                    </form>
				<?php endif; ?>
            </div>
        </div>
		<?php
	}
}
