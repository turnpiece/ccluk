<?php
$polls_retain_number = get_option( 'forminator_retain_votes_interval_number', 0 );
$polls_retain_unit   = get_option( 'forminator_retain_votes_interval_unit', 'days' );

$cform_retain_number             = get_option( 'forminator_retain_submissions_interval_number', 0 );
$cfrom_retain_unit               = get_option( 'forminator_retain_submissions_interval_unit', 'days' );
$form_submission_erasure_enabled = get_option( 'forminator_enable_erasure_request_erase_form_submissions', false );
?>
<div class="wpmudev-hidden-popup wpmudev-popup-form" style="display: none">

	<div class="wpmudev-row">
		<div class="wpmudev-col col-12">
			<table class="wpmudev-table">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Forms', Forminator::DOMAIN ); ?></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>

						<label><?php esc_html_e( "Account erasure requests", Forminator::DOMAIN ); ?></label>
						<select class="wpmudev-select" name="erase_form_submissions">
							<option value="true" <?php selected( $form_submission_erasure_enabled, true ); ?>>
								<?php esc_html_e( "Remove Submissions", Forminator::DOMAIN ); ?></option>
							<option value="false" <?php selected( $form_submission_erasure_enabled, false ); ?>>
								<?php esc_html_e( "Retain Submissions", Forminator::DOMAIN ); ?></option>
						</select>
						<div style="padding-bottom: 10px;">
							<?php echo( sprintf( __( 'When handling an <a href="%s">account erasure request</a>, should form submission contains requested email address removed or retained ?',
							                         Forminator::DOMAIN ),
							                     esc_url
							                     ( admin_url( 'tools.php?page=remove_personal_data' ) ) ) ); ?>

						</div>

					</td>
				</tr>

				<tr>
					<td>

						<label><?php esc_html_e( "Submissions Retention", Forminator::DOMAIN ); ?></label>
						<div class="wpmudev-row">
							<div class="wpmudev-col col-md-4">
								<input class="wpmudev-input" type="number" min="1" name="submissions_retention_number" value="<?php echo esc_attr( $cform_retain_number ); ?>">
							</div>

							<div class=" wpmudev-col col-md-8">
								<select class="wpmudev-select" name="submissions_retention_unit">
									<option value="days" <?php selected( $cfrom_retain_unit, 'days' ); ?>>
										<?php esc_html_e( "Day(s)", Forminator::DOMAIN ); ?></option>
									<option value="weeks" <?php selected( $cfrom_retain_unit, 'weeks' ); ?>>
										<?php esc_html_e( "Week(s)", Forminator::DOMAIN ); ?></option>
									<option value="months" <?php selected( $cfrom_retain_unit, 'months' ); ?>>
										<?php esc_html_e( "Month(s)", Forminator::DOMAIN ); ?></option>
									<option value="years" <?php selected( $cfrom_retain_unit, 'years' ); ?>>
										<?php esc_html_e( "Years(s)", Forminator::DOMAIN ); ?></option>
								</select>
							</div>
						</div>
						<div style="padding-bottom: 10px;">
							<?php esc_html_e( 'Choose how long to retain form submissions. Leave the following options blank to retain form submissions forever.',
							                  Forminator::DOMAIN ); ?>

						</div>

					</td>
				</tr>

				</tbody>

			</table>

			<table class="wpmudev-table">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Polls', Forminator::DOMAIN ); ?></th>
				</tr>
				</thead>
				<tbody>

				<tr>
					<td>

						<label><?php esc_html_e( "IP address retention", Forminator::DOMAIN ); ?></label>
						<div class="wpmudev-row">
							<div class="wpmudev-col col-md-4">
								<input class="wpmudev-input" type="number" min="1" name="votes_retention_number" value="<?php echo esc_attr( $polls_retain_number ); ?>">
							</div>

							<div class=" wpmudev-col col-md-8">
								<select class="wpmudev-select" name="votes_retention_unit">
									<option value="days" <?php selected( $polls_retain_unit, 'days' ); ?>>
										<?php esc_html_e( "Day(s)", Forminator::DOMAIN ); ?></option>
									<option value="weeks" <?php selected( $polls_retain_unit, 'weeks' ); ?>>
										<?php esc_html_e( "Week(s)", Forminator::DOMAIN ); ?></option>
									<option value="months" <?php selected( $polls_retain_unit, 'months' ); ?>>
										<?php esc_html_e( "Month(s)", Forminator::DOMAIN ); ?></option>
									<option value="years" <?php selected( $polls_retain_unit, 'years' ); ?>>
										<?php esc_html_e( "Years(s)", Forminator::DOMAIN ); ?></option>
								</select>
							</div>
						</div>
						<div style="padding-bottom: 10px;">
							<?php esc_html_e( 'Choose how long to retain IP address before its getting anonymized.
							Keep in mind, that this IP address probably being used to checking multiple votes.
							Leave the following options blank to retain IP address forever.',
							                  Forminator::DOMAIN ); ?>

						</div>

					</td>
				</tr>

				</tbody>

			</table>

		</div>
	</div>

	<div class="wpmudev-row">
		<div class="wpmudev-col col-12">
			<button class="wpmudev-button wpmudev-action-done wpmudev-button-blue"
			        data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_save_privacy_settings' ) ); ?>">
				<?php esc_html_e( "Save Changes", Forminator::DOMAIN ); ?>
			</button>
		</div>
	</div>

</div>