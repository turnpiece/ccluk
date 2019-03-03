<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Component\Error_Code;

class Blacklist extends Behavior {
	const CACHE_KEY = 'wpdefender_blacklist_status', CACHE_TIME = 1800;
	private $end_point = "https://premium.wpmudev.org/api/defender/v1/blacklist-monitoring";

	public function renderBlacklistWidget() {
		if ( wp_defender()->isFree == false ) {
			$this->_renderPlaceholder();
		} else {
			$this->_renderFree();
		}
	}

	private function _renderPlaceholder() {
		?>
        <div class="sui-box">
            <div class="wd-overlay">
                <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
            </div>
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    <i class="sui-icon-target" aria-hidden="true"></i>
					<?php _e( "Blacklist Monitor", wp_defender()->domain ) ?>
                </h3>
            </div>
            <div class="sui-box-body">
                <p>
					<?php _e( "Automatically check if you’re on Google’s blacklist every 6 hours. If something’s wrong, we’ll let you know.", wp_defender()->domain ) ?>
                </p>
                <div class="sui-notice sui-notice-info">
                    <p>
						<?php _e( "Automatically check if you’re on Google’s blacklist every 6 hours. If something’s
                    wrong, we’ll let you know via email.", wp_defender()->domain ) ?>
                    </p>
                </div>
                <div class="sui-center-box no-padding-bottom">
                    <p class="sui-p-small">
						<?php printf( __( "Want to know more about blacklisting? <a href=\"%s\">Read this article.</a>", wp_defender()->domain ), "https://premium.wpmudev.org/blog/get-off-googles-blacklist/" ) ?>
                    </p>
                </div>
                <form method="post" class="blacklist-widget">
                    <input type="hidden" name="action" value="blacklistWidgetStatus"/>
					<?php wp_nonce_field( 'blacklistWidgetStatus' ) ?>
                </form>
            </div>
        </div>
		<?php
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
            <div class="sui-box-body">

            </div>
            <div class="box-title">
                <span class="span-icon icon-blacklist"></span>
                <h3><?php _e( "BLACKLIST MONITOR", wp_defender()->domain ) ?></h3>
                <a href="#pro-feature" rel="dialog"
                   class="button button-small button-pre"
                   tooltip="<?php esc_attr_e( "Try Defender Pro free today", wp_defender()->domain ) ?>">
					<?php _e( "PRO FEATURE", wp_defender()->domain ) ?></a>
            </div>
            <div class="box-content">
                <div class="line">
					<?php _e( "Automatically check if you’re on Google’s blacklist every 6 hours. If something’s
                    wrong, we’ll let you know via email.", wp_defender()->domain ) ?>
                </div>
                <a href="#pro-feature" rel="dialog"
                   class="button button-green button-small"><?php esc_html_e( "Upgrade to Pro", wp_defender()->domain ) ?></a>
            </div>
        </div>
		<?php
	}


	public function toggleStatus( $status = null, $format = true ) {
		$api = Utils::instance()->getAPIKey();
		if ( ! $api ) {
			wp_send_json_error( array(
				'message' => __( "A WPMU DEV subscription is required for blacklist monitoring", wp_defender()->domain )
			) );
		}
		$status   = get_site_transient( self::CACHE_KEY );
		$endpoint = $this->end_point . '?domain=' . Utils::instance()->stripProtocol( network_site_url() );
		if ( intval( $status ) === - 1 ) {
			$result = Utils::instance()->devCall( $endpoint, array(), array(
				'method' => 'POST'
			), true );
			//re update status
			set_site_transient( self::CACHE_KEY, 1, self::CACHE_TIME );
		} else {
			$result = Utils::instance()->devCall( $endpoint, array(), array(
				'method' => 'DELETE'
			), true );
			set_site_transient( self::CACHE_KEY, - 1, self::CACHE_TIME );
		}

		if ( $format == false ) {
			return;
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array(
				'message' => __( "Whoops, it looks like something went wrong. Details: ", wp_defender()->domain ) . $result->get_error_message()
			) );
		}

		$this->pullBlackListStatus();
	}

	private function _renderDisabled() {
		ob_start();
		?>
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    <i class="sui-icon-target" aria-hidden="true"></i>
					<?php _e( "Blacklist Monitor", wp_defender()->domain ) ?>
                </h3>
            </div>
            <div class="sui-box-body">
                <div>
					<?php _e( "Automatically check if you’re on Google’s blacklist every 6 hours. If something’s
                    wrong, we’ll let you know via email.", wp_defender()->domain ) ?>
                </div>
                <form method="post" class="toggle-blacklist-widget margin-top-30">
                    <input type="hidden" name="action" value="toggleBlacklistWidget"/>
					<?php wp_nonce_field( 'toggleBlacklistWidget' ) ?>
                    <button type="submit"
                            class="sui-button sui-button-blue"><?php _e( "Activate", wp_defender()->domain ) ?>
                    </button>
                </form>
            </div>
        </div>
		<?php
		return ob_get_clean();
	}

	private function _renderError( $error ) {
		ob_start();
		?>
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-header-title">
                    <i class="sui-icon-target" aria-hidden="true"></i>
					<?php _e( "Blacklist Monitor", wp_defender()->domain ) ?>
                </h3>
            </div>
            <div class="sui-box-body">
                <div class="sui-notice sui-notice-error">
                    <p>
						<?php echo $error->get_error_message() ?>
                        <a href="<?php echo network_admin_url( "admin.php?page=wp-defender" ) ?>"
                           class="sui-button"><?php _e( "Try Again", wp_defender()->domain ) ?></a>
                    </p>
                </div>
            </div>
        </div>
		<?php
		return ob_get_clean();
	}

	private function _renderResult( $status ) {
		ob_start();
		?>
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
                    <i class="sui-icon-target" aria-hidden="true"></i>
					<?php _e( "Blacklist Monitor", wp_defender()->domain ) ?>
                </h3>
                <div class="sui-actions-right">
                    <label class="sui-toggle">
                        <input type="checkbox" checked="checked" name="enabled" value="1" class="toggle-checkbox"
                               id="toggle_blacklist">
                        <span class="sui-toggle-slider"></span>
                    </label>
                    <form method="post" class="toggle-blacklist-widget">
                        <input type="hidden" name="action" value="toggleBlacklistWidget"/>
						<?php wp_nonce_field( 'toggleBlacklistWidget' ) ?>
                    </form>
                </div>
            </div>
            <div class="sui-box-body">
                <p>
					<?php _e( " Automatically check if you’re on Google’s blacklist every 6 hours. If something’s
                    wrong, we’ll let you know via email.", wp_defender()->domain ) ?>
                </p>
				<?php if ( $status === 0 ): ?>
                    <div class="sui-notice sui-notice-error">
                        <p>
							<?php _e( "Your domain is currently on Google’s blacklist. Check out the article below to find out how to fix up your domain.", wp_defender()->domain ) ?>
                        </p>
                    </div>
				<?php else: ?>
                    <div class="sui-notice sui-notice-success">
                        <p>
							<?php _e( 'Your domain is currently clean.', wp_defender()->domain ) ?>
                        </p>
                    </div>
				<?php endif; ?>
                <div class="sui-center-box no-padding-bottom">
                    <p class="sui-p-small">
						<?php printf( __( "Want to know more about blacklisting? <a href=\"%s\">Read this article.</a>", wp_defender()->domain ), "https://premium.wpmudev.org/blog/get-off-googles-blacklist/" ) ?>
                    </p>
                </div>
            </div>
        </div>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param bool $format
	 *
	 * @return int|\WP_Error
	 */
	public function pullBlackListStatus( $format = true ) {
		$currStatus = get_site_transient( self::CACHE_KEY );
		if ( $currStatus === false ) {
			$currStatus = $this->_pullStatus();
			set_site_transient( self::CACHE_KEY, $currStatus, self::CACHE_TIME );
		}
		if ( $format == false ) {
			return $currStatus;
		}
		if ( is_wp_error( $currStatus ) ) {
			$html = $this->_renderError( $currStatus );
		} elseif ( intval( $currStatus ) === - 1 ) {
			$html = $this->_renderDisabled();
		} else {
			$html = $this->_renderResult( $currStatus );
		}

		wp_send_json_success( array(
			'html' => $html
		) );
	}

	/**
	 * @return int|\WP_Error
	 */
	private function _pullStatus() {
		$endpoint = $this->end_point . '?domain=' . network_site_url();
		$result   = Utils::instance()->devCall( $endpoint, array(), array(
			'method'  => 'GET',
			'timeout' => 5
		), true );
		if ( is_wp_error( $result ) ) {
			//this mean error when firing to API
			return new \WP_Error( Error_Code::API_ERROR, $result->get_error_message() );
		}
		$response_code = wp_remote_retrieve_response_code( $result );
		$body          = wp_remote_retrieve_body( $result );
		$body          = json_decode( $body, true );
		if ( $response_code == 412 ) {
			//this mean disable
			return - 1;
		} elseif ( isset( $body['services'] ) && is_array( $body['services'] ) ) {
			$status = 1;
			foreach ( $body['services'] as $service ) {
				if ( $service['blacklisted'] == true && $service['last_checked'] != false ) {
					$status = 0;
					break;
				}
			}

			return $status;
		} else {
			//fallbacl error
			return new \WP_Error( Error_Code::INVALID, esc_html__( "Something wrong happened, please try again.", wp_defender()->domain ) );
		}
	}
}