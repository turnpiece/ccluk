<?php
$path		= forminator_plugin_url();
$entries	= $this->get_table();
$form_type	= $this->get_form_type();
$count		= $this->get_total_entries();
$per_page	= $this->get_per_page();
$total_page	= ceil( $count / $per_page );
?>

<?php if ( $count > 0 ) : ?>

	<form method="post" class="sui-box">

		<?php wp_nonce_field( 'forminator_quiz_bulk_action', 'forminatorEntryNonce' ); ?>

		<input type="hidden" name="form_id" value="<?php echo esc_attr( $this->form_id ); ?>"/>

		<div class="sui-box-body">

			<div class="fui-form-actions">

				<?php $this->bulk_actions(); ?>

				<div class="sui-pagination-wrap">

					<span class="sui-pagination-results"><?php if ( 1 === $count ) { printf( __( '%s result', Forminator::DOMAIN ), $count ); } else { printf( __( '%s results', Forminator::DOMAIN ), $count ); } // phpcs:ignore ?></span>

					<?php $this->paginate(); ?>

				</div>

			</div>

		</div>

		<table class="sui-table sui-accordion fui-table-listings">

			<thead>

				<tr>

					<th><label class="sui-checkbox">
						<input id="wpf-cform-check_all" type="checkbox">
						<span></span>
						<div class="sui-description"><?php esc_html_e( "ID", Forminator::DOMAIN ); ?></div>
					</label></th>

					<th colspan="5"><?php esc_html_e( "Date Submitted", Forminator::DOMAIN ); ?></th>

				</tr>

			</thead>

			<tbody>

				<?php
				$first_item 	= $count;
				$page_number 	= $this->get_paged();

				if ( $page_number > 1 ) {
					$first_item = $count - ( ( $page_number - 1 ) * $per_page );
				}

				foreach ( $entries as $entry ) :
					?>

					<tr class="sui-accordion-item">

						<td><label class="sui-checkbox">
							<input name="ids[]" value="<?php echo esc_attr( $entry->entry_id ); ?>" type="checkbox" id="quiz-answer-<?php echo esc_attr( $entry->entry_id ); ?>">
							<span></span>
							<div class="sui-description"><?php echo esc_attr( $first_item ); ?></div>
						</label></td>

						<td colspan="5"><?php echo date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $entry->date_created_sql ) ); // WPCS: XSS ok. ?>
							<span class="sui-accordion-open-indicator">
								<i class="sui-icon-chevron-down"></i>
							</span>
						</td>

					</tr>

					<tr class="sui-accordion-item-content">

						<td colspan="6">

							<div class="sui-box">

								<div class="sui-box-body">

									<h2><?php echo forminator_get_form_name( $this->form_id, 'quiz'); // WPCS: XSS ok. ?></h2>

								</div>

								<?php
								if ( 'knowledge' === $form_type ) {

									$meta	= $entry->meta_data['entry']['value'];
									$total	= 0;
									$right	= 0;
									?>

									<table class="sui-table fui-table-dashboard">

										<thead>

											<tr>

												<th><?php esc_html_e( "Question", Forminator::DOMAIN ); ?></th>

												<th><?php esc_html_e( "Answer", Forminator::DOMAIN ); ?></th>

											</tr>

										</thead>

										<tbody>

											<?php
											foreach ( $meta as $answer ) :

												$total ++;

												if ( $answer['isCorrect'] ) {
													$right ++;
												}

												$user_answer = $answer['answer'];
												?>

												<tr>

													<td><?php echo esc_html( $answer['question'] ); ?></td>

													<td>
														<?php
														if ( $answer['isCorrect'] ) {

														echo '<span class="sui-tag sui-tag-success">' . $user_answer . '</span>'; // WPCS: XSS ok.

													} else {

														echo '<span class="sui-tag sui-tag-error">' . $user_answer . '</span>'; // WPCS: XSS ok.

													}
													?>
													</td>

												</tr>

											<?php endforeach; ?>

										</tbody>

									</table>

									<div class="sui-box-footer">

										<p><?php echo sprintf( __( "You got <strong>%s / %s</strong> correct answers.", Forminator::DOMAIN ), $right, $total ); // phpcs:ignore ?></p>

									</div>

								<?php
								} else {

									$meta = $entry->meta_data['entry']['value'][0]['value'];
									?>

									<?php if ( isset( $meta['answers'] ) && is_array( $meta['answers'] ) ) : ?>

										<table class="sui-table fui-table-dashboard">

											<thead>

												<tr>

													<th><?php esc_html_e( "Question", Forminator::DOMAIN ); ?></th>

													<th><?php esc_html_e( "Answer", Forminator::DOMAIN ); ?></th>

												</tr>

											</thead>

											<tbody>

												<?php foreach ( $meta['answers'] as $answer ) : ?>

													<tr>

														<td><?php echo $answer['question']; // WPCS: XSS ok. ?></td>

														<td><?php echo $answer['answer']; // WPCS: XSS ok. ?></td>

													</tr>

												<?php endforeach; ?>

											</tbody>

										</table>

									<?php endif; ?>

									<div class="sui-box-footer">

										<p><?php printf( __( "<strong>Quiz Result:</strong> %s", Forminator::DOMAIN ), $meta['result']['title'] ); // WPCS: XSS ok. ?></p>

									</div>

								<?php } ?>

							</div>

						</td>

					</tr>

				<?php
					$first_item--;

				endforeach;
				?>

			</tbody>

		</table>

		<div class="sui-box-footer">

			<div class="fui-form-actions">

				<?php $this->bulk_actions( 'bottom' ); ?>

				<div class="sui-pagination-wrap">

					<span class="sui-pagination-results"><?php if ( 1 === $count ) { printf( __( '%s result', Forminator::DOMAIN ), $count ); } else { printf( __( '%s results', Forminator::DOMAIN ), $count ); } // phpcs:ignore ?></span>

					<?php $this->paginate(); ?>

				</div>

			</div>

		</div>

	</form>

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

					<td><form class="wpmudev-export--form" method="post">
						<input type="hidden" name="forminator_export" value="1">
						<input type="hidden" name="form_id" value="<?php echo esc_attr( $this->form_id ); ?>">
						<input type="hidden" name="form_type" value="quiz">
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

	<?php
	else :
	?>

	<div class="sui-box">

		<div class="sui-box-body sui-block-content-center">

			<img src="<?php echo $path . 'assets/img/forminator-submissions.png'; // WPCS: XSS ok. ?>"
				srcset="<?php echo $path . 'assets/img/forminator-submissions.png'; // WPCS: XSS ok. ?> 1x, <?php echo $path . 'assets/img/forminator-submissions@2x.png'; // WPCS: XSS ok. ?> 2x" alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
				class="sui-image sui-image-center fui-image" />

			<h2><?php echo forminator_get_form_name( $this->form_id, 'quiz'); // WPCS: XSS ok. ?></h2>

			<p class="fui-limit-block-600 fui-limit-block-center"><?php esc_html_e( "You haven’t received any submissions for this quiz yet. When you do, you’ll be able to view all the data here.", Forminator::DOMAIN ); ?></p>

		</div>

	</div>

<?php endif; ?>