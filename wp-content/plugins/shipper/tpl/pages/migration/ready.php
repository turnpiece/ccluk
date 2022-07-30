<?php
/**
 * Shipper migrate pages: begin migration partial
 *
 * @package shipper
 */

?>
<div class="sui-box shipper-migration-ready">
	<div class="sui-box-body">

		<div class="shipper-actions">
			<div class="shipper-actions-left">
				<a href="<?php echo esc_url( remove_query_arg( 'check' ) ); ?>" class="shipper-button-back clear-db-prefix">
					<i class="sui-icon-arrow-left" aria-hidden="true"></i>
					<span><?php esc_html_e( 'Go back', 'shipper' ); ?></span>
				</a>
			</div>
			<div class="shipper-actions-right">
				<a href="<?php echo esc_url( remove_query_arg( array( 'site', 'type', 'check' ) ) ); ?>" class="shipper-button-back">
					<i class="sui-icon-close" aria-hidden="true"></i>
					<span><?php esc_html_e( 'Go back', 'shipper' ); ?></span>
				</a>
			</div>
		</div>

		<div class="shipper-content">

			<div class="shipper-header">
				<i class="sui-icon-shipper-anchor" aria-hidden="true"></i>
				<h2><?php echo esc_html( __( 'Ready to ship?', 'shipper' ) ); ?></h2>
				<?php
					$this->render( 'tags/domains-tag' );
				?>
			</div>

			<table class="sui-table shipper-migration-ready-table">
				<tbody>
				<tr>
					<td class="sui-table-item-title">
						<?php esc_html_e( 'Package Size', 'shipper' ); ?>
					</td>
					<td class="shipper-align-right">
						<?php echo esc_html( $size ); ?>
					</td>
				</tr>

				<tr>
					<td class="sui-table-item-title">
						<?php esc_html_e( 'Migration ETA', 'shipper' ); ?>
					</td>
					<td class="shipper-align-right">
						<?php
						echo esc_html(
							/* translators: %s: migration eta time. */
							sprintf( __( 'Up to %s', 'shipper' ), $time )
						);
						?>

						<?php if ( 'hours' === $time_unit ) : ?>
							<span
								class="sui-tooltip sui-tooltip-constrained"
								style="--tooltip-width: 340px;"
								data-tooltip="<?php esc_html_e( 'Looks like a long time? That’s because the API method causes no load to your server and is super reliable. If you’d like a much quicker migration, use the Package Migration method – you can upload a package of your site onto any server (local or live) and be migrated in a matter of minutes.', 'shipper' ); ?>"
							>
								<i class="sui-icon-info" aria-hidden="true"></i>
							</span>
						<?php endif; ?>
					</td>
				</tr>
				</tbody>
			</table>

			<p>
				<a href="<?php echo esc_url( add_query_arg( 'begin', 'true' ) ); ?>"
					class="sui-button sui-button-primary">
					<?php esc_html_e( 'Begin migration', 'shipper' ); ?>
				</a>
			</p>

			<p>
				<?php esc_html_e( 'Note that Shipper overwrites any existing files or database tables on your destination website. Please make sure you have a backup.', 'shipper' ); ?>
			</p>

			<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
		</div><?php // .shipper-content ?>
	</div><?php // .sui-box-body ?>
</div><?php // .sui-box ?>