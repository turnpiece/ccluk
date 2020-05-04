<tr valign="top" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . esc_attr( $field['wrapper_class'] ) . '"' : ''; ?>>
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
	</th>
	<td class="give-forminp">
		<select name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>">
			<?php
			$period = ( 'renewal' === $this->notice_type )
				? $this->renewal_reminder_periods
				: $this->expiration_reminder_periods;

			foreach ( $period as $key => $value ) {
				if ( 'edit' === $this->notice_action && $key === $stored_notices[ $this->notice_id ]['send_period'] ) {
					printf( '<option selected value="%1$s">%2$s</option>', esc_attr( $key ), esc_html( $value ) );
				} else {
					printf( '<option value="%1$s">%2$s</option>', esc_attr( $key ), esc_html( $value ) );
				}
			}
			?>
		</select>
		<p class="give-field-description"><?php esc_html_e( 'The time before renewal/expiration, at which this email should be sent out.', 'give-recurring' ) ?></p>
	</td>
</tr>
