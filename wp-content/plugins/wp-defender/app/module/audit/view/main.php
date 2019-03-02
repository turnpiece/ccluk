<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Event Logs", wp_defender()->domain ) ?>
        </h3>
        <div class="sui-actions-right">
            <button type="button" class="sui-button sui-button-ghost audit-csv">
                <i class="sui-icon-upload-cloud" aria-hidden="true"></i>
				<?php _e( "Export CSV", wp_defender()->domain ) ?>
            </button>
        </div>
    </div>
    <div class="sui-box-body">
        <p>
			<?php _e( "Here are your latest event logs showing what’s been happening behind the scenes.", wp_defender()->domain ) ?>
        </p>
        <div class="sui-row">
            <div class="sui-col">
                <small class="font-heavy"><?php _e( "Date range", wp_defender()->domain ) ?></small>
                <div class="sui-date">
                    <i class="sui-icon-calendar" aria-hidden="true"></i>
                    <input name="date_from" id="wd_range_from" type="text"
                           class="sui-form-control filterable"
                           value="<?php echo $from . ' - ' . $to ?>">
                </div>
            </div>
            <div class="sui-col">
                <div class="sui-pagination-wrap float-r">
                    <button rel="show-filter" data-target=".audit-filter"
                            class="sui-button-icon sui-button-outlined sui-pagination-open-filter">
                        <i class="sui-icon-filter" aria-hidden="true"></i>
                        <span class="sui-screen-reader-text">Open search filters</span>
                    </button>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="sui-pagination-filter audit-filter">
            <form method="post">
                <div class="sui-row">
                    <div class="sui-col-md-4">
                        <div class="sui-form-field">
                            <label class="sui-label"><?php _e( "Username", wp_defender()->domain ) ?></label>
                            <div class="sui-control-with-icon sui-right-icon">
                                <input data-name="username" id="term" name="term" type="text" class="sui-form-control"/>
                                <i class="sui-icon-magnifying-glass-search" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="sui-col-md-3">
                        <div class="sui-form-field">
                            <label class="sui-label"><?php _e( "IP Address", wp_defender()->domain ) ?></label>
                            <input type="text" data-name="ip" name="ip" id="ip" placeholder="E.g. 192.168.1.1"
                                   class="sui-form-control"/>
                        </div>
                    </div>
                </div>
                <div class="sui-row">
                    <div class="sui-col">
                        <div class="sui-form-field">
                            <div class="sui-side-tabs sui-tabs">
                                <div data-tabs>
                                    <div rel="input_value" data-target="all_type" data-value="1"
                                         class="active"><?php _e( "All", wp_defender()->domain ) ?></div>
                                    <div rel="input_value" data-target="all_type"
                                         data-value="0"><?php _e( "Specific", wp_defender()->domain ) ?></div>
                                </div>

                                <div data-panes>
                                    <div class="sui-tab-boxed wd-hide"></div>
                                    <div class="sui-tab-boxed">
										<?php foreach ( \WP_Defender\Module\Audit\Component\Audit_API::get_event_type() as $event ): ?>
                                            <label for="chk_<?php echo $event ?>" class="sui-checkbox">
												<?php $checked = in_array( $event, \Hammer\Helper\HTTP_Helper::retrieve_get( 'event_type', \WP_Defender\Module\Audit\Component\Audit_API::get_event_type() ) ) ? 'checked="checked"' : null ?>
                                                <input data-name="type" id="chk_<?php echo $event ?>"
                                                       type="checkbox" <?php echo $checked ?>
                                                       class="filterable"
                                                       name="event_type[]" value="<?php echo $event ?>">
                                                <span aria-hidden="true"></span>
                                                <span><?php echo esc_html( ucwords( str_replace( '_', ' ', $event ) ) ) ?></span>
                                            </label>
										<?php endforeach; ?>
                                    </div>
                                </div>
                                <input type="hidden" name="all_type" value="0"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sui-box-footer no-padding-bottom">
                    <div class="sui-actions-right">
                        <button type="submit" class="sui-button sui-button-blue">
							<?php _e( "Apply", wp_defender()->domain ) ?></button>
                    </div>
                </div>
            </form>
        </div>
        <div class="filter-container wd-hide margin-bottom-20">
            <small class="font-heavy"><?php _e( "Active Filters", wp_defender()->domain ) ?></small>
            <div class="sui-pagination-active-filters">
            </div>
        </div>
        <div id="audit-table-container">
            <div class="sui-notice sui-notice-loading">
                <p>
					<?php _e( "Loading events....", wp_defender()->domain ) ?>
                </p>
            </div>
        </div>
        <div class="sui-pagination-wrap">

        </div>
        <div class="clear"></div>
    </div>
</div>