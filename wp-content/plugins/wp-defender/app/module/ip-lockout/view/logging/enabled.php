<div class="dev-box">
    <div class="box-title">
        <h3><?php esc_html_e( "Logs", wp_defender()->domain ) ?></h3>
        <a href="<?php echo admin_url('admin-ajax.php?action=lockoutExportAsCsv') ?>" class="button button-small button-secondary"><?php _e( "Export CSV", wp_defender()->domain ) ?></a>
        <div class="sort">
            <span><?php _e( "Sort by", wp_defender()->domain ) ?></span>
            <select name="sort" id="lockout-logs-sort">
                <option value="latest"><?php _e( "Latest", wp_defender()->domain ) ?></option>
                <option value="oldest"><?php _e( "Oldest", wp_defender()->domain ) ?></option>
                <option value="ip"><?php _e( "IP Address", wp_defender()->domain ) ?></option>
            </select>
        </div>
        <!--        <button type="button" data-target=".lockout-logs-filter" rel="show-filter"-->
        <!--                class="button button-secondary button-small">-->
		<?php //_e( "Filter", wp_defender()->domain ) ?><!--</button>-->
    </div>
    <div class="box-content">
		<?php
		$table = new \WP_Defender\Module\IP_Lockout\Component\Logs_Table();
		$table->prepare_items();
		$table->display();
		?>
    </div>
</div>
<!--<dialog id="bulk" class="no-close">-->
<!--    <form id="lockout-bulk" method="post" class="tc">-->
<!--        <h4>--><?php //_e( "Bulk Actions", wp_defender()->domain ) ?><!--</h4>-->
<!--        <button type="submit" class="button button-primary button-small">--><?php //_e( "Ban", wp_defender()->domain ) ?><!--</button>-->
<!--        <button type="submit" class="button button-secondary button-small">--><?php //_e( "Whitelist", wp_defender()->domain ) ?><!--</button>-->
<!--    </form>-->
<!--</dialog>-->