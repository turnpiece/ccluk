<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Module\Scan\Model\Settings;

class Report extends Behavior {
	public function renderReportWidget() {
		?>
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    <i class="sui-icon-graph-line" aria-hidden="true"></i>
					<?php _e( "Reporting", wp_defender()->domain ) ?>
                </h3>
            </div>
            <div class="sui-box-body no-padding-bottom">
                <p><?php _e( "Get tailored security reports delivered to your inbox so you don’t have to worry about checking in.", wp_defender()->domain ) ?></p>
                <div class="sui-field-list sui-flushed no-border">
                    <div class="sui-field-list-body">
                        <div class="sui-field-list-item">
                            <label class="sui-field-list-item-label">
                                <small><strong><?php _e( "File Scanning", wp_defender()->domain ) ?></strong></small>
                            </label>
							<?php echo $this->getScanReport() ?>
                        </div>
                        <div class="sui-field-list-item">
                            <label class="sui-field-list-item-label">
                                <small><strong><?php _e( "IP Lockouts", wp_defender()->domain ) ?></strong></small>
                            </label>
							<?php echo $this->getIpLockoutReport() ?>
                        </div>
                        <div class="sui-field-list-item">
                            <label class="sui-field-list-item-label">
                                <small><strong><?php _e( "Audit Logging", wp_defender()->domain ) ?></strong></small>
                            </label>
							<?php echo $this->getAuditReport() ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sui-box-footer">
                <div class="sui-center-box no-padding-bottom">
                    <p class="sui-p-small">
						<?php printf( __( "You can also <a target='_blank' href=\"%s\">create PDF reports</a> to send to your clients via The Hub.", wp_defender()->domain ), "https://premium.wpmudev.org/reports/" ) ?>
                    </p>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * @return string|void
	 */
	public function getIpLockoutReport() {
		$settings = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
		$text     = "";
		if ( $settings->report ) {
			switch ( $settings->report_frequency ) {
				case '1':
					$text = __( "Daily", wp_defender()->domain );
					break;
				case '7':
					$text = __( "Weekly", wp_defender()->domain );
					break;
				case '30':
					$text = __( "Monthly", wp_defender()->domain );
					break;
				default:
					error_log( sprintf( 'Unexpected value %s', $settings->report_frequency ) );
					break;
			}
			$text = '<span class="sui-tag sui-tag-blue">' . $text . '</span>';
		} else {
			$text = '<span class="sui-tag sui-tag-disabled">' . __( "Inactive", wp_defender()->domain ) . '</span>';
		}

		return $text;
	}

	/**
	 * @return string|void
	 */
	public function getAuditReport() {
		$settings = \WP_Defender\Module\Audit\Model\Settings::instance();
		if ( $settings->enabled && $settings->notification ) {
			switch ( $settings->frequency ) {
				case '1':
					$text = __( "Daily", wp_defender()->domain );
					break;
				case '7':
					$text = __( "Weekly", wp_defender()->domain );
					break;
				case '30':
					$text = __( "Monthly", wp_defender()->domain );
					break;
				default:
					error_log( sprintf( 'Unexpected value %s', $settings->frequency ) );
					break;
			}
			$text = '<span class="sui-tag sui-tag-blue">' . $text . '</span>';
		} else {
			$text = '<span class="sui-tag sui-tag-disabled">' . __( "Inactive", wp_defender()->domain ) . '</span>';
		}

		return $text;
	}

	private function getScanReport() {
		if ( Settings::instance()->report ) {
			$text = "";
			switch ( Settings::instance()->frequency ) {
				case '1':
					$text = __( "Daily", wp_defender()->domain );
					break;
				case '7':
					$text = __( "Weekly", wp_defender()->domain );
					break;
				case '30':
					$text = __( "Monthly", wp_defender()->domain );
					break;
				default:
					error_log( sprintf( 'Unexpected value %s', Settings::instance()->frequency ) );
					break;
			}

			return '<span class="sui-tag sui-tag-blue">' . $text . '</span>';
		} else {
			return '<span class="sui-tag sui-tag-disabled">' . __( "Inactive", wp_defender()->domain ) . '</span>';
		}
	}

	/**
	 * @return null|string
	 */
	private function getScanToolTip() {
		$isPre    = Utils::instance()->getAPIKey();
		$settings = Settings::instance();
		$active   = $settings->notification;
		if ( ! $isPre || ! $active ) {
			return null;
		}

		$toolstip = sprintf( __( "Scan reports are active scheduled to send %s", wp_defender()->domain ),
			$settings->frequency == 1 ? $this->frequencyToText( $settings->frequency ) . '/' . strftime( '%I:%M %p', strtotime( $settings->time ) ) : $this->frequencyToText( $settings->frequency ) . '/' . $settings->day . '/' . strftime( '%I:%M %p', strtotime( $settings->time ) ) );
		$toolstip = strlen( $toolstip ) ? ' tooltip="' . esc_attr( $toolstip ) . '" ' : null;

		return $toolstip;
	}

	private function getAuditToolTip() {
		$settings = \WP_Defender\Module\Audit\Model\Settings::instance();
		$active   = $settings->notification && $settings->enabled;
		if ( ! $active ) {
			return null;
		}

		$toolstip = sprintf( __( "Audit reports are active scheduled to send %s", wp_defender()->domain ),
			$settings->frequency == 1 ? $this->frequencyToText( $settings->frequency ) . '/' . strftime( '%I:%M %p', strtotime( $settings->time ) ) : $this->frequencyToText( $settings->frequency ) . '/' . $settings->day . '/' . strftime( '%I:%M %p', strtotime( $settings->time ) ) );
		$toolstip = strlen( $toolstip ) ? ' tooltip="' . esc_attr( $toolstip ) . '" ' : null;

		return $toolstip;
	}

	private function getLockoutTooltips() {
		$settings = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
		$active   = $settings->report && ( $settings->detect_404 || $settings->login_protection );
		if ( ! $active ) {
			return null;
		}

		$toolstip = sprintf( __( "Lockout reports are active scheduled to send %s", wp_defender()->domain ),
			$settings->report_frequency == 1 ? $this->frequencyToText( $settings->report_frequency ) . '/' . strftime( '%I:%M %p', strtotime( $settings->report_time ) ) : $this->frequencyToText( $settings->report_frequency ) . '/' . $settings->report_day . '/' . strftime( '%I:%M %p', strtotime( $settings->report_time ) ) );
		$toolstip = strlen( $toolstip ) ? ' tooltip="' . esc_attr( $toolstip ) . '" ' : null;

		return $toolstip;
	}

	/**
	 * @param $freq
	 *
	 * @return string
	 */
	private function frequencyToText( $freq ) {
		$text = '';
		switch ( $freq ) {
			case 1:
				$text = __( "daily", wp_defender()->domain );
				break;
			case 7:
				$text = __( "weekly", wp_defender()->domain );
				break;
			case 30:
				$text = __( "monthly", wp_defender()->domain );
				break;
			default:
				error_log( sprintf( 'Unexpected value %s', $freq ) );
				break;
		}

		return $text;
	}
}