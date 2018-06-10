<?php
$form_elements = $default_form_fields;
if ( $module ) {
	$module_content = $module->get_content();
	$form_elements = $module_content->form_elements;
	if ( empty( $form_elements ) || !is_array( $form_elements ) ) {
		$form_elements = $default_form_fields;
	}
}
?>

<div id="wph-wizard-content-form_elements" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Manage form elements", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "Configure what fields do you want to display on this form, which are required and what is their default text / placeholder.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<label class="wpmudev-helper"><?php _e( "Required form elements marked with <span class=\"wpdui-fi wpdui-fi-asterisk\"></span>", Opt_In::TEXT_DOMAIN ); ?></label>

        <table cellspacing="0" cellpadding="0" class="wpmudev-table">

			<thead>

				<tr>

					<th><?php _e( "Form Element", Opt_In::TEXT_DOMAIN ); ?></th>
					<th><?php _e( "Type", Opt_In::TEXT_DOMAIN ); ?></th>
					<th><?php _e( "Default Text", Opt_In::TEXT_DOMAIN ); ?></th>

				</tr>

			</thead>

			<tbody class="wph-form-element-list">

				<?php foreach( $form_elements as $form_element ) : ?>
					<?php
						$required = false;
						if(is_string($form_element['required'])){
							$required = ($form_element['required'] == 'true');
						}else{
							$required = $form_element['required'];
						}
					?>
					<tr class="wph-form-element-row-<?php echo $form_element['name']; ?>">
						<td<?php if ($required) { echo ' class="wpmudev-field-required"'; } ?> data-text="<?php _e( "Form Element", Opt_In::TEXT_DOMAIN ); ?>"><?php if ($required) { echo '<span class="wpdui-fi wpdui-fi-asterisk"></span>'; } ?><?php echo $form_element['label']; ?></td>
						<td data-text="<?php _e( "Form Type", Opt_In::TEXT_DOMAIN ); ?>"><?php echo $form_element['type']; ?></td>
						<td data-text="<?php _e( "Default Text", Opt_In::TEXT_DOMAIN ); ?>"><?php echo $form_element['placeholder']; ?></td>
					</tr>
				<?php endforeach; ?>

			</tbody>

			<tfoot>

				<tr><td><a href="" id="wph-edit-form" class="wpmudev-button wpmudev-button-blue"><?php _e( "Edit Form", Opt_In::TEXT_DOMAIN ); ?></a></td></tr>

			</tfoot>

		</table>

	</div>

</div><?php // #wph-wizard-content-form_elements ?>