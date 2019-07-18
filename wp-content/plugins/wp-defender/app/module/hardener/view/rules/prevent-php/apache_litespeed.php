<form method="post" class="hardener-frm hardener-update-frm rule-process">
	<?php $controller->createNonceField(); ?>
    <input type="hidden" name="action" value="updateHardener"/>
    <input type="hidden" name="current_server"
           value="<?php echo $setting->active_server; ?>"/>
    <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
    <p class="no-margin-bottom">
		<?php _e( "We can automatically add an .htaccess file to your root folder to action this fix.", wp_defender()->domain ) ?>
    </p>
    <button class="sui-button sui-button-blue" type="submit">
		<?php _e( "Update .htaccess file", wp_defender()->domain ) ?>
    </button>
    <div class="sui-form-field margin-top-30">
        <label class="sui-label"><?php _e( "Exceptions", wp_defender()->domain ) ?></label>
        <textarea name="file_paths" class="sui-form-control"></textarea>
        <span class="sui-description">
            <?php _e( "Add exceptions to PHP files you want to continue to run. Include the full paths to the file.", wp_defender()->domain ) ?>
        </span>
    </div>
</form>