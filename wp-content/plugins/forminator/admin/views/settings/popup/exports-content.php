<?php
$export_logs = forminator_get_export_logs($form_id);

if ( !empty($export_logs) ) :
?>
	<table class="wpmudev-table" cellspacing="0" cellpadding="0">

		<thead class="wpmudev-table-head">

		<tr>

			<th class="forminator-export--name"><?php esc_html_e( "Date", Forminator::DOMAIN ); ?></th>

			<th class="forminator-export--fields"><?php esc_html_e( "Records", Forminator::DOMAIN ); ?></th>

			<th class="forminator-export--file"></th>

			<th class="forminator-export--trash"></th>

		</tr>

		</thead>

		<tbody id="wpmudev-exports-table" class="wpmudev-table-body">
			<?php
			$html = '';
			foreach( $export_logs as $export ){
				$html .= '<tr>';
				$html .= '<td>' . $export['time'] . '</td>';
				$html .= '<td>' . $export['count'] . '</td>';
				$html .= '</tr>';
			}

			echo $html // WPCS: XSS ok.;
			?>
		</tbody>

	</table>

	<button class="wpmudev-button wpmudev-button-clear-exports wpmudev-button-sm wpmudev-button-ghost" data-nonce="<?php echo wp_create_nonce( 'forminator_clear_exports' ); // WPCS: XSS ok. ?>" data-form-id="<?php echo esc_attr( $form_id ); ?>"><?php esc_html_e( "Clear All", Forminator::DOMAIN ); ?>
	</button>
<?php else : ?>
	<p>Your list of exports is empty. <br/>Go back and export your entries to see them appear here.</p>
<?php endif; ?>