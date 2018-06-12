<?php
$path = forminator_plugin_url();
$count = $this->countModules();
?>

<div class="sui-box sui-summary fui-summary-alt">

	<div class="sui-summary-image-space"></div>

	<div class="sui-summary-segment">

		<div class="sui-summary-details">

			<span class="sui-summary-large"><?php echo esc_html( $count ); ?></span>
			<span class="sui-summary-sub"><?php esc_html_e( "Active Forms", Forminator::DOMAIN ); ?></span>

		</div>

	</div>

	<div class="sui-summary-segment">
		<ul class="sui-list">
			<li>
				<span class="sui-list-label"><?php esc_html_e( "Top Converting Form", Forminator::DOMAIN ); ?></span>
				<span class="sui-list-detail"><?php echo forminator_top_converting_form(); // WPCS: XSS ok. ?></span>
			</li>
			<li>
				<span class="sui-list-label"><?php esc_html_e( "Last Submission", Forminator::DOMAIN ); ?></span>
				<span class="sui-list-detail"><?php echo forminator_get_latest_entry_time( 'custom-forms' ); // WPCS: XSS ok. ?></span>
			</li>
		</ul>
	</div>

</div>

<div class="sui-box">

	<div class="sui-box-header">

		<h3 class="sui-box-title"><i class="sui-icon-clipboard-notes" aria-hidden="true"></i><?php esc_html_e( "Forms", Forminator::DOMAIN ); ?></h3>

		<?php if ( $count > 0 ) { ?>

			<div class="sui-actions-right">

				<button class="sui-button sui-button-blue wpmudev-button-open-modal" data-modal="custom_forms"><i class="sui-icon-plus" aria-hidden="true"></i> <?php esc_html_e( "Create", Forminator::DOMAIN ); ?></button>

			</div>

		<?php } ?>

	</div>

	<?php if ( $count > 0 ) { ?>

		<div class="sui-box-body">

			<p><?php esc_html_e( "Create custom forms for all your needs with as many fields as you like. From contact forms to quote requests and everything in between.", Forminator::DOMAIN ); ?></p>

			<form method="post" name="bulk-action-form" class="fui-form-actions">

				<?php wp_nonce_field( 'forminatorCustomFormRequest', 'forminatorNonce' ); ?>

				<input type="hidden" name="ids" value=""/>

				<div class="fui-bulk-actions">

					<select class="fui-select-small" name="formninator_action">

						<option value=""><?php esc_html_e( "Bulk Actions", Forminator::DOMAIN ); ?></option>

						<?php
						$bulk_actions = $this->bulk_actions();
						foreach ( $bulk_actions as $action => $label ) {
							?>

							<option value="<?php echo esc_attr( $action ); ?>"><?php echo esc_html( $label ); ?></option>

						<?php } ?>

					</select>

					<button class="sui-button"><?php esc_html_e( "Apply", Forminator::DOMAIN ); ?></button>

				</div>

				<div class="sui-pagination-wrap">

					<span class="sui-pagination-results"><?php if ( 1 === $count ) { printf( esc_html__( "%s result", Forminator::DOMAIN ), $count ); } else { printf( esc_html__( "%s results", Forminator::DOMAIN ), $count ); } // phpcs:ignore ?></span>

					<?php $this->pagination(); ?>

					<!-- <span class="sui-pagination-open-filter"><i class="sui-icon-filter" aria-hidden="true"></i></span> -->

				</div>

			</form>

		</div>

		<table class="sui-table sui-accordion fui-table-listings">

			<thead>

				<tr>

					<th><label class="sui-checkbox">
						<input type="checkbox" id="wpf-cform-check_all">
						<span></span>
						<div class="sui-description"><?php esc_html_e( "Form Name", Forminator::DOMAIN ); ?></div>
					</label></th>

					<th><?php esc_html_e( "Shortcode", Forminator::DOMAIN ); ?></th>

					<th><?php esc_html_e( "Submissions", Forminator::DOMAIN ); ?></th>

				</tr>

			</thead>

			<tbody>

				<?php
				$i = 0;
				foreach ( $this->getModules() as $module ) {
					$i ++;
					?>

					<tr class="sui-accordion-item">

						<td class="sui-accordion-item-title">

							<label class="sui-checkbox">
								<input type="checkbox" id="wpf-cform-module-<?php echo esc_attr( $i ); ?>" value="<?php echo esc_html( $module['id'] ); ?>">
								<span></span>
							</label>

							<a href="<?php echo admin_url( 'admin.php?page=forminator-cform-wizard&id=' . $module['id'] ); // WPCS: XSS ok. ?>"><?php echo forminator_get_form_name( $module['id'], 'custom_form'); // WPCS: XSS ok. ?></a>

						</td>

						<td>[forminator_form id="<?php echo esc_html( $module['id'] ); ?>"]</td>

						<td><a href="<?php echo admin_url( 'admin.php?page=forminator-cform-view&form_id=' . $module['id'] ); // WPCS: XSS ok. ?>"><?php echo esc_html( $module["entries"] ); ?></a>
							<span class="sui-accordion-open-indicator">
								<i class="sui-icon-chevron-down"></i>
							</span>
						</td>

					</tr>

					<tr class="sui-accordion-item-content">

						<td colspan="3">

							<div class="sui-box">

								<div class="sui-box-body">

									<div class="fui-form-intro">

										<h2 class="fui-form-title"><?php echo forminator_get_form_name( $module['id'], 'custom_form'); // WPCS: XSS ok. ?></h2>

										<div class="fui-form-element-actions">

											<form method="post" style="display: inline-block">
												<input type="hidden" name="formninator_action" value="clone">
												<input type="hidden" name="id" value="<?php echo esc_attr( $module['id'] ); ?>"/>
												<?php wp_nonce_field( 'forminatorCustomFormRequest', 'forminatorNonce' ); ?>
												<button type="submit" class="sui-button sui-button-ghost"><i class="sui-icon-page-multiple" aria-hidden="true"></i> <?php esc_html_e( "Clone", Forminator::DOMAIN ); ?></button>
											</form>

											<a href="#" class="sui-button sui-button-primary wpmudev-open-modal" data-modal="preview_cforms"
												data-modal-title="<?php echo sprintf("%s - %s", esc_html__( "Preview Custom Form", Forminator::DOMAIN),  forminator_get_form_name( $module['id'], 'custom_form' ) ); // WPCS: XSS ok. ?>"
												data-form-id="<?php echo esc_attr( $module['id'] ); ?>"
												data-nonce="<?php echo wp_create_nonce( 'forminator_popup_preview_cforms' ); // WPCS: XSS ok. ?>"><i class="sui-icon-eye" aria-hidden="true"></i> <?php esc_html_e( "Preview", Forminator::DOMAIN ); ?></a>

										</div>

									</div>

									<table class="fui-table-ghost">

										<thead>

											<tr>

												<th colspan="2"><?php esc_html_e( "Last Submission", Forminator::DOMAIN ); ?></th>

												<th><?php esc_html_e( "Views", Forminator::DOMAIN ); ?></th>

												<th><?php esc_html_e( "Submissions", Forminator::DOMAIN ); ?></th>

												<th><?php esc_html_e( "Conversion Rate", Forminator::DOMAIN ); ?></th>

											</tr>

										</thead>

										<tbody>

											<tr>

												<td colspan="2"><?php echo esc_html( $module["last_entry_time"] ); ?></td>

												<td><?php echo esc_html( $module["views"] ); ?></td>

												<td><a href="<?php echo admin_url( 'admin.php?page=forminator-cform-view&form_id=' . $module['id'] ); // WPCS: XSS ok. ?>"><?php echo esc_html( $module["entries"] ); ?></a></td>

												<td><?php echo $this->getRate( $module ); // WPCS: XSS ok. ?>%</td>

											</tr>

										</tbody>

									</table>

								</div>

								<div class="sui-box-footer">

									<a href="#" class="sui-button sui-button-ghost sui-button-red wpmudev-open-modal" data-modal="delete-module" data-form-id="<?php echo esc_attr( $module['id'] ); ?>" data-nonce="<?php echo wp_create_nonce( 'forminatorCustomFormRequest' ); // WPCS: XSS ok. ?>"><i class="sui-icon-trash" aria-hidden="true"></i> <?php esc_html_e( "Delete", Forminator::DOMAIN ); ?></a>

									<div class="sui-actions-right">

										<a href="<?php echo admin_url( 'admin.php?page=forminator-cform-wizard&id=' . $module['id'] ); // WPCS: XSS ok. ?>" class="sui-button sui-button-ghost"> <i class="sui-icon-layout" aria-hidden="true"></i><?php esc_html_e( "Edit Layout", Forminator::DOMAIN ); ?></a>

										<a href="<?php echo admin_url( 'admin.php?page=forminator-cform-wizard&id=' . $module['id'] ); // WPCS: XSS ok. ?>#appearance" class="sui-button sui-button-ghost"> <i class="sui-icon-pencil" aria-hidden="true"></i><?php esc_html_e( "Edit Settings", Forminator::DOMAIN ); ?></a>

									</div>

								</div>

							</div>

						</td>

					</tr>

				<?php
				}
				?>

			</tbody>

		</table>

		<div class="sui-box-footer">

			<form method="post" name="bulk-action-form" class="fui-form-actions">

				<?php wp_nonce_field( 'forminatorCustomFormRequest', 'forminatorNonce' ); ?>

				<input type="hidden" name="ids" value=""/>

				<div class="fui-bulk-actions">

					<select class="fui-select-small" name="formninator_action">

						<option value=""><?php esc_html_e( "Bulk Actions", Forminator::DOMAIN ); ?></option>

						<?php
						$bulk_actions = $this->bulk_actions();
						foreach ( $bulk_actions as $action => $label ) {
							?>

							<option value="<?php echo esc_attr( $action ); ?>"><?php echo esc_html( $label ); ?></option>

						<?php } ?>

					</select>

					<button class="sui-button"><?php esc_html_e( "Bulk Actions", Forminator::DOMAIN ); ?></button>

				</div>

				<div class="sui-pagination-wrap">

					<span class="sui-pagination-results"><?php if ( 1 === $count ) { printf( __( "%s result", Forminator::DOMAIN ), $count ); } else { printf( __( "%s results", Forminator::DOMAIN ), $count ); } // phpcs:ignore ?></span>

					<?php $this->pagination(); ?>

					<!-- <span class="sui-pagination-open-filter"><i class="sui-icon-filter" aria-hidden="true"></i></span> -->

				</div>

			</form>

		</div>

	<?php } else { ?>

		<div class="sui-box-body sui-block-content-center">

			<img src="<?php echo $path . 'assets/img/forminator-face.png'; // WPCS: XSS ok. ?>"
				srcset="<?php echo $path . 'assets/img/forminator-face.png'; // WPCS: XSS ok. ?> 1x, <?php echo $path . 'assets/img/forminator-face@2x.png'; // WPCS: XSS ok. ?> 2x" alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
				class="sui-image sui-image-center fui-image" />

			<p class="fui-limit-block-600 fui-limit-block-center"><?php esc_html_e( "Create custom forms for all your needs with as many fields as you like. From contact forms to quote requests and everything in between.", Forminator::DOMAIN ); ?></p>

			<p><button class="sui-button sui-button-blue wpmudev-button-open-modal" data-modal="custom_forms"><i class="sui-icon-plus" aria-hidden="true"></i> <?php esc_html_e( "Create", Forminator::DOMAIN ); ?></button></p>

		</div>

	<?php } ?>

</div>