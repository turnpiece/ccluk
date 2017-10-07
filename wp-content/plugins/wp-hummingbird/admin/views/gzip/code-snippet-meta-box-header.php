<?php if ( ! $full_enabled ) : ?>
	<div class="extra">
		<div class="wphb-select-group">
			<label for="wphb-server-type" class="inline-label"><?php echo esc_html( 'Server Type:', 'wphb' ); ?></label>
			<?php wphb_get_servers_dropdown( array( 'selected' => $gzip_server_type ), false ); ?>
		</div>
	</div>
<?php else : ?>
	<input id="wphb-server-type" type="hidden" value="<?php echo esc_attr( $gzip_server_type ); ?>">
<?php endif; ?>
<h3><?php echo esc_html( $title ); ?></h3>