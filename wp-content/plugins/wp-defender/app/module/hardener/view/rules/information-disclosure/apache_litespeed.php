<form method="post" class="hardener-frm hardener-update-frm rule-process">
	<?php $controller->createNonceField(); ?>
    <input type="hidden" name="action" value="processHardener"/>
    <input type="hidden" name="current_server"
           value="<?php echo $setting->active_server; ?>"/>
    <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
    <p class="no-margin-bottom">
		<?php _e( "We can automatically add an .htaccess file to your root folder to action this fix.", wp_defender()->domain ) ?>
    </p>
    <button class="sui-button sui-button-blue" type="submit">
		<?php _e( "Update .htaccess file", wp_defender()->domain ) ?>
    </button>
</form>