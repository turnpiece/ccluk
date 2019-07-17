<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\IP_Lockout\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\IP_Lockout\Model\Settings;

class Widget extends Behavior {
	public function renderLockoutWidget() {
		$isOff = ! Settings::instance()->detect_404 && ! Settings::instance()->login_protection;
		?>
        <div class="sui-box" id="lockoutSummary">
			<?php if ( ! $isOff ): ?>
                <div class="wd-overlay">
                    <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                </div>
                <input type="hidden" id="summaryNonce" value="<?php echo wp_create_nonce( 'lockoutSummaryData' ) ?>"/>
			<?php endif; ?>
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    <i class="sui-icon-lock" aria-hidden="true"></i>
					<?php _e( "IP Lockouts", wp_defender()->domain ) ?>
                </h3>
            </div>
            <div class="sui-box-body <?php echo ! $isOff ? 'no-padding-bottom' : null ?>">
                <p><?php _e( "Protect to your login area and have Defender automatically lockout any suspicious behaviour.", wp_defender()->domain ) ?></p>
				<?php if ( $isOff ): ?>
                    <form method="post" id="settings-frm" class="ip-frm">
						<?php wp_nonce_field( 'saveLockoutSettings' ) ?>
                        <input type="hidden" name="action" value="saveLockoutSettings"/>
                        <input type="hidden" name="login_protection" value="1"/>
                        <input type="hidden" name="detect_404" value="1"/>
                        <button type="submit" class="sui-button sui-button-blue">
							<?php esc_html_e( "Activate", wp_defender()->domain ) ?>
                        </button>
                    </form>
				<?php else: ?>
                    <div class="sui-field-list sui-flushed no-border">
                        <div class="sui-field-list-body">
                            <div class="sui-field-list-item">
                                <label class="sui-field-list-item-label">
                                    <strong><?php _e( "Last lockout", wp_defender()->domain ) ?></strong>
                                </label>
                                <span class="lastLockout">.</span>
                            </div>
                            <div class="sui-field-list-item">
                                <label class="sui-field-list-item-label">
                                    <strong><?php _e( "Login lockouts this week", wp_defender()->domain ) ?></strong>
                                </label>
                                <span class="loginLockoutThisWeek">.</span>
                            </div>
                            <div class="sui-field-list-item">
                                <label class="sui-field-list-item-label">
                                    <strong><?php _e( "404 lockouts this week", wp_defender()->domain ) ?></strong>
                                </label>
                                <span class="lockout404ThisWeek">.</span>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>
            </div>
			<?php if ( ! $isOff ): ?>
                <div class="sui-box-footer">
                    <div class="sui-actions-left">
                        <a href="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=logs', wp_defender()->domain
						) ?>"
                           class="sui-button sui-button-ghost">
                            <i class="sui-icon-eye" aria-hidden="true"></i>
							<?php _e( "View logs", wp_defender()->domain ) ?>
                        </a>
                    </div>
                    <div class="sui-actions-right">
                        <p class="sui-p-small">
		                    <?php if ( Settings::instance()->ip_lockout_notification && Settings::instance()->login_lockout_notification ) {
			                    echo _e( "Lockout notifications are enabled", wp_defender()->domain );
		                    } else {
			                    echo _e( "Lockout notifications are disabled", wp_defender()->domain );
		                    }
		                    ?>
                        </p>
                    </div>
                </div>
			<?php endif; ?>
        </div>
		<?php
	}
}