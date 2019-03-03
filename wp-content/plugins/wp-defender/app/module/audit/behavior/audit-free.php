<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Audit\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Audit\Model\Settings;

class Audit_Free extends Behavior {
	public function renderAuditWidget() {
		?>
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    <i class="sui-icon-eye" aria-hidden="true"></i>
					<?php _e( "Audit Logging", wp_defender()->domain ) ?>
                </h3>
                <div class="sui-actions-left">
                    <span class="sui-tag sui-tag-pro"><?php _e( "Pro", wp_defender()->domain ) ?></span>
                </div>
            </div>
            <div class="sui-box-body sui-upsell-items">
                <div class="sui-box-settings-row no-margin-bottom">
                    <p>
						<?php _e( "Track and log events when changes are made to your website giving you full visibility of what’s going on behind the scenes.", wp_defender()->domain ) ?>
                    </p>
                </div>
                <div class="sui-box-settings-row sui-upsell-row">
                    <img class="sui-image sui-upsell-image"
                         src="<?php echo wp_defender()->getPluginUrl() . 'assets/img/audit-presale.svg' ?>">
                    <div class="sui-upsell-notice">
                        <p>
							<?php
							printf( __( "Audit Logging is a Pro feature that requires a WPMU DEV monthly membership. <a target='_blank' href='%s'>Try it out today</a>!", wp_defender()->domain ),
								Utils::instance()->campaignURL( 'defender_dash_auditlogging_upsell_link' ) )
							?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}
}