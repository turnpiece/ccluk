<?php
$path = forminator_plugin_dir();

$icon_close = $path . "assets/icons/admin-icons/close.php";
$hero_happy = $path . "assets/icons/forminator-icons/hero-happy.php";
$hero_face = $path . "assets/icons/forminator-icons/hero-face.php";
?>

<div class="wpmudev-row">

	<div class="wpmudev-col col-12">

		<div id="forminator-dashboard-box--welcome" class="wpmudev-box wpmudev-box--hero wpmudev-can--close" data-nonce="<?php echo wp_create_nonce('forminator_dismiss_welcome'); ?>">

			<div class="wpmudev-box-header">

				<div class="wpmudev-header--text">

					<h2 class="wpmudev-title"><?php _e( "Welcome to Forminator", Forminator::DOMAIN ); ?></h2>

				</div>

				<div class="wpmudev-header--action">

					<button class="wpmudev-box--action wpmudev-action-close" aria-hidden="true"><span class="wpmudev-icon--close"></span></button>

					<button class="wpmudev-sr-only"><?php _e( "Close box", Forminator::DOMAIN ); ?></button>

				</div>

			</div>

			<div class="wpmudev-box-section">

				<div class="wpmudev-hero--image" aria-hidden="true">

					<div class="wpmudev-image--wrap wpmudev-image--desktop"><?php include( $hero_happy ); ?></div>
					<div class="wpmudev-image--wrap wpmudev-image--mobile"><?php include( $hero_face ); ?></div>

				</div>

				<div class="wpmudev-hero--text">

					<h2 class="wpmudev-title"><?php _e( "Are you ready to take your forms to the next level?", Forminator::DOMAIN ); ?></h2>

					<p><?php _e( "With Forminator you can create forms, quizzes and polls to use anywhere on your website.", Forminator::DOMAIN ); ?></p>

					<p><?php _e( "Pick what kind of form type you want to create below to get started.", Forminator::DOMAIN ); ?></p>

					<p><button class="wpmudev-button wpmudev-button-blue wpmudev-open-modal" data-modal="custom_forms"><?php _e( "Create New Form", Forminator::DOMAIN ); ?></button></p>

				</div>

			</div>

		</div><?php // .wpmudev-box ?>

	</div><?php // .wpmudev-col ?>

</div><?php // .wpmudev-row ?>