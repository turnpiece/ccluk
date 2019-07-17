<?php
$servers = \WP_Defender\Behavior\Utils::instance()->serverTypes();

if ( DIRECTORY_SEPARATOR == '\\' ) {
	//Windows
	$wp_includes = str_replace( ABSPATH, '', WPINC );
	$wp_content  = str_replace( ABSPATH, '', WP_CONTENT_DIR );
} else {
	$wp_includes = str_replace( $_SERVER['DOCUMENT_ROOT'], '', ABSPATH . WPINC );
	$wp_content  = str_replace( $_SERVER['DOCUMENT_ROOT'], '', WP_CONTENT_DIR );
}
global $is_nginx, $is_IIS, $is_iis7;
$setting = \WP_Defender\Module\Hardener\Model\Settings::instance();
if ( $is_nginx ) {
	$setting->active_server = 'nginx';
} else if ( $is_IIS ) {
	$setting->active_server = 'iis';
} else if ( $is_iis7 ) {
	$setting->active_server = 'iis-7';
}
$checked = $controller->check();
?>
<div id="prevent-php-execute" class="sui-accordion-item <?php echo $controller->getCssClass() ?>">
    <div class="sui-accordion-item-header">
        <div class="sui-accordion-item-title">
            <i aria-hidden="true" class="<?php echo $checked ? 'sui-icon-check-tick sui-success'
				: 'sui-icon-warning-alert sui-warning' ?>"></i>
			<?php _e( "PHP Execution", wp_defender()->domain ) ?>
        </div>
        <div class="sui-accordion-col-4">
            <button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item">
                <i class="sui-icon-chevron-down" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <div class="sui-accordion-item-body">
        <div class="sui-box">
            <div class="sui-box-body">
                <strong><?php _e( "Overview", wp_defender()->domain ) ?></strong>
                <p>
					<?php
					_e( "By default, a plugin/theme vulnerability could allow a PHP file to get uploaded into your site's directories and in turn execute harmful scripts that can wreak havoc on your website. Prevent this altogether by disabling direct PHP execution in directories that don't require it.", wp_defender()->domain )
					?>
                </p>
                <strong>
					<?php _e( "Status", wp_defender()->domain ) ?>
                </strong>
				<?php if ( $checked ): ?>
                    <div class="sui-notice sui-notice-success">
                        <p>
							<?php _e( "You've automatically disabled PHP execution..", wp_defender()->domain ) ?>
                        </p>
                    </div>
				<?php else: ?>
                    <div class="sui-notice sui-notice-warning">
                        <p>
							<?php _e( "PHP execution is currently allowed in all directories.", wp_defender()->domain ) ?>
                        </p>
                    </div>
                    <p>
						<?php _e( "Currently, all directories can have PHP code executed in them. It’s best to lock this down to only the directories that require, and add any further execeptions you need.", wp_defender()->domain ) ?>
                    </p>
                    <strong>
						<?php _e( "How to fix", wp_defender()->domain ) ?>
                    </strong>
                    <p>
						<?php _e( "We can lock down directories WordPress doesn’t need to protect you from PHP execution attacks. You can also add exceptions for specific files you need to run. Alternately, you can ignore this tweak if you don’t require it. Either way, you can easily revert these actions at any time.", wp_defender()->domain ) ?>
                    </p>
                    <div class="sui-tabs sui-side-tabs">
                        <div data-tabs>
                            <div class="<?php echo $setting->active_server == 'apache' ? 'active' : '' ?>"><?php _e( "Apache", wp_defender()->domain ) ?></div>
                            <div class="<?php echo $setting->active_server == 'litespeed' ? 'active' : '' ?>"><?php _e( "Litespeed", wp_defender()->domain ) ?></div>
                            <div class="<?php echo $setting->active_server == 'nginx' ? 'active' : '' ?>"><?php _e( "Nginx", wp_defender()->domain ) ?></div>
                            <div class="<?php echo $setting->active_server == 'iis' ? 'active' : '' ?>"><?php _e( "IIS", wp_defender()->domain ) ?></div>
                            <div class="<?php echo $setting->active_server == 'iis7' ? 'active' : '' ?>"><?php _e( "IIS7", wp_defender()->domain ) ?></div>
                        </div>

                        <div data-panes>
                            <div class="sui-tab-boxed <?php echo $setting->active_server == 'apache' ? 'active' : '' ?>">
								<?php $controller->renderPartial( 'rules/prevent-php/apache_litespeed', array(
									'setting' => $setting
								) ) ?>
                            </div>
                            <div class="sui-tab-boxed <?php echo $setting->active_server == 'litespeed' ? 'active' : '' ?>">
								<?php $controller->renderPartial( 'rules/prevent-php/apache_litespeed', array(
									'setting' => $setting
								) ) ?>
                            </div>
                            <div class="sui-tab-boxed hardener-instructions <?php echo $setting->active_server == 'nginx' ? 'active' : '' ?>">
								<?php $controller->renderPartial( 'rules/prevent-php/nginx', array(
									'setting' => $setting
								) ) ?>
                            </div>
                            <div class="sui-tab-boxed <?php echo $setting->active_server == 'iis' ? 'active' : '' ?>">
                                <p><?php printf( __( 'For IIS servers, <a href="%s">visit Microsoft TechNet</a>', wp_defender()->domain ), 'https://technet.microsoft.com/en-us/library/cc725855(v=ws.10).aspx' ); ?></p>
                            </div>
                            <div class="sui-tab-boxed <?php echo $setting->active_server == 'iis7' ? 'active' : '' ?>">
								<?php $controller->renderPartial( 'rules/prevent-php/iis7', array(
									'setting' => $setting
								) ) ?>
                            </div>

                        </div>

                    </div>
				<?php endif; ?>
            </div>
			<?php if ( ! $checked ): ?>
                <div class="sui-box-footer">
                    <div class="sui-actions-left">
						<?php $controller->showIgnoreForm() ?>
                    </div>
                </div>
			<?php else: ?>
				<?php if ( $setting->active_server == 'apache' || $setting->active_server == 'lite_speed' ): ?>
                    <div class="sui-box-footer">
                        <div class="sui-actions-left">
                            <form method="post" class="hardener-frm rule-process">
								<?php $controller->createNonceField(); ?>
                                <input type="hidden" name="action" value="processRevert"/>
                                <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                                <button class="sui-button sui-button-gray"
                                        type="submit"><?php _e( "Revert", wp_defender()->domain ) ?></button>
                            </form>
                        </div>
                    </div>
				<?php endif; ?>
			<?php endif; ?>
        </div>
    </div>
</div>