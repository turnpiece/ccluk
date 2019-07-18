<?php
$checked = $controller->check();
$days    = $controller->getService()->getDuration();
?>
<div id="login-duration" class="sui-accordion-item <?php echo $controller->getCssClass() ?>">
    <div class="sui-accordion-item-header">
        <div class="sui-accordion-item-title">
            <i aria-hidden="true" class="<?php echo $checked ? 'sui-icon-check-tick sui-success'
				: 'sui-icon-warning-alert sui-warning' ?>"></i>
			<?php echo $controller->getTitle() ?>
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
                <strong>
					<?php _e( "Overview", wp_defender()->domain ) ?>
                </strong>
                <p>
					<?php _e( "By default, users who select the 'remember me' option will stay logged in for 14 days. If you and your users don’t need to login to your website backend regularly, it’s good practice to reduce this default time to reduce the risk of someone gaining access to your automatically logged in account.", wp_defender()->domain ) ?>
                </p>
				<?php if ( $checked ): ?>
                    <div class="sui-notice sui-notice-success">
                        <p><?php printf( __( "You've adjusted the default login duration to %d days.", wp_defender()->domain ), $days ) ?></p>
                    </div>
				<?php else: ?>
                    <strong>
						<?php _e( "Status", wp_defender()->domain ) ?>
                    </strong>
                    <div class="sui-notice sui-notice-warning">
                        <p>
							<?php printf( __( "Your current login duration is the default %d days.", wp_defender()->domain ), $days ) ?>
                        </p>
                    </div>
					<?php if ( $days > 7 ): ?>
                        <p>
							<?php printf( __( "If you don’t need to stay logged in for %d days, we recommend you reduce this duration to 7 days or less.", wp_defender()->domain ), $days ) ?>
                        </p>
					<?php endif; ?>
                    <strong>
						<?php _e( "How to fix", wp_defender()->domain ) ?>
                    </strong>
                    <p>
						<?php _e( "Choose the shortest login duration that most suit your website’s use case.", wp_defender()->domain ) ?>
                    </p>
                    <div class="sui-form-field">
                        <label class="sui-label"><?php _e( "Login duration", wp_defender()->domain ) ?></label>
                        <input type="text" id="duration"
                               class="sui-input-sm sui-field-has-suffix defender-login-duration sui-form-control"/>
                        <span class="sui-field-suffix"><?php _e( "Days", wp_defender()->domain ) ?></span>
                    </div>
				<?php endif; ?>
            </div>
            <div class="sui-box-footer">
				<?php if ( $checked ): ?>
                    <form method="post" class="hardener-frm rule-process">
						<?php $controller->createNonceField(); ?>
                        <input type="hidden" name="action" value="processRevert"/>
                        <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                        <button class="sui-button" type="submit">
                            <i class="sui-icon-undo" aria-hidden="true"></i>
							<?php _e( "Revert", wp_defender()->domain ) ?></button>
                    </form>
				<?php else: ?>
                    <div class="sui-actions-left">
						<?php $controller->showIgnoreForm() ?>
                    </div>
                    <div class="sui-actions-right">
                        <form method="post" class="hardener-frm rule-process hardener-frm-process-xml-rpc">
							<?php $controller->createNonceField(); ?>
                            <input type="hidden" name="action" value="processHardener"/>
                            <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                            <input type="hidden" name="duration">
                            <button class="sui-button sui-button-blue" type="submit">
								<?php _e( "Update", wp_defender()->domain ) ?></button>
                        </form>
                    </div>
				<?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        $('#duration').keyup(function () {
            $('input[name="duration"]').val($(this).val())
        })
    })
</script>
<!---->
<!--<div class="rule closed" id="login-duration">-->
<!--    <div class="rule-title" role="link" tabindex="0">-->
<!--		--><?php //if ( $controller->check() == false ): ?>
<!--            <i class="def-icon icon-warning" aria-hidden="true"></i>-->
<!--		--><?php //else: ?>
<!--            <i class="def-icon icon-tick" aria-hidden="true"></i>-->
<!--		--><?php //endif; ?>
<!--		--><?php //echo $controller->getTitle() ?>
<!--    </div>-->
<!--    <div class="rule-content">-->
<!--        <h3>--><?php //_e( "Overview", wp_defender()->domain ) ?><!--</h3>-->
<!--        <div class="line end">-->
<!--			--><?php //_e( "By default, users who select the 'remember me' option stay logged in for 14 days", wp_defender()->domain ) ?>
<!--        </div>-->
<!--        <h3>-->
<!--			--><?php //_e( "How to fix", wp_defender()->domain ) ?>
<!--        </h3>-->
<!--        <div class="well">-->
<!--			--><?php
//			$setting = \WP_Defender\Module\Hardener\Model\Settings::instance();
//
//			if ( $controller->check() ):
//				?>
<!--                <p class="line">--><?php //esc_attr_e( sprintf( __( 'Login Duration is locked down. Current duration is %d days', wp_defender()->domain ), $controller->getService()->getDuration() ) ); ?><!--</p>-->
<!--                <form method="post" class="hardener-frm rule-process">-->
<!--					--><?php //$controller->createNonceField(); ?>
<!--                    <input type="hidden" name="action" value="processRevert"/>-->
<!--                    <input type="hidden" name="slug" value="--><?php //echo $controller::$slug ?><!--"/>-->
<!--                    <button class="button button-small button-grey" type="submit">-->
<!--                        <i class="sui-icon-undo" aria-hidden="true"></i>-->
<!--						--><?php //_e( "Revert", wp_defender()->domain ) ?><!--</button>-->
<!--                </form>-->
<!--			--><?php
//			else:
//				?>
<!--                <div class="line">-->
<!--                    <p>--><?php //_e( "Please change the number of days a user can stay logged in", wp_defender()->domain ) ?><!--</p>-->
<!--                </div>-->
<!--                <form method="post" class="hardener-frm rule-process">-->
<!--					--><?php //$controller->createNonceField(); ?>
<!--                    <input type="hidden" name="action" value="processHardener"/>-->
<!--                    <input type="text"-->
<!--                           placeholder="--><?php //esc_attr_e( "Enter number of days", wp_defender()->domain ) ?><!--"-->
<!--                           name="duration" class="block defender-login-duration"/>-->
<!--                    <input type="hidden" name="slug" value="--><?php //echo $controller::$slug ?><!--"/>-->
<!--                    <button class="button float-r"-->
<!--                            type="submit">--><?php //_e( "Update", wp_defender()->domain ) ?><!--</button>-->
<!--                </form>-->
<!--				--><?php //$controller->showIgnoreForm() ?>
<!--                <div class="clear"></div>-->
<!--			--><?php
//			endif;
//			?>
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
