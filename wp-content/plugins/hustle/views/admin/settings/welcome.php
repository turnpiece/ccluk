<main id="wpmudev-hustle" class="wpmudev-ui wpmudev-hustle-welcome-view">

	<header id="wpmudev-hustle-title">

		<h1><?php _e( "Settings", Opt_In::TEXT_DOMAIN ); ?></h1>

	</header>

	<section id="wpmudev-hustle-content">

		<div class="wpmudev-row">

			<div class="wpmudev-col col-12">

				<div id="wph-welcome-box" class="wpmudev-box">

					<div class="wpmudev-box-head">

						<h2><?php printf( __('Hello there, %s', Opt_In::TEXT_DOMAIN), $user_name ); ?></h2>

					</div>

					<div class="wpmudev-box-body wpmudev-box-hero">

						<div class="wpmudev-box-character"><?php $this->render("general/characters/character-one" ); ?></div>

						<div class="wpmudev-box-content">

							<h2><?php _e( "A little extra Hustle", Opt_In::TEXT_DOMAIN ); ?></h2>

							<p><?php _e( "Hide specific modules from the admin and logged in users and manage plugin integrations when available.", Opt_In::TEXT_DOMAIN ); ?></p>

							<p></p>

						</div>

					</div>

				</div><?php // .wpmudev-box ?>

			</div><?php // .wpmudev-col ?>

		</div><?php // .wpmudev-row ?>

	</section>

	<?php $this->render( "admin/commons/footer", array() ); ?>

</main>