<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Module\Advanced_Tools\Model\Auth_Settings;

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
                <div class="at-line">
                    <strong>
						<?php _e( "2 Factor Authentication", wp_defender()->domain ) ?>
                    </strong>
                    <span>
						<?php
						_e( "Protect your user accounts by requiring a second passcode sent to users phones in order to get past your login screen", wp_defender()->domain )
						?>
                    </span>
					<?php
					$settings = Auth_Settings::instance();
					if ( $settings->enabled ):
						?>
                        <div class="well well-green with-cap">
                            <i class="def-icon icon-tick"></i>
							<?php _e( "2 factor authentication is active.", wp_defender()->domain ) ?>
                        </div>
					<?php else: ?>
                        <form method="post" id="advanced-settings-frm" class="advanced-settings-frm">
                            <input type="hidden" name="action" value="saveAdvancedSettings"/>
	                        <?php wp_nonce_field( 'saveAdvancedSettings' ) ?>
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