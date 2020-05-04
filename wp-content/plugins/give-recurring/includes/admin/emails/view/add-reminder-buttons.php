<?php
/** @var $remind_for string notificaiton type */
switch ( $remind_for ) {
	case 'renewal':
		$label = __( 'Add Renewal Reminder', 'give-recurring' );
		break;

	case 'expiration':
		$label = __( 'Add Expiration Reminder', 'give-recurring' );
		break;
}
?>

<tr valign="top" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . esc_attr( $field['wrapper_class'] ) . '"' : ''; ?>>
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
	</th>
	<td class="give-forminp">
		<?php
		$stored_notices = get_option( 'give_recurring_reminder_notices', array() );
		$notices_saved  = false;
		$row_template   = include GIVE_RECURRING_PLUGIN_DIR . 'includes/admin/emails/view/reminder-email-table-row.php';

		foreach ( $stored_notices as $key => $value ) {
			if ( $value['notice_type'] === $remind_for ) {
				$notices_saved = true;
				break;
			}
		}

		if ( ! empty( $stored_notices ) && $notices_saved ) {
			?>
			<table class="wp-list-table widefat fixed striped give-subscriber-notifications">
				<thead>
				<tr>
					<th class="check-column-title"></th>
					<td><?php esc_html_e( 'Subject', 'give-recurring' ); ?></td>
					<td><?php esc_html_e( 'Send Period', 'give-recurring' ); ?></td>
					<td><?php esc_html_e( 'Content Type', 'give-recurring' ); ?></td>
					<td><?php esc_html_e( 'Gateways', 'give-recurring' ); ?></td>
				</tr>
				</thead>
				<tbody>
				<?php

				foreach ( $stored_notices as $key => $value ) {
					if ( $value['notice_type'] === $remind_for ) {

						switch ( $remind_for ) {
							case 'renewal':
								$period = $this->renewal_reminder_periods;
								break;

							case 'expiration':
								$period = $this->expiration_reminder_periods;
								break;
						}

						printf(
							$row_template,
							$value['status'],
							$key,
							$value['status'],
							( 'enabled' === $value['status'] ) ? 'yes' : 'no-alt',
							$value['subject'],
							$period[ $value['send_period'] ],
							add_query_arg( array(
								'notice_action' => 'edit',
								'notice_id'     => $key,
								'notice_type'   => $remind_for,
								'_wpnonce'      => wp_create_nonce( "edit_{$remind_for}_reminder_{$key}" )
							) ),
							esc_html__( 'Edit', 'give-recurring' ),
							add_query_arg( array(
								'notice_action' => 'delete',
								'notice_id'     => $key,
								'notice_type'   => $remind_for,
								'_wpnonce'      => wp_create_nonce( "delete_{$remind_for}_reminder_{$key}" )
							) ),
							esc_html__( 'Delete', 'give-recurring' ),
							wp_nonce_url(
								add_query_arg( array(
									'give_action' => 'preview_email',
									'email_type'  => 'subscription-reminder',
									'notice_id'   => $key,
									'notice_type' => $remind_for
								) ),
								'give-preview-email'
							),
							esc_html__( 'Preview', 'give-recurring' ),
							$this->get_gateway_info( $key, $stored_notices ),
							( 'text/plain' === $stored_notices[ $key ]['content_type'] ) ? __( 'Plain', 'give-recurring' ) : __( 'HTML', 'give-recurring' )
						);
					}
				}
				?>
				</tbody>
			</table>
			<br>
			<?php
		}
		?>
		<a class="<?php echo esc_attr( $field['class'] ); ?>"
		   href="
			<?php
		   echo add_query_arg( array( // XSS ok.
			   'notice_action' => 'add',
			   'notice_type'   => $remind_for,
			   '_wpnonce'      => wp_create_nonce( "add_{$remind_for}_reminder" )
		   ) );
		   ?>
			">
			<?php echo esc_html( $label ); ?>
		</a>
	</td>
</tr>
