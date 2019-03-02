<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title"><?php esc_html_e( "Logs", wp_defender()->domain ) ?></h3>
        <div class="sui-actions-right">
            <div class="box-filter">
                <span>
                    <?php _e( "Sort by", wp_defender()->domain ) ?>
                </span>
                <select class="sui-select-sm" name="sort" id="lockout-logs-sort">
                    <option value="latest"><?php _e( "Latest", wp_defender()->domain ) ?></option>
                    <option value="oldest"><?php _e( "Oldest", wp_defender()->domain ) ?></option>
                    <option value="ip"><?php _e( "IP Address", wp_defender()->domain ) ?></option>
                </select>
            </div>
            <a href="<?php echo admin_url( 'admin-ajax.php?action=lockoutExportAsCsv' ) ?>"
               class="sui-button sui-button-outlined">
				<?php _e( "Export CSV", wp_defender()->domain ) ?>
            </a>
        </div>
    </div>
    <div class="sui-box-body">
        <p>
			<?php
			_e( "Here's your comprehensive IP lockout log. You can whitelist and ban IPs from there.", wp_defender()->domain )
			?>
        </p>
	    <?php
	    $table = new \WP_Defender\Module\IP_Lockout\Component\Logs_Table();
	    $table->prepare_items();
	    $table->display();
	    ?>
    </div>
</div>