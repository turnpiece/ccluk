<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Model\Result_Item;
use WP_Defender\Module\Scan\Model\Settings;

class Scan extends Behavior {
	private $lastScan;
	private $activeScan;
	private $settled = false;
	private $countAll;

	private function pullStatus() {
		if ( $this->settled == false ) {
			$this->activeScan = Scan_Api::getActiveScan();
			$this->lastScan   = Scan_Api::getLastScan();
			$this->countAll   = is_object( $this->lastScan ) ? $this->lastScan->countAll( Result_Item::STATUS_ISSUE ) : 0;
			$this->settled    = true;
		}
	}

	public function renderScanWidget() {
		$this->pullStatus();

		?>
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                <i class="sui-icon-layers" aria-hidden="true"></i>
                <?php _e("File Scanning") ?>
                </h3>
                <?php if($this->countAll > 0):?>
                <div class="sui-actions-left">
                <span class="sui-tag sui-tag-error">
                <?php echo $this->countAll; ?>
                </span>
                </div>
                <?php endif; ?>
            </div>
           <?php
				$activeScan = $this->activeScan;
				$lastScan   = $this->lastScan;
				if ( ! is_object( $activeScan ) && ! is_object( $lastScan ) ) {
					echo $this->_renderNewScan();
				} elseif ( is_object( $activeScan ) && $activeScan->status != \WP_Defender\Module\Scan\Model\Scan::STATUS_ERROR ) {
					echo $this->_renderScanning( $activeScan );
				} elseif ( is_object( $activeScan ) && $activeScan->status == \WP_Defender\Module\Scan\Model\Scan::STATUS_ERROR ) {

				} else {
					echo $this->_renderResult( $lastScan );
				}
				?>
<!--            <div class="sui-box-body">-->
<!--				--><?php
//				$activeScan = $this->activeScan;
//				$lastScan   = $this->lastScan;
//				if ( ! is_object( $activeScan ) && ! is_object( $lastScan ) ) {
//					echo $this->_renderNewScan();
//				} elseif ( is_object( $activeScan ) && $activeScan->status != \WP_Defender\Module\Scan\Model\Scan::STATUS_ERROR ) {
//					echo $this->_renderScanning( $activeScan );
//				} elseif ( is_object( $activeScan ) && $activeScan->status == \WP_Defender\Module\Scan\Model\Scan::STATUS_ERROR ) {
//
//				} else {
//					echo $this->_renderResult( $lastScan );
//				}
//				?>
<!--            </div>-->
        </div>
		<?php
	}

	public function renderScanStatusText() {
		$this->pullStatus();
		$activeScan = $this->activeScan;
		$lastScan   = $this->lastScan;
		if ( ! is_object( $activeScan ) && ! is_object( $lastScan ) ) {
			?>
            <form id="start-a-scan" method="post" class="scan-frm">
				<?php
				wp_nonce_field( 'startAScan' );
				?>
                <input type="hidden" name="action" value="startAScan"/>
                <button type="submit"
                        class="button button-small"><?php _e( "RUN SCAN", wp_defender()->domain ) ?></button>
            </form>
			<?php
		} elseif ( is_object( $activeScan ) && $activeScan->status != \WP_Defender\Module\Scan\Model\Scan::STATUS_ERROR ) {
			?>
            <img src="<?php echo wp_defender()->getPluginUrl() ?>assets/img/loading.gif" width="18"
                 height="18"/> <?php _e( "Scanning…", wp_defender()->domain ) ?>
			<?php
		} elseif ( is_object( $activeScan ) && $activeScan->status == \WP_Defender\Module\Scan\Model\Scan::STATUS_ERROR ) {
			echo $this->activeScan->statusText;
		} else {
			?>
				<?php
				if ( $this->countAll == 0 ): ?>
					<span class="def-tag tag-success">
				<?php else:
					?>
					<span class="def-tag tag-error">
				<?php endif;
				echo $this->countAll;
				?>
				</span>
			<?php
		}
	}

	private function _renderResult(\WP_Defender\Module\Scan\Model\Scan $model){
	    ob_start();
	    ?>
	    <div class="sui-box-body no-padding-bottom">
	        <p>
			<?php _e( "Scan your website for file changes, vulnerabilities and injected code and get and get notified about anything suspicious.", wp_defender()->domain ) ?>
            </p>
            <?php
		if ( $this->countAll == 0 ) {
			?>
            <div class="sui-notice sui-notice-success">
				<p><?php _e( "Your code is clean, the skies are clear.", wp_defender()->domain ) ?></p>
            </div>
			<?php
		} else {
			?>
			<div class="sui-field-list sui-flushed no-border">
					<div class="sui-field-list-body">
						<div class="sui-field-list-item">
							<label class="sui-field-list-item-label">
								<?php _e( "WordPress Core", wp_defender()->domain ) ?>
							</label>
							<?php echo $model->getCount( 'core' ) == 0 ? ' <i class="sui-icon-check" aria-hidden="true"></i>' : '<span class="sui-tag sui-tag-error">' . $model->getCount( 'core' ) . '</span>' ?>
						</div>
						<div class="sui-field-list-item">
							<label class="sui-field-list-item-label">
								<?php _e( "Plugins & Themes", wp_defender()->domain ) ?>
							</label>
							<?php if ( Utils::instance()->getAPIKey() ): ?>
	                            <?php echo $model->getCount( 'vuln' ) == 0 ? ' <i class="sui-icon-check" aria-hidden="true"></i>' : '<span class="sui-tag sui-tag-error">' . $model->getCount( 'vuln' ) . '</span>' ?>
                            <?php else: ?>
                                <a href="<?php echo Utils::instance()->campaignURL('defender_dash_filescan_pro_tag') ?>" target="_blank" class="sui-button sui-button-purple"
								data-tooltip="<?php esc_attr_e( "Try Defender Pro free today", wp_defender()->domain ) ?>">
                                    <?php _e( "Pro Feature", wp_defender()->domain ) ?>
                                </a>
                            <?php endif; ?>
						</div>
						<div class="sui-field-list-item">
							<label class="sui-field-list-item-label">
								<?php _e( "Suspicious Code", wp_defender()->domain ) ?>
							</label>
							<?php if ( Utils::instance()->getAPIKey() ): ?>
				                <?php echo $model->getCount( 'content' ) == 0 ? ' <i class="sui-icon-check" aria-hidden="true"></i>' : '<span class="sui-tag sui-tag-error">' . $model->getCount( 'content' ) . '</span>' ?>
			                <?php else: ?>
                                <a href="<?php echo Utils::instance()->campaignURL('defender_dash_filescan_pro_tag') ?>" target="_blank" class="sui-button sui-button-purple"
								tooltip="<?php esc_attr_e( "Try Defender Pro free today", wp_defender()->domain ) ?>">
                                    <?php _e( "Pro Feature", wp_defender()->domain ) ?>
                                </a>
			                <?php endif; ?>
						</div>
					</div>
			</div>
			<?php
		}
		?>
        </div>

        <div class="sui-box-footer">
        <div class="sui-actions-left">
        <a href="<?php echo network_admin_url( 'admin.php?page=wdf-scan' ) ?>"
                   class="sui-button sui-button-ghost">
                   <?php _e( "View Report", wp_defender()->domain ) ?>
                   </a>
</div>
<?php if(!wp_defender()->isFree): ?>
<div class="sui-actions-right">
<?php
					if ( !empty( Settings::instance()->notification ) ) {
						switch ( Settings::instance()->frequency ) {
							case '1':
								_e( "Automatic scans are running daily", wp_defender()->domain );
								break;
							case '7':
								_e( "Automatic scans are running weekly", wp_defender()->domain );
								break;
							case '30':
								_e( "Automatic scans are running monthly", wp_defender()->domain );
								break;
						}
					}
					?>
</div>
<?php endif; ?>
</div>
	    <?php
	    return ob_get_clean();
	}

	private function _renderNewScan() {
		ob_start();
		?>
		<div class="sui-box-body">
		<p>
		<?php _e( "Scan your website for file changes, vulnerabilities and injected code and get
        notified about anything suspicious.", wp_defender()->domain ) ?>
</p>
<form id="start-a-scan" method="post" class="scan-frm">
			<?php
			wp_nonce_field( 'startAScan' );
			?>
            <input type="hidden" name="action" value="startAScan"/>
            <button type="submit" class="sui-button sui-button-blue"><?php _e( "Run Scan", wp_defender()->domain ) ?></button>
        </form>
</div>
		<?php
		return ob_get_clean();
	}

	private function _renderScanning( $model ) {
		ob_start();
		$percent = Scan_Api::getScanProgress();
		?>
		<div class="wdf-scanning"></div>
		<div class="sui-box-body">
		<p>
		<?php _e( "Defender is scanning your files for malicious code. This will take a few minutes depending on the size of your website.", wp_defender()->domain ) ?>
</p>
         <div class="sui-progress-block sui-progress-can-close">
                        <div class="sui-progress">
                            <div class="sui-progress-text sui-icon-loader sui-loading">
                                <span><?php echo $percent ?>%</span>
                            </div>
                            <div class="sui-progress-bar">
                                <span style="width: <?php echo $percent ?>%"></span>
                            </div>
                            <form method="post" class="scan-frm">
                                <input type="hidden" name="action" value="cancelScan"/>
								<?php wp_nonce_field( 'cancelScan', '_wpnonce', true ) ?>
                                <button class="sui-button-icon" type="submit">
                                    <i class="sui-icon-close"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="sui-progress-state">
                        <span class="sui-progress-state-text">
                            <?php echo $model->statusText ?>
                        </span>
                    </div>
</div>
        <form method="post" id="process-scan" class="scan-frm">
            <input type="hidden" name="action" value="processScan"/>
			<?php
			wp_nonce_field( 'processScan' );
			?>
        </form>
		<?php
		return ob_get_clean();
	}
}