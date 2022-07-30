<?php
/**
 * Shipper package migration templates: previously existing migration template
 *
 * @since v1.1
 * @package shipper
 */

$assets = new Shipper_Helper_Assets();
$model  = new Shipper_Model_Stored_Package();
?>
<div class="shipper-ready">
	<i class="sui-icon-check" aria-hidden="true"></i>
	<h2><?php esc_html_e( 'Ready To Migrate', 'shipper' ); ?></h2>
	<p><?php esc_html_e( 'Your package is ready! Follow the instructions below to migrate this site to another server:', 'shipper' ); ?></p>
</div>

<div class="shipper-instructions">

	<div class="shipper-instruction">
		<div class="shipper-instruction-title">
			<span class="shipper-instruction-icon">
				<i class="sui-icon-download" aria-hidden="true"></i>
			</span>
			<span class="shipper-instruction-order">1</span>
			<span class="shipper-instruction-brief">
				<?php esc_html_e( 'Download package archive and installer', 'shipper' ); ?>
			</span><!-- shipper-instruction-brief -->
		</div><!-- shipper-instrction-title -->

		<div class="shipper-instruction-body">
			<p class="sui-p-small"><?php esc_html_e( 'The first step is to download both the archive ZIP file, and the installer.php file. You\'ll need both of these to complete the migration.', 'shipper' ); ?></p>
			<div class="shipper-download">

				<input
					type="hidden"
					name="_wpnonce"
					value="<?php echo esc_attr( wp_create_nonce( 'shipper-package-download' ) ); ?>"
				>

				<div class="shipper-download-item archive">
					<div class="shipper-download-icon">
						<img src="<?php echo esc_url( $assets->get_asset( 'img/icon-zip.svg' ) ); ?>"/>
					</div>
					<div class="shipper-download-meta">
						<b><?php esc_html_e( 'Package Archive', 'shipper' ); ?></b>
						<span><?php echo esc_html( size_format( $model->get_size() ) ); ?></span>
					</div><!-- shipper-download-meta -->
					<div class="shipper-download-action">
						<a href="#download" class="sui-tooltip" data-tooltip="Download">
							<i class=" sui-icon-download" aria-hidden="true"></i>
						</a>
					</div>
				</div><!-- shipper-download-item -->

				<div class="shipper-download-item installer">
					<div class="shipper-download-icon">
						<img src="<?php echo esc_url( $assets->get_asset( 'img/icon-php.svg' ) ); ?>"/>
					</div>
					<div class="shipper-download-meta">
						<b><?php esc_html_e( 'Installer', 'shipper' ); ?></b>
						<span><?php echo esc_html( $model->get_installer_size() ); ?></span>
					</div><!-- shipper-download-meta -->
					<div class="shipper-download-action">
						<a href="#download" class="sui-tooltip" data-tooltip="Download">
							<i class="sui-icon-download" aria-hidden="true"></i>
						</a>
					</div>
				</div><!-- shipper-download-item -->
			</div><!-- shipper-download -->

			<div class="sui-description shipper-package-note">
				<span class="sui-icon-info sui-sm" aria-hidden="true"></span>
				<?php esc_html_e( 'Note: For a successful migration, do not rename the files after download.', 'shipper' ); ?>
			</div>

		</div><!-- shipper-instruction-body -->
	</div><!-- shipper-instruction -->

	<div class="shipper-instruction">
		<div class="shipper-instruction-title">
			<span class="shipper-instruction-icon">
				<i class="sui-icon-upload-cloud" aria-hidden="true"></i>
			</span>
			<span class="shipper-instruction-order">2</span>
			<span class="shipper-instruction-brief">
				<?php esc_html_e( 'Upload both files to your destination server', 'shipper' ); ?>
			</span><!-- shipper-instruction-brief -->
		</div><!-- shipper-instrction-title -->

		<div class="shipper-instruction-body">
			<p class="sui-p-small filezilla">
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: filezilla web address */
						__( 'The next step is to upload both the package archive and installer files to the root directory of your destination server. We recommend uploading them via FTP using an FTP client such as <a href="%s" target="_blank">FileZilla</a>. You’ll need an FTP account to connect to your destination server, and if you are not sure how to create an FTP account, please contact your hosting support.', 'shipper' ),
						'https://filezilla-project.org/'
					)
				);
				?>
			</p>

			<div class="sui-description shipper-package-note">
				<span class="sui-icon-info sui-sm" aria-hidden="true"></span>
				<?php esc_html_e( 'Note: For a successful migration, do not try to unzip and compress the files again. Please make sure you upload the ZIP files exactly as they are from when you download them.', 'shipper' ); ?>
			</div>
		</div><!-- shipper-instruction-body -->
	</div><!-- shipper-instruction -->

	<div class="shipper-instruction">
		<div class="shipper-instruction-title">
			<span class="shipper-instruction-icon">
				<i class="sui-icon-open-new-window" aria-hidden="true"></i>
			</span>
			<span class="shipper-instruction-order">3</span>
			<span class="shipper-instruction-brief">
				<?php esc_html_e( 'Visit installer.php on your destination server and follow the instructions', 'shipper' ); ?>
			</span><!-- shipper-instruction-brief -->
		</div><!-- shipper-instrction-title -->

		<div class="shipper-instruction-body">
			<p class="sui-p-small">
				<?php
				echo wp_kses_post(
					__( 'Once you\'ve uploaded both the archive and installer files to your new server, you need to visit the installer.php in your browser. To do this, open up your web browser and type in your new website domain along with /installer.php. I.e. <b>https://example.com/installer.php</b>. Follow the instructions on the installer wizard to complete the migration.', 'shipper' )
				);
				?>
			</p>
		</div><!-- shipper-instruction-body -->
	</div><!-- shipper-instruction -->

</div><!-- shipper-instructions -->