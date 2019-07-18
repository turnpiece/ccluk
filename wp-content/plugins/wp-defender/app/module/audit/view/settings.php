<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Settings", wp_defender()->domain ) ?>
        </h3>
    </div>
    <form method="post" id="settings-frm" class="audit-frm">
        <div class="sui-box-body">
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Deactivate", wp_defender()->domain ) ?>
                    </span>
                    <span class="sui-description">
                       <?php esc_html_e( "If you no longer want to use this feature you can turn it off at any time.", wp_defender()->domain ) ?>
                    </span>
                </div>
				<?php wp_nonce_field( 'saveAuditSettings' ) ?>
                <input type="hidden" name="action" value="saveAuditSettings">
                <div class="sui-box-settings-col-2">
                    <button type="button" class="sui-button sui-button-ghost deactivate-audit">
						<?php _e( "Deactivate", wp_defender()->domain ) ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
