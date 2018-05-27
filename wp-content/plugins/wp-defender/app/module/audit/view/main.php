<div class="dev-box">
    <div class="box-title">
        <h3><?php _e( "EVENT LOGS", wp_defender()->domain ) ?></h3>
        <button type="button"
                class="button button-secondary button-small audit-csv"><?php _e( "Export CSV", wp_defender()->domain ) ?></button>
    </div>
    <div class="box-content">
        <p class="mline"><?php _e( "Here are your latest event logs showing what’s been happening behind the scenes.", wp_defender()->domain ) ?></p>
        <div id="audit-table-container">
            <div class="columns">
                <div class="column is-3">

                </div>
                <div class="column is-7">
                    <div class="bulk-nav">

                    </div>
                </div>
                <div class="column is-2 tr">
                    <button type="button" rel="show-filter" data-target=".audit-filter"
                            class="button button-small button-secondary"><?php _e( "Filter", wp_defender()->domain ) ?></button>
                </div>
            </div>
            <div class="well well-white audit-filter wd-hide mline">
                <form method="post">
                    <strong>
						<?php _e( "Filter", wp_defender()->domain ) ?>
                    </strong>
                    <div class="columns">
                        <div class="column is-4">
							<?php echo $email_search->renderInput() ?>

                        </div>
                        <div class="column is-4 tc">
                            <input name="ip" id="ip" type="text"
                                   placeholder="<?php esc_attr_e( "192.168.1.1", wp_defender()->domain ) ?>">
                        </div>
                        <div class="column is-4">
                            <input name="date_from" id="wd_range_from" type="text" class="wd-calendar filterable"
                                   value="<?php echo $from . ' - ' . $to ?>">
                        </div>
                    </div>
                    <div class="events">
						<?php foreach ( \WP_Defender\Module\Audit\Component\Audit_API::get_event_type() as $event ): ?>
                            <div class="event">
                                <input id="chk_<?php echo $event ?>" type="checkbox" class="filterable"
                                       name="event_type[]"
									<?php echo in_array( $event, \Hammer\Helper\HTTP_Helper::retrieve_get( 'event_type', \WP_Defender\Module\Audit\Component\Audit_API::get_event_type() ) ) ? 'checked="checked"' : null ?>
                                       value="<?php echo $event ?>">
                                <label
                                        for="chk_<?php echo $event ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $event ) ) ) ?></label>
                            </div>
						<?php endforeach; ?>
                        <div class="clear mline"></div>
                    </div>
                    <div class="well-footer tr">
                        <button type="submit" class="button button-small">
							<?php _e( "Apply", wp_defender()->domain ) ?>
                        </button>
                    </div>
                    <div class="clear"></div>
                </form>
            </div>
            <div id="audit-table-inner">
				<?php _e( "Loading events...", wp_defender()->domain ) ?>
            </div>
            <div class="columns">
                <div class="column is-3">

                </div>
                <div class="column is-7">
                    <div class="bulk-nav">

                    </div>
                </div>
                <div class="column is-2 tr">
                    <button type="button" rel="show-filter" data-target=".audit-filter"
                            class="button button-small button-secondary"><?php _e( "Filter", wp_defender()->domain ) ?></button>
                </div>
            </div>
        </div>
    </div>
</div>