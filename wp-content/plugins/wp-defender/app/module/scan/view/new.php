<div class="sui-wrap">
    <div class="wp-defender">
        <div class="wdf-scanning">
            <div class="sui-header">
                <h1 class="sui-header-title">
					<?php _e( "File Scanning", wp_defender()->domain ) ?>
                </h1>
            </div>
            <div class="sui-box sui-message">
                <img src="<?php echo wp_defender()->getPluginUrl() ?>assets/img/scan-man.svg" class="sui-image"
                     aria-hidden="true">
                <div class="sui-message-content">
                    <p><?php _e( "Scan your website for file changes, vulnerabilities and injected code and get notified about anything suspicious. Defender will keep an eye on your code without you having to worry.", wp_defender()->domain ) ?></p>
                    <form id="start-a-scan" method="post" class="scan-frm">
						<?php
						wp_nonce_field( 'startAScan' );
						?>
                        <input type="hidden" name="action" value="startAScan"/>
                        <p>
                            <button type="submit" class="sui-button sui-button-blue">
								<?php _e( "Run Scan", wp_defender()->domain ) ?></button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>