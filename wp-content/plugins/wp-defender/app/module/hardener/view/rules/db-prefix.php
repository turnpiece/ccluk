<?php
$checked = $controller->check();
global $wpdb;
$prefix = uniqid();
?>
<div id="db_prefix" class="sui-accordion-item <?php echo $controller->getCssClass() ?>">
    <div class="sui-accordion-item-header">
        <div class="sui-accordion-item-title">
            <i aria-hidden="true" class="<?php echo $checked ? 'sui-icon-check-tick sui-success'
				: 'sui-icon-warning-alert sui-warning' ?>"></i>
			<?php _e( "Database Prefix", wp_defender()->domain ) ?>
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
		            <?php _e( "When you first install WordPress on a new database, the default settings start with wp_ as the prefix to anything that gets stored in the tables. This makes it easier for hackers to perform SQL injection attacks if they find a code vulnerability. ", wp_defender()->domain ) ?>
                </p>
				<?php if ( $checked ): ?>
                    <div class="sui-notice sui-notice-success">
                        <p><?php printf( __( "You're database prefix is set to <strong>%s</strong> and is unique, %s would be proud.", wp_defender()->domain ), $wpdb->prefix, \WP_Defender\Behavior\Utils::instance()->getDisplayName() ) ?></p>
                    </div>
				<?php else: ?>
                    <strong>
						<?php _e( "Status", wp_defender()->domain ) ?>
                    </strong>
                    <div class="sui-notice sui-notice-warning">
                        <p>
							<?php _e( "Your database prefix is the default wp_ prefix.", wp_defender()->domain ) ?>
                        </p>
                    </div>
                    <p>
						<?php _e( "You’re currently using the default prefix, it’s much safer to change this to something random.", wp_defender()->domain ) ?>
                    </p>
                    <strong>
						<?php _e( "How to fix", wp_defender()->domain ) ?>
                    </strong>
                    <p>
						<?php _e( "It’s good practice to come up with a unique prefix to protect yourself from this. We’ve automatically generated a random prefix for you which will make it near impossible for hackers to guess, but feel free to choose your own. Alternately, you can ignore this tweak if you really want to keep the wp_ prefix at your own risk.", wp_defender()->domain ) ?>
                    </p>
                    <div class="sui-border-frame">
                        <div class="sui-form-field ">
                            <label class="sui-label"><?php _e( "New database prefix", wp_defender()->domain ) ?></label>
                            <input type="text" value="<?php echo $prefix ?>" name="dbprefix" id="dbprefix" class="sui-form-control"/>
                        </div>
                    </div>
				<?php endif; ?>
            </div>
            <div class="sui-box-footer">
				<?php if ( $checked ): ?>
<!--                    <form method="post" class="hardener-frm rule-process">-->
<!--						--><?php //$controller->createNonceField(); ?>
<!--                        <input type="hidden" name="action" value="processRevert"/>-->
<!--                        <input type="hidden" name="slug" value="--><?php //echo $controller::$slug ?><!--"/>-->
<!--                        <button class="sui-button" type="submit">-->
<!--                            <i class="sui-icon-undo" aria-hidden="true"></i>-->
<!--							--><?php //_e( "Revert", wp_defender()->domain ) ?><!--</button>-->
<!--                    </form>-->
				<?php else: ?>
                    <div class="sui-actions-left">
						<?php $controller->showIgnoreForm() ?>
                    </div>
                    <div class="sui-actions-right">
                        <form method="post" class="hardener-frm rule-process hardener-frm-process-xml-rpc">
							<?php $controller->createNonceField(); ?>
                            <input type="hidden" name="dbprefix" value="<?php echo $prefix ?>"/>
                            <input type="hidden" name="action" value="processHardener"/>
                            <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                            <button class="sui-button sui-button-blue" type="submit">
								<?php _e( "Update Prefix", wp_defender()->domain ) ?></button>
                        </form>
                    </div>
				<?php endif; ?>
            </div>
			<?php if ( ! $checked ): ?>
                <div class="sui-center-box">
                    <p>
						<?php _e( "Ensure you backup your database before performing this tweak.", wp_defender()->domain ) ?>
                    </p>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        $('#dbprefix').keyup(function () {
            $('input[name="dbprefix"]').val($(this).val())
        })
    })
</script>