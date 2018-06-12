<?php
$path = forminator_plugin_url();
?>

<div class="sui-box">

	<div class="sui-box-body sui-block-content-center">

		<img src="<?php echo $path . 'assets/img/forminator-face.png'; // WPCS: XSS ok. ?>"
			srcset="<?php echo $path . 'assets/img/forminator-face.png'; // WPCS: XSS ok. ?> 1x, <?php echo $path . 'assets/img/forminator-face@2x.png'; // WPCS: XSS ok. ?> 2x" alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
			class="sui-image sui-image-center fui-image" />

		<h2><?php esc_html_e( "Documentation", Forminator::DOMAIN ); ?></h2>

		<p class="fui-limit-block-600 fui-limit-block-center"><?php esc_html_e( "Check out the docs to get the most out of the tools and features inside Forminator.", Forminator::DOMAIN ); ?></p>

		<p class="fui-limit-block-600 fui-limit-block-center"><a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/forminator/" target="_blank" class="sui-button sui-button-primary"><?php esc_html_e( "Forminator Docs", Forminator::DOMAIN ); ?></a></p>

	</div>

</div>