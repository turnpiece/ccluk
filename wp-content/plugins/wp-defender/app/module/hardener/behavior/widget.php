<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Module\Hardener\Model\Settings;

class Widget extends Behavior {
	public function renderHardenerWidget() {
		$issues = Settings::instance()->getIssues();
		$count  = count( $issues );
		$issues = array_slice( $issues, 0, 3 );
		?>
        <div class="sui-box hardener-widget">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    <i class="sui-icon-wrench-tool" aria-hidden="true"></i>
					<?php _e( "Security Tweaks", wp_defender()->domain ) ?>
                </h3>
				<?php
				if ( $count ): ?>
                    <div class="sui-actions-left">
                        <div class="sui-tag sui-tag-warning">
							<?php echo $count ?>
                        </div>
                    </div>
				<?php endif; ?>
            </div>
            <div class="sui-box-body">
                <p>
					<?php _e( "Defender checks for basic security tweaks you can make to enhance your website’s defense against hackers and bots.", wp_defender()->domain ) ?>
                </p>
				<?php if ( $count ): ?>

				<?php else: ?>
                    <div class="sui-notice sui-notice-success">
                        <p>
							<?php
							_e( "You’ve actioned all of the recommended security tweaks.", wp_defender()->domain )
							?>
                        </p>
                    </div>
				<?php endif; ?>
            </div>
			<?php if ( $count ): ?>
                <div class="sui-accordion sui-accordion-flushed no-border-bottom">
					<?php foreach ( $issues as $issue ): ?>
                        <div class="sui-accordion-item sui-warning"
                             onClick="window.location = '<?php echo network_admin_url( 'admin.php?page=wdf-hardener#' . $issue::$slug ) ?>'">
                            <div class="sui-accordion-item-header">
                                <div class="sui-accordion-item-title">
                                    <i aria-hidden="true" class="sui-icon-warning-alert sui-warning"></i>
									<?php echo $issue->getTitle() ?>
                                    <div class="sui-actions-right">
                                        <i class="sui-icon-chevron-right" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
					<?php endforeach; ?>
                </div>
			<?php endif; ?>
            <div class="sui-box-footer">
                <div class="sui-actions-left">
                    <a href="<?php echo network_admin_url( 'admin.php?page=wdf-hardener' ) ?>"
                       class="sui-button sui-button-ghost">
                        <i class="sui-icon-eye" aria-hidden="true"></i>
						<?php _e( "View All", wp_defender()->domain ) ?>
                    </a>
                </div>
            </div>
        </div>
		<?php
	}

	private function _renderNew() {

	}

	private function _render() {

	}
}