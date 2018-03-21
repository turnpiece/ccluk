<?php
$path = forminator_plugin_url();
?>

<div class="wpmudev-box wpmudev-can--hide">

	<div class="wpmudev-box-header">

		<div class="wpmudev-header--text">

			<h2 class="wpmudev-subtitle"><?php _e( "Documentation", Forminator::DOMAIN ); ?></h2>

		</div>

		<div class="wpmudev-header--action">

			<button class="wpmudev-box--action">

                <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                <span class="wpmudev-sr-only"><?php _e( "Hide box", Forminator::DOMAIN ); ?></span>

            </button>

		</div>

	</div>

	<div class="wpmudev-box-section">

		<div class="wpmudev-section--text wpmudev-align--center">

			<img src="<?php echo $path . 'assets/img/forminator.png'; ?>" srcset="<?php echo $path . 'assets/img/forminator.png'; ?> 1x, <?php echo $path . 'assets/img/forminator@2x.png'; ?> 2x" alt="Forminator Docs" />

			<p><?php _e( "Check out the docs to get the most out of the tools and features inside Forminator.", Forminator::DOMAIN ); ?></p>

			<p><a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/forminator/" target="_blank" class="wpmudev-button wpmudev-button-blue"><?php _e( "Forminator Docs", Forminator::DOMAIN ); ?></a></p>

		</div>

	</div>

</div>