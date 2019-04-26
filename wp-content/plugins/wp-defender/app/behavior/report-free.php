<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Module\Scan\Model\Settings;

class Report_Free extends Behavior {
	public function renderReportWidget() {
		?>
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    <i class="sui-icon-graph-line" aria-hidden="true"></i>
					<?php _e( "Reporting", wp_defender()->domain ) ?>
                </h3>
            </div>
            <div class="sui-box-body sui-upsell-items">
                <div class="sui-box-settings-row no-margin-bottom">
                    <p><?php _e( "Get tailored security reports delivered to your inbox so you don’t have to worry about checking in.", wp_defender()->domain ) ?></p>
                </div>
                <div class="sui-field-list no-border">
                    <div class="sui-field-list-body">
                        <div class="sui-field-list-item">
                            <label class="sui-field-list-item-label">
                                <small><strong><?php _e( "File Scanning", wp_defender()->domain ) ?></strong>
                                </small>
                            </label>
                            <span class="sui-tag sui-tag-disabled"><?php _e( "Inactive", wp_defender()->domain ) ?></span>
                        </div>
                        <div class="sui-field-list-item">
                            <label class="sui-field-list-item-label">
                                <small><strong><?php _e( "IP Lockouts", wp_defender()->domain ) ?></strong></small>
                            </label>
                            <span class="sui-tag sui-tag-disabled"><?php _e( "Inactive", wp_defender()->domain ) ?></span>
                        </div>
                        <div class="sui-field-list-item">
                            <label class="sui-field-list-item-label">
                                <small><strong><?php _e( "Audit Logging", wp_defender()->domain ) ?></strong>
                                </small>
                            </label>
                            <span class="sui-tag sui-tag-disabled"><?php _e( "Inactive", wp_defender()->domain ) ?></span>
                        </div>
                    </div>
                </div>
                <div class="sui-box-settings-row sui-upsell-row">
                    <img class="sui-image sui-upsell-image"
                         src="<?php echo wp_defender()->getPluginUrl() . 'assets/img/dev-man-pre.svg' ?>">
                    <div class="sui-upsell-notice">
                        <p>
							<?php
							printf( __( "Automated reports are included in a WPMU DEV membership along with 100+ plugins & themes, 24/7 support and lots of handy site management tools. <a href='%s'>Try it all absolutely free.</a>", wp_defender()->domain ), Utils::instance()->campaignURL( 'defender_dash_reports_upsell_link' ) )
							?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
		<?php
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
		$isPre    = Utils::instance()->getAPIKey();
		$settings = \WP_Defender\Module\Audit\Model\Settings::instance();
		$active   = $settings->notification;
		if ( ! $isPre || ! $active ) {
			return null;
		}

		$toolstip = sprintf( __( "Audit reports are active scheduled to send %s", wp_defender()->domain ),
			$settings->frequency == 1 ? $this->frequencyToText( $settings->frequency ) . '/' . strftime( '%I:%M %p', strtotime( $settings->time ) ) : $this->frequencyToText( $settings->frequency ) . '/' . $settings->day . '/' . strftime( '%I:%M %p', strtotime( $settings->time ) ) );
		$toolstip = strlen( $toolstip ) ? ' tooltip="' . esc_attr( $toolstip ) . '" ' : null;

		return $toolstip;
	}

	private function getLockoutTooltips() {
		$isPre    = Utils::instance()->getAPIKey();
		$settings = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
		$active   = $settings->report;
		if ( ! $isPre || ! $active ) {
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
				//param not from the button on frontend, log it
				error_log( sprintf( 'Unexpected value %s from IP %s', $freq, Utils::instance()->getUserIp() ) );
				break;
		}

		return $text;
	}
}