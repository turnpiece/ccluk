<?php
$path = forminator_plugin_url();
?>

<div class="sui-box">

	<div class="sui-box-header">

		<h3 class="sui-box-title"><i class="sui-icon-clipboard-notes" aria-hidden="true"></i><?php esc_html_e( "Forms", Forminator::DOMAIN ); ?></h3>

	</div>

	<?php if ( forminator_cforms_total() > 0 ) { ?>

		<div class="sui-box-body"><?php esc_html_e( "Create custom forms for all your needs with as many fields as your like. From contact forms to quote requests and everything in between.", Forminator::DOMAIN ); ?></div>

		<table class="fui-table">

			<thead>

				<tr>

					<th colspan="4"><?php esc_html_e( "Form Name", Forminator::DOMAIN ); ?></th>

					<th><?php esc_html_e( "Views", Forminator::DOMAIN ); ?></th>

					<th><?php esc_html_e( "Entries", Forminator::DOMAIN ); ?></th>

					<th><?php esc_html_e( "Conversion Rate", Forminator::DOMAIN ); ?></th>

					<th class="fui-table-action"></th>

					<th class="fui-table-action"></th>

				</tr>

			</thead>

			<tbody>

				<?php foreach( forminator_cform_modules() as $module ) { ?>

					<tr>

						<td colspan="4" class="fui-cell-title"><?php echo forminator_get_form_name( $module['id'], 'custom_form'); // WPCS: XSS ok. ?></td>

						<td><?php echo esc_html( $module["views"] ); ?></td>

						<td><?php echo esc_html( $module["entries"] ); ?></td>

						<td><?php echo forminator_get_rate( $module ); // WPCS: XSS ok. ?>%</td>

						<td class="fui-table-action"><a href="<?php echo admin_url( 'admin.php?page=forminator-cform-wizard&id=' . $module['id'] ); // WPCS: XSS ok. ?>"
							class="sui-button-icon sui-tooltip sui-tooltip-top-left"
							data-tooltip="<?php esc_html_e( 'Edit form layout', Forminator::DOMAIN ); ?>"><i class="sui-icon-layout" aria-hidden="true"></i></a>
						</td>

						<td class="fui-table-action"><a href="<?php echo admin_url( 'admin.php?page=forminator-cform-wizard&id=' . $module['id'] ); // WPCS: XSS ok. ?>#appearance"
							class="sui-button-icon sui-tooltip sui-tooltip-top-left"
							data-tooltip="<?php esc_html_e( 'Edit form settings', Forminator::DOMAIN ); ?>"><i class="sui-icon-widget-settings-config" aria-hidden="true"></i></a>
						</td>

					</tr>

				<?php } ?>

			</tbody>

		</table>

	<?php } else { ?>

		<div class="sui-box-body sui-block-content-center">

			<img src="<?php echo $path . 'assets/img/forminator-face.png'; // WPCS: XSS ok. ?>"
				srcset="<?php echo $path . 'assets/img/forminator-face.png'; // WPCS: XSS ok. ?> 1x, <?php echo $path . 'assets/img/forminator-face@2x.png'; // WPCS: XSS ok. ?> 2x" alt="<?php esc_html_e( 'Forminator Forms', Forminator::DOMAIN ); ?>"
				class="sui-image sui-image-center" />

			<p class="fui-limit-block-600 fui-limit-block-center"><?php esc_html_e( "Create custom forms for all your needs with as many fields as your like. From contact forms to quote requests and everything in between.", Forminator::DOMAIN ); ?></p>

			<p><button href="/" class="sui-button sui-button-blue wpmudev-open-modal" data-modal="custom_forms"><?php esc_html_e( "Create Form", Forminator::DOMAIN ); ?></button></p>

		</div>

	<?php } ?>

	<?php if ( forminator_cforms_total() > 0 ) { ?>

		<div class="sui-box-footer">

			<div class="fui-action-buttons">

				<a href="<?php echo forminator_get_admin_link( 'forminator-cform' ); // WPCS: XSS ok. ?>" class="sui-button sui-button-ghost"><i class="sui-icon-eye" aria-hidden="true"></i> <?php esc_html_e( "View All", Forminator::DOMAIN ); ?></a>

				<button class="sui-button sui-button-blue wpmudev-open-modal" data-modal="custom_forms"><?php esc_html_e( "Create Form", Forminator::DOMAIN ); ?></button>

			</div>

		</div>

	<?php } ?>

</div>