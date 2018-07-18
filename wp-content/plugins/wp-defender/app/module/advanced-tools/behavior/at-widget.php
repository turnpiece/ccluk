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
        <div class="dev-box advanced-tools">
            <div class="box-title">
                <span class="span-icon icon-scan"></span>
                <h3><?php _e( "Advanced Tools", wp_defender()->domain ) ?>
                </h3>

            </div>
            <div class="box-content">
                <p class="line end">
					<?php _e( "Enable advanced tools for enhanced protection against even the most aggressive of hackers and bots.", wp_defender()->domain ) ?>
                </p>
                <div class="at-line end">
                    <strong>
						<?php _e( "Two-Factor Authentication", wp_defender()->domain ) ?>
                    </strong>
                    <span>
						<?php
						_e( "Add an extra layer of security to your WordPress account to ensure that you’re the only person who can log in, even if someone else knows your password", wp_defender()->domain )
						?>
                    </span>
					<?php
					$settings = Auth_Settings::instance();
					if ( $settings->enabled ):
						$enabledRoles = $settings->userRoles;
						if ( count( $enabledRoles ) ):
							?>
                            <div class="well well-small well-green with-cap">
                                <i class="def-icon icon-tick"></i>
                                <span>
                                <?php printf( __( "<strong>Two-factor authentication is now active.</strong> To turn on this feature for your account, go to <a href='%s'>Your Profile</a> to complete setup and sync your account with the Authenticator app.", wp_defender()->domain ),
	                                admin_url( 'profile.php' ) ) ?>
                            </span>
                            </div>
						<?php else: ?>
                            <div class="well well-small well-yellow with-cap">
                                <i class="def-icon icon-warning"></i>
                                <span>
                                    <?php _e( "Two-factor authentication is currently inactive. Configure and save your settings to finish setup. ", wp_defender()->domain ) ?>
                                </span>
                                <a href="<?php echo network_admin_url( 'admin.php?page=wdf-advanced-tools' ) ?>"><?php _e( "Finish Setup", wp_defender()->domain ) ?></a>
                            </div>
						<?php endif; ?>
                        <p>
                            <span>
                            <?php _e( "Note: Each user on your website must individually enable two-factor authentication via their user profile in order to enable and use this security feature.", wp_defender()->domain ) ?>
                        </span>
                        </p>
					<?php else: ?>
                        <form method="post" id="advanced-settings-frm" class="advanced-settings-frm line">
                            <input type="hidden" name="action" value="saveAdvancedSettings"/>
							<?php wp_nonce_field( 'saveAdvancedSettings' ) ?>
                            <input type="hidden" name="enabled" value="1"/>
                            <button type="submit" class="button button-primary button-small">
								<?php _e( "Activate", wp_defender()->domain ) ?>
                            </button>
                        </form>
					<?php endif; ?>
                </div>
                <div class="at-line">
                    <strong>
						<?php _e( "Mask Login Area", wp_defender()->domain ) ?>
                    </strong>
                    <span>
						<?php
						_e( 'Change the location of WordPress’s default login area.', wp_defender()->domain )
						?>
                    </span>
					<?php
					$settings = Mask_Settings::instance();
					if ( $settings->enabled ):?>
						<?php if ( $settings->isEnabled() == false ): ?>
                            <div class="well well-small well-yellow with-cap">
                                <i class="def-icon icon-warning"></i>
                                <span>
                                    <?php _e( "<strong>Masking is currently inactive.</strong> Choose your URL and save your settings to finish setup.", wp_defender()->domain ) ?>
                                </span>
                                <a href="<?php echo network_admin_url( 'admin.php?page=wdf-advanced-tools&view=mask-login' ) ?>"><?php _e( "Finish Setup", wp_defender()->domain ) ?></a>
                            </div>
						<?php else: ?>
                            <div class="well well-green with-cap">
                                <i class="def-icon icon-tick"></i>
								<?php printf( __( "Masking is currently active at <strong>%s</strong>", wp_defender()->domain ), \WP_Defender\Module\Advanced_Tools\Component\Mask_Api::getNewLoginUrl() ) ?>
                            </div>
						<?php endif; ?>
					<?php else: ?>
                        <form method="post" id="advanced-settings-frm" class="advanced-settings-frm">
                            <input type="hidden" name="action" value="saveATMaskLoginSettings"/>
							<?php wp_nonce_field( 'saveATMaskLoginSettings' ) ?>
                            <input type="hidden" name="enabled" value="1"/>
                            <button type="submit" class="button button-primary button-small">
								<?php _e( "Activate", wp_defender()->domain ) ?>
                            </button>
                        </form>
					<?php endif; ?>
                </div>
            </div>
        </div>
		<?php
	}
}