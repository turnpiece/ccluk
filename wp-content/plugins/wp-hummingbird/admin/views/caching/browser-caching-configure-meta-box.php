<?php
/**
 * Browser caching meta box.
 *
 * @package Hummingbird
 *
 * @var array  $results            Current report.
 * @var array  $labels             List of labels for titles.
 * @var array  $human_results      Current report in readable format.
 * @var array  $expires            Current expiration value settings.
 * @var bool   $cf_active          CloudFlare status.
 * @var bool   $show_cf_notice     Show CloudFlare notice.
 * @var bool   $cf_server          Do we detect CloudFlare headers.
 * @var int    $cf_current         CloudFlare expiration value.
 * @var string $cf_disable_url     CloudFlare deactivate URL.
 * @var string $server_type        Current server type.
 * @var array  $snippets           Code snippets for servers.
 * @var bool   $htaccess_written   File .htaccess is written.
 * @var bool   $htaccess_writable  File .htaccess is writable.
 * @var bool   $already_enabled    Caching is enabled.
 * @var bool   $all_expiry         All expiry values the same.
 * @var string $enable_link        Activate automatic caching link.
 * @var string $disable_link       Disable automatic caching link.
 * @var string $recheck_expiry_url Url to recheck status.
 */

?>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Server Type', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php
			esc_html_e(
				'Choose your server type so Hummingbird can give you the
			rules to apply caching.',
				'wphb'
			);
			?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<label for="wphb-server-type" class="sui-label"><?php esc_html_e( 'Server type', 'wphb' ); ?></label>
		<?php
		WP_Hummingbird_Utils::get_servers_dropdown(
			array(
				'class'    => 'server-type',
				'selected' => $server_type,
			)
		);
		if ( ! $cf_active && ! $show_cf_notice ) :
			?>
			<div class="sui-notice sui-notice-sm">
				<p>
					<?php
					printf(
						/* translators: %1$s: server type, %2$s: opening a tag, %3$s: closing a tag */
						esc_html__(
							"We've automatically detected your server type is %1\$s. If this is incorrect, manually
                        select your server type to generate the relevant rules and instructions. If you are using Cloudflare
                        %2\$sconnect your account%3\$s to control your cache settings from here.",
							'wphb'
						),
						ucfirst( esc_html( $server_type ) ),
						'<a href="#" class="connect-cloudflare-link">',
						'</a>'
					);
					?>
				</p>
			</div>
		<?php elseif ( ! $cf_active ) : ?>
			<div class="wphb-cf-detected-notice sui-notice sui-notice-sm">
				<p>
					<?php
					esc_html_e(
						'We’ve detected you’re using Cloudflare which handles browser caching
					for you. You can control your CloudFlare settings from Hummingbird by connecting
					your account below.',
						'wphb'
					);
					?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Expiry Time', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php
			esc_html_e(
				'Please choose your desired expiry time. Google recommends 8 days
			as a good benchmark.',
				'wphb'
			);
			?>
		</span>
	</div><!-- end sui-box-settings-col-1 -->
	<div class="sui-box-settings-col-2">
		<form method="post" id="expiry-settings">
			<input type="hidden" class="hb-server-type" name="hb_server_type" value="<?php echo esc_attr( $server_type ); ?>">
			<?php wp_nonce_field( 'wphb-caching' ); ?>
			<div class="sui-side-tabs sui-tabs">
				<?php if ( ! $cf_active && ! $cf_server ) : ?>
					<div data-tabs>
						<div class="<?php echo $all_expiry ? 'active' : ''; ?>" data-name="expiry-set-type" data-value="all">
							<?php esc_html_e( 'All file types', 'wphb' ); ?>
						</div>
						<div class="<?php echo $all_expiry ? '' : 'active'; ?>" data-name="expiry-set-type" data-value="single">
							<?php esc_html_e( 'Individual file types', 'wphb' ); ?>
						</div>
					</div>
					<div data-panes>
						<div class="sui-tab-boxed <?php echo $different_expiry ? 'active' : ''; ?>">
							<label class="sui-label">
								<?php esc_html_e( 'JavaScript, CSS, Media, Images', 'wphb' ); ?>
							</label>
							<?php
							WP_Hummingbird_Utils::get_caching_frequencies_dropdown(
								array(
									'name'      => 'set-expiry-all',
									'class'     => 'wphb-expiry-select',
									'selected'  => $expires['css'],
									'data-type' => 'all',
								)
							);
							?>
						</div>
						<div class="sui-tab-boxed <?php echo $different_expiry ? '' : 'active'; ?>">
							<?php foreach ( $human_results as $type => $result ) : ?>
								<div class="sui-form-field">
									<label class="sui-label">
										<?php echo esc_html( $labels[ $type ] ); ?>
									</label>
									<?php
									WP_Hummingbird_Utils::get_caching_frequencies_dropdown(
										array(
											'name'      => "set-expiry-{$type}",
											'class'     => 'wphb-expiry-select',
											'selected'  => $expires[ $type ],
											'data-type' => $type,
										)
									);
									?>
								</div>
							<?php endforeach; ?>
						</div>

						<div class="wphb-expiry-changes sui-notice sui-notice-warning sui-notice-sm sui-margin-top" style="display: none" id="wphb-expiry-change-notice">
							<p>
								<?php if ( $htaccess_writable && $already_enabled ) : ?>
									<?php esc_html_e( 'You’ve made changes to your browser cache settings. You need to update your .htaccess file with the newly generated code below.', 'wphb' ); ?>
									<br />
									<a class="sui-button update-htaccess" id="view-snippet-code" >
										<?php esc_attr_e( 'View code', 'wphb' ); ?>
									</a>
								<?php elseif ( $htaccess_writable && $htaccess_written ) : ?>
									<?php esc_html_e( 'You’ve made changes to your browser cache settings. You need to update your .htaccess for the new settings to take effect.', 'wphb' ); ?>
									<br />
									<input type="submit" class="sui-button update-htaccess" name="submit" value="<?php esc_attr_e( 'Update .htaccess', 'wphb' ); ?>"/>
									<span class="spinner standalone"></span>
								<?php else : ?>
									<?php esc_html_e( 'Code snippet updated.', 'wphb' ); ?>
								<?php endif; ?>
							</p>
						</div><!-- end wphb-expiry-change-notice -->
					</div><!-- end data-panes -->
				<?php elseif ( $cf_active || $cf_server ) : ?>
					<div class="sui-border-frame">
						<label class="sui-label">
							<?php esc_html_e( 'JavaScript, CSS, Media, Images', 'wphb' ); ?>
						</label>
						<?php
						WP_Hummingbird_Utils::get_caching_frequencies_dropdown(
							array(
								'name'      => 'set-expiry-all',
								'class'     => 'wphb-expiry-select',
								'selected'  => $cf_current,
								'data-type' => 'all',
							),
							true
						);
						?>
						<div id="wphb-expiry-change-notice" style="display: none">
							<?php if ( ! $cf_active && $cf_server ) : ?>
								<div class="wphb-cf-detected-notice sui-notice sui-notice-sm">
									<p>
										<?php esc_html_e( 'Note: You need to connect your CloudFlare account below for your selected expiry time to take effect.', 'wphb' ); ?>
									</p>
								</div>
							<?php elseif ( $cf_active ) : ?>
								<div class="wphb-expiry-changes sui-notice sui-notice-warning sui-notice-sm sui-margin-top">
									<p>
										<?php esc_html_e( 'You’ve made changes to your browser cache settings. You need to save changes for the new settings to take effect.', 'wphb' ); ?>
										<br />
										<input type="submit" class="sui-button update-htaccess" name="submit" value="<?php esc_attr_e( 'Save Changes', 'wphb' ); ?>" id="set-cf-expiry-button"/>
										<span class="spinner standalone"></span>
									</p>
								</div>
							<?php endif; ?>
						</div><!-- end wphb-expiry-change-notice -->
					</div><!-- end sui-border-frame -->
				<?php endif; ?>
			</div><!-- end sui-side-tabs -->
		</form>
	</div><!-- end sui-box-settings-col-2 -->
</div><!-- end row -->

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Setup', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Follow the instructions provided to enable browser caching.', 'wphb' ); ?>
		</span>
	</div><!-- end sui-box-settings-col-1 -->

	<div class="sui-box-settings-col-2">

		<div class="spinner standalone hide visible"></div>

		<div id="wphb-server-instructions-apache" class="wphb-server-instructions sui-hidden" data-server="apache">
			<div class="sui-tabs">
				<div data-tabs>
					<div id="auto-apache" class="active"><?php esc_html_e( 'Automatic', 'wphb' ); ?></div>
					<div id="manual-apache"><?php esc_html_e( 'Manual', 'wphb' ); ?></div>
				</div>
				<div data-panes>
					<div class="active">
						<p>
							<?php
							esc_html_e(
								'Hummingbird can automatically apply browser caching for Apache
							servers by writing your .htaccess file. Alternately, switch to Manual to apply these
							rules yourself.',
								'wphb'
							);
							?>
						</p>

						<?php if ( $htaccess_writable && $already_enabled ) : ?>
							<div class="sui-notice sui-notice-success">
								<p><?php esc_html_e( 'Your browser caching is already enabled and working well', 'wphb' ); ?></p>
							</div>
						<?php elseif ( $htaccess_writable && $htaccess_written ) : ?>
							<div class="sui-notice sui-notice-info">
								<p><?php esc_html_e( 'Automatic browser caching is active.', 'wphb' ); ?></p>
							</div>
						<?php endif; ?>

						<?php if ( ! $cf_active && $htaccess_writable ) : ?>
							<div id="enable-cache-wrap" class="enable-cache-wrap-apache <?php echo 'apache' === $server_type ? '' : 'sui-hidden'; ?>">
								<?php if ( $htaccess_written ) : ?>
									<a href="<?php echo esc_url( $disable_link ); ?>" class="sui-button sui-button-ghost">
										<?php esc_html_e( 'Deactivate', 'wphb' ); ?>
									</a>
								<?php elseif ( ! $already_enabled ) : ?>
									<a href="<?php echo esc_url( $enable_link ); ?>" class="sui-button sui-button-blue activate-button">
										<span class="sui-loading-text"><?php esc_html_e( 'Activate', 'wphb' ); ?></span>
										<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<div>
						<div class="apache-instructions">
							<p>
								<?php esc_html_e( 'Follow the steps below to add browser caching to your Apache server.', 'wphb' ); ?>
							</p>

							<ol class="wphb-listing wphb-listing-ordered">
								<li><?php esc_html_e( 'Copy the generated code into your .htaccess file & save your changes.', 'wphb' ); ?></li>
								<li><?php esc_html_e( 'Restart Apache.', 'wphb' ); ?></li>
								<li><a href="<?php echo esc_url( $recheck_expiry_url ); ?>"><?php esc_html_e( 'Re-check expiry status.', 'wphb' ); ?></a></li>
							</ol>

							<pre class="sui-code-snippet" id="wphb-apache"><?php echo htmlentities2( $snippets['apache'] ); ?></pre>

							<p><strong>Troubleshooting</strong></p>
							<p><?php esc_html_e( 'If adding the rules to your .htaccess doesn’t work and you have access to vhosts.conf or httpd.conf try to find the line that starts with <Directory> - add the code above into that section and save the file.', 'wphb' ); ?></p>
							<p><?php esc_html_e( "If you don't know where those files are, or you aren't able to reload Apache, you would need to consult with your hosting provider or a system administrator who has access to change the configuration of your server", 'wphb' ); ?></p>
							<p><?php WP_Hummingbird_Utils::_still_having_trouble_link(); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div><!-- end wphb-server-instructions -->

		<div id="wphb-server-instructions-nginx" class="wphb-server-instructions sui-hidden" data-server="nginx">
			<?php if ( $already_enabled ) : ?>
				<div class="sui-notice sui-notice-success">
					<p><?php esc_html_e( 'Your browser caching is already enabled and working well', 'wphb' ); ?></p>
				</div>
			<?php elseif ( $htaccess_writable && $htaccess_written ) : ?>
				<div class="sui-notice sui-notice-info">
					<p><?php esc_html_e( 'Automatic browser caching is active.', 'wphb' ); ?></p>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'Follow the steps below to add browser caching to your NGINX server.', 'wphb' ); ?></p>

				<ol class="wphb-listing wphb-listing-ordered">
					<li><?php esc_html_e( 'Copy the generated code into your nginx.conf usually located at /etc/nginx/nginx.conf or /usr/local/nginx/conf/nginx.conf', 'wphb' ); ?></li>
					<li><?php esc_html_e( 'Add the code above to the http or inside server section in the file.', 'wphb' ); ?></li>
					<li><?php esc_html_e( 'Restart NGINX.', 'wphb' ); ?></li>
					<li><a href="<?php echo esc_url( $recheck_expiry_url ); ?>"><?php esc_html_e( 'Re-check expiry status.', 'wphb' ); ?></a></li>
				</ol>
				<pre class="sui-code-snippet" id="wphb-nginx"><?php echo htmlentities2( $snippets['nginx'] ); ?></pre>
				<p><?php esc_html_e( 'Note: If you do not have access to your NGINX config files you will need to contact your hosting provider to make these changes.', 'wphb' ); ?></p>
				<p><?php WP_Hummingbird_Utils::_still_having_trouble_link(); ?></p>
			<?php endif; ?>
		</div>

		<div id="wphb-server-instructions-iis" class="wphb-server-instructions sui-hidden" data-server="iis">
			<?php if ( $already_enabled ) : ?>
				<div class="sui-notice sui-notice-success">
					<p><?php esc_html_e( 'Your browser caching is already enabled and working well', 'wphb' ); ?></p>
				</div>
			<?php elseif ( $htaccess_writable && $htaccess_written ) : ?>
				<div class="sui-notice sui-notice-info">
					<p><?php esc_html_e( 'Automatic browser caching is active.', 'wphb' ); ?></p>
				</div>
			<?php else : ?>
				<p>
					<?php
					printf(
						/* translators: %s: Link to TechNet */
						__( 'For IIS 7 servers and above, <a href="%s" target="_blank">visit Microsoft TechNet</a>', 'wphb' ),
						'https://technet.microsoft.com/en-us/library/cc732475(v=ws.10).aspx'
					);
					?>
				</p>
			<?php endif; ?>
		</div>

		<div id="wphb-server-instructions-cloudflare" class="wphb-server-instructions sui-hidden" data-server="cloudflare">
			<span class="sui-description">
				<?php esc_html_e( 'Hummingbird can control your CloudFlare Browser Cache settings from here. Simply add your CloudFlare API details and configure away.', 'wphb' ); ?>
			</span>
			<?php
			/* @var WP_Hummingbird_Module_Cloudflare $cf_module */
			$cf_module    = WP_Hummingbird_Utils::get_module( 'cloudflare' );
			$current_step = 'credentials';
			$zones        = array();
			if ( $cf_module->is_zone_selected() && $cf_module->is_connected() ) {
				$current_step = 'final';
			} elseif ( ! $cf_module->is_zone_selected() && $cf_module->is_connected() ) {
				$current_step = 'zone';
				$zones        = $cf_module->get_zones_list();
				if ( is_wp_error( $zones ) ) {
					$zones = array();
				}
			}

			$cf_settings            = $cf_module->get_options();
			$cloudflare_js_settings = array(
				'currentStep' => $current_step,
				'email'       => $cf_settings['email'],
				'apiKey'      => $cf_settings['api_key'],
				'zone'        => $cf_settings['zone'],
				'zoneName'    => $cf_settings['zone_name'],
				'plan'        => $cf_module->get_plan(),
				'zones'       => $zones,
			);

			$cloudflare_js_settings = wp_json_encode( $cloudflare_js_settings );
			?>

			<script type="text/template" id="cloudflare-step-credentials">
				<div class="cloudflare-step">
					<form class="sui-border-frame" action="" method="post" id="cloudflare-credentials">
						<div class="sui-form-field">
							<label for="cloudflare-email" class="sui-label"><?php esc_html_e( 'Cloudflare account email', 'wphb' ); ?></label>
							<input type="text" class="sui-form-control" autocomplete="off" value="{{ data.email }}" name="cloudflare-email" id="cloudflare-email" placeholder="<?php esc_attr_e( 'Enter email address', 'wphb' ); ?>">
						</div>

						<div class="sui-form-field">
							<label for="cloudflare-api-key" class="sui-label"><?php esc_html_e( 'Cloudflare Global API Key', 'wphb' ); ?></label>
							<input type="text" class="sui-form-control" autocomplete="off" value="{{ data.apiKey }}" name="cloudflare-api-key" id="cloudflare-api-key" placeholder="<?php esc_attr_e( 'Enter your 37 digit API key', 'wphb' ); ?>">
						</div>

						<div class="cloudflare-submit sui-margin-top sui-no-padding-bottom">
							<a href="#cloudflare-how-to" class="cloudflare-how-to-title"><?php esc_html_e( 'Need help getting your API Key?', 'wphb' ); ?></a>
							<input type="submit" class="sui-button sui-button-blue" value="<?php echo esc_attr( _x( 'Connect', 'Connect to Cloudflare button text', 'wphb' ) ); ?>">
						</div>

						<ol id="cloudflare-how-to" class="wphb-block-content-blue">
							<li><?php printf( __( '<a target="_blank" href="%s">Log in</a> to your Cloudflare account.', 'wphb' ), 'https://www.cloudflare.com/a/login' ); ?></li>
							<li><?php esc_html_e( 'Go to My Settings.', 'wphb' ); ?></li>
							<li><?php esc_html_e( 'Scroll down to API Key.', 'wphb' ); ?></li>
							<li><?php esc_html_e( "Click 'View API Key' button and copy your API identifier.", 'wphb' ); ?></li>
						</ol>
					</form>
				</div>
			</script>

			<script type="text/template" id="cloudflare-step-zone">
				<div class="cloudflare-step">
					<form action="" class="sui-border-frame" method="post" id="cloudflare-zone">
						<# if ( ! data.zones.length ) { #>
							<p><?php esc_html_e( 'It appears you have no active zones available. Double check your domain has been added to Cloudflare and try again.', 'wphb' ); ?></p>
							<p class="cloudflare-submit">
								<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_admin_menu_url( 'caching' ) ); ?>&reload=<?php echo time(); ?>#wphb-box-dashboard-cloudflare" class="sui-button sui-button-blue"><?php esc_html_e( 'Re-Check', 'wphb' ); ?></a>
							</p>
						<# } else { #>
							<# var zone = false; #>
							<# var current_host = location.host; #>
							<# for( var i = 0, len = data.zones.length; i < len; i++ ) { #>
								<# if( current_host.indexOf(data.zones[i].label) !== -1 ) { #>
									<# zone = true; #>
									<# break; #>
								<# } #>
							<# } #>
							<# if ( zone ) { #>
								<label for="cloudflare-zone" class="sui-label"><?php esc_html_e( 'Select the domain that matches this website', 'wphb' ); ?></label>
								<select name="cloudflare-zone" id="cloudflare-zone">
									<option value=""><?php esc_html_e( 'Select domain', 'wphb' ); ?></option>
									<# for ( i in data.zones ) { #>
										<option value="{{ data.zones[i].value }}">{{{ data.zones[i].label }}}</option>
									<# } #>
								</select>
								<div class="cloudflare-submit">
									<input type="submit" class="sui-button sui-button-blue" value="<?php esc_attr_e( 'Enable Cloudflare', 'wphb' ); ?>">
								</div>
							<# } else { #>
								<div class="wphb-cloudflare sui-notice sui-notice-sm sui-notice-warning">
									<p>
										<?php
										esc_html_e(
											'CloudFlare is connected, but it appears you don’t have any active
                                        zones for this domain. Double check your domain has been added to Cloudflare and tap
                                        re-check when ready.',
											'wphb'
										);
										?>
									</p>
									<p>
										<button type="button" class="sui-button sui-button-icon-left" id="cf-recheck-zones">
											<i class="sui-icon-update" aria-hidden="true"></i>
											<span class="sui-loading-text"><?php esc_html_e( 'Re-check', 'wphb' ); ?></span>
											<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
										</button>
									</p>
								</div>
								<a href="<?php echo esc_url( $cf_disable_url ); ?>" class="sui-button sui-button-ghost "><?php esc_attr_e( 'Deactivate', 'wphb' ); ?></a>
							<# } #>
						<# } #>
					</form>
				</div>
			</script>

			<script type="text/template" id="cloudflare-step-final">
				<div class="cloudflare-step">
					<div class="sui-notice sui-notice-info sui-notice-sm sui-margin-top">
						<p>
							<?php esc_html_e( 'Cloudflare is connected for this domain. Adjust your expiry settings and save your settings to update your Cloudflare cache settings.', 'wphb' ); ?>
						</p>
					</div>
					<div class="buttons buttons-on-left">
						<a href="<?php echo esc_url( $cf_disable_url ); ?>" class="cloudflare-deactivate sui-button sui-button-ghost sui-button-icon-left">
							<i class="sui-icon-power-on-off" aria-hidden="true"></i>
							<?php esc_attr_e( 'Deactivate', 'wphb' ); ?>
						</a>
						<span class="alignright sui-tooltip sui-tooltip-top-right" data-tooltip="<?php esc_attr_e( 'Clear all assets cached by CloudFlare', 'wphb' ); ?>">
							<input type="submit" class="cloudflare-clear-cache sui-button" value="<?php esc_attr_e( 'Clear Cache', 'wphb' ); ?>">
						</span>
						<span class="spinner cloudflare-spinner"></span>
					</div>
				</div>
			</script>

			<div id="cloudflare-steps"></div>
			<div id="cloudflare-info"></div>
		</div>

	</div><!-- end sui-box-settings-col-1 -->

	<script>
		jQuery(document).ready( function() {
			window.WPHB_Admin.DashboardCloudFlare.init( <?php echo $cloudflare_js_settings; ?> );
			<?php if ( $cf_active ) : ?>
				if ( window.WPHB_Admin ) {
					window.WPHB_Admin.getModule( 'cloudflare' );
				}
			<?php endif; ?>
		});
	</script>
</div><!-- end row -->


