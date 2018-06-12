<?php
$path  = forminator_plugin_url();

/** @var $this Forminator_CForm_View_Page */
$count = $this->total_entries();
?>

<?php if ( $count > 0 ) : ?>

	<div class="sui-row">

		<?php // Display Settings ?>
		<div class="sui-col-md-6">

			<form method="POST" class="sui-box">

				<?php wp_nonce_field( 'forminatorCustomFormEntries', 'forminatorEntryNonce' ); ?>

				<div class="sui-box-header">

					<h2 class="sui-box-title"><?php esc_html_e( "Display Settings", Forminator::DOMAIN ); ?></h2>

				</div>

				<div class="sui-box-body">

					<div class="fui-multicheck-actions">

						<span class="fui-multicheck-action-resume"><?php $this->fields_header(); ?></span>

						<span class="fui-multicheck-action-selectors"><?php printf( __( "Select <a class='wpmudev-check-all' href='%s'>All</a> | <a class='wpmudev-uncheck-all' href='%s'>None</a>" ), "#", "#" ); // phpcs:ignore ?></span>

					</div>

					<ul class="fui-multicheck">

						<?php
						$ignored_field_types	= Forminator_Form_Entry_Model::ignored_fields();

						foreach ( $this->get_fields() as $field ) {

							$label       = $field->__get( 'field_label' );
							$field_type  = $field->__get( 'type' );

							if ( in_array( $field_type, $ignored_field_types, true ) ) {
								continue;
							}

							if ( !$label ) {
								$label =  $field->title;
							}

							if ( empty( $label ) ) {
								$label = ucfirst( $field_type );
							}

							$slug	= isset( $field->slug ) ? $field->slug : sanitize_title( $label );
							?>

							<li class='fui-multicheck-item'>
								<label class='sui-checkbox' for="<?php echo esc_attr( $slug ); ?>-enable">
									<input type="checkbox" id="<?php echo esc_attr( $slug ); ?>-enable" name="field[]" <?php $this->checked_field( $slug ); ?> value="<?php echo esc_attr( $slug ); ?>">
									<span></span>
									<div for="<?php echo esc_attr( $slug ); ?>-enable" class="sui-description"><?php echo esc_html( $label ); ?></div>
								</label>
							</li>

						<?php } ?>

					</ul>

				</div>

				<div class="sui-box-footer">

					<button class="sui-button"><?php esc_html_e( "Filter Entries", Forminator::DOMAIN ); ?></button>

				</div>

			</form>

		</div>

		<?php // Export Entries ?>
		<div class="sui-col-md-6">

			<div class="sui-box">

				<div class="sui-box-header">

					<h2 class="sui-box-title"><?php esc_html_e( "Export Settings", Forminator::DOMAIN ); ?></h2>

				</div>

				<div class="sui-box-body">

					<p><?php esc_html_e( "You can do manual exports or schedule automatic exports and receive them on your mailbox.", Forminator::DOMAIN ); ?></p>

				</div>

				<table class="sui-table sui-accordion fui-table-exports">

					<tbody>

						<tr>

							<td><?php esc_html_e( "Manual Exports", Forminator::DOMAIN ); ?></td>

							<td><form method="post">
								<input type="hidden" name="forminator_export" value="1">
								<input type="hidden" name="form_id" value="<?php echo esc_attr( $this->form_id ); ?>">
								<input type="hidden" name="form_type" value="cform">
								<?php wp_nonce_field( 'forminator_export', '_forminator_nonce' ); ?>
								<button class="sui-button sui-button-primary"><?php esc_html_e( "Download", Forminator::DOMAIN ); ?></button>
							</form></td>

						</tr>

						<tr>

							<td><?php esc_html_e( "Scheduled Exports", Forminator::DOMAIN ); ?></td>

							<td><a href="/" class="sui-button wpmudev-open-modal" data-modal="exports-schedule"><?php esc_html_e( "Edit", Forminator::DOMAIN ); ?></a></td>

					</tbody>

				</table>

			</div>

		</div>

	</div>

	<?php // Entries ?>
	<form method="POST" class="sui-box">

		<?php wp_nonce_field( 'forminatorCustomFormEntries', 'forminatorEntryNonce' ); ?>

		<div class="sui-box-body">

			<div class="fui-form-actions">

				<?php $this->bulk_actions(); ?>

				<div class="sui-pagination-wrap">

					<span class="sui-pagination-results"><?php if ( 1 === $count ) { printf( esc_html__( '%s result', Forminator::DOMAIN ), $count ); } else { printf( esc_html__( '%s results', Forminator::DOMAIN ), $count ); } // phpcs:ignore ?></span>

					<?php $this->paginate(); ?>

				</div>

			</div>

		</div>

		<table class="sui-table sui-accordion fui-table-listings">

			<?php $this->entries_header(); ?>

			<tbody>

				<?php
				foreach ( $this->entries_iterator() as $entries ) {

					$entry_id    = $entries['id'];
					$db_entry_id = isset( $entries['entry_id'] ) ? $entries['entry_id'] : '';

					$summary       = $entries['summary'];
					$summary_items = $summary['items'];

					$detail       = $entries['detail'];
					$detail_items = $detail['items'];
					?>

					<tr class="sui-accordion-item" data-entry-id="<?php echo esc_attr($db_entry_id); ?>">

						<?php foreach ( $summary_items as $key => $summary_item ) { ?>

							<td colspan="<?php echo esc_attr( $summary_item['colspan'] ); ?>">
								<?php if( 1 === $summary_item['colspan'] ): ?>
								<label class="sui-checkbox">
									<input type="checkbox" name="entry[]" id="wpf-cform-module-<?php echo esc_attr( $db_entry_id ); ?>"
										   value="<?php echo esc_attr( $db_entry_id ); ?>">
									<span></span>
									<div class="sui-description"><?php echo esc_html( $summary_item['value'] ); ?></div>
								</label>
								<?php else: ?>
								<?php echo esc_html( $summary_item['value'] ); ?>
								<?php endif; ?>
								<?php if ( ! $summary['num_fields_left'] && ( count( $summary_items ) - 1 ) === $key ) { ?>
									<span class="sui-accordion-open-indicator">
									<i class="sui-icon-chevron-down"></i>
								</span>
								<?php } ?>
							</td>

						<?php } ?>

						<?php if ( $summary['num_fields_left'] ) { ?>

							<td colspan="3">+ <?php echo esc_html( $summary['num_fields_left'] ); ?> other fields
								<span class="sui-accordion-open-indicator">
									<i class="sui-icon-chevron-down"></i>
								</span>
							</td>

						<?php } ?>

					</tr>

					<tr class="sui-accordion-item-content">

						<td colspan="<?php echo esc_attr( $detail['colspan'] ); ?>">

							<div class="sui-box">

								<div class="sui-box-body">

									<h2><?php printf( esc_html__( "Submission #%s", Forminator::DOMAIN ), $entry_id ); // WPCS: XSS ok. ?></h2>

									<?php foreach ( $detail_items as $detail_item ) { ?>

										<div class="fui-box-entries-resume">

											<div class="fui-box-entries-field-title"><?php echo esc_html( $detail_item['label'] ); ?></div>

											<div class="fui-box-entries-field-content">

												<?php $sub_entries = $detail_item['sub_entries']; ?>

												<?php
												if ( empty( $sub_entries ) ) {

													echo ( $detail_item['value'] ); // wpcs xss ok. html output intended

												} else {

													foreach ( $sub_entries as $sub_entry ) {
														?>

														<strong><?php echo esc_html( $sub_entry['label'] ); ?></strong>: <?php echo ( $sub_entry['value'] ); // wpcs xss ok. html output intended ?><br />

													<?php
													}

												}
												?>

											</div>

										</div>

									<?php } ?>

								</div>

								<div class="sui-box-footer">

									<button type="button" class="sui-button sui-button-ghost sui-button-red wpmudev-open-modal"
											data-modal="delete-module"
											data-form-id="<?php echo esc_attr( $db_entry_id ); ?>"
											data-nonce="<?php echo wp_create_nonce( 'forminatorCustomFormEntries' ); // WPCS: XSS ok. ?>"><i class="sui-icon-trash" aria-hidden="true"></i> <?php esc_html_e( "Delete", Forminator::DOMAIN ); ?></button>

								</div>

							</div>

						</td>

					</tr>

				<?php } ?>

			</tbody>

		</table>

		<div class="sui-box-footer">

			<div class="fui-form-actions">

				<?php $this->bulk_actions( 'bottom' ); ?>

				<div class="sui-pagination-wrap">

					<span class="sui-pagination-results">
						<?php
						if ( 1 === $count ) {
							echo esc_html( sprintf( __( '%s result', Forminator::DOMAIN ), $count ) );
						} else {
							echo esc_html( sprintf( __( '%s results', Forminator::DOMAIN ), $count ) );
						}
						?>
					</span>

					<?php $this->paginate(); ?>

				</div>

			</div>

		</div>

	</form>

<?php else : ?>

	<div class="sui-box">

		<div class="sui-box-body sui-block-content-center">

			<img src="<?php echo $path . 'assets/img/forminator-submissions.png'; // WPCS: XSS ok. ?>"
				srcset="<?php echo $path . 'assets/img/forminator-submissions.png'; // WPCS: XSS ok. ?> 1x, <?php echo $path . 'assets/img/forminator-submissions@2x.png'; // WPCS: XSS ok. ?> 2x" alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
				class="sui-image sui-image-center fui-image" />

			<h2><?php echo forminator_get_form_name( $this->form_id, 'custom_form' ); // WPCS: XSS ok. ?></h2>

			<p class="fui-limit-block-600 fui-limit-block-center"><?php esc_html_e( "You haven’t received any submissions for this form yet. When you do, you’ll be able to view all the data here.", Forminator::DOMAIN ); ?></p>

		</div>

	</div>

<?php endif; ?>