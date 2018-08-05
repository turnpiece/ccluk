<?php


/**
 * Class Snapshot_Process_Import_Archives
 */
class Snapshot_Process_Import_Archives {

	/**
	 * @var int
	 */
	public $error_count;

	/**
	 * @param array $error_status
	 */
	private function print_error_status( $error_status ) {

		if ( ! isset( $error_status['errorStatus'] ) ) {
			return;
		}

		if ( $error_status['errorStatus'] ) {

			if ( ! empty( $error_status['errorText'] ) ) {
				echo wp_kses_post( '<div class="wps-auth-message error"><p>' . sprintf( __( 'Error: %s', SNAPSHOT_I18N_DOMAIN ), $error_status['errorText'] ) . '</p></div>' );
				$this->error_count ++;
			}

		} else {
			if ( ! empty( $error_status['responseText'] ) ) {
				echo wp_kses_post( '<div class="wps-auth-message success"><p>' . sprintf( __( 'Success: %s', SNAPSHOT_I18N_DOMAIN ), $error_status['responseText'] ) . '</p></div>' );
			}

		}
	}

	/**
	 * @param string $dir
	 *
	 * @return bool
	 */
	private function process_local_archives( $dir = '' ) {

		$base_dir = trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupBaseFolderFull' ) );

		if ( empty( $dir ) ) {
			$dir = $base_dir;
		} else {

			// If the path is relative, append it to the base backup folder
			$base_dir = '/' === substr( $dir, 0, 1 ) ? '' : $base_dir;
			$dir = $base_dir . $dir;
		}

		$dir = trailingslashit( $dir );

		if ( ! is_dir( $dir ) ) {
			return false;
		}

		echo wp_kses_post( '<div class="wps-notice"><p>' . sprintf( __( 'Importing archives from: %s', SNAPSHOT_I18N_DOMAIN ), $dir ) . '</p></div>' );

		$dh = opendir( $dir );

		if ( ! $dh ) {
			return false;
		}

		$restore_folder = trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupRestoreFolderFull' ) ) . '_imports';

		echo '<ol>';

		$file = readdir( $dh );
		while ( false !== $file ) {

			if ( '.' === $file || '..' === $file || 'index.php' === $file || '.' === $file[0] ) {
				$file = readdir( $dh );
				continue;
			}

			if ( 'zip' !== pathinfo( $file, PATHINFO_EXTENSION ) ) {
				$file = readdir( $dh );
				continue;
			}

			$restore_file = $dir . $file;

			if ( is_dir( $restore_file ) ) {
				$file = readdir( $dh );
				continue;
			}

			// Check if the archive is full backup - we don't import those
			if ( Snapshot_Helper_Backup::is_full_backup( $file ) ) {
				$file = readdir( $dh );
				continue;
			}

			echo wp_kses_post( sprintf( '<li><strong>%s: %s</strong> (%s)<ul><li>',
							__( 'Processing archive', SNAPSHOT_I18N_DOMAIN ),
							basename( $restore_file ),
							Snapshot_Helper_Utility::size_format( filesize( $restore_file ) )
						) );

			flush();

			$error_status = Snapshot_Helper_Utility::archives_import_proc( $restore_file, $restore_folder );
			$this->print_error_status( $error_status );

			echo '</li></ul></li>';

			$file = readdir( $dh );
		}

		echo '</ol>';
		closedir( $dh );
		return true;
	}

	/**
	 * @param string $remote_file
	 */
	private function process_remote_archive( $remote_file ) {

		// phpcs:ignore
		@set_time_limit( 15 * 60 ); // 15 minutes - technically, server to server should be quick for large files.

		echo wp_kses_post( sprintf( '<p>%s: %ds</p>', __( 'PHP max_execution_time', SNAPSHOT_I18N_DOMAIN ), ini_get( 'max_execution_time' ) ) );
		echo wp_kses_post( sprintf( '<p>%s: %s</p>', __( 'Attempting to download remote file', SNAPSHOT_I18N_DOMAIN ), esc_html( $remote_file ) ) );

		flush();

		$restore_file = trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupBaseFolderFull' ) ) . basename( $remote_file );

		Snapshot_Helper_Utility::remote_url_to_local_file( $remote_file, $restore_file );
		// $response_file = Snapshot_Helper_Utility::remote_url_to_local_file( $remote_file, $restore_file );
		// if ( ! $response_file)
		// 	$response_file = __( 'Not a zip file', SNAPSHOT_I18N_DOMAIN );
		if ( ! file_exists( $restore_file ) ) {

			echo wp_kses_post( "<div class='wps-notice'><p>" . __( 'local import file not found. This could mean either the entered URL was not valid or the file was not publicly accessible.', SNAPSHOT_I18N_DOMAIN ) . "</p></div>" );
			return;
		}

		$restore_folder = trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupRestoreFolderFull' ) ) . "_imports";

		echo '<ol>';

		echo wp_kses_post( sprintf( '<li><strong>%s: %s</strong> (%s)<ul><li>',
					__( 'Processing archive', SNAPSHOT_I18N_DOMAIN ),
					basename( $restore_file ),
					Snapshot_Helper_Utility::size_format( filesize( $restore_file ) )
				) );

		flush();

		$error_status = Snapshot_Helper_Utility::archives_import_proc( $restore_file, $restore_folder );
		$this->print_error_status( $error_status );

		echo '</li></ul></li>';
		echo '</ol>';
	}

	/**
	 *
	 */
	public function process() {
		$this->error_count = 0;
		if ( ! wp_verify_nonce( $_POST['snapshot-noonce-field'], 'snapshot-import' ) ) {
			return;
		}

		/* If no URL or directory is specified, check the local directory */
		if ( empty( $_POST['snapshot-import-archive-remote-url'] ) ) {
			$this->process_local_archives();
			return;
		}

		if ( substr( $_POST['snapshot-import-archive-remote-url'], 0, 4 ) !== 'http' ) {
			$dir = sanitize_text_field( $_POST['snapshot-import-archive-remote-url'] );

			if ( ! $this->process_local_archives( $dir ) ) {
				echo wp_kses_post( '<div class="wps-notice"><p>' . sprintf( __( 'local import file not found %s. This could mean either the entered path was not valid or accessible.', SNAPSHOT_I18N_DOMAIN ), $dir ) . '</p></div>' );
			}

		} else {

			if ( ! function_exists( 'curl_version' ) ) {

				echo wp_kses_post( '<div class="wps-auth-message error"><p>' .  __( 'Error: Your server does not have lib_curl installed. So the import process cannot retrieve remote file.', SNAPSHOT_I18N_DOMAIN ) . '</p></div>' );
				return;
			}

			$this->process_remote_archive( esc_url_raw( $_POST['snapshot-import-archive-remote-url'] ) );
		}
	}
}

?>
<section id="header">
	<h1><?php esc_html_e( 'Import', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-import">
	<section class="wpmud-box">

		<div class="wpmud-box-title">
			<h3><?php esc_html_e( 'Local Import', SNAPSHOT_I18N_DOMAIN ); ?></h3>
		</div>

		<div class="wpmud-box-content">
			<form action="?page=snapshot_pro_import" method="post">
				<input type="hidden" value="archives-import" name="snapshot-action">
				<?php wp_nonce_field( 'snapshot-import', 'snapshot-noonce-field' ); ?>

				<div id="wps-import-message" class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<p><?php esc_html_e( 'Missing a snapshot? You can use this import tool to find any missing snapshots. Snapshot will automatically check your integrations but you can also add a custom directory below.', SNAPSHOT_I18N_DOMAIN ); ?></p>
						<div class="wps-notice">
							<h4><?php esc_html_e( 'Import options', SNAPSHOT_I18N_DOMAIN); ?></h4>
							<h5><?php esc_html_e( 'Remote archives', SNAPSHOT_I18N_DOMAIN ); ?></h5>
							<p><?php echo wp_kses_post( __( 'The <strong>import</strong> process can import an archive from a remote system server via FTP, Amazon S3 or Dropbox. The remote archive <strong>must</strong> be publicly accessible as this import process does not yet support authentication. See notes below on specific services.', SNAPSHOT_I18N_DOMAIN ) ); ?></p>
							<ul>
								<li><?php echo wp_kses_post( __( '<strong>Remote FTP:</strong> When downloading from a remote FTP server you must ensure the file is moved to a location where it will be accessible via a simple http:// or https:// URL.', SNAPSHOT_I18N_DOMAIN ) ); ?></li>
								<li><?php echo wp_kses_post( __( '<strong>Dropbox:</strong> If you are attempting to download a Dropbox Snapshot archive written to the <strong>App/WPMU DEV Snapshot</strong> you first need to copy the file to a public folder within your Dropbox account before grabbing the public link.', SNAPSHOT_I18N_DOMAIN ) ); ?></li>
								<li><?php echo wp_kses_post( __( '<strong>Amazon S3:</strong> When downloading a file from S3 you need to ensure the file is public.', SNAPSHOT_I18N_DOMAIN ) ); ?></li>
							</ul>
							<h5><?php esc_html_e( 'Local archives', SNAPSHOT_I18N_DOMAIN ); ?></h5>
							<p><?php echo wp_kses_post( __( 'For archives already in your server but not showing in the ALL Snapshots listing you can simply submit this form without entering a value below. This will scan the snapshot archives directory <strong>/media/storage/www/wp/snapshotold/wp-content/uploads/snapshots</strong> for any missing archives and add them to the listing.', SNAPSHOT_I18N_DOMAIN ) ); ?></p>
							<p><?php echo wp_kses_post( __( 'If the missing archive is on the server but saved to a different path. Maybe you setup the archive to save to an alternate directory. Then you can enter the full server path to the <strong>directory</strong> where the archive resides.', SNAPSHOT_I18N_DOMAIN ) ); ?></p>
						</div>
					</div>
				</div>

				<div id="wps-import-integrations" class="row">
					<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
						<label class="label-box"><?php esc_html_e( 'Integrations', SNAPSHOT_I18N_DOMAIN ); ?></label>
					</div>

					<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
						<div class="wpmud-box-mask">
							<p class="wps-integration-item"><span class="wps-typecon dropbox"></span>Dropbox</p>
							<p class="wps-integration-item"><span class="wps-typecon amazon"></span>Amazon S3</p>
						</div>
					</div>

				</div>

				<div id="wps-import-directory" class="row">
					<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
						<label class="label-box"><?php esc_html_e( 'Directory URL', SNAPSHOT_I18N_DOMAIN ); ?></label>
					</div>

					<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
						<div class="wpmud-box-mask">
							<input id="snapshot-import-archive-remote-url" type="text"
								   name="snapshot-import-archive-remote-url" class="inline" value=""
								   placeholder="<?php esc_html_e( 'Enter directory', SNAPSHOT_I18N_DOMAIN ); ?>"/>

							<p>
								<small><?php echo wp_kses_post( sprintf( __( 'Your current snapshot directory is %s. We will automatically check this directory also.', SNAPSHOT_I18N_DOMAIN ), trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupBaseFolderFull' ) ) ) ); ?></small>
							</p>

						</div>
					</div>
				</div>

				<div class="row">

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<div class="form-button-container">
							<input id="snapshot-add-button" class="button button-blue float-r" type="submit" value="<?php esc_html_e( 'Import', SNAPSHOT_I18N_DOMAIN ); ?>">
						</div>

					</div>

				</div>

			</form>

			<div class="row">

				<div class="col-xs-12">

					<?php

					if ( isset( $_POST['snapshot-noonce-field'] ) && wp_verify_nonce( $_POST['snapshot-noonce-field'], 'snapshot-import' ) && isset( $_REQUEST['snapshot-action'] ) && esc_attr( $_REQUEST['snapshot-action'] ) === "archives-import" ) {

						$import_class = new Snapshot_Process_Import_Archives();
						$import_class->process();

						if ( $import_class->error_count > 0 ) {
							echo '<div class="wps-auth-message error"><p>' . esc_html__( 'Oh no! One or more of your Snapshot archives was not imported successfully. Please check your Snapshot logs for more details, or try restoring again in a few moments.', SNAPSHOT_I18N_DOMAIN ) . '</p></div>';
						} else {
							echo "<div class='wps-auth-message success'><p>" . esc_html__( 'No errors were encountered during the import process.', SNAPSHOT_I18N_DOMAIN ) . "</p></div>";
						}

					}

					?>

				</div><?php // .col-xs-12 ?>

			</div><?php // .row ?>

		</div>

	</section>
</div>