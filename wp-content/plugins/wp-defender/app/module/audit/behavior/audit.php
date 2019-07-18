<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Audit\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Audit\Model\Settings;

class Audit extends Behavior {
	public function renderAuditWidget() {
		$this->_renderAuditSample();
	}

	private function _renderAuditSample() {
		?>
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    <i class="sui-icon-eye" aria-hidden="true"></i>
					<?php _e( "Audit Logging", wp_defender()->domain ) ?>
                </h3>
            </div>
            <div class="sui-box-body auditing">
				<?php if ( Settings::instance()->enabled ): ?>
                    <form method="post" class="audit-frm audit-widget">
                        <input type="hidden" name="action" value="dashboardSummary"/>
						<?php wp_nonce_field( 'dashboardSummary' ) ?>
                    </form>
                    <div class="">
						<?php __( "Please hold on, Defender will update Audit information soon...", wp_defender()->domain ) ?>
                    </div>
                    <div class="wd-overlay">
                        <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                    </div>
				<?php else: ?>
                    <p>
						<?php _e( "Track and log events when changes are made to your website, giving you full visibility over what's going on behind the scenes.", wp_defender()->domain ) ?>
                    </p>
                    <form method="post" class="audit-frm active-audit">
                        <input type="hidden" name="action" value="activeAudit"/>
						<?php wp_nonce_field( 'activeAudit' ) ?>
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