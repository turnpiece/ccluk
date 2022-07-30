<?php
/**
 * Shipper package migration templates: existing migration, additional info template
 *
 * @since v1.1
 * @package shipper
 */

$model = new Shipper_Model_Stored_Package();
$meta  = new Shipper_Model_Stored_PackageMeta();
?>

<div class="sui-accordion shipper-additional-info">

	<div class="sui-accordion-item">

		<div class="sui-accordion-item-header">
			<div><b><?php esc_html_e( 'Additional Details', 'shipper' ); ?></b></div>
			<div>
				<?php esc_html_e( 'This section contains complete details about the package', 'shipper' ); ?>
				<button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item"><i
							class="sui-icon-chevron-down" aria-hidden="true"></i></button>
			</div>
		</div><!-- sui-accordion-item-header -->

		<div class="sui-accordion-item-body">

			<div class="sui-box">
				<div class="sui-box-body">

					<div class="shipper-info-group">

						<div class="shipper-info-item">
							<div class="shipper-info-item-title">
								<?php esc_html_e( 'Package Name', 'shipper' ); ?>
							</div>
							<div class="shipper-info-item-body">
								<?php
								echo esc_html(
									$model->get( Shipper_Model_Stored_Package::KEY_NAME )
								);
								?>
							</div><!-- shipper-info-item-body -->
						</div><!-- shipper-info-item -->

						<div class="shipper-info-item">
							<div class="shipper-info-item-title">
								<?php esc_html_e( 'Created on', 'shipper' ); ?>
							</div>
							<div class="shipper-info-item-body">
								<?php
								$date = $model->get(
									Shipper_Model_Stored_Package::KEY_DATE
								);
								echo esc_html( gmdate( 'Y-m-d H:i:s', $date ) );
								$created = $model->get(
									Shipper_Model_Stored_Package::KEY_CREATED
								);
								if ( ! empty( $created ) ) {
									echo esc_html( '(' . human_time_diff( $created, $date ) . ')' );
								}
								?>
							</div><!-- shipper-info-item-body -->
						</div><!-- shipper-info-item -->

						<div class="shipper-info-item">
							<div class="shipper-info-item-title">
								<?php esc_html_e( 'Size', 'shipper' ); ?>
							</div>
							<div class="shipper-info-item-body">
								<?php echo esc_html( size_format( $model->get_size() ) ); ?>
							</div><!-- shipper-info-item-body -->
						</div><!-- shipper-info-item -->

					</div> <!-- shipper-info-group -->

					<div class="shipper-info-group">

						<div class="shipper-info-item">
							<div class="shipper-info-item-title">
								<?php esc_html_e( 'File Exclusions', 'shipper' ); ?>
							</div>
							<div class="shipper-info-item-body">
								<?php
								$items = $meta->get(
									$meta::KEY_EXCLUSIONS_FS,
									array()
								);
								echo wp_kses_post(
									join(
										'<br />',
										array_map( 'sanitize_text_field', $items )
									)
								);
								?>
							</div><!-- shipper-info-item-body -->
						</div><!-- shipper-info-item -->

						<div class="shipper-info-item">
							<div class="shipper-info-item-title">
								<?php esc_html_e( 'Database Exclusions', 'shipper' ); ?>
							</div>
							<div class="shipper-info-item-body">
								<?php
								$tables     = $meta->get( $meta::TABLES_PICKED, array() );
								$all_tables = Shipper_Helper_Template_Sorter::get_grouped_tables( $meta );
								$all_tables = array_merge(
									$all_tables[ Shipper_Helper_Template_Sorter::WP_TABLES ],
									$all_tables[ Shipper_Helper_Template_Sorter::NONWP_TABLES ],
									$all_tables[ Shipper_Helper_Template_Sorter::OTHER_TABLES ]
								);

								$items = ( array_diff( $all_tables, $tables ) );
								echo wp_kses_post(
									join(
										'<br />',
										array_map( 'sanitize_text_field', $items )
									)
								);
								?>
							</div><!-- shipper-info-item-body -->
						</div><!-- shipper-info-item -->

						<div class="shipper-info-item">
							<div class="shipper-info-item-title">
								<?php esc_html_e( 'Advanced Exclusions', 'shipper' ); ?>
							</div>
							<div class="shipper-info-item-body">
								<?php
								$items = $meta->get(
									$meta::KEY_EXCLUSIONS_XX,
									array()
								);

								echo wp_kses_post(
									join(
										'<br />',
										array_map( 'sanitize_text_field', $items )
									)
								);
								?>
							</div><!-- shipper-info-item-body -->
						</div><!-- shipper-info-item -->

					</div> <!-- shipper-info-group -->

					<div class="shipper-info-group">

						<div class="shipper-info-item">
							<div class="shipper-info-item-title">
								<?php esc_html_e( 'Password Protection', 'shipper' ); ?>
							</div>
							<div class="shipper-info-item-body">
								<?php
								echo esc_html(
									$model->get( Shipper_Model_Stored_Package::KEY_PWD )
								);
								?>
							</div><!-- shipper-info-item-body -->
						</div><!-- shipper-info-item -->

					</div> <!-- shipper-info-group -->

				</div><!-- sui-box-body -->

				<div class="sui-box-footer">
					<div class="sui-actions-left">
						<button
							type="button"
							class="sui-button sui-button-red"
							data-modal-open="shipper-delete-modal"
							data-modal-open-focus="shipper-delete-modal"
							data-modal-close-focus="shipper-delete-modal"
							data-modal-mask="true"
						>
							<i class="sui-icon-trash" aria-hidden="true"></i>
							<?php esc_html_e( 'Delete', 'shipper' ); ?>
						</button>
					</div>
					<div class="sui-actions-right">
						<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper-tools' ) ); ?>" type="button" class="sui-button shipper-logs">
							<i class="sui-icon-eye" aria-hidden="true"></i>
							<?php esc_html_e( 'View Logs', 'shipper' ); ?>
						</a>
					</div>
				</div><!-- sui-box-footer -->

			</div><!-- sui-box -->

		</div><!-- sui-accordion-item-body -->

	</div><!-- sui-accordion-item -->

</div><!-- sui-accordion -->




<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="shipper-delete-modal"
		class="sui-modal-content sui-content-fade-in"
		aria-modal="true"
		aria-labelledby="shipper-delete-modal-title"
		aria-describedby="shipper-delete-modal-description"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<button class="sui-button-icon sui-button-float--right shipper-cancel">
					<i class="sui-icon-close sui-md" aria-hidden="true"></i>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog window', 'shipper' ); ?></span>
				</button>

				<h3 id="shipper-delete-modal-title" class="sui-box-title sui-lg">
					<?php esc_html_e( 'Delete Package', 'shipper' ); ?>
				</h3>

				<p class="shipper-description" id="shipper-delete-modal-description">
					<?php esc_html_e( 'Are you sure you wish to permanently delete this package?', 'shipper' ); ?>
				</p>
			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">
				<button class="sui-button sui-button-ghost shipper-cancel" data-modal-close="">
					<?php esc_html_e( 'Cancel', 'shipper' ); ?>
				</button>
				<button class="sui-button sui-button-red shipper-delete">
					<i class="sui-icon-trash" aria-hidden="true"></i>
					<?php esc_html_e( 'Delete', 'shipper' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>