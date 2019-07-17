<p><?php _e( "We will place <strong>web.config</strong> file into the uploads folder to lock down the files and folders inside.", wp_defender()->domain ) ?></p>
<p><?php printf( __( 'For more information, please <a href="%s">visit Microsoft TechNet</a>', wp_defender()->domain ), 'https://technet.microsoft.com/en-us/library/cc725855(v=ws.10).aspx' ); ?></p>
<form method="post" class="hardener-frm hardener-litespeed-frm rule-process">
	<?php $controller->createNonceField(); ?>
	<input type="hidden" name="action" value="processHardener"/>
	<input type="hidden" name="current_server" value="iis-7"/>
	<input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
	<button class="sui-button sui-button-blue" type="submit">
		<?php _e( "Add web.config file", wp_defender()->domain ) ?></button>
</form>