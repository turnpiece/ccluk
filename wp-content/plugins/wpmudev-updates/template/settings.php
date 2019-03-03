<?php
/**
 * Dashboard template: Settings page
 *
 * Here the user can manage the settings for the current website and
 * review/change his subscription details.
 *
 * Following variables are passed into the template:
 *   $data (projects data)
 *   $member (user profile data)
 *   $urls (urls of all dashboard menu items)
 *   $membership_type (full|single|free)
 *   $allowed_users (list of all users that can see the WPUDEV Dashboard)
 *   $auto_update (bool. current value of the auto-update setting)
 *   $single_id (int. ID of the single-license project)
 *
 * @since   4.0.0
 * @package WPMUDEV_Dashboard
 */

$can_manage_users = true;
$profile          = $member['profile'];

// Upgrade membership URL.
$url_membership  = $urls->remote_site . 'hub/account/';
$url_api_setting = $urls->remote_site . 'hub/account/';
$url_settings    = $urls->settings_url;

// Render the page header section.
$page_title = __( 'Settings', 'wpmudev' );
$this->render_header( $page_title );

// Adding users is only passible when the admin did not define a hardcoded
// user-list in wp-config.
if ( WPMUDEV_LIMIT_TO_USER ) {
	$can_manage_users = false;
}

?>
<div class="row">

	<div class="col-half">
		<section class="box-membership dev-box">
			<div class="box-title">
			<span class="buttons">
				<a href="<?php echo esc_url( $url_membership ); ?>" class="wpmudui-btn is-ghost is-sm" target="_blank">
					<?php _e( 'Manage Account', 'wpmudev' ); ?>
				</a>
			</span>
				<h3><?php _e( 'Membership', 'wpmudev' ); ?></h3>
			</div>
			<div class="box-content">
				<h4><?php _e( 'Current subscription', 'wpmudev' ); ?></h4>
				<div class="subscription-detail">
					<span class="label"><?php _e( 'Type:', 'wpmudev' ); ?></span>
					<span class="value">
					<?php
					switch ( $membership_type ) {
						case 'full':
							_e( 'Full', 'wpmudev' );
							echo '<i aria-hidden="true" class="status-ok dev-icon dev-icon-radio_checked"></i>';
							break;

						case 'single':
							_e( 'Single', 'wpmudev' );
							echo '<i aria-hidden="true" class="status-ok dev-icon dev-icon-radio_checked"></i>';
							break;

						default:
							_e( 'Free', 'wpmudev' );
							break;
					}
					?>
				</span>
				</div>
				<?php if ( 'single' == $membership_type ) : ?>
					<div class="subscription-detail">
						<span class="label"><?php _e( 'Active Subscription:', 'wpmudev' ); ?></span>
						<span class="value">
					<?php
					$item = WPMUDEV_Dashboard::$site->get_project_infos( $single_id );
					echo esc_html( $item->name );
					?>
				</span>
					</div>
				<?php endif; ?>
				<?php if ( 'Staff' == $profile['title'] ) : ?>
					<div class="subscription-detail">
						<span class="label"><?php _e( 'Status:', 'wpmudev' ); ?></span>
						<span class="value">
							Staff-Hero
							<span class="status-ok" tooltip="<?php echo "Your duty is no easy one:\n\nHelp members in need...\nMake strangers smile...\nFight evil...\nSave kittens!"; ?>">
								<i aria-hidden="true" class="dev-icon dev-icon-logo_alt"></i>
							</span>
						</span>
					</div>
				<?php endif; ?>
				<div class="subscription-detail">
					<span class="label"><?php _e( 'Member since:', 'wpmudev' ); ?></span>
					<span class="value">
					<?php echo esc_html( date_i18n( 'F d, Y', $profile['member_since'] ) ); ?>
				</span>
				</div>
			</div>
		</section>

		<section class="box-analytics dev-box">

			<div class="box-title">
				<h3><?php esc_html_e( 'Analytics', 'wpmudev' ); ?></h3>
			</div>

			<div class="box-content">
				<form method="POST" action="<?php echo esc_url( $url_settings ); ?>">
					<input type="hidden" name="action" value="analytics-setup"/>
					<?php wp_nonce_field( 'analytics-setup', 'hash' ); ?>

					<?php if ( isset( $_GET['failed'] ) ) { ?>
					<div class="wpmudui-alert is-error">
						<p><i aria-hidden="true" class="wpmudui-fi wpmudui-fi-circle-warning"></i> <?php esc_html_e( 'There was an API error, please try again.', 'wpmudev' ); ?></p>
					</div>
					<?php } ?>

					<p><?php esc_html_e( "Add basic analytics tracking that doesn't require any third party integration, and display the data in the WordPress admin dashboard area, the Hub, and Hub Reports.", 'wpmudev' ); ?></p>

					<?php
					if ( $analytics_enabled && is_wpmudev_member() ) {
						$role_names = wp_roles()->get_names();
						$role_name  = isset( $role_names[ $analytics_role ] ) ? $role_names[ $analytics_role ] : 'Administrator';
						?>

						<div class="wpmudui-alert is-success is-standalone">
							<?php if ( is_multisite() ) { ?>
								<p><i aria-hidden="true" class="wpmudui-fi wpmudui-fi-circle-warning"></i> <?php printf( esc_html( 'Analytics are now being tracked and the widget is being displayed to %s and above in subsite admin dashboards.', 'wpmudev' ), esc_html( $role_name ) ); ?></p>
							<?php } else { ?>
								<p><i aria-hidden="true" class="wpmudui-fi wpmudui-fi-circle-warning"></i> <?php printf( esc_html( 'Analytics are now being tracked and the widget is being displayed to %s and above in the admin dashboard.', 'wpmudev' ), esc_html( $role_name ) ); ?></p>
							<?php } //end if multisite ?>
						</div>
						<hr class="wpmud-split-border"/>
						<div class="wpmud-split">
							<div class="half-side">
								<label class="wpmud-split-title"><?php esc_html_e( 'Minimum User Role', 'wpmudev' ); ?></label>
								<span class="wpmud-split-desc"><?php esc_html_e( 'Choose the minimum user role that should be able to view the analytics widget.', 'wpmudev' ); ?></span>
							</div>
							<div class="half-block">
								<select name="analytics_role">
									<?php
									$roles = wp_roles()->roles;
									foreach ( $roles as $key => $role ) {
										// core roles define level_X caps, that's what we'll use to check permissions.
										if ( ! isset( $role['capabilities']['level_0'] ) ) {
											continue;
										}
										?>
										<option <?php selected( $analytics_role, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $role['name'] ); ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
						<?php /* if ( is_multisite() ) { ?>
					<hr class="wpmud-split-border"/>
					<div class="wpmud-split">
						<div class="half-side">
							<label class="wpmud-split-title"><?php esc_html_e( 'Metric Types', 'wpmudev' ); ?></label>
							<span class="wpmud-split-desc"><?php esc_html_e( 'Select the types of analytics the selected User Roles will see in their WordPress Admin area.', 'wpmudev' ); ?></span>
						</div>
						<div class="half-block">
							<ul>
								<li>
									<label class="inline-label" for="page-views"><?php esc_html_e( 'Page views', 'wpmudev' ); ?></label>
									<span class="float-l"><input type="checkbox" id="page-views"/></span>
								</li>
								<li>
									<label class="inline-label" for="unique-page-views"><?php esc_html_e( 'Unique page views', 'wpmudev' ); ?></label>
									<span class="float-l"><input type="checkbox" id="unique-page-views"/></span>
								</li>
								<li>
									<label class="inline-label" for="avg-time-on-page"><?php esc_html_e( 'Avg time on page', 'wpmudev' ); ?></label>
									<span class="float-l"><input type="checkbox" id="avg-time-on-page"/></span>
								</li>
								<li>
									<label class="inline-label" for="bounce-rate"><?php esc_html_e( 'Bounce rate', 'wpmudev' ); ?></label>
									<span class="float-l"><input type="checkbox" id="bounce-rate"/></span>
								</li>
								<li>
									<label class="inline-label" for="exit-rate"><?php esc_html_e( 'Exit rate', 'wpmudev' ); ?></label>
									<span class="float-l"><input type="checkbox" id="exit-rate"/></span>
								</li>
								<li>
									<label class="inline-label" for="avg-gen-time"><?php esc_html_e( 'Avg generation time', 'wpmudev' ); ?></label>
									<span class="float-l"><input type="checkbox" id="avg-gen-time"/></span>
								</li>
							</ul>
						</div>
					</div>
					<?php } // end if multisite */ ?>

						<hr class="wpmud-split-border"/>
						<div class="wpmud-split-buttons">
							<button type="submit" value="deactivate" name="status" class="wpmudui-btn is-ghost is-sm"><?php esc_html_e( 'Deactivate', 'wpmudev' ); ?></button>
							<button type="submit" value="settings" name="status" class="wpmudui-btn is-brand is-sm"><?php esc_html_e( 'Save Settings', 'wpmudev' ); ?></button>
						</div>

					<?php } else { ?>

						<hr class="wpmud-split-border"/>
						<button type="submit" value="activate" name="status" class="wpmudui-btn is-brand is-sm <?php echo ( ! is_wpmudev_member() ) ? 'disabled' : ''; ?>"><?php esc_html_e( 'Activate', 'wpmudev' ); ?></button>

					<?php } ?>
				</form>
			</div>

		</section>

		<section class="box-apikey dev-box">
			<div class="box-title">
			<span class="buttons">
				<a href="<?php echo esc_url( $url_api_setting ); ?>" class="wpmudui-btn is-ghost is-sm" target="_blank">
					<?php _e( 'Manage global API settings', 'wpmudev' ); ?>
				</a>
			</span>
				<h3><?php _e( 'API KEY', 'wpmudev' ); ?></h3>
			</div>
			<div class="box-content">
				<p>
					<?php _e( 'This is your WPMU DEV API Key.', 'wpmudev' ); ?>
				</p>
				<input
						type="text"
						readonly="readonly"
						value="<?php echo esc_attr( strtolower( WPMUDEV_Dashboard::$api->get_key() ) ); ?>"
						class="block disabled apikey sel-all"/>
			</div>
		</section>
	</div>

	<div class="col-half">
		<section class="box-settings dev-box">
			<div class="box-title">
				<h3><?php _e( 'General settings', 'wpmudev' ); ?></h3>
			</div>
			<div class="box-content">
				<p>
				<span class="toggle float-r">
					<input type="checkbox" class="toggle-checkbox" id="chk_autoupdate" name="autoupdate_dashboard" data-action="save-setting-bool" data-hash="<?php echo esc_attr( wp_create_nonce( 'save-setting-bool' ) ); ?>" <?php checked( $auto_update ); ?> />
					<label class="toggle-label" for="chk_autoupdate"></label>
				</span>
					<label class="inline-label" for="chk_autoupdate">
						<?php _e( 'Enable automatic updates of the WPMU DEV Dashboard plugin', 'wpmudev' ); ?>
					</label>
				</p>
			</div>
		</section>

		<section class="box-permissions dev-box">
			<div class="box-title">
				<h3><?php _e( 'Permissions', 'wpmudev' ); ?></h3>
			</div>
			<div class="box-content">
				<ul class="dev-list userlist">
					<li>
						<div>
					<span class="list-label">
					<?php
					if ( $can_manage_users ) {
						_e( 'Control which administrators (manage_options enabled) can access/see the WPMU DEV Dashboard plugin and announcements. Note: ONLY these users will see announcements.', 'wpmudev' );
					} else {
						_e( 'The following admin users can access/see the WPMU DEV Dashboard plugin and announcements. Note: ONLY these users will see announcements.', 'wpmudev' );
					}
					?>
					</span>
						</div>
					</li>
					<?php foreach ( $allowed_users as $user ) : ?>
						<?php
						$remove_url = wp_nonce_url(
							add_query_arg(
								array(
									'user'   => $user['id'],
									'action' => 'admin-remove',
								),
								$url_settings
							),
							'admin-remove',
							'hash'
						);
						?>
						<li class="user-<?php echo esc_attr( $user['id'] ); ?>">
							<div class="has-hover">
					<span class="list-label">
						<a href="<?php echo esc_url( $user['profile_link'] ); ?>">
							<?php echo get_avatar( $user['id'], 40 ); ?>
							<?php echo esc_html( ucwords( $user['name'] ) ); ?>
						</a>
						<?php if ( $can_manage_users && $user['is_me'] ) : ?>
							<span class="dev-label" tooltip="<?php esc_attr_e( 'You cannot remove yourself', 'wpmudev' ); ?>">
							<?php _e( 'You', 'wpmudev' ); ?>
						</span>
						<?php endif; ?>
					</span>
								<span class="list-detail">
						<?php if ( $can_manage_users && ! $user['is_me'] ) : ?>
							<a href="<?php echo esc_url( $remove_url ); ?>" class="one-click button button-text show-on-hover">
							<span class="wpdui-sr-only">Remove user</span>
							<i aria-hidden="true" class="dashicons dashicons-no-alt"></i>
						</a>
						<?php endif; ?>
					</span>
							</div>
						</li>
					<?php endforeach; ?>

					<?php if ( ! $can_manage_users ) : ?>
						<li>
							<div>
								<em class="list-label tc" style="width: 100%">
									<?php _e( 'To manage user permissions here you need to remove the constant <code>WPMUDEV_LIMIT_TO_USER</code> from your wp-config file.', 'wpmudev' ); ?>
								</em>
							</div>
						</li>
					<?php endif; ?>
				</ul>

				<?php if ( $can_manage_users ) : ?>
					<ul class="dev-list top standalone">
						<li>
							<div>
								<form method="POST" action="<?php echo esc_url( $url_settings ); ?>">
									<input type="hidden" name="action" value="admin-add"/>
									<?php wp_nonce_field( 'admin-add', 'hash' ); ?>
									<span class="list-label" style="width: 100%">
							<label for="user-search" class="wpdui-sr-only"><?php esc_attr_e( "Type an admin user's name", 'wpmudev' ); ?></label>
							<input
									type="search"
									name="user"
									placeholder="<?php esc_attr_e( "Type an admin user's name", 'wpmudev' ); ?>"
									id="user-search"
									class="user-search"
									data-hash="<?php echo esc_attr( wp_create_nonce( 'usersearch' ) ); ?>"
									data-empty-msg="<?php esc_attr_e( 'We did not find an admin user with this name...', 'wpmudev' ); ?>"/>
						</span>
									<span class="list-detail">
							<button id="user-add" type="submit" class="wpmudui-btn is-brand one-click">
								<?php _e( 'Add', 'wpmudev' ); ?>
							</button>
						</span>
								</form>
							</div>
						</li>
					</ul>
				<?php endif; ?>
			</div>
		</section>

		<section class="box-branding dev-box">

			<div class="box-title">
				<h3><?php esc_html_e( 'White-label WPMU DEV plugins', 'wpmudev' ); ?></h3>
			</div>

			<div class="box-content">
				<form method="POST" action="<?php echo esc_url( $url_settings ); ?>">
					<input type="hidden" name="action" value="whitelabel-setup"/>
					<?php wp_nonce_field( 'whitelabel-setup', 'hash' ); ?>
					<p><?php esc_html_e( 'Remove WPMU DEV branding from all our plugins, and replace it with your own branding for your clients.', 'wpmudev' ); ?></p>
					<?php
					if ( $whitelabel_settings['enabled'] && is_wpmudev_member() ) : ?>
						<hr class="wpmud-split-border"/>
						<div class="wpmud-split">
							<div class="half-block">
								<label class="wpmud-split-title"><?php esc_html_e( 'Hide WPMU DEV branding', 'wpmudev' ); ?></label>
								<span class="wpmud-split-desc">
								<?php esc_html_e( 'Remove Super Hero images from our plugins entirely, and upload your own logo for the dashboard section of each plugin. Maximum height and width of logo should be 192px and 172px respectively.', 'wpmudev' ); ?>
							</span>
							</div>
							<div class="half-side">
						<span class="toggle">
							<input type="checkbox" class="toggle-checkbox" id="branding_enabled" name="branding_enabled" value="1" <?php checked( $whitelabel_settings['branding_enabled'] ); ?>/>
							<label class="toggle-label" for="branding_enabled"></label>
						</span>
							</div>
						</div>
						<div class="wpmud-split-content" style="display: block">
							<label class="sui-label"><?php esc_html_e( 'Upload Logo (optional)', 'wpmudev' ); ?></label>
							<div class="sui-upload-image wpdmu-media-upload">
								<div class="sui-upload-field">
								<span class="sui-upload-preview">
									<span id="branding_image_preview" style="background-image: url(<?php echo esc_attr( $whitelabel_settings['branding_image'] ); ?>);"></span>
								</span>
									<input type="url"
									       placeholder="<?php esc_html_e( 'Click browse to add image...', 'wpmudev' ); ?>"
									       name="branding_image"
									       id="branding_image"
									       readonly="readonly"
									       value="<?php echo esc_attr( $whitelabel_settings['branding_image'] ); ?>">
									<button class="wpmudui-btn is-ghost is-sm wpmud-clear-image-input" data-input-id="branding_image" data-preview-id="branding_image_preview">
										<?php esc_html_e( 'Clear', 'wpmudev' ); ?>
									</button>
								</div>
								<button class="wpmudui-btn wpmud-media-library"
								        data-frame-title="<?php esc_html_e( 'Select or Upload Media for Branding Logo', 'wpmudev' ); ?>"
								        data-button-text="<?php esc_html_e( 'Use this as Branding Logo', 'wpmudev' ); ?>"
								        data-input-id="branding_image"
								        data-preview-id="branding_image_preview">
									<?php esc_html_e( 'Browse', 'wpmudev' ); ?>
								</button>
							</div>
							<span class="sui-description"><?php esc_html_e( 'This Logo will appear only in the dashboard section of each plugin', 'wpmudev' ); ?></span>
						</div>
						<hr class="wpmud-split-border"/>
						<div class="wpmud-split">
							<div class="half-block">
								<label class="wpmud-split-title"><?php esc_html_e( 'Change Footer Text', 'wpmudev' ); ?></label>
								<span class="wpmud-split-desc"><?php esc_html_e( 'Remove or replace the default WPMU DEV footer text from all plugin screens.', 'wpmudev' ); ?></span>
							</div>
							<div class="half-side">
						<span class="toggle">
							<input type="checkbox" class="toggle-checkbox" id="footer_enabled" name="footer_enabled" value="1" <?php checked( $whitelabel_settings['footer_enabled'] ); ?>/>
							<label class="toggle-label" for="footer_enabled"></label>
						</span>
							</div>
						</div>
						<div class="wpmud-split-content" style="display: block">
							<label class="sui-label" for="footer_text"><?php esc_html_e( 'Footer text', 'wpmudev' ); ?></label>
							<input type="text"
							       class="sui-field"
							       placeholder="<?php esc_html_e( 'Your brand name', 'wpmudev' ); ?>"
							       name="footer_text"
							       id="footer_text"
							       value="<?php echo esc_attr( $whitelabel_settings['footer_text'] ); ?>">
							<span class="sui-description"><?php esc_html_e( 'Leave the field empty to hide the footer completely', 'wpmudev' ); ?></span>
						</div>
						<hr class="wpmud-split-border"/>
						<div class="wpmud-split">
							<div class="half-block">
								<label class="wpmud-split-title"><?php esc_html_e( 'Hide Documentation Links', 'wpmudev' ); ?></label>
								<span class="wpmud-split-desc"><?php esc_html_e( 'Remove all references to WPMU DEV documentation throughout plugins.', 'wpmudev' ); ?></span>
							</div>
							<div class="half-side">
						<span class="toggle">
							<input type="checkbox" class="toggle-checkbox" id="doc_links_enabled" name="doc_links_enabled" value="1" <?php checked( $whitelabel_settings['doc_links_enabled'] ); ?>/>
							<label class="toggle-label" for="doc_links_enabled"></label>
						</span>
							</div>
						</div>
						<hr class="wpmud-split-border"/>
						<div class="wpmud-split-buttons">
							<button type="submit" value="deactivate" name="status" class="wpmudui-btn is-ghost is-sm"><?php esc_html_e( 'Deactivate', 'wpmudev' ); ?></button>
							<button type="submit" value="settings" name="status" class="wpmudui-btn is-brand is-sm"><?php esc_html_e( 'Save Settings', 'wpmudev' ); ?></button>
						</div>
					<?php else: ?>
						<button type="submit" value="activate" name="status" class="wpmudui-btn is-brand is-sm <?php echo ( ! is_wpmudev_member() ) ? 'disabled' : ''; ?>"><?php esc_html_e( 'Activate', 'wpmudev' ); ?></button>
					<?php endif; ?>
				</form>
			</div>

		</section>
	</div>

</div>
<?php $this->load_template('footer'); ?>