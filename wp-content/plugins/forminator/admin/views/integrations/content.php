<?php $path = forminator_plugin_url(); ?>

<div class="sui-row-with-sidenav forminator-integrations-wrapper">

	<div class="sui-sidenav">

		<ul class="sui-vertical-tabs sui-sidenav-hide-md">

			<li class="sui-vertical-tab forminator-integrations" data-tab-id="forminator-integrations">
				<a href="#forminator-integrations" role="button"><?php esc_html_e( "Applications", Forminator::DOMAIN ); ?></a>
			</li>

			<li class="sui-vertical-tab forminator-api" data-tab-id="forminator-api">
				<a href="#forminator-api" role="button"><?php esc_html_e( "API", Forminator::DOMAIN ); ?></a>
			</li>

		</ul>

	</div>

	<div id="forminator-integrations" class="wpmudev-settings--box" style="display: block;">

		<div class="sui-box">

			<div class="sui-box-header">

				<h2 class="sui-box-title"><?php esc_html_e( "Applications", Forminator::DOMAIN ); ?></h2>

			</div>

			<div id="forminator-integrations-page" class="sui-box-body">

				<p><?php esc_html_e( "Forminator integrates with your favourite email and storage apps. Here’s a list of the currently available apps, you can configure them in your Form / Integrations area.", Forminator::DOMAIN ); ?></p>

				<div id="forminator-integrations-display"></div>

			</div>

		</div>

	</div>

	<div id="forminator-api" class="wpmudev-settings--box" style="display: none;">

		<div class="sui-box">

			<div class="sui-box-header">

				<h2 class="sui-box-title"><?php esc_html_e( "API", Forminator::DOMAIN ); ?></h2>

				<div class="sui-actions-left">
					<span class="sui-tag sui-tag-pro"><?php esc_html_e( "PRO", Forminator::DOMAIN ); ?></span>
				</div>

			</div>

			<div class="sui-box">

				<div class="sui-box-body sui-block-content-center">

					<img src="<?php echo $path . 'assets/img/forminator-disabled.png'; // WPCS: XSS ok. ?>"
						 srcset="<?php echo $path . 'assets/img/forminator-disabled.png'; // WPCS: XSS ok. ?> 1x, <?php echo $path . 'assets/img/forminator-disabled@2x.png'; // WPCS: XSS ok. ?> 2x"
						 alt="<?php esc_html_e( 'Forminator APIs', Forminator::DOMAIN ); ?>"
						 class="sui-image sui-image-center fui-image"/>

					<div class="fui-limit-block-600 fui-limit-block-center">

						<p>
							<?php
							esc_html_e( "Connect Forminator to your custom built apps using our full featured API. This is currently in development and will be made available to Pro members soon.",
											 Forminator::DOMAIN );
							?>
						</p>

						<p><span class="sui-tag sui-tag-disabled"><?php esc_html_e( "Coming Soon", Forminator::DOMAIN ); ?></span></p>

					</div>

				</div>

			</div>

		</div>

	</div>

</div>