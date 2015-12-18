<?php

/**
 * Class WPMUDEV_Notifications_Output
 *
 * Loaded on every admin page to handle output of notices
 */
class WPMUDEV_Notifications_Output {

	/**
	 * Constructor registering hooks
	 */
	function __construct() {
		//add_action( 'all_admin_notices', array( &$this, 'upgrade_notice_output' ), 2);
		add_action( 'all_admin_notices', array( &$this, 'old_plugin_check' ) );
		add_action( 'all_admin_notices', array( &$this, 'apikey_notice_output' ) );
		add_action( 'all_admin_notices', array( &$this, 'upfront_notice_output' ) );
		add_action( 'all_admin_notices', array( &$this, 'admin_notice_output' ) );

		add_action( 'admin_footer', array( &$this, 'admin_footer_scripts' ) );
		add_action( 'wp_ajax_wpmudev-dismiss', array( &$this, 'ajax_dismiss' ) );
	}

	/**
	 * Outputs a notice when they havn't registered or entered an api key on first install
	 */
	function apikey_notice_output() {
		global $wpmudev_un;

		if ( current_user_can( 'update_plugins' ) ) {
			if ( ! $wpmudev_un->get_apikey() ) {
				$link = $wpmudev_un->dashboard_url;
				?>
				<div class="wpmudev-message wpdv-connect" id="message">
					<div class="squish">
						<h4>
							<strong><?php _e( 'Get started with WPMU DEV', 'wpmudev' ); ?></strong> &ndash; <?php _e( "it will transform your WordPress experience.", 'wpmudev' ); ?>
							<a id="api-add" class="wpmu-button" href="<?php echo $link; ?>"><i
									class="wdvicon-pencil wdvicon-large"></i> <?php _e( 'Get Started', 'wpmudev' ); ?></a>
						</h4>

						<div class="clear"></div>
					</div>
				</div>
			<?php
			}
		}
	}

	/**
	 * Outputs a notice when an upfront theme is installed but upfront is not
	 */
	function upfront_notice_output() {
		global $wpmudev_un, $current_screen;

		if ( current_user_can( 'install_themes' ) && $current_screen->id != 'update-network' && $current_screen->id != 'update' ) {
			if ( ! $wpmudev_un->is_upfront_installed() && $wpmudev_un->upfront_theme_installed() ) {
				?>
				<div class="wpmudev-message wpdv-connect" id="message">
					<div class="squish">
						<h4>
							<strong><?php _e( 'The Upfront parent theme is missing!', 'wpmudev' ); ?></strong> &ndash; <?php _e( "Please install it now to use your Upfront child themes.", 'wpmudev' ); ?>
							<?php if ( ! $wpmudev_un->get_apikey() ) { //no api key yet
								?><a id="wdv-release-install" href="<?php echo $wpmudev_un->dashboard_url; ?>"
								     class="wpmu-button button-disabled"
								     title="<?php _e( 'Setup your WPMU DEV account to install', 'wpmudev' ); ?>"><i
									class="wdvicon-download-alt wdvicon-large"></i> <?php _e( 'INSTALL', 'wpmudev' ); ?></a><?php
							} else if ( $url = $wpmudev_un->auto_install_url( $wpmudev_un->upfront ) ) {
								?><a id="wdv-release-install" href="<?php echo $url; ?>" class="wpmu-button"><i
									class="wdvicon-download-alt wdvicon-large"></i> <?php _e( 'INSTALL', 'wpmudev' ); ?></a><?php
							} else { //needs to upgrade
								?><a id="wdv-release-install"
								     href="<?php echo apply_filters( 'wpmudev_upgrade_url', 'https://premium.wpmudev.org/membership/' ); ?>"
								     target="_blank" class="wpmu-button"><i
									class="wdvicon-arrow-up wdvicon-large"></i> <?php _e( 'Upgrade to Install', 'wpmudev' ); ?></a><?php
							} ?>
						</h4>

						<div class="clear"></div>
					</div>
				</div>
			<?php
			}
		}
	}

	/**
	 * Handles output of all the normal admin notices for upgrades and marketing
	 */
	function admin_notice_output() {
		global $wpmudev_un, $current_screen;

		if ( ! current_user_can( 'update_plugins' ) || ! $wpmudev_un->allowed_user() || ! $wpmudev_un->get_apikey() || $current_screen->id == 'update-network' || $current_screen->id == 'update' ) {
			return;
		}

		//check delay
		$delay = get_site_option( 'wdp_un_delay' );
		if ( ! $delay ) {
			$delay = time() + 86400;
			update_site_option( 'wdp_un_delay', $delay );
		}
		if ( $delay > time() ) {
			return;
		}

		//handle ad messages
		$data      = $wpmudev_un->get_updates();
		$dismissed = get_site_option( 'wdp_un_dismissed' );
		if ( isset( $data['membership'] ) && $data['membership'] == 'full' ) { //full member
			if ( false == ( $dismissed['id'] == $data['full_notice']['id'] && $dismissed['expire'] > time() ) ) {
				$msg = $data['full_notice']['msg'];
				$id  = $data['full_notice']['id'];
				if ( isset( $data['full_notice']['url'] ) ) {
					$button = '<a id="wdv-upgrade" class="wpmu-button" target="_blank" href="' . esc_url( $data['full_notice']['url'] ) . '"><i class="wdvicon-share-alt wdvicon-large"></i> ' . __( 'Go Now', 'wpmudev' ) . '</a>';
					$class  = 'with-button';
				} else {
					$class  = '';
					$button = '';
				}
			}
		} else if ( isset( $data['membership'] ) && is_numeric( $data['membership'] ) ) { //single member
			if ( false == ( $dismissed['id'] == $data['single_notice']['id'] && $dismissed['expire'] > time() ) ) {
				$msg    = $data['single_notice']['msg'];
				$id     = $data['single_notice']['id'];
				$class  = 'with-button';
				$button = '<a id="wdv-upgrade" class="wpmu-button" target="_blank" href="' . apply_filters( 'wpmudev_upgrade_url', 'https://premium.wpmudev.org/membership/' ) . '"><i class="wdvicon-arrow-up wdvicon-large"></i> ' . __( 'Upgrade Now', 'wpmudev' ) . '</a>';
			}
		} else { //free member
			if ( isset( $data['free_notice'] ) && false == ( $dismissed['id'] == $data['free_notice']['id'] && $dismissed['expire'] > time() ) ) {
				$msg    = $data['free_notice']['msg'];
				$id     = $data['free_notice']['id'];
				$class  = 'with-button';
				$button = '<a id="wdv-upgrade" class="wpmu-button" target="_blank" href="' . apply_filters( 'wpmudev_join_url', 'http://premium.wpmudev.org/join/' ) . '"><i class="wdvicon-arrow-up wdvicon-large"></i> ' . __( 'Upgrade Now', 'wpmudev' ) . '</a>';
			}
		}

		if ( ! empty( $msg ) && ! get_site_option( 'wdp_un_hide_notices' ) ) {
			?>
			<div class="wpmudev-message wpdv-msg" id="message">
				<div class="squish <?php echo $class; ?>">
					<h4 class="<?php echo $class; ?>">
						<?php echo strip_tags( stripslashes( $msg ), '<a><strong>' ); ?>
						<?php echo $button; ?>
					</h4>
					<a class="wpmudev-dismiss" data-key="dismiss" data-id="<?php echo $id; ?>"
					   title="<?php _e( 'Dismiss this notice for one month', 'wpmudev' ); ?>"
					   href="<?php echo $wpmudev_un->dashboard_url; ?>&dismiss=<?php echo $id; ?>"><?php _e( 'Dismiss', 'wpmudev' ); ?></a>

					<div class="clear"></div>
				</div>
			</div>
		<?php
		}

		//show latest project information
		if ( ! get_site_option( 'wdp_un_hide_releases' ) && isset( $data['latest_release'] ) && isset( $data['projects'][ $data['latest_release'] ] ) ) {
			$dismissed_release = get_site_option( 'wdp_un_dismissed_release' );
			$local_projects    = $wpmudev_un->get_local_projects();
			if ( $dismissed_release != $data['latest_release'] && ! isset( $local_projects[ $data['latest_release'] ] ) ) { //if not dismissed or not installed
				$project  = $data['projects'][ $data['latest_release'] ];
				$info_url = ( $project['type'] == 'theme' ) ? $wpmudev_un->themes_url . '#pid=' . $data['latest_release'] : $wpmudev_un->plugins_url . '#pid=' . $data['latest_release'];
				?>
				<div class="wpmudev-new" id="message">
					<div class="dev-widget-content">
						<h4><strong><?php _e( 'New WPMU DEV Release:', 'wpmudev' ); ?></strong></h4>

						<div class="dev-content-wrapper">
							<a id="wdv-release-img" title="<?php _e( 'More Information &raquo;', 'wpmudev' ); ?>"
							   href="<?php echo $info_url; ?>">
								<img src="<?php echo $project['thumbnail']; ?>" width="186" height="105"/>
							</a>
							<h4 id="wdv-release-title"><?php echo esc_html( $project['name'] ); ?></h4>

							<div id="wdv-release-desc"><?php echo esc_html( $project['short_description'] ); ?></div>
							<div class="dev-cta-wrap">
								<?php if ( ! $wpmudev_un->get_apikey() ) { //no api key yet
									?><a id="wdv-release-install" href="<?php echo $wpmudev_un->dashboard_url; ?>"
									     class="wpmu-button button-disabled"
									     title="<?php _e( 'Setup your WPMU DEV account to install', 'wpmudev' ); ?>"><i
										class="wdvicon-download-alt wdvicon-large"></i> <?php _e( 'INSTALL', 'wpmudev' ); ?></a><?php
								} else if ( $url = $wpmudev_un->auto_install_url( $data['latest_release'] ) ) {
									?><a id="wdv-release-install" href="<?php echo $url; ?>" class="wpmu-button"><i
										class="wdvicon-download-alt wdvicon-large"></i> <?php _e( 'INSTALL', 'wpmudev' ); ?></a><?php
								} else if ( $wpmudev_un->user_can_install( $data['latest_release'] ) ) { //has permission, but it's not autoinstallable
									?><a id="wdv-release-install" href="<?php echo esc_url( $project['url'] ); ?>" target="_blank"
									     class="wpmu-button"><i
										class="wdvicon-download wdvicon-large"></i> <?php _e( 'DOWNLOAD', 'wpmudev' ); ?></a><?php
								} else { //needs to upgrade
									?><a id="wdv-release-install"
									     href="<?php echo apply_filters( 'wpmudev_project_upgrade_url', esc_url( 'https://premium.wpmudev.org/wp-login.php?redirect_to=' . urlencode( $project['url'] ) . '#signup' ), $data['latest_release'] ); ?>"
									     target="_blank" class="wpmu-button"><i
										class="wdvicon-arrow-up wdvicon-large"></i> <?php _e( 'Upgrade to Install', 'wpmudev' ); ?></a><?php
								} ?>
								<a id="wdv-release-info"
								   href="<?php echo $info_url; ?>"><?php _e( 'More Information &raquo;', 'wpmudev' ); ?></a>
							</div>
						</div>
						<a class="wpmudev-dismiss" data-key="dismiss-release" data-id="<?php echo $data['latest_release']; ?>"
						   title="<?php _e( 'Dismiss this announcement', 'wpmudev' ); ?>"
						   href="<?php echo $wpmudev_un->dashboard_url; ?>&dismiss-release=<?php echo $data['latest_release']; ?>"><?php _e( 'Dismiss', 'wpmudev' ); ?></a>

						<div class="clear"></div>
					</div>
				</div>
			<?php
			}
		}

	}

	/**
	 * Give a warning if old old version of the plugin is installed.
	 */
	function old_plugin_check() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		if ( function_exists( 'update_notificiations_process' ) ) {
			?>
			<div class="wpmudev-message" id="message">
				<div class="squish">
					<h4>
						<strong><?php _e( 'Whoops!', 'wpmudev' ); ?></strong> &ndash; <?php _e( 'You need to remove the old version of the WPMU DEV Update Notifications plugin! Check for the update-notifications.php file in the /mu-plugins/ folder and delete it.', 'wpmudev' ); ?>
					</h4>
				</div>
			</div>
		<?php
		}
	}

	/**
	 * Put needed JS in footer for ajax notice actions
	 */
	function admin_footer_scripts() {
		?>
		<script type="text/javascript">
			jQuery(function ($) {
				$('.wpmudev-dismiss').click(function () {
					var $link = $(this), data = {'action': 'wpmudev-dismiss'};
					$link.closest('.wpmudev-new, .wpmudev-message, .update-nag').fadeOut('fast');
					data[ $link.attr('data-key') ] = $link.attr('data-id');
					$.post(ajaxurl, data);
					return false;
				});
			});
		</script>
	<?php
	}

	/**
	 * Handles dismiss of notices via ajax
	 */
	function ajax_dismiss() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		global $wpmudev_un;
		$wpmudev_un->handle_dismiss();
		die;
	}
}

new WPMUDEV_Notifications_Output;