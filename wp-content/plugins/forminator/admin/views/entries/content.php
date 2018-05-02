<?php /**@var Forminator_Entries_Page $this */ ?>
<section id="wpmudev-section">

    <div class="wpmudev-row">

		<div class="wpmudev-col col-12">

			<div id="wpmudev-choose-entries" class="wpmudev-box">

				<div class="wpmudev-box-section">

					<div class="wpmudev-section--text">

						<p class="wpmudev-subtitle"><?php _e( "Choose your entries:", Forminator::DOMAIN ); ?></p>

						<form method="get" name="bulk-action-form" style="width:100%">

							<input type="hidden" name="page" value="forminator-entries">

							<div class="wpmudev-actions">

								<div class="wpmudev-action--bulk">

									<select class="wpmudev-select" name="form_type" onchange="submit()">

										<?php foreach ( $this->get_form_types() as $post_type => $name ) { ?>

											<option value="<?php echo $post_type ?>" <?php echo selected( $post_type, $this->get_current_form_type() ) ?>><?php echo $name ?></option>

										<?php } ?>

									</select>

									<?php echo $this->render_form_switcher() ?>

								</div>

							</div>

						</form>

					</div>

				</div>

			</div>

		</div>

	</div>

</section>

<?php echo $this->render_entries() ?>