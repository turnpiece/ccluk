<div class="wpmudev-row">

	<div class="wpmudev-col col-12">

		<div id="wph-welcome" class="wpmudev-box wpmudev-box-close" data-nonce="<?php echo wp_create_nonce('hustle_new_welcome_notice'); ?>">

			<div class="wpmudev-box-head">

				<h2><?php _e( "Welcome to Hustle 3.0", Opt_In::TEXT_DOMAIN ); ?></h2>

				<?php $this->render("general/icons/icon-close" ); ?>

			</div>

			<div class="wpmudev-box-body wpmudev-box-hero">

				<div class="wpmudev-box-character" aria-hidden="true"><?php $this->render("general/characters/character-one" ); ?></div>

				<div class="wpmudev-box-content">

					<h2><?php _e( "LET'S GET YOU STARTED", Opt_In::TEXT_DOMAIN ); ?></h2>

					<p><?php _e( "First, choose what type of marketing material you want to set-up.", Opt_In::TEXT_DOMAIN ); ?></p>

					<ul>
						<li><?php _e( "Email opt ins – perfect for collecting your visitors' email addresses.", Opt_In::TEXT_DOMAIN ); ?></li>
						<li><?php _e( "Custom Content – create any kind of pop-up, slide-in, widget or shortcode. e.g. An Ad.", Opt_In::TEXT_DOMAIN ); ?></li>
					</ul>

				</div>

			</div>

		</div><?php // .wpmudev-box ?>

	</div><?php // .wpmudev-col ?>

</div><?php // .wpmudev-row ?>