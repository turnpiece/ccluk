<?php

/** @var WP_Hummingbird_Module_Cloudflare $cf_module */
$cf_module = wphb_get_module( 'cloudflare' );
$current_step = 'credentials';
$zones = array();
if ( $cf_module->is_zone_selected() && $cf_module->is_connected() ) {
	$current_step = 'final';
}
elseif ( ! $cf_module->is_zone_selected() && $cf_module->is_connected() ) {
	$current_step = 'zone';
	$zones = $cf_module->get_zones_list();
	if ( is_wp_error( $zones ) ) {
		$zones = array();
	}
}


$cloudflare_js_settings = array(
	'currentStep' => $current_step,
	'email' => wphb_get_setting( 'cloudflare-email' ),
	'apiKey' => wphb_get_setting( 'cloudflare-api-key' ),
	'zone' => wphb_get_setting( 'cloudflare-zone' ),
	'zoneName' => wphb_get_setting( 'cloudflare-zone-name' ),
	'plan' => $cf_module->get_plan(),
	'zones' => $zones,
);

$cloudflare_js_settings = wp_json_encode( $cloudflare_js_settings );
?>

<script type="text/template" id="cloudflare-step-credentials">
	<div class="cloudflare-step">

		<p><?php _e( 'Hummingbird can control your Cloudflare Browser Cache settings from here. Simply add your Cloudflare API details and configure away.', 'wphb' ); ?></p>

		<form class="wphb-border-frame with-padding" action="" method="post" id="cloudflare-credentials">
			<label for="cloudflare-email"><?php _e( 'Cloudflare email', 'wphb' ); ?>
				<input type="text" autocomplete="off" value="{{ data.email }}" name="cloudflare-email" id="cloudflare-email" placeholder="<?php _e( 'Your Cloudflare account email', 'wphb' ); ?>">
			</label>

			<label for="cloudflare-api-key"><?php _e( 'Cloudflare Global API Key', 'wphb' ); ?>
				<input type="text" autocomplete="off" value="{{ data.apiKey }}" name="cloudflare-api-key" id="cloudflare-api-key" placeholder="<?php _e( 'Enter your 37 digit API key', 'wphb' ); ?>">
			</label>

			<p class="cloudflare-submit">
				<span class="spinner cloudflare-spinner"></span>
				<input type="submit" class="button" value="<?php echo esc_attr( _x( 'Connect', 'Connect to Cloudflare button text', 'wphb' ) ); ?>">
			</p>
			<p id="cloudflare-how-to-title"><a href="#cloudflare-how-to"><?php _e( 'Need help getting your API Key?', 'wphb' ); ?></a></p>
			<div class="clear"></div>
			<ol id="cloudflare-how-to" class="wphb-block-content-blue">
				<li><?php printf( __( '<a target="_blank" href="%s">Log in</a> to your Cloudflare account.', 'wphb' ), 'https://www.cloudflare.com/a/login' ); ?></li>
				<li><?php _e( 'Go to My Settings.', 'wphb' ); ?></li>
				<li><?php _e( 'Scroll down to API Key.', 'wphb' ); ?></li>
				<li><?php _e( "Click 'View API Key' button and copy your API identifier.", 'wphb' ); ?></li>
			</ol>
		</form>
	</div>
</script>

<script type="text/template" id="cloudflare-step-zone">
	<div class="cloudflare-step">
		<form action="" method="post" id="cloudflare-zone">
			<# if ( ! data.zones.length ) { #>
				<p><?php _e( 'It appears you have no active zones available. Double check your domain has been added to Cloudflare and try again.', 'wphb' ); ?></p>
				<p class="cloudflare-submit">
					<a href="<?php echo esc_url( wphb_get_admin_menu_url( 'caching' ) ); ?>&reload=<?php echo time(); ?>#wphb-box-dashboard-cloudflare" class="button"><?php esc_html_e( 'Re-Check', 'wphb' ); ?></a>
				</p>
			<# } else { #>
				<p>
					<label for="cloudflare-zone"><?php _e( 'Select the domain that matches this website', 'wphb' ); ?></label>
					<select name="cloudflare-zone" id="cloudflare-zone">
						<option value=""><?php _e( 'Select domain', 'wphb' ); ?></option>
						<# for ( i in data.zones ) { #>
							<option value="{{ data.zones[i].value }}">{{{ data.zones[i].label }}}</option>
						<# } #>
					</select>
				<p class="cloudflare-submit">
					<span class="spinner cloudflare-spinner"></span>
					<input type="submit" class="button" value="<?php esc_attr_e( 'Enable Cloudflare', 'wphb' ); ?>">
				</p>
			<# } #>
			<div class="clear"></div>
		</form>
	</div>
</script>

<script type="text/template" id="cloudflare-step-final">
	<div class="cloudflare-step">
		<div class="wphb-notice wphb-notice-blue">
			<p><?php esc_html_e( 'Cloudflare is active on this domain. The settings you choose here will also update Cloudflare settings.', 'wphb' ); ?></p>
		</div>
		<p class="cloudflare-data">
			<?php
			$zone_name = wphb_get_setting( 'cloudflare-zone-name' );
			if ( ! empty( $zone_name ) ) : ?>
				<span><strong><?php _ex( 'Zone', 'Cloudflare Zone', 'wphb' ); ?>:</strong> {{ data.zoneName }}</span>
			<?php endif;
			$plan = $cf_module->get_plan();
			if ( ! empty( $plan ) ) : ?>
				<span><strong><?php _ex( 'Plan', 'Cloudflare Plan', 'wphb' ); ?>:</strong> {{ data.plan }}</span>
			<?php endif; ?>
		</p>
		<hr>
		<p class="cloudflare-clear-cache-text"><?php esc_html_e( 'Made changes to your website? Use Purge Cache button to clear Cloudflare\'s cache', 'wphb' ); ?></p class="cloudflare-clear-cache-text">
		<p class="cloudflare-clear-cache">
			<input type="submit" class="button button-ghost" value="<?php esc_attr_e( 'Purge Cache', 'wphb' ); ?>">
			<span class="spinner cloudflare-spinner"></span>
		</p>
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

