<?php
/**
 * Shipper modal template partials: migration ready to begin
 *
 * @package shipper
 */

$cancel_url = remove_query_arg(
	array(
		'begin',
		'check',
		'site',
		'type',
	)
);
?>

<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="shipper-migration-ready"
		class="sui-modal-content"
	>
		<div class="sui-box" role="document">
			<div class="sui-box-body">
				<button
					data-cancel-url="<?php echo esc_url( $cancel_url ); ?>"
					data-wpnonce="<?php echo esc_attr( wp_create_nonce( 'shipper-reset-migration' ) ); ?>"
					data-a11y-dialog-hide=""
					class="sui-dialog-close">
				</button>
				<h3>
					<?php if ( 'export' === $type ) { ?>
						<?php esc_html_e( 'Ready to migrate!', 'shipper' ); ?>
					<?php } else { ?>
						<?php esc_html_e( 'Ready to import!', 'shipper' ); ?>
					<?php } ?>
				</h3>

				<?php
				$this->render(
					'pages/migration/sourcedest-tag',
					array(
						'destinations' => $destinations,
						'site'         => $site,
					)
				);
				?>

				<div class="sui-notice sui-notice-success">
					<p>
						<?php esc_html_e( 'Pre-flight check passed.', 'shipper' ); ?>
					<?php if ( 'import' === $type ) { ?>
						<?php esc_html_e( 'Let\'s import your website!', 'shipper' ); ?>
					<?php } ?>
					</p>
				</div>

				<div class="shipper-content">
					<p>
					<?php if ( 'export' === $type ) { ?>
						<?php esc_html_e( 'You\'re ready to go!', 'shipper' ); ?>
						<?php esc_html_e( 'Note that Shipper will overwrite any existing files or database tables on your destination website.', 'shipper' ); ?>
					<?php } else { ?>
						<?php /* translators: %s: admin username. */ ?>
						<?php echo esc_html( sprintf( __( '%s, importing will overwrite any files or database tables on this website.', 'shipper' ), shipper_get_user_name() ) ); ?>
					<?php } ?>
						<?php esc_html_e( 'Please make sure to have a backup of your destination site so you can easily restore it if needed.', 'shipper' ); ?>
					</p>
					<p>
						<a href="<?php echo esc_url( add_query_arg( 'begin', 'true' ) ); ?>" class="sui-button sui-button-primary">
						<?php if ( 'export' === $type ) { ?>
							<?php esc_html_e( 'Begin migration', 'shipper' ); ?>
						<?php } else { ?>
							<?php esc_html_e( 'Begin import', 'shipper' ); ?>
						<?php } ?>
						</a>
					</p>
					<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
				</div>

			</div> <!-- .sui-box-body -->
		</div> <!-- .sui-box -->

	</div> <!-- .sui-modal-content -->
</div> <!-- .sui-modal -->

