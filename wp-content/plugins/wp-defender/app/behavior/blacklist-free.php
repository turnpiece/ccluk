<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Component\Error_Code;

class Blacklist_Free extends Behavior {
	public function renderBlacklistWidget() {
		$this->_renderFree();
	}

	private function _renderFree() {
		?>
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    <i class="sui-icon-target" aria-hidden="true"></i>
					<?php _e( "Blacklist Monitor", wp_defender()->domain ) ?>
                </h3>
                <div class="sui-actions-left">
                    <span class="sui-tag sui-tag-pro"><?php _e( "Pro", wp_defender()->domain ) ?></span>
                </div>
            </div>
            <div class="sui-box-body sui-upsell-items">
                <div class="sui-box-settings-row no-margin-bottom">
                    <p>
						<?php _e( "Automatically check if you’re on Google’s blacklist every 6 hours. If something’s wrong, we’ll let you know via email.", wp_defender()->domain ) ?>
                    </p>
                </div>
                <div class="sui-box-settings-row sui-upsell-row">
                    <img class="sui-image sui-upsell-image"
                         src="<?php echo wp_defender()->getPluginUrl() . 'assets/img/dashboard-blacklist.svg' ?>">
                    <div class="sui-upsell-notice">
                        <p>
							<?php
							printf( __( "Blacklist Monitor is a Pro feature, included as part of a WPMU DEV monthly membership. <a target='_blank' href='%s'>Learn more</a>.", wp_defender()->domain ), Utils::instance()->campaignURL( 'defender_dash_blacklist_upgrade_button' ) )
							?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}
}