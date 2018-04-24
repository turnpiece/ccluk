<?php
/**
 * Advanced tools: system information meta box.
 *
 * @var array $system_info Array of system information ( PHP, MySQL, WordPress & Server) settings and values.
 *
 */
?>

<div class="sui-margin-bottom">
	<p>
		<?php esc_html_e( 'Use this info if you are having issues with Hummingbird and your server setup. It will give you the most up to date information about your stack.', 'wphb' ); ?>
	</p>
</div>
<select id="wphb-system-info-dropdown" class="sui-form-field wphb-system-info-dropdown" name="system-info">
	<option value="php"><?php esc_html_e( 'PHP', 'wphb' ); ?></option>
	<option value="db"><?php esc_html_e( 'MySQL', 'wphb' ); ?></option>
	<option value="wp"><?php esc_html_e( 'WordPress', 'wphb' ); ?></option>
	<option value="server"><?php esc_html_e( 'Server', 'wphb' ); ?></option>
</select>

<?php foreach ( $system_info as $system_name => $system_info_arr ) : ?>
	<table id="wphb-system-info-<?php echo esc_attr( $system_name ); ?>" class="sui-table wphb-sys-info-table sui-hidden">
		<tbody>
		<?php foreach ( $system_info_arr as $name => $value ) : ?>
			<tr>
				<td><?php echo esc_html( $name ); ?></td>
				<td><?php echo $value; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endforeach; ?>