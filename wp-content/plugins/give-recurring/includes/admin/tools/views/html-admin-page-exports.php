<?php

/**
 * This function adds a new table row for
 * Exporting Subscriptions.
 *
 * The output is visible on the -
 * Donations > Tools > Export page
 *
 * @since 1.6
 *
 * @return void
 */
function give_recurring_add_export_subscriptions_row() {
	?>
	<tr class="give-export-subscriptions">
		<td scope="row" class="row-title">
			<h3>
				<span><?php esc_html_e( 'Export Upcoming Subscriptions Renewals', 'give-recurring' ); ?></span>
			</h3>
			<p><?php esc_html_e( 'Download a CSV of upcoming subscriptions renewals.', 'give-recurring' ); ?></p>
		</td>
		<td>
			<form method="post" id="give_subscriptions_export" class="give-export-form">
				<div class="give-sr-export">
					<?php
					// Show donation forms with recurring enabled.
					echo Give()->html->forms_dropdown( array(
							'name'        => 'subscription_renewal_per_form',
							'id'          => 'subscription_renewal_per_form',
							'chosen'      => true,
							'placeholder' => esc_html__( 'All Recurring Forms', 'give-recurring' ),
							'query_args'  => array(
								'meta_query' => array(
									array(
										'key'     => '_give_recurring',
										'value'   => array( 'no' ),
										'compare' => 'NOT IN',
									),
								),
							),
						)
					);
					?>
				</div>
				<?php
				// Field to select the start date.
				echo Give()->html->date_field( array(
					'id'          => 'give_renewal_subscriptions_start_date',
					'name'        => 'give_renewal_subscriptions_start_date',
					'placeholder' => esc_html__( 'Start date', 'give-recurring' ),
				) );

				// Field to select the end date.
				echo Give()->html->date_field( array(
					'id'          => 'give_renewal_subscriptions_end_date',
					'name'        => 'give_renewal_subscriptions_end_date',
					'placeholder' => esc_html__( 'End date', 'give-recurring' ),
				) );

				$date_format = get_option( 'date_format' );

				echo sprintf(
					'<p class="give-field-description"><i>%1$s <time>%2$s</time> and <time>%3$s</time> %4$s</i></p>',
					esc_html__( 'If the date parameters are not set, then the upcoming renewals between the period', 'give-recurring' ),
					date_i18n( $date_format, current_time( 'timestamp' ) ),
					date_i18n( $date_format, strtotime( current_time( 'mysql' ) . ' +1 month' ) ),
					esc_html__( 'will be fetched.', 'give-recurring' )
				);
				?>
				<input type="submit" value="<?php esc_attr_e( 'Generate CSV', 'give-recurring' ); ?>" class="button-secondary"/>
				<?php wp_nonce_field( 'give_ajax_export', 'give_ajax_export' ); ?>
				<input type="hidden" name="give-export-class" value="Give_Subscriptions_Renewals_Export"/>
				<input type="hidden" name="give_export_option[query_id]" value="<?php echo uniqid( 'give' ); ?>"/>
			</form>
		</td>
	</tr>
	<?php
}

add_action( 'give_tools_tab_export_table_bottom', 'give_recurring_add_export_subscriptions_row' );

/**
 * Return only recurring forms for subscription export form dropdown
 * Note: only for internal logic
 *
 * @since 1.7
 *
 * @param $args
 *
 * @return array
 */
function __give_recurring_give_ajax_form_search_args( $args ) {
	if ( ! empty( $_POST['fields'] ) ) {
		$_post = array_map( 'give_clean', wp_parse_args( $_POST['fields'] ) );

		if (
			array_key_exists( 'give-action', $_post )
			&& 'subscriptions_renewal_export' === $_post['give-action']
		) {
			$args['meta_query'] = array(
				array(
					'key'     => '_give_recurring',
					'value'   => array( 'no' ),
					'compare' => 'NOT IN'
				)
			);
		}
	}

	return $args;
}

add_action( 'give_ajax_form_search_args', '__give_recurring_give_ajax_form_search_args' );


