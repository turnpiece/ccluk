<?php
$polls_retain_number = get_option( 'forminator_retain_votes_interval_number', 0 );
$polls_retain_unit   = get_option( 'forminator_retain_votes_interval_unit', 'days' );

$cform_retain_number             = get_option( 'forminator_retain_submissions_interval_number', 0 );
$cfrom_retain_unit               = get_option( 'forminator_retain_submissions_interval_unit', 'days' );
$form_submission_erasure_enabled = get_option( 'forminator_enable_erasure_request_erase_form_submissions', false );
?>

<div class="sui-box-body wpmudev-popup-form">

	<h4 style="margin-bottom: 5px"><?php esc_html_e( 'Forms', Forminator::DOMAIN ); ?></h4>
	<div class="sui-form-field">
		<label class="sui-label"><?php esc_html_e( "Account erasure requests", Forminator::DOMAIN ); ?></label>
		<select class="sui-select" name="erase_form_submissions">
			<option value="true" <?php selected( $form_submission_erasure_enabled, true ); ?>>
				<?php esc_html_e( "Remove Submissions", Forminator::DOMAIN ); ?></option>
			<option value="false" <?php selected( $form_submission_erasure_enabled, false ); ?>>
				<?php esc_html_e( "Retain Submissions", Forminator::DOMAIN ); ?></option>
		</select>
		<span class="sui-description">
			<?php echo( sprintf( __( 'When handling an <a href="%s">account erasure request</a>, should form submission contains requested email address removed or retained ?',
			                         Forminator::DOMAIN ),
			                     esc_url
			                     ( admin_url( 'tools.php?page=remove_personal_data' ) ) ) );// wpcs: xss ok. ?></span>
	</div>

	<div class="sui-form-field">
		<label class="sui-label"><?php esc_html_e( "Submissions Retention", Forminator::DOMAIN ); ?></label>
		<div class="sui-row">
			<div class="sui-col-md-4">
				<input class="sui-form-control" type="number" min="1" name="submissions_retention_number" value="<?php echo esc_attr( $cform_retain_number ); ?>">
			</div>

			<div class=" sui-col-md-8">
				<select class="sui-select" name="submissions_retention_unit">
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
		<span class="sui-description">
			<?php esc_html_e( 'Choose how long to retain form submissions. Leave the following options blank to retain form submissions forever.',
			                  Forminator::DOMAIN ); ?>

		</span>
	</div>

	<h4 style="margin-bottom: 5px"><?php esc_html_e( 'Polls', Forminator::DOMAIN ); ?></h4>
	<div class="sui-form-field">
		<label class="sui-label"><?php esc_html_e( "IP address retention", Forminator::DOMAIN ); ?></label>
		<div class="sui-row">
			<div class="sui-col-md-4">
				<input class="sui-form-control" type="number" min="1" name="votes_retention_number" value="<?php echo esc_attr( $polls_retain_number ); ?>">
			</div>

			<div class=" sui-col-md-8">
				<select class="sui-select" name="votes_retention_unit">
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
		<span class="sui-description">
			<?php esc_html_e( 'Choose how long to retain IP address before its getting anonymized.
							Keep in mind, that this IP address probably being used to checking multiple votes.
							Leave the following options blank to retain IP address forever.',
			                  Forminator::DOMAIN ); ?>

		</span>
	</div>

</div>

<div class="sui-box-footer">

	<button class="sui-button forminator-popup-cancel" data-a11y-dialog-hide="forminator-popup"><?php esc_html_e( 'Cancel', Forminator::DOMAIN ); ?></button>

	<div class="sui-actions-right">

		<button class="sui-button sui-button-primary wpmudev-action-done"
		        data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_save_privacy_settings' ) ); ?>">
			<?php esc_html_e( "Save Changes", Forminator::DOMAIN ); ?>
		</button>

	</div>

</div>
