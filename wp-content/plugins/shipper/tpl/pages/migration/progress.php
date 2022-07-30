<?php
/**
 * Shipper migrate page templates: migration progress page
 *
 * @package shipper
 */

$target = $destinations->get_by_site_id( $site );
$title  = 'export' === $type ? __( 'Export', 'shipper' ) : __( 'Import', 'shipper' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
?>
<div class="<?php echo esc_attr( Shipper_Helper_Assets::get_page_class( 'migrate' ) ); ?>" >
	<?php if ( ! $notice_dismissed ) : ?>
	<div class="sui-floating-notices shipper-migration-starts" data-wpnonce="<?php echo esc_attr( wp_create_nonce( 'shipper_api_notice_dismissed' ) ); ?>">
		<div class="sui-notice sui-notice-top sui-notice-info sui-can-dismiss">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: site username */
								__( '%s, your migration is underway! As long as your site isn’t a local installation, you can close this tab and we’ll email you when it’s all done.', 'shipper' ),
								shipper_get_user_name()
							)
						);
						?>
					</p>
				</div>

				<div class="sui-notice-actions">
					<span class="sui-notice-dismiss sui-button-icon">
						<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
					</span>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<div class="sui-header">
		<h1 class="sui-header-title">
			<?php echo esc_html( $title ); ?>
		</h1>
		<?php $this->render( 'pages/migration/view-docs' ); ?>
	</div>

	<div class="sui-box shipper-page-migrate-progress">
		<div class="sui-box-body">

		<div class="shipper-actions">
			<div class="shipper-actions-left">
				<a href="<?php echo esc_url( remove_query_arg( array( 'begin', 'check', 'site', 'type' ) ) ); ?>" class="shipper-button-back shipper-migration-cancel">
					<i class="sui-icon-arrow-left" aria-hidden="true"></i>
					<span><?php esc_html_e( 'Go back', 'shipper' ); ?></span>
				</a>
			</div>
			<div class="shipper-actions-right">
				<a href="<?php echo esc_url( remove_query_arg( array( 'begin', 'site', 'type', 'check' ) ) ); ?>" class="shipper-button-back shipper-migration-cancel">
					<i class="sui-icon-close" aria-hidden="true"></i>
					<span><?php esc_html_e( 'Go back', 'shipper' ); ?></span>
				</a>
			</div>
		</div>

			<div class="shipper-content">

				<?php
				$this->render(
					'pages/migration/progress-bar',
					array(
						'type'      => $type,
						'site'      => $site,
						'size'      => $size,
						'time'      => $time,
						'time_unit' => $time_unit,
						'progress'  => $progress,
					)
				);
				?>

				<?php
				$this->render(
					'pages/migration/progress-done',
					array(
						'type'         => $type,
						'destinations' => $destinations,
						'site'         => $site,
					)
				);
				?>

				<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
			</div><?php // .shipper-content ?>

		<?php $this->render( 'modals/migration-cancel' ); ?>

		</div>
	</div>

</div> <?php // .sui-wrap ?>