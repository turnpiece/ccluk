<?php if ( ! is_wp_error( $data ) ): ?>
	<?php if ( count( $data['data'] ) ): ?>
        <table id="audit-table" class="sui-table sui-accordion">
            <thead>
            <tr>
                <th><?php _e( "Event summary", wp_defender()->domain ) ?></th>
                <th>
					<?php _e( "Date", wp_defender()->domain ) ?>
                </th>
                <th></th>
            </tr>
            </thead>
            <tbody>
			<?php foreach ( $data['data'] as $row ): ?>
				<?php
				$timestamp = is_array( $row['timestamp'] ) ? $row['timestamp'][1] : $row['timestamp'];
				?>
                <tr class="sui-accordion-item sui-default">
                    <td class="sui-table-item-title">
						<?php echo wp_trim_words( $row['msg'], 10 ) ?>
                    </td>
                    <td>
						<?php
						echo \WP_Defender\Module\Audit\Component\Audit_API::time_since( $timestamp ) . esc_html__( " ago", wp_defender()->domain );
						?>
                    </td>
                    <td>
                        <span class="sui-accordion-open-indicator" aria-label="Expand">
                            <i class="sui-icon-chevron-down" aria-hidden="true"></i>
                        </span>
                    </td>
                </tr>
                <tr class="sui-accordion-item-content">
                    <td colspan="3">
                        <div class="sui-box">
                            <div class="sui-box-body">
                                <strong>
									<?php _e( "Description", wp_defender()->domain ) ?>
                                </strong>
                                <p>
									<?php echo wp_trim_words( $row['msg'] ) ?>
                                </p>
                                <div class="sui-row">
                                    <div class="sui-col">
                                        <strong><?php _e( "Context", wp_defender()->domain ) ?></strong>
                                        <p>
                                            <a class="afilter"
                                               href="<?php echo $controller->buildFilterUrl( 'context', $row['context'] ) ?>">
												<?php echo ucwords( \WP_Defender\Module\Audit\Component\Audit_API::get_action_text( $row['context'] ) ) ?>
                                            </a>
                                        </p>
                                    </div>
                                    <div class="sui-col">
                                        <strong><?php _e( "Type", wp_defender()->domain ) ?></strong>
                                        <p>
                                            <a class="afilter"
                                               href="<?php echo $controller->buildFilterUrl( 'event_type[]', $row['event_type'] ) ?>">
												<?php echo ucwords( \WP_Defender\Module\Audit\Component\Audit_API::get_action_text( $row['event_type'] ) ) ?>
                                            </a>
                                        </p>
                                    </div>
                                    <div class="sui-col">
                                        <strong><?php _e( "IP address", wp_defender()->domain ) ?></strong>
                                        <p>
                                            <a class="afilter"
                                               href="<?php echo $controller->buildFilterUrl( 'ip', $row['ip'] ) ?>">
												<?php echo $row['ip'] ?>
                                            </a>
                                        </p>
                                    </div>
                                    <div class="sui-col">
                                        <strong><?php _e( "User", wp_defender()->domain ) ?></strong>
                                        <p>
                                            <a class="afilter"
                                               href="<?php echo $controller->buildFilterUrl( 'term', $row['user_id'] ) ?>">
												<?php
												if ( $row['user_id'] == 0 ) {
													_e( "Guest", wp_defender()->domain );
												} else {
													echo \WP_Defender\Behavior\Utils::instance()->getDisplayName( $row['user_id'] );
												}
												?>
                                            </a>
                                        </p>
                                    </div>
                                    <div class="sui-col">
                                        <strong><?php _e( "Date / Time", wp_defender()->domain ) ?></strong>
                                        <p>
											<?php
											echo $controller->formatDateTime( date( 'Y-m-d H:i:s', $timestamp ) );
											?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
	<?php else: ?>
        <div class="sui-col">
            <div class="sui-notice">
                <p>
					<?php _e( "There have been no events logged in the selected time period.", wp_defender()->domain ) ?>
                </p>
            </div>
        </div>
	<?php endif; ?>
<?php else: ?>
    <div class="sui-col">
        <div class="sui-notice sui-notice-error">
            <p>
				<?php echo $data->get_error_message() ?>
            </p>
        </div>
    </div>
<?php endif; ?>