<?php
$path  = forminator_plugin_url();
$count = Forminator_Form_Entry_Model::count_all_entries();
?>

<?php if ( $count > 0 ) { ?>

	<div class="sui-box">

		<div class="sui-box-header">

			<h2 class="sui-box-title"><i class="sui-icon-filter" aria-hidden="true"></i> <?php esc_html_e( "Filter your modules", Forminator::DOMAIN ); ?></h2>

		</div>

		<div class="sui-box-body">

			<p><?php esc_html_e( 'Choose the module you want to display and submissions you have for it will show up here.', Forminator::DOMAIN ); ?></p>

			<form method="get" name="bulk-action-form" class="fui-select-actions">

				<input type="hidden" name="page" value="forminator-entries">

				<select name="form_type" onchange="submit()">

					<?php foreach ( $this->get_form_types() as $post_type => $name ) { ?>

						<option value="<?php echo esc_attr( $post_type ); ?>" <?php echo selected( $post_type, $this->get_current_form_type() ); ?>><?php echo esc_html( $name ); ?></option>

					<?php } ?>

				</select>

				<?php echo $this->render_form_switcher(); // phpcs:ignore ?>

			</form>

		</div>

	</div>

	<?php echo $this->render_entries(); // phpcs:ignore ?>

<?php } else { ?>

	<div class="sui-box">

		<div class="sui-box-body sui-block-content-center">

			<img src="<?php echo $path . 'assets/img/forminator-submissions.png'; // WPCS: XSS ok. ?>"
				srcset="<?php echo $path . 'assets/img/forminator-submissions.png'; // WPCS: XSS ok. ?> 1x, <?php echo $path . 'assets/img/forminator-submissions@2x.png'; // WPCS: XSS ok. ?> 2x" alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
				class="sui-image sui-image-center fui-image" />

			<h2><?php esc_html_e( "Submissions", Forminator::DOMAIN ); ?></h2>

			<p class="fui-limit-block-600 fui-limit-block-center"><?php esc_html_e( "You haven’t received any form, poll or quiz submissions yet. When you do, you’ll be able to view all the data here.", Forminator::DOMAIN ); ?></p>

		</div>

	</div>

<?php
}
?>
