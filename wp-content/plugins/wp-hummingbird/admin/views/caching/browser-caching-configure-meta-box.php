<?php
/**
 * Browser caching meta box.
 *
 * @package Hummingbird
 *
 * @var array  $results            Current report.
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
<div class="row settings-form with-bottom-border" xmlns="http://www.w3.org/1999/html">
	<div class="col-third">
		<strong><?php esc_html_e( 'Server type', 'wphb' ); ?></strong>
		<span class="sub">
			<?php esc_html_e( 'Choose your server type so Hummingbird can give you the rules to apply caching.', 'wphb' ); ?>
		</span>
	</div><!-- end col-third -->
	<div class="col-two-third">
		<label for="wphb-server-type"><?php esc_html_e( 'Server type', 'wphb' ); ?></label>
		<?php
		WP_Hummingbird_Utils::get_servers_dropdown( array(
			'class'    => 'server-type',
			'selected' => $server_type,
		) );
		if ( ! $cf_active ) :
			if ( ! $show_cf_notice ) :
			?>
				<span class="desc">
					<?php esc_html_e( 'This is the server type your website is hosted on. If you are using CloudFlare', 'wphb' ); ?>
					<a href="#" id="connect-cloudflare-link"><?php esc_html_e( 'connect your account' , 'wphb' ); ?></a>
					<?php esc_html_e( 'to control your cache settings from here.', 'wphb' ); ?>
				</span>
		<?php else : ?>
				<div class="wphb-cf-detected-notice wphb-notice wphb-notice-grey">
					<p>
						<?php esc_html_e( 'We’ve detected you’re using CloudFlare which handles browser caching for you. You can control your CloudFlare settings from Hummingbird by connecting your account below.', 'wphb' ); ?><br />
					</p>
				</div>
		<?php
			endif;
		endif;
		?>
	</div>
</div>
<div class="row settings-form with-bottom-border">
	<div class="col-third">
		<strong><?php esc_html_e( 'Expiry Time', 'wphb' ); ?></strong>
		<span class="sub">
			<?php esc_html_e( 'Please choose your desired expiry time. Google recommends 8 days as a good benchmark.', 'wphb' ); ?>
		</span>
	</div><!-- end col-third -->
	<div class="col-two-third">
		<form action="" method="post">
			<input type="hidden" class="hb-server-type" name="hb_server_type" value="<?php echo esc_attr( $server_type ); ?>">
			<input type="hidden" name="expiry-settings" value="1">
			<?php if ( ! $cf_active && ! $cf_server ) : ?>
				<div class="wphb-radio-group">
					<input type="radio" name="expiry-set-type" id="expiry-all-types" value="all" <?php checked( $all_expiry ); ?>>
					<label for="expiry-all-types"><?php esc_html_e( 'All file types', 'wphb' ); ?></label>
				</div>
				<div class="wphb-radio-group">
					<input type="radio" name="expiry-set-type" id="expiry-single-type" value="single" <?php checked( ! $all_expiry ); ?>>
					<label for="expiry-single-type"><?php esc_html_e( 'Individual file types', 'wphb' ); ?></label>
				</div>
			<?php endif; ?>
			<?php
			$expiry_box_classes = 'wphb-border-frame with-padding';
			if ( $cf_server ) :
				$expiry_box_classes = 'wphb-expiry-select-box';
			endif;
			?>

			<div class="<?php echo esc_attr( $expiry_box_classes, 'wphb' ); ?> no-top-padding">
				<?php if ( ! $cf_active && ! $cf_server ) : ?>
					<div data="expiry-all-types">
						<label><?php esc_html_e( 'JavaScript, CSS, Media, Images', 'wphb' ); ?></label>
						<?php
						WP_Hummingbird_Utils::get_caching_frequencies_dropdown( array(
							'name'      => 'set-expiry-all',
							'class'     => 'wphb-expiry-select',
							'selected'  => $expires['css'],
							'data-type' => 'all',
						));
						?>
					</div>
					<div class="hidden" data="expiry-single-type">
						<?php foreach ( $human_results as $type => $result ) : ?>
							<?php
							switch ( $type ) {
								case 'javascript':
									$label = 'JavaScript';
									break;
								case 'images':
									$label = 'Images';
									break;
								case 'css':
									$label = 'CSS';
									break;
								case 'media':
									$label = 'Media';
									break;
								default:
									$label = esc_html( $type );
									break;
							}
							?>
							<label><?php echo esc_html( $label ); ?></label>
							<?php
							WP_Hummingbird_Utils::get_caching_frequencies_dropdown( array(
								'name'      => 'set-expiry-' . $type,
								'class'     => 'wphb-expiry-select',
								'selected'  => $expires[ $type ],
								'data-type' => $type,
							));
						endforeach;
						?>
					</div>
				<?php elseif ( $cf_active || $cf_server ) : ?>
					<label><?php esc_html_e( 'JavaScript, CSS, Media, Images', 'wphb' ); ?></label>
					<?php
					WP_Hummingbird_Utils::get_caching_frequencies_dropdown( array(
						'name'      => 'set-expiry-all',
						'class'     => 'wphb-expiry-select',
						'selected'  => $cf_current,
						'data-type' => 'all',
					), true );
				endif;
				?>
				<div id="wphb-expiry-change-notice" style="display: none">
					<?php if ( ! $cf_active && $cf_server ) : ?>
						<div class="wphb-cf-detected-notice wphb-notice wphb-notice-grey">
							<p>
								<?php esc_html_e( 'Note: You need to connect your CloudFlare account below for your selected expiry time to take effect.', 'wphb' ); ?>
							</p>
						</div>
					<?php elseif ( $cf_active ) : ?>
						<div class="wphb-expiry-changes wphb-notice wphb-notice-warning">
							<p>
								<?php esc_html_e( 'You’ve made changes to your browser cache settings. You need to save changes for the new settings to take effect.', 'wphb' ); ?><br />
								<input type="submit" class="button button-grey update-htaccess" id="set-cf-expiry-button" name="submit" value="<?php esc_attr_e( 'Save Changes', 'wphb' ); ?>"/>
							</p>
						</div>
					<?php elseif ( $htaccess_writable && $already_enabled ) : ?>
						<div class="wphb-expiry-changes wphb-notice wphb-notice-warning">
							<p>
								<?php esc_html_e( 'You’ve made changes to your browser cache settings. You need to update your .htaccess file with the newly generated code below.', 'wphb' ); ?><br />
								<a class="button button-grey update-htaccess" id="view-snippet-code" ><?php esc_attr_e( 'View code', 'wphb' ); ?></a>
							</p>
						</div>
					<?php elseif ( $htaccess_writable && $htaccess_written ) : ?>
						<div class="wphb-expiry-changes wphb-notice wphb-notice-warning">
							<p>
								<?php esc_html_e( 'You’ve made changes to your browser cache settings. You need to update your .htaccess for the new settings to take effect.', 'wphb' ); ?><br />
								<input type="submit" class="button button-grey update-htaccess" name="submit" value="<?php esc_attr_e( 'Update .htaccess', 'wphb' ); ?>"/>
							</p>
						</div>
					<?php endif; ?>
				</div>
			</div><!-- end wphb-border-frame -->
			<?php wp_nonce_field( 'wphb-caching' ); ?>
		</form>
	</div><!-- end col-two-third -->
</div><!-- end row -->

<div class="row settings-form">
	<div class="col-third">
		<strong><?php esc_html_e( 'Setup', 'wphb' ); ?></strong>
		<span class="sub">
			<?php esc_html_e( 'Follow the instructions provided to enable browser caching.', 'wphb' ); ?>
		</span>
	</div><!-- end col-third -->

	<div class="col-two-third">

		<div class="spinner standalone hide visible"></div>

		<div class="wphb-content">
			<div id="wphb-server-instructions-apache" class="wphb-server-instructions hidden" data-server="apache">
				<div class="tabs">
					<div class="tab">
						<label id="auto-apache" for="apache-config-auto" class="active"><?php esc_html_e( 'Automatic', 'wphb' ); ?></label>
						<input type="radio" name="apache-config-type" id="apache-config-auto" checked>
						<div class="content">
							<span class="desc">
								<?php esc_html_e( 'Hummingbird can automatically apply browser caching for Apache servers by writing your .htaccess file. Alternately, switch to Manual to apply these rules yourself.', 'wphb' ); ?>
							</span>

							<?php if ( $htaccess_writable && $already_enabled ) : ?>
								<div class="wphb-caching-success wphb-notice wphb-notice-success">
									<p><?php esc_html_e( 'Your browser caching is already enabled and working well', 'wphb' ); ?></p>
								</div>
							<?php elseif ( $htaccess_writable && $htaccess_written ) : ?>
								<div class="wphb-caching-active wphb-notice wphb-notice-blue">
									<p><?php esc_html_e( 'Automatic browser caching is active.', 'wphb' ); ?></p>
								</div>
							<?php endif; ?>

							<?php
							if ( ! $cf_active ) :
								if ( true === $htaccess_writable ) :
									?>
									<div id="enable-cache-wrap" class="enable-cache-wrap-apache <?php echo 'apache' === $server_type ? '' : 'hidden'; ?>">
										<?php if ( true === $htaccess_written ) : ?>
											<a href="<?php echo esc_url( $disable_link ); ?>" class="button button-ghost button-small"><?php esc_html_e( 'Deactivate', 'wphb' ); ?></a>
										<?php elseif ( ! $already_enabled ) : ?>
											<a href="<?php echo esc_url( $enable_link ); ?>" class="button button-small activate-button"><?php esc_html_e( 'Activate', 'wphb' ); ?></a>
										<?php endif; ?>
									</div>
								<?php
								endif;
							endif;
							?>
						</div><!-- end content -->
					</div><!-- end tab -->
					<div class="tab">
						<label id="manual-apache" for="apache-config-manual"><?php esc_html_e( 'Manual', 'wphb' ); ?></label>
						<input type="radio" name="apache-config-type" id="apache-config-manual">
						<div class="content">
							<div class="apache-instructions">
								<p><?php esc_html_e( 'Follow the steps below to add browser caching to your Apache server.', 'wphb' ); ?></p>

								<ol class="wphb-listing wphb-listing-ordered">
									<li><?php esc_html_e( 'Copy the generated code into your .htaccess file & save your changes.', 'wphb' ); ?></li>
									<li><?php esc_html_e( 'Restart Apache.', 'wphb' ); ?></li>
									<li><a href="<?php echo esc_url( $recheck_expiry_url ); ?>"><?php esc_html_e( 'Re-check expiry status.', 'wphb' ); ?></a></li>
								</ol>

								<div id="wphb-code-snippet">
									<div id="wphb-code-snippet-apache" class="wphb-code-snippet">
										<div class="wphb-block-content">
											<button class="button button-grey" data-clipboard-target="#wphb-apache"><?php esc_html_e( 'Copy', 'wphb' ); ?></button>
											<pre id="wphb-apache"><?php echo htmlentities2( $snippets['apache'] ); ?></pre>
										</div>
									</div>
								</div>
								<p><strong>Troubleshooting</strong></p>
								<p><?php esc_html_e( 'If adding the rules to your .htaccess doesn’t work and you have access to vhosts.conf or httpd.conf try to find the line that starts with <Directory> - add the code above into that section and save the file.', 'wphb' ); ?></p>
								<p><?php esc_html_e( 'If you don\'t know where those files are, or you aren\'t able to reload Apache, you would need to consult with your hosting provider or a system administrator who has access to change the configuration of your server', 'wphb' ); ?></p>
								<p><?php WP_Hummingbird_Utils::_still_having_trouble_link(); ?></p>
							</div><!-- end apache-instructions -->
						</div><!-- end content -->
					</div><!-- end tab -->
				</div><!-- end tabs -->
			</div><!-- end wphb-server-instructions -->

			<div id="wphb-server-instructions-litespeed" class="wphb-server-instructions hidden" data-server="LiteSpeed">
				<div class="tabs">
					<div class="tab">
						<label id="auto-litespeed" for="litespeed-config-auto" class="active"><?php esc_html_e( 'Automatic', 'wphb' ); ?></label>
						<input type="radio" name="litespeed-config-type" id="litespeed-config-auto">
						<div class="content">
						<span class="sub">
							<?php esc_html_e( 'Hummingbird can automatically apply browser caching for LiteSpeed servers by writing your .htaccess file. Alternately, switch to Manual to apply these rules yourself.', 'wphb' ); ?>
						</span>

							<?php if ( $htaccess_writable && $already_enabled ) : ?>
								<div class="wphb-caching-success wphb-notice wphb-notice-success">
									<p><?php esc_html_e( 'Your browser caching is already enabled and working well', 'wphb' ); ?></p>
								</div>
							<?php elseif ( $htaccess_writable && $htaccess_written ) : ?>
								<div class="wphb-caching-active wphb-notice wphb-notice-blue">
									<p><?php esc_html_e( 'Automatic browser caching is active.', 'wphb' ); ?></p>
								</div>
							<?php endif; ?>

							<?php
							if ( ! $cf_active ) :
								if ( true === $htaccess_writable ) :
									?>
									<div id="enable-cache-wrap" class="enable-cache-wrap-LiteSpeed <?php echo 'LiteSpeed' === $server_type ? '' : 'hidden'; ?>">
										<?php if ( true === $htaccess_written ) : ?>
											<a href="<?php echo esc_url( $disable_link ); ?>" class="button button-ghost button-small"><?php esc_html_e( 'Deactivate', 'wphb' ); ?></a>
										<?php elseif ( ! $already_enabled ) : ?>
											<a href="<?php echo esc_url( $enable_link ); ?>" class="button button-small activate-button"><?php esc_html_e( 'Activate', 'wphb' ); ?></a>
										<?php endif; ?>
									</div>
								<?php
								endif;
							endif;
							?>
						</div><!-- end content -->
					</div><!-- end tab -->
					<div class="tab">
						<label id="manual-litespeed" for="litespeed-config-manual"><?php esc_html_e( 'Manual', 'wphb' ); ?></label>
						<input type="radio" name="litespeed-config-type" id="litespeed-config-manual">
						<div class="content">
							<div class="litespeed-instructions">
								<p><?php esc_html_e( 'Follow the steps below to add browser caching to your LiteSpeed server.', 'wphb' ); ?></p>

								<ol class="wphb-listing wphb-listing-ordered">
									<li><?php esc_html_e( 'Copy the generated code into your .htaccess file & save your changes.', 'wphb' ); ?></li>
									<li><?php esc_html_e( 'Restart LiteSpeed.', 'wphb' ); ?></li>
									<li><a href="<?php echo esc_url( $recheck_expiry_url ); ?>"><?php esc_html_e( 'Re-check expiry status.', 'wphb' ); ?></a></li>
								</ol>

								<div id="wphb-code-snippet">
									<div id="wphb-code-snippet-litespeed" class="wphb-code-snippet">
										<div class="wphb-block-content">
											<button class="button button-grey" data-clipboard-target="#wphb-litespeed"><?php esc_html_e( 'Copy', 'wphb' ); ?></button>
											<pre id="wphb-litespeed"><?php echo htmlentities2( $snippets['litespeed'] ); ?></pre>
										</div>
									</div>
								</div>
								<p><strong>Troubleshooting</strong></p>
								<p><?php esc_html_e( 'If adding the rules to your .htaccess doesn’t work and you have access to vhosts.conf or httpd.conf try to find the line that starts with <Directory> - add the code above into that section and save the file.', 'wphb' ); ?></p>
								<p><?php esc_html_e( 'If you don\'t know where those files are, or you aren\'t able to reload Apache, you would need to consult with your hosting provider or a system administrator who has access to change the configuration of your server', 'wphb' ); ?></p>
								<p><?php WP_Hummingbird_Utils::_still_having_trouble_link(); ?></p>
							</div><!-- end litespeed-instructions -->
						</div><!-- end content -->
					</div><!-- end tab -->
				</div><!-- end tabs -->
			</div><!-- end wphb-server-instructions -->

			<div id="wphb-server-instructions-nginx" class="wphb-server-instructions hidden" data-server="nginx">
				<?php if ( $already_enabled ) : ?>
					<div class="wphb-caching-success wphb-notice wphb-notice-success">
						<p><?php esc_html_e( 'Your browser caching is already enabled and working well', 'wphb' ); ?></p>
					</div>
				<?php elseif ( $htaccess_writable && $htaccess_written ) : ?>
					<div class="wphb-caching-active wphb-notice wphb-notice-blue">
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
					<div id="wphb-code-snippet">
						<div id="wphb-code-snippet-nginx" class="wphb-code-snippet">
							<div class="wphb-block-content">
								<button class="button button-grey" data-clipboard-target="#wphb-nginx"><?php esc_html_e( 'Copy', 'wphb' ); ?></button>
								<pre id="wphb-nginx"><?php echo htmlentities2( $snippets['nginx'] ); ?></pre>
							</div>
						</div>
					</div>
					<p><?php esc_html_e( 'Note: If you do not have access to your NGINX config files you will need to contact your hosting provider to make these changes.', 'wphb' ); ?></p>
					<p><?php WP_Hummingbird_Utils::_still_having_trouble_link(); ?></p>
				<?php endif; ?>

			</div>

			<div id="wphb-server-instructions-iis" class="wphb-server-instructions hidden" data-server="iis">
				<?php if ( $already_enabled ) : ?>
					<div class="wphb-caching-success wphb-notice wphb-notice-success">
						<p><?php esc_html_e( 'Your browser caching is already enabled and working well', 'wphb' ); ?></p>
					</div>
				<?php elseif ( $htaccess_writable && $htaccess_written ) : ?>
					<div class="wphb-caching-active wphb-notice wphb-notice-blue">
						<p><?php esc_html_e( 'Automatic browser caching is active.', 'wphb' ); ?></p>
					</div>
				<?php else : ?>
					<p>
						<?php
						printf(
							/* translators: %s: Link to TechNet */
							__( 'For IIS servers, <a href="%s" target="_blank">visit Microsoft TechNet</a>', 'wphb' ),
						'https://www.microsoft.com/technet/prodtechnol/WindowsServer2003/Library/IIS/25d2170b-09c0-45fd-8da4-898cf9a7d568.mspx?mfr=true' );
						?>
					</p>
				<?php endif; ?>
			</div>

			<div id="wphb-server-instructions-iis-7" class="wphb-server-instructions hidden" data-server="iis-7">
				<?php if ( $already_enabled ) : ?>
					<div class="wphb-caching-success wphb-notice wphb-notice-success">
						<p><?php esc_html_e( 'Your browser caching is already enabled and working well', 'wphb' ); ?></p>
					</div>
				<?php elseif ( $htaccess_writable && $htaccess_written ) : ?>
					<div class="wphb-caching-active wphb-notice wphb-notice-blue">
						<p><?php esc_html_e( 'Automatic browser caching is active.', 'wphb' ); ?></p>
					</div>
				<?php else : ?>
					<p>
						<?php
						printf(
							/* translators: %s: Link to TechNet */
							__( 'For IIS 7 servers, <a href="%s" target="_blank">visit Microsoft TechNet</a>', 'wphb' ),
						'https://technet.microsoft.com/en-us/library/cc771003(v=ws.10).aspx' );
						?>
					</p>
				<?php endif; ?>
			</div>
		</div><!-- end wphb-content -->

		<div id="wphb-server-instructions-cloudflare" class="wphb-server-instructions hidden" data-server="cloudflare">
			<span class="desc">
				<?php esc_html_e( 'Hummingbird can control your Cloudflare Browser Cache settings from here. Simply add your Cloudflare API details and configure away.', 'wphb' ); ?>
			</span>
			<?php
			/* @var WP_Hummingbird_Module_Cloudflare $cf_module */
			$cf_module = WP_Hummingbird_Utils::get_module( 'cloudflare' );
			$current_step = 'credentials';
			$zones = array();
			if ( $cf_module->is_zone_selected() && $cf_module->is_connected() ) {
				$current_step = 'final';
			} elseif ( ! $cf_module->is_zone_selected() && $cf_module->is_connected() ) {
				$current_step = 'zone';
				$zones = $cf_module->get_zones_list();
				if ( is_wp_error( $zones ) ) {
					$zones = array();
				}
			}

			$cf_settings = $cf_module->get_options();
			$cloudflare_js_settings = array(
				'currentStep' => $current_step,
				'email'       => $cf_settings['email'],
				'apiKey'      => $cf_settings['api_key'],
				'zone'        => $cf_settings['zone'],
				'zoneName'    => $cf_settings['zone_name'],
				'plan'        => $cf_module->get_plan(),
				'zones'       => $zones,
			);

			$cloudflare_js_settings = wp_json_encode( $cloudflare_js_settings ); ?>

			<script type="text/template" id="cloudflare-step-credentials">
				<div class="cloudflare-step">
					<form class="wphb-border-frame with-padding" action="" method="post" id="cloudflare-credentials">
						<label for="cloudflare-email"><?php esc_html_e( 'Cloudflare account email', 'wphb' ); ?>
							<input type="text" autocomplete="off" value="{{ data.email }}" name="cloudflare-email" id="cloudflare-email" placeholder="<?php esc_attr_e( 'Enter email address', 'wphb' ); ?>">
						</label>

						<label for="cloudflare-api-key"><?php esc_html_e( 'Cloudflare Global API Key', 'wphb' ); ?>
							<input type="text" autocomplete="off" value="{{ data.apiKey }}" name="cloudflare-api-key" id="cloudflare-api-key" placeholder="<?php esc_attr_e( 'Enter your 37 digit API key', 'wphb' ); ?>">
						</label>

						<p class="cloudflare-submit">
							<input type="submit" class="button" value="<?php echo esc_attr( _x( 'Connect', 'Connect to Cloudflare button text', 'wphb' ) ); ?>">
						</p>
						<p id="cloudflare-how-to-title"><a href="#cloudflare-how-to"><?php esc_html_e( 'Need help getting your API Key?', 'wphb' ); ?></a></p>
						<div class="clear"></div>
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
					<form action="" class="wphb-border-frame with-padding" method="post" id="cloudflare-zone">
						<# if ( ! data.zones.length ) { #>
							<p><?php _e( 'It appears you have no active zones available. Double check your domain has been added to Cloudflare and try again.', 'wphb' ); ?></p>
							<p class="cloudflare-submit">
								<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_admin_menu_url( 'caching' ) ); ?>&reload=<?php echo time(); ?>#wphb-box-dashboard-cloudflare" class="button"><?php esc_html_e( 'Re-Check', 'wphb' ); ?></a>
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
								<p>
									<label for="cloudflare-zone"><?php esc_html_e( 'Select the domain that matches this website', 'wphb' ); ?></label>
									<select name="cloudflare-zone" id="cloudflare-zone">
										<option value=""><?php esc_html_e( 'Select domain', 'wphb' ); ?></option>
										<# for ( i in data.zones ) { #>
											<option value="{{ data.zones[i].value }}">{{{ data.zones[i].label }}}</option>
										<# } #>
									</select>
								</p>
								<p class="cloudflare-submit">
									<input type="submit" class="button" value="<?php esc_attr_e( 'Enable Cloudflare', 'wphb' ); ?>">
								</p>
							<# } else { #>
								<div class="wphb-cloudflare wphb-notice wphb-notice-warning">
									<p>
										<strong><?php esc_html_e( 'CloudFlare is connected, but it appears you don’t have any active zones for this domain.', 'wphb' ); ?></strong>
										<?php esc_html_e( 'Double check your domain has been added to Cloudflare and tap re-check when ready.', 'wphb' ); ?><br />
										<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_admin_menu_url( 'caching' ) ); ?>&view=browser&reload=<?php echo esc_attr( time() ); ?>#wphb-server-instructions-cloudflare" class="button button-grey notice-button"><?php esc_html_e( 'Re-check', 'wphb' ); ?></a>
									</p>
								</div>
								<a href="<?php echo esc_url( $cf_disable_url ); ?>" class="button button-ghost button-small"><?php esc_attr_e( 'Deactivate', 'wphb' ); ?></a>
							<# } #>
						<# } #>
						<div class="clear"></div>
					</form>
				</div>
			</script>

			<script type="text/template" id="cloudflare-step-final">
				<div class="cloudflare-step">
					<div class="wphb-caching-success wphb-notice wphb-notice-blue">
						<p>
							<strong><?php esc_html_e( 'Cloudflare is connected for this domain.', 'wphb' ); ?></strong>
							<?php esc_html_e( 'Adjust your expiry settings and save your settings to update your Cloudflare cache settings.', 'wphb' ); ?>
						</p>
					</div>
					<div class="buttons buttons-on-left">
						<a href="<?php echo esc_url( $cf_disable_url ); ?>" class="cloudflare-deactivate button button-ghost"><?php esc_attr_e( 'Deactivate', 'wphb' ); ?></a>
						<span class="alignright cloudflare-clear-cache-tooltip" tooltip="<?php esc_attr_e( 'Clear all assets cached by CloudFlare', 'wphb' ); ?>">
							<input type="submit" class="cloudflare-clear-cache button button-grey" value="<?php esc_attr_e( 'Clear Cache', 'wphb' ); ?>">
						</span>
						<span class="spinner cloudflare-spinner"></span>
					</div>
				</div>
			</script>

			<script>
				jQuery(document).ready( function( $ ) {
					window.WPHB_Admin.DashboardCloudFlare.init( <?php echo $cloudflare_js_settings; ?> );
				});
			</script>

			<div class="wphb-block-entry">
				<div class="wphb-block-entry-content">
					<div id="cloudflare-steps"></div>
					<div id="cloudflare-info"></div>
				</div><!-- end wphb-block-entry-content -->
			</div><!-- end wphb-block-entry -->
		</div>

	</div><!-- end col-two-third -->
</div><!-- end row -->

<?php if ( $cf_active ) : ?>
	<script>
		jQuery(document).ready( function() {
			if ( window.WPHB_Admin ) {
				window.WPHB_Admin.getModule( 'cloudflare' );
			}
			//jq.on('click', '.tab > input[type=radio]', updateHash);
			//jQuery('.tab > input[type=radio]').trigger('wpmu:change')
			var content = jQuery('.tab > #apache-config-manual');
			window.console.log( content );
			content.trigger('click')
		});
	</script>
<?php endif; ?>
<script>
	jQuery(window).load(function() {
		var caching = window.WPHB_Admin.getModule( 'caching' );
		caching.updateTabSize();
	});
</script>