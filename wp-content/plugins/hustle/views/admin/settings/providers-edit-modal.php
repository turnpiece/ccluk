<script type="text/template" id="wph-edit-provider-modal-tpl">

	<div class="modal-mask"></div>

	<div class="hustle-two">

		<div class="box content-box can-close">

			<div class="box-title">

				<h3><?php _e('Edit List', Opt_In::TEXT_DOMAIN); ?></h3>

				<a class="wph-icon i-close"></a>

			</div>
			<form action="" method="post">
			<div class="box-content">

				<div class="wph-toggletabs wph-toggletabs--open" style="padding: 0;">

					<section class="wph-toggletabs--content" style="padding: 0;">

						<div class="wph-edit-provider-modal-content"></div>

					</section>

					<footer class="wph-toggletabs--footer">

						<div class="row">

							<div class="col-half">

								<button class="wph-button wph-button--gray js-wph-button-cancel"><?php _e('Cancel', Opt_In::TEXT_DOMAIN); ?></button>

							</div>

							<div class="col-half">

								<button id="wph-edit-service-save" data-nonce="<?php echo wp_create_nonce( 'hustle-edit-service-save' ); ?>" class="wph-button wph-button--filled wph-button--blue" style="width: auto"><?php _e('Save Settings', Opt_In::TEXT_DOMAIN); ?></button>

							</div>

						</div>

					</footer>

				</div>

			</div>
			</form>
		</div>

	</div>

</script>