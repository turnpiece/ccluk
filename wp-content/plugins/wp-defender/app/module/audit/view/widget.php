<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
            <i class="sui-icon-eye" aria-hidden="true"></i>
			<?php _e( "Audit Logging", wp_defender()->domain ) ?>
        </h3>
    </div>
    <div class="sui-box-body no-padding-bottom">
        <p>
			<?php _e( "Track and log events when changes are made to your website giving you full visibility of what’s going on behind the scenes.", wp_defender()->domain ) ?>
        </p>
        <div class="sui-notice">
            <p>
				<?php printf( __( "%d events logged in the past 7 days.", wp_defender()->domain ), $weekCount ) ?>
            </p>
        </div>
        <div class="sui-field-list sui-flushed no-border">
            <div class="sui-field-list-body">
                <div class="sui-field-list-item">
                    <label class="sui-field-list-item-label">
                        <strong><?php _e( "Last event logged", wp_defender()->domain ) ?></strong>
                    </label>
                    <span>
                        <?php echo $lastEvent ?>
                    </span>
                </div>
                <div class="sui-field-list-item">
                    <label class="sui-field-list-item-label">
                        <strong><?php _e( "Events logged this month", wp_defender()->domain ) ?></strong>
                    </label>
                    <span>
                        <?php echo $eventMonth ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="sui-box-footer">
        <div class="sui-actions-left">
            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-logging' ) ?>"
               class="sui-button sui-button-ghost">
                <i class="sui-icon-eye" aria-hidden="true"></i>
				<?php _e( "View Logs", wp_defender()->domain ) ?></a>
        </div>
        <div class="sui-actions-right">
            <p class="sui-p-small">
		        <?php
		        if ( \WP_Defender\Module\Audit\Model\Settings::instance()->notification ) {
			        _e( "Audit log reports are enabled", wp_defender()->domain );
		        } else {
			        _e( "Audit log reports are disabled", wp_defender()->domain );
		        }
		        ?>
            </p>
        </div>
    </div>
</div>