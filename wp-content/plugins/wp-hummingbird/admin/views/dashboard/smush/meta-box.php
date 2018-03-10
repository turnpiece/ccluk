<?php
/**
 * Smush meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $activate_pro_url  URL to activate Pro version.
 * @var string $activate_url      URL to activate Free version.
 * @var bool   $is_active         Activation status.
 * @var bool   $is_installed      Installation status.
 * @var bool   $is_pro            Pro status.
 * @var array  $smush_data        Smush data.
 * @var int    $unsmushed         Number of uncompressed images.
 */

?>
<div class="wphb-block-entry">

	<div class="wphb-block-entry-content">

		<div class="content">
			<p><?php esc_html_e( 'Automatically compress and optimize your images with our super popular Smush plugin.', 'wphb' ); ?></p>
			<!-- No plugin is installed -->
			<?php if ( ! $is_installed ) : ?>
				<div class="buttons">
					<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_link( 'smush' ) ); ?>" class="button" id="smush-install">
						<?php
						if ( WP_Hummingbird_Utils::is_member() ) {
							esc_html_e( 'Install Smush Pro', 'wphb' );
						} else {
							esc_html_e( 'Install Smush', 'wphb' );
						} ?>
					</a>
				</div>
				<!-- Plugin is installed but not active -->
			<?php elseif ( $is_installed && ! $is_active ) : ?>
				<div class="wphb-notice wphb-notice-warning">
					<p><?php esc_html_e( 'WP Smush is installed but not activated! Activate and set up now to reduce page load time.', 'wphb' ); ?></p>
				</div>
				<div class="buttons">
					<?php if ( $is_pro ) : ?>
						<a href="<?php echo esc_url( $activate_pro_url ); ?>" class="button" id="smush-activate">
							<?php esc_html_e( 'Activate Smush Pro', 'wphb' ); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( $activate_url ); ?>" class="button" id="smush-activate">
							<?php esc_html_e( 'Activate Smush', 'wphb' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<!-- Plugin is installed and active -->
			<?php elseif ( $is_installed && $is_active ) : ?>
				<?php if ( ( 0 === $smush_data['bytes'] || 0 === $smush_data['percent'] ) && 0 === $unsmushed ) : ?>
					<div class="wphb-notice wphb-notice-success">
						<p><?php esc_html_e( 'WP Smush is installed but no images have been smushed yet. Get in there and smush away!', 'wphb' ); ?></p>
					</div>
				<?php elseif ( $unsmushed > 0 ) : ?>
					<div class="wphb-notice wphb-notice-warning">
						<?php /* translators: %s: uncompressed images */ ?>
						<p><?php printf( esc_html__( 'There are %s images that need smushing!', 'wphb' ), absint( $unsmushed ) ); ?></p>
					</div>
				<?php else : ?>
					<div class="wphb-notice wphb-notice-success">
						<p><?php printf( esc_html__( "WP Smush is installed. So far you've saved %s of space. That's a total savings of %s. Nice one!", 'wphb' ), $smush_data['human'], number_format_i18n( $smush_data['percent'], 2 ) . '%' ); ?></p>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<!-- Regular version is installed and the user in not a PRO member -->
			<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
				<div class="content-box content-box-two-cols-image-left">
					<div class="wphb-block-entry-content wphb-upsell-free-message">
						<p>
							<?php printf(
								__( 'Did you know WP Smush Pro delivers up to 2x better compression, allows you to smush your originals and removes any bulk smushing limits? <a href="%s" target="_blank">Try it absolutely FREE</a>', 'wphb' ),
								WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_smush_upsell_link' )
							); ?>
						</p>
					</div>
				</div>
			<?php endif; ?>
		</div><!-- end content -->

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->