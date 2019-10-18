<?php /** @var WPMUDEVSnapshot_New_Ui_Tester $this */ ?>
<?php
$disabled_buttons_styling = ' style="background-color: #bcbcbc!important; color: #ffffff!important; cursor: default; text-shadow: none!important;"';
if ( version_compare(PHP_VERSION, '5.5.0', '<') ) {
	$aws_sdk_compatible = false;
} else {
	$aws_sdk_compatible = true;
}
?>

<div class="form-content">

	<div id="wps-destination-type" class="form-row">

		<div class="form-col-left">
			<label><?php esc_html_e( 'Type', SNAPSHOT_I18N_DOMAIN ); ?></label>
		</div>

		<div class="form-col">
			<i class="wps-typecon aws"></i>
			<label><?php esc_html_e( 'Amazon S3', SNAPSHOT_I18N_DOMAIN ); ?></label>
		</div>

	</div>

	<div id="wps-destination-name" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-name"><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">

			<input name="snapshot-destination[name]" id="snapshot-destination-name" type="text" class="<?php $this->input_error_class( 'name' ); ?>"
				value="<?php if ( isset( $item['name'] ) ) echo esc_attr( stripslashes( sanitize_text_field( $item['name'] ) ) ); ?>" <?php echo ( ! $aws_sdk_compatible ) ? 'readonly': ''; ?>>
			<?php $this->input_error_message( 'name' ); ?>
		</div>

	</div>

	<div id="wps-destination-id" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-awskey"><?php esc_html_e( 'AWS Access Key ID', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">
			<input type="text" name="snapshot-destination[awskey]" id="snapshot-destination-awskey" class="<?php $this->input_error_class( 'awskey' ); ?>"
				value="<?php if ( isset( $item['awskey'] ) ) echo esc_attr( sanitize_text_field( $item['awskey'] ) ); ?>" <?php echo ( ! $aws_sdk_compatible ) ? 'readonly': ''; ?>>

			<?php $this->input_error_message( 'awskey' ); ?>

			<p><small><?php echo wp_kses_post( sprintf( __( 'You can get your access keys via the <a target="_blank" href="%s">AWS Console</a>', SNAPSHOT_I18N_DOMAIN ), esc_url( 'https://aws-portal.amazon.com/gp/aws/securityCredentials' ) ) ); ?></small></p>
		</div>

	</div>

	<div id="wps-destination-key" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-secretkey"><?php esc_html_e( "AWS Secret Access Key", SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">
			<input type="password" name="snapshot-destination[secretkey]" id="snapshot-destination-secretkey" class="<?php $this->input_error_class( 'secretkey' ); ?>" value="<?php if ( isset( $item['secretkey'] ) ) echo esc_attr( sanitize_text_field( $item['secretkey'] ) ); ?>" <?php echo ( ! $aws_sdk_compatible ) ? 'readonly': ''; ?>/>
			<?php $this->input_error_message( 'secretkey' ); ?>
		</div>

	</div>
	<?php
    if ( ! isset( $item['ssl'] ) ) {
			$item['ssl'] = "yes";
	}
    ?>
	<div id="wps-destination-ssl" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-ssl"><?php esc_html_e( "Use SSL Connection", SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">
			<div class="wps-input--checkbox">
				<input name="snapshot-destination[ssl]" type="hidden" value="no" <?php checked( $item['ssl'], "no" ); ?> />
				<input name="snapshot-destination[ssl]" id="snapshot-destination-ssl" type="checkbox" <?php checked( $item['ssl'], "yes" ); ?> value="yes" <?php echo ( ! $aws_sdk_compatible ) ? 'disabled': ''; ?> />
				<label for="snapshot-destination-ssl"></label>
				<?php $this->input_error_message( 'ssl' ); ?>
			</div>
		</div>

	</div>

	<?php
    if ( ! isset( $item['region'] ) ) {
		$item['region'] = ( $aws_sdk_compatible ) ? Snapshot_Model_Destination_AWS::REGION_US_E1 : 'US Standard (s3.amazonaws.com)';
	}
	?>

	<div id="wps-destination-region" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-region"><?php esc_html_e( 'AWS Region', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">

			<select class="inline<?php $this->input_error_class( 'region' ); ?>" name="snapshot-destination[region]" id="snapshot-destination-region" <?php echo ( ! $aws_sdk_compatible ) ? 'disabled': ''; ?> >

				<?php
				if ( $aws_sdk_compatible ) {
					foreach ( $item_object->get_regions() as $_key => $_name ) :
					?>
						<option value="<?php echo esc_attr( $_key ); ?>"
							<?php
							if ( $item['region'] === $_key ) {
								echo ' selected="selected" ';
							}
							?>
							>
							<?php echo esc_html( $_name ); ?> (<?php echo esc_html( $_key ); ?>)
						</option>

					<?php
					endforeach;
				} else {
					?>
					<option value="<?php echo esc_attr( $item['region'] ); ?>"><?php echo esc_html( $item['region'] ); ?></option>
					<?php
				}
				?>


			</select>

			<?php $this->input_error_message( 'region' ); ?>

			<?php
			if ( $aws_sdk_compatible ) {
			?>
				<div id="snapshot-destination-region-other-container"
				<?php
				if ( 'other' !== $item['region'] ) {
					echo ' style="display: none;" ';
				}
				?>
				>
					<br /><label
							id="snapshot-destination-region-other"><?php esc_html_e( 'Alternate Region host', SNAPSHOT_I18N_DOMAIN ); ?></label>
						<input name="snapshot-destination[region-other]"
								id="snapshot-destination-region-other"
								type="text"
								value="<?php echo esc_attr( $item['region-other'] ); ?>"/>
					<br />
				</div>
				<?php
			}
			?>

			<?php
			if ( $aws_sdk_compatible ) {
			?>
				<div id="snapshot-destination-region-non-aws-container"
				<?php
				if ( 'non-aws' !== $item['region'] ) {
					echo ' style="display: none;" ';
				}
				?>
				>
					<br /><label
							id="snapshot-destination-region-non-aws-host"><?php esc_html_e( 'Non AWS host', SNAPSHOT_I18N_DOMAIN ); ?></label>
						<input name="snapshot-destination[region-non-aws-host]"
								id="snapshot-destination-region-non-aws-host"
								type="text"
								value="<?php echo esc_attr( $item['region-non-aws-host'] ); ?>"/>
					<label
							id="snapshot-destination-region-non-aws-region"><?php esc_html_e( 'Non AWS region', SNAPSHOT_I18N_DOMAIN ); ?></label>
						<input name="snapshot-destination[region-non-aws-region]"
								id="snapshot-destination-region-non-aws-region"
								type="text"
								value="<?php echo esc_attr( $item['region-non-aws-region'] ); ?>"/>
					<br />
				</div>
				<?php
			}
			?>

		</div>

	</div>

	<?php
	if ( ! isset( $item['storage'] ) ) {
		$item['storage'] = ( $aws_sdk_compatible ) ? Snapshot_Model_Destination_AWS::STORAGE_STANDARD : 'Standard';
	}
	?>

	<div id="wps-destination-storage" class="form-row">

		<div class="form-col-left">
			<label for="snapshot-destination-storage"><?php esc_html_e( 'Storage Type', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">

			<select class="inline<?php $this->input_error_class( 'storage' ); ?>" name="snapshot-destination[storage]" id="snapshot-destination-storage" <?php echo ( ! $aws_sdk_compatible ) ? 'disabled': ''; ?> >

				<?php
				if ( $aws_sdk_compatible ) {
					foreach ( $item_object->get_storage() as $_key => $_name ) :
					?>

						<option value="<?php echo esc_attr( $_key ); ?>"
							<?php
							if ( $item['storage'] === $_key ) {
								echo ' selected="selected" ';
							}
							?>
							>
							<?php echo esc_html( $_name ); ?> (<?php echo esc_attr( $_key ); ?>)
						</option>

					<?php
					endforeach;
				} else {
					?>
					<option value="<?php echo esc_attr( $item['storage'] ); ?>"><?php echo esc_html( $item['storage'] ); ?></option>
					<?php
				}
				?>

			</select>

			<?php $this->input_error_message( 'storage' ); ?>
		</div>
	</div>

	<div id="wps-destination-bucket" class="form-row">

		<div class="form-col-left">
			<label><?php esc_html_e( 'Bucket', SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">

			<div class="wps-aws-bucket-align">

				<?php
				if ( isset( $item['bucket'] ) ) {
                ?>
					<span id="snapshot-destination-bucket-display"><?php echo esc_html( $item['bucket'] ); ?></span>
					<input
					type="hidden" name="snapshot-destination[bucket]"
					id="snapshot-destination-bucket"
					value="<?php if ( isset( $item['bucket'] ) ) echo esc_attr( $item['bucket'] ); ?>" />
                    <?php
				}
                ?>

				<button id="snapshot-destination-aws-get-bucket-list" class="button-seconary button button-gray<?php if ( empty ( $item['bucket']  ) ) echo ' wps-last-item'; ?>" name="" <?php echo ( ! $aws_sdk_compatible ) ? 'disabled' . $disabled_buttons_styling : ''; ?>><?php esc_html_e( 'Select Bucket', SNAPSHOT_I18N_DOMAIN ); ?></button> <?php // phpcs:ignore ?>

			</div>

			<div id="snapshot-ajax-destination-bucket-error" style="display:none" class="inline-notice err"></div>
			<div id="snapshot-ajax-destination-bucket-result" style="display:none">
				<select name="snapshot-destination[bucket]" id="snapshot-destination-bucket-list"></select>
			</div>

			<?php $this->input_error_message( 'bucket' ); ?>
		</div>

	</div>

	<div id="wps-destination-permission" class="form-row">
		<div class="form-col-left">
			<label><?php esc_html_e( "File Permissions", SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>
		</div>

		<div class="form-col">

			<?php
            if ( ! isset( $item['acl'] ) ) {
				$item['acl'] = ( $aws_sdk_compatible ) ? Snapshot_Model_Destination_AWS::ACL_PRIVATE : 'Private';
			}
            ?>
			<select name="snapshot-destination[acl]" id="snapshot-destination-acl" class="<?php $this->input_error_class( 'acl' ); ?>" <?php echo ( ! $aws_sdk_compatible ) ? 'disabled': ''; ?> >
				<?php
				if ( $aws_sdk_compatible ) {
				?>
					<option value="<?php echo esc_attr( Snapshot_Model_Destination_AWS::ACL_PRIVATE ); ?>" <?php selected( $item['acl'], Snapshot_Model_Destination_AWS::ACL_PRIVATE ); ?>>
						<?php esc_html_e( 'Private', SNAPSHOT_I18N_DOMAIN ); ?>
					</option>
					<option value="<?php echo esc_attr( Snapshot_Model_Destination_AWS::ACL_PUBLIC ); ?>" <?php selected( $item['acl'], Snapshot_Model_Destination_AWS::ACL_PUBLIC ); ?>>
						<?php esc_html_e( 'Public Read', SNAPSHOT_I18N_DOMAIN ); ?>
					</option>
					<option value="<?php echo esc_attr( Snapshot_Model_Destination_AWS::ACL_OPEN ); ?>" <?php selected( $item['acl'], Snapshot_Model_Destination_AWS::ACL_OPEN ); ?>>
						<?php esc_html_e( 'Public Read/Write', SNAPSHOT_I18N_DOMAIN ); ?>
					</option>
					<option value="<?php echo esc_attr( Snapshot_Model_Destination_AWS::ACL_AUTH_READ ); ?>" <?php selected( $item['acl'], Snapshot_Model_Destination_AWS::ACL_AUTH_READ ); ?>>
						<?php esc_html_e( 'Authenticated Read', SNAPSHOT_I18N_DOMAIN ); ?>
					</option>
				<?php
				} else {
					?>
					<option value="<?php echo esc_attr( $item['acl'] ); ?>"><?php echo esc_html( $item['acl'] ); ?></option>
					<?php
				}
				?>

			</select>

			<?php $this->input_error_message( 'acl' ); ?>

			<p><small><?php esc_html_e('Control who will have access to your backup files.', SNAPSHOT_I18N_DOMAIN); ?></small></p>
		</div>

	</div>

	<div id="wps-destination-dir" class="form-row">

		<div class="form-col-left">
			<label><?php esc_html_e( "Directory (optional)", SNAPSHOT_I18N_DOMAIN ); ?></label>
		</div>

		<div class="form-col">
			<input type="text" name="snapshot-destination[directory]" id="snapshot-destination-directory" placeholder="i.e. static/files" value="<?php if ( isset( $item['directory'] ) ) echo esc_attr( $item['directory'] ); ?>" <?php echo ( ! $aws_sdk_compatible ) ? 'disabled': ''; ?> />

			<p><small><?php esc_html_e( "If directory is blank the snapshot file will be stored at the bucket root. If the directory is provided it will be created inside the bucket. This is a global setting and will be used by all snapshot configurations using this destination. You can also define a directory used by a specific snapshot.", SNAPSHOT_I18N_DOMAIN ); ?></small></p>

			<button id="snapshot-destination-test-connection" class="button button-gray" <?php echo ( ! $aws_sdk_compatible ) ? 'disabled' . $disabled_buttons_styling : ''; ?>><?php esc_html_e( "Test Connection", SNAPSHOT_I18N_DOMAIN ); ?></button> <?php // phpcs:ignore ?>
			<div id="snapshot-ajax-destination-test-result" style="display:none"></div>
		</div>

	</div>

	<input type="hidden" name="snapshot-destination[type]" id="snapshot-destination-type" value="<?php echo esc_attr( $item['type'] ); ?>"/>
	<input type="hidden" name="snapshot-ajax-nonce" id="snapshot-ajax-nonce" value="<?php echo esc_attr( wp_create_nonce( 'snapshot-ajax-nonce' ) ); ?>" />


</div>