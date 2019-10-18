<?php /** @var WPMUDEVSnapshot_New_Ui_Tester $this */ ?>

<div class="form-content">

	<div id="wps-destination-type" class="form-row">
		<div class="form-col-left">
			<label><?php esc_html_e('Type', SNAPSHOT_I18N_DOMAIN); ?></label>
		</div>

		<div class="form-col">
			<i class="wps-typecon sftp"></i>
			<label><?php esc_html_e('FTP', SNAPSHOT_I18N_DOMAIN); ?></label>
		</div>

	</div>

	<div id="wps-destination-name" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-name"><?php esc_html_e( "Name", SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col upload-progress">
			<input name="snapshot-destination[name]" id="snapshot-destination-name" type="text" class="inline<?php $this->input_error_class( 'name' ); ?>"
					value="<?php if ( isset( $item['name'] ) ) echo esc_attr( stripslashes( $item['name'] ) ); ?>" />
			<?php $this->input_error_message( 'name' ); ?>
		</div>

	</div>

	<div id="wps-destination-contype" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-protocol"><?php esc_html_e( "Connection Type", SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">

			<select class="<?php $this->input_error_class( 'protocol' ); ?>" name="snapshot-destination[protocol]" id="snapshot-destination-protocol">

				<?php foreach ( $item_object->protocols as $_key => $_name ) : ?>

					<option value="<?php echo esc_attr( $_key ); ?>"<?php selected( isset( $item['protocol'] ) && $item['protocol'] === $_key ); ?>>
						<?php echo esc_html( $_name ); ?> (<?php echo esc_html( $_key ); ?>)
					</option>

				<?php endforeach; ?>

			</select>

			<?php $this->input_error_message( 'protocol' ); ?>

			<p><small><?php echo wp_kses_post( sprintf( __( 'The FTP option will use the standard PHP library functions. Choosing FTPS will use the <a target="_blank" href="%s">PHP Secure Communications Library</a>This option may not work depending on how your PHP binaries are compiled. FTPS with TSL/SSL attemts a secure connection, however it will only work if PHP and OpenSSL are properly configured on your host and destination host. This option will also not work with Windows using the default PHP binaries. Check the PHP docs for ftp_ssl_connection. For SFTP, a PHP version equal or greater than 5.3.8 is required.', SNAPSHOT_I18N_DOMAIN ), esc_url( '#' ) ) ); ?></small></p>

		</div>

	</div>

	<div id="wps-destination-host" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-address"><?php esc_html_e('Host', SNAPSHOT_I18N_DOMAIN); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">

			<input type="text" name="snapshot-destination[address]" id="snapshot-destination-address" class="<?php $this->input_error_class( 'address' ); ?>"
					value="<?php if ( isset( $item['address'] ) ) echo esc_attr( $item['address'] ); ?>" />

			<span class="inbetween"><?php esc_html_e( 'Port', SNAPSHOT_I18N_DOMAIN ); ?></span>

			<input type="text" name="snapshot-destination[port]" id="snapshot-destination-port" class="<?php $this->input_error_class( 'port' ); ?>"
					value="<?php if ( isset( $item['port'] ) ) echo esc_attr( $item['port'] ); ?>" />

			<?php
			$this->input_error_message( 'address' );
			$this->input_error_message( 'port' );
			?>
		</div>

	</div>

	<div id="wps-destination-host" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-username"><?php esc_html_e('User', SNAPSHOT_I18N_DOMAIN); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">
			<input type="text" name="snapshot-destination[username]" id="snapshot-destination-username" class="<?php $this->input_error_class( 'username' ); ?>"
					value="<?php if ( isset( $item['username'] ) ) echo esc_attr( $item['username'] ); ?>" />

			<?php $this->input_error_message( 'username' ); ?>
		</div>

	</div>

	<div id="wps-destination-password" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-password"><?php esc_html_e('Password', SNAPSHOT_I18N_DOMAIN); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">
			<input type="password" name="snapshot-destination[password]" id="snapshot-destination-password" class="<?php $this->input_error_class( 'password' ); ?>"
					value="<?php if ( isset( $item['password'] ) ) echo esc_attr( $item['password'] ); ?>" />

			<?php $this->input_error_message( 'password' ); ?>
		</div>

	</div>

	<div id="wps-destination-dir" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-directory"><?php esc_html_e('Directory', SNAPSHOT_I18N_DOMAIN); ?></label>
		</div>

		<div class="form-col">
			<input type="text" name="snapshot-destination[directory]" id="snapshot-destination-directory" class="<?php $this->input_error_class( 'directory' ); ?>"
					value="<?php if ( isset( $item['directory'] ) ) echo esc_attr( $item['directory'] ); ?>" />

			<?php $this->input_error_message( 'directory' ); ?>

			<p><small><?php esc_html_e( "This directory will be used to store your Snapshot archives and must already exist on the server. If the remote path is left blank, the FTP home directory will be used as the destination for your Snapshot files.", SNAPSHOT_I18N_DOMAIN ); ?></small></p>
		</div>

	</div>

	<?php
	if ( ! isset( $item['passive'] ) ) {
		$item['passive'] = "no";
	}
    ?>

	<div id="wps-destination-mode" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-passive"><?php esc_html_e('Use Passive Mode', SNAPSHOT_I18N_DOMAIN); ?></label>
		</div>

		<div class="form-col">

			<input name="snapshot-destination[passive]" type="hidden" value="no" <?php checked( $item['passive'], "no" ); ?> />

			<div class="wps-input--checkbox">

				<input name="snapshot-destination[passive]" id="snapshot-destination-passive" type="checkbox" value="yes" <?php checked( $item['passive'], "yes" ); ?> />

				<label for="snapshot-destination-passive"></label>

			</div>

			<p><small><?php esc_html_e( "In passive mode, data connections are initiated by the client, rather than by the server. It may be needed if the client is behind firewall. Passive mode is off by default.", SNAPSHOT_I18N_DOMAIN ); ?></small></p>

		</div>

	</div>

	<div id="wps-destination-server" class="form-row">

		<div class="form-col-left">
			<label><?php esc_html_e('Server Timeout', SNAPSHOT_I18N_DOMAIN); ?></label>
		</div>

		<div class="form-col">
			<input type="text" name="snapshot-destination[timeout]" id="snapshot-destination-timeout" value="<?php echo ( isset( $item['timeout'] ) ) ? esc_attr( $item['timeout'] ) : 90 ; ?>" style="min-width: 10%;" />

			<p><small><?php esc_html_e( "The default timeout for PHP FTP connections is 90 seconds. Sometimes this timeout needs to be longer for slower connections to busy servers.", SNAPSHOT_I18N_DOMAIN ); ?></small></p>

			<button id="snapshot-destination-test-connection" class="button button-gray"><?php esc_html_e( "Test Connection", SNAPSHOT_I18N_DOMAIN ); ?></button>
			<div id="snapshot-ajax-destination-test-result" style="display:none"></div>
		</div>

	</div>

	<input type="hidden" name="snapshot-destination[type]" id="snapshot-destination-type" value="<?php echo esc_attr( $item['type'] ); ?>"/>
	<input type="hidden" name="snapshot-ajax-nonce" id="snapshot-ajax-nonce" value="<?php echo esc_attr( wp_create_nonce( 'snapshot-ajax-nonce' ) ); ?>" />
</div>
