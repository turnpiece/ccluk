<div class="sui-wrap <?php echo \WP_Defender\Behavior\Utils::instance()->maybeHighContrast() ?>">
    <div class="wp-defender">
        <div class="wdf-scanning">
            <div class="sui-header">
                <h1 class="sui-header-title">
					<?php _e( "File Scanning", wp_defender()->domain ) ?>
                </h1>
                <div class="sui-actions-left">
                    <form id="start-a-scan" method="post" class="scan-frm">
						<?php
						wp_nonce_field( 'startAScan' );
						?>
                        <input type="hidden" name="action" value="startAScan"/>
                        <button type="submit" class="sui-button sui-button-blue">
							<?php _e( "New Scan", wp_defender()->domain ) ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="sui-dialog <?php echo wp_defender()->isFree ? 'scanning-free' : null ?>" aria-hidden="true"
         tabindex="-1" id="scanning">

        <div class="sui-dialog-overlay"></div>

        <div class="sui-dialog-content" aria-labelledby="dialogTitle" aria-describedby="dialogDescription"
             role="dialog">

            <div class="sui-box" role="document">

                <div class="sui-box-header">
                    <h3 class="sui-box-title" id="dialogTitle">
						<?php _e( "Scan in progress", wp_defender()->domain ) ?>
                    </h3>
                </div>

                <div class="sui-box-body">
                    <p id="dialogDescription">
						<?php _e( "Defender is scanning your files for malicious code. This will take a few minutes depending on the size of your website.", wp_defender()->domain ) ?>
                    </p>
                    <div class="sui-progress-block sui-progress-can-close">
                        <div class="sui-progress">
                            <span class="sui-progress-icon" aria-hidden="true">
                                <i class="sui-icon-loader sui-loading"></i>
                            </span>
                            <span class="sui-progress-text">
                                <span><?php echo $percent ?>%</span>
		                    </span>
                            <div class="sui-progress-bar" aria-hidden="true">
                                <span style="width: <?php echo $percent ?>%"></span>
                            </div>
                        </div>
                        <form method="post" class="scan-frm">
                            <input type="hidden" name="action" value="cancelScan"/>
		                    <?php wp_nonce_field( 'cancelScan', '_wpnonce', true ) ?>
                            <button class="sui-button-icon" type="submit">
                                <i class="sui-icon-close"></i>
                            </button>
                        </form>
                    </div>
                    <div class="sui-progress-state">
                        <span class="sui-progress-state-text">
                            <?php echo $model->statusText ?>
                        </span>
                    </div>
					<?php if ( wp_defender()->isFree ): ?>
                        <div class="sui-row">
                            <div class="sui-col-md-3">
                            </div>
                            <div class="sui-col-md-9">
                                <div class="sui-notice sui-notice-info">
                                    <p>
										<?php printf( __( "Did you know the Pro version of Defender comes with advanced full code scanning and automated reporting?
                    Get enhanced security protection as part of a WPMU DEV membership including 100+ plugins, 24/7
                    support and lots of handy site management tools – <a target='_blank' href=\"%s\">Try Defender Pro today for FREE</a>", wp_defender()->domain ), \WP_Defender\Behavior\Utils::instance()->campaignURL( 'defender_filescanning_modal_inprogress_upsell_link' ) ) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
					<?php endif; ?>
                    <form method="post" id="process-scan" class="scan-frm">
                        <input type="hidden" name="action" value="processScan"/>
						<?php
						wp_nonce_field( 'processScan' );
						?>
                    </form>
                </div>
            </div>

        </div>

    </div>
</div>