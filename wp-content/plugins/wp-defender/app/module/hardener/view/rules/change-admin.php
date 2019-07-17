<?php
$checked = $controller->check();
global $wpdb;
?>
<div id="change_admin" class="sui-accordion-item <?php echo $controller->getCssClass() ?>">
    <div class="sui-accordion-item-header">
        <div class="sui-accordion-item-title">
            <i aria-hidden="true" class="<?php echo $checked ? 'sui-icon-check-tick sui-success'
				: 'sui-icon-warning-alert sui-warning' ?>"></i>
			<?php _e( "Admin User", wp_defender()->domain ) ?>
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
		            <?php _e( "One of most common methods of gaining access to websites is through brute force attacks on login areas using default/common usernames and passwords. If you're using the default ‘admin’ username, you're giving away an important piece of the puzzle hackers need to hijack your website.", wp_defender()->domain ) ?>
                </p>
				<?php if ( $checked ): ?>
                    <div class="sui-notice sui-notice-success">
                        <p><?php _e( "You don't have a user account sporting the admin username, great.", wp_defender()->domain ) ?></p>
                    </div>
				<?php else: ?>
                    <strong>
						<?php _e( "Status", wp_defender()->domain ) ?>
                    </strong>
                    <div class="sui-notice sui-notice-warning">
                        <p>
							<?php _e( "You have a user account with the admin username.", wp_defender()->domain ) ?>
                        </p>
                    </div>
                    <p>
						<?php _e( "Using the default admin username is widely considered bad practice and opens you up to the easitest form of entry to your website. We recommend avoiding generic usernames like admin, administrator, and anything that matches your hostname (mattebutter) as these are the usernames hackers and bots will attempt first.", wp_defender()->domain ) ?>
                    </p>
                    <strong>
						<?php _e( "How to fix", wp_defender()->domain ) ?>
                    </strong>
                    <p>
						<?php _e( "Choose a new admin username name below. Alternately, you can ignore this tweak if you really want to keep the admin username at your own risk.", wp_defender()->domain ) ?>
                    </p>
                    <div class="sui-border-frame">
                        <div class="sui-form-field ">
                            <label class="sui-label"><?php _e( "New admin username", wp_defender()->domain ) ?></label>
                            <input type="text" id="username" class="sui-form-control"/>
                        </div>
                    </div>
				<?php endif; ?>
            </div>
			<?php if ( ! $checked ): ?>
                <div class="sui-box-footer">
                    <div class="sui-actions-left">
						<?php $controller->showIgnoreForm() ?>
                    </div>
                    <div class="sui-actions-right">
                        <form method="post" class="hardener-frm rule-process hardener-frm-process-xml-rpc">
							<?php $controller->createNonceField(); ?>
                            <input type="hidden" name="username"/>
                            <input type="hidden" name="action" value="processHardener"/>
                            <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                            <button class="sui-button sui-button-blue" type="submit">
								<?php _e( "Update Username", wp_defender()->domain ) ?></button>
                        </form>
                    </div>
                </div>
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
        $('#username').keyup(function () {
            $('input[name="username"]').val($(this).val())
        })
    })
</script>