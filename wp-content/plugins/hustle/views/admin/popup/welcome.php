<main id="wpmudev-hustle" class="wpmudev-ui wpmudev-hustle-welcome-view">

	<header id="wpmudev-hustle-title" class="wpmudev-has-button">

		<h1><?php _e( "Pop-ups Dashboard", Opt_In::TEXT_DOMAIN ); ?></h1>

		<a class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost" href="<?php echo esc_url( $new_url ); ?>"><?php _e('New Pop-up', Opt_In::TEXT_DOMAIN); ?></a>

	</header>

	<section id="wpmudev-hustle-content">

		<div class="wpmudev-row">

			<div class="wpmudev-col col-12">

				<div id="wph-welcome-box" class="wpmudev-box" data-nonce="<?php echo wp_create_nonce('hustle_new_welcome_notice'); ?>">

					<div class="wpmudev-box-head">

						<h2><?php printf( __('Hello there, %s', Opt_In::TEXT_DOMAIN), $user_name ); ?></h2>

					</div>

					<div class="wpmudev-box-body wpmudev-box-hero">

						<div class="wpmudev-box-character" aria-hidden="true"><?php $this->render("general/characters/character-one" ); ?></div>

						<div class="wpmudev-box-content">

							<h2><?php _e( "Promote Your Site", Opt_In::TEXT_DOMAIN ); ?></h2>

							<p><?php _e( "You currently don't have any pop-ups. Create a new pop-up with any kind of content e.g. An advert or a promotion. You can also create pop-ups for collecting your customers' emails.", Opt_In::TEXT_DOMAIN ); ?></p>

							<p><a href="<?php echo esc_url( $new_url ); ?>" class="wpmudev-button wpmudev-button-blue"><?php _e('Create', Opt_In::TEXT_DOMAIN); ?></a></p>

						</div>

					</div>

				</div><?php // .wpmudev-box ?>

			</div><?php // .wpmudev-col ?>

		</div><?php // .wpmudev-row ?>

	</section>

	<?php $this->render( "admin/commons/footer", array() ); ?>

</main>