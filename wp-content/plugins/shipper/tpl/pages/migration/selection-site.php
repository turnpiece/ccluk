<?php
/**
 * Shipper templates: site selection subpage
 *
 * @package shipper
 */

?>
<div class="sui-box shipper-select-site">
	<div class="sui-box-body">
		<div class="shipper-content">

			<div class="shipper-header">
				<i class="sui-icon-shipper-anchor" aria-hidden="true"></i>
				<h2><?php echo esc_html( sprintf( __( 'Ready to ship it, %s?', 'shipper' ), shipper_get_user_name() ) ); ?></h2>
			</div>

			<p>
			<?php
			if ( 'export' === $type ) {
				esc_html_e( 'Great, where would you like to migrate this website to?', 'shipper' );
			} else {
				esc_html_e( 'Great, what website would you like to import?', 'shipper' );
				echo ' ';
				esc_html_e( 'If you don\'t see your website in the list, make sure you\'ve got both Shipper and the WPMU DEV Dashboard installed on your source website.', 'shipper' );
			}
			?>
			</p>

			<form action="<?php echo esc_url( add_query_arg( 'run', 'yes' ) ); ?>" method="GET">
				<input type="hidden" name="page" value="shipper" />
				<input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>" />

				<div class="shipper-form">
					<div class="shipper-form-bit">
						<a href="#refresh-locations"
							data-tooltip="<?php esc_attr_e( 'Reload list', 'shipper' ); ?>"
							class="shipper-button-refresh sui-tooltip shipper-work-activator">
							<i class="sui-icon-update" aria-hidden="true"></i>
						</a>
					</div>
					<div class="shipper-form-bit shipper-selection">
						<select name="site">
						<?php foreach ( $destinations->get_data() as $item ) { ?>
							<?php if ( $destinations->is_current( $item ) ) { continue; } ?>
							<option value="<?php echo esc_attr( $item['site_id'] ); ?>">
								<?php echo esc_html( $item['domain'] ); ?>
							</option>
						<?php } ?>
						</select>
					</div><?php // .shipper-form-bit ?>

					<div class="shipper-form-bit">
						<button type="submit" class="sui-button sui-button-primary">
							<i class="sui-icon-arrow-right" aria-hidden="true"></i>
							<span><?php esc_html_e( 'Migrate', 'shipper' ); ?></span>
						</button>
					</div><?php // .shipper-form-bit ?>
				</div><?php // .shipper-form ?>

			</form>

			<div>
				<a href="<?php echo esc_url( remove_query_arg( 'type' ) ); ?>" class="shipper-button-back shipper-work-activator">
					<i class="sui-icon-arrow-left" aria-hidden="true"></i>
					<span><?php esc_html_e( 'Go back', 'shipper' ); ?></span>
				</a>
			</div>

			<div class="sui-notice sui-notice-info shipper-page-notice">
				<p>
				<?php if ( 'export' === $type ) { ?>
					<?php esc_html_e( 'Want to add a new destination?', 'shipper' ); ?>
					<?php esc_html_e( 'Tap &quot;Add destination&quot; at the top of this page.', 'shipper' ); ?>
				<?php } else { ?>
					<?php esc_html_e( 'Note, Shipper can\'t import local development sites.', 'shipper' ); ?>
					<?php esc_html_e( 'To migrate a local site to a staging or live server, initiate the migration from the local site itself.', 'shipper' ); ?>

				<?php } ?>
				</p>
			</div>

		</div><?php // .shipper-content ?>
	</div><?php // .sui-box-body ?>
</div><?php // .sui-box ?>

<?php $this->render( 'modals/migration-deletedest' ); ?>
<?php $this->render( 'msgs/migration-destdelete-success' ); ?>