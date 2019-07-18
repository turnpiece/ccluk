<?php
/**
 * Smush meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $activate_pro_url  URL to activate Pro version.
 * @var string $activate_url      URL to activate Free version.
 * @var bool   $can_activate      Can the user activate Smush.
 * @var bool   $is_active         Activation status.
 * @var bool   $is_installed      Installation status.
 * @var bool   $is_pro            Pro status.
 * @var array  $smush_data        Smush data.
 * @var int    $unsmushed         Number of uncompressed images.
 */

?>
<div class="<?php echo ! WP_Hummingbird_Utils::is_member() ? 'sui-box-body' : ''; ?>">
	<p class="sui-margin-bottom">
		<?php esc_html_e( 'Automatically compress and optimize your images with our super popular Smush plugin.', 'wphb' ); ?>
	</p>

	<!-- No plugin is installed -->
	<?php if ( ! $is_installed ) : ?>
		<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_link( 'smush' ) ); ?>" class="sui-button sui-button-blue" id="smush-install">
			<?php
			if ( WP_Hummingbird_Utils::is_member() ) {
				esc_html_e( 'Install Smush Pro', 'wphb' );
			} else {
				esc_html_e( 'Install Smush', 'wphb' );
			}
			?>
		</a>

	<!-- Plugin is installed but not active -->
	<?php elseif ( $is_installed && ! $is_active && $can_activate ) : ?>
		<div class="sui-notice sui-notice-warning">
			<p><?php esc_html_e( 'WP Smush is installed but not activated! Activate and set up now to reduce page load time.', 'wphb' ); ?></p>
		</div>
		<?php if ( $is_pro ) : ?>
			<a href="<?php echo esc_url( $activate_pro_url ); ?>" class="sui-button sui-button-blue" id="smush-activate">
				<?php esc_html_e( 'Activate Smush Pro', 'wphb' ); ?>
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url( $activate_url ); ?>" class="sui-button sui-button-blue" id="smush-activate">
				<?php esc_html_e( 'Activate Smush', 'wphb' ); ?>
			</a>
		<?php endif; ?>

	<!-- Plugin is installed and active -->
	<?php elseif ( $is_installed && $is_active ) : ?>
		<?php if ( ( 0 === $smush_data['bytes'] || 0 === $smush_data['percent'] ) && 0 === $unsmushed ) : ?>
			<div class="sui-notice sui-notice-success">
				<p><?php esc_html_e( 'WP Smush is installed but no images have been smushed yet. Get in there and smush away!', 'wphb' ); ?></p>
			</div>
		<?php elseif ( $unsmushed > 0 ) : ?>
			<div class="sui-notice sui-notice-warning">
				<?php /* translators: %s: uncompressed images */ ?>
				<p><?php printf( esc_html__( 'There are %s images that need smushing!', 'wphb' ), absint( $unsmushed ) ); ?></p>
			</div>
		<?php else : ?>
			<div class="sui-notice sui-notice-success">
				<p><?php printf( esc_html__( "WP Smush is installed. So far you've saved %1\$s of space. That's a total savings of %2\$s. Nice one!", 'wphb' ), $smush_data['human'], number_format_i18n( $smush_data['percent'], 2 ) . '%' ); ?></p>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>

<!-- Regular version is installed and the user in not a PRO member -->
<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="sui-box-settings-row sui-upsell-row sui-no-padding-top">
		<img class="sui-image sui-upsell-image"
			 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/smush-share-widget.png' ); ?>"
			 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/smush-share-widget@2x.png' ); ?> 2x"
			 alt="<?php esc_attr_e( 'Get WP Smush Pro', 'wphb' ); ?>">

		<div class="sui-upsell-notice sui-margin-bottom">
			<p>
				<?php
				printf(
					__( 'Did you know WP Smush Pro delivers up to 2x better compression, allows you to smush your originals and removes any bulk smushing limits? <a href="%s" target="_blank">Try it absolutely FREE</a>', 'wphb' ),
					WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_smush_upsell_link' )
				);
				?>
			</p>
		</div>
	</div>
<?php endif; ?>
