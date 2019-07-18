<?php
/**
 * Shipper checks body copy templates: working directory not writable
 *
 * @since v1.0.3
 * @package shipper
 */

?>
<div>
	<h4><?php esc_html_e( 'Overview', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'During migrations, Shipper needs somewhere to write temporary files to keep track of progress. Usually, it uses a  temporary directory as the working directory, but it needs to have the correct permissions for that to work.', 'shipper' );
		?>
	</p>

	<h4><?php esc_html_e( 'Status', 'shipper' ); ?></h4>
	<div class="sui-notice sui-notice-error">
		<p>
			<?php
				echo wp_kses_post( sprintf(
					__( 'Incorrect permissions for the working directory on <b>%s</b>.', 'shipper' ),
					$domain
				) );
			?>
		</p>
	</div>

	<h4><?php esc_html_e( 'How To Fix', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'Directories have permissions that specify who and what can read, write, modify, and access them. You need to provide proper permissions to the working directory for Shipper to operate correctly. ', 'shipper' );
		?>
	</p>
	<p>
		<?php
			echo wp_kses_post( sprintf( __( 'Your working directory is located at <code>%s</code>. You can change permissions of your working directory via either FTP or SSH. Follow the steps below to add the proper permissions:'  , 'shipper' ), $value ) );
		?>
	</p>
	<div class="sui-side-tabs sui-tabs">
		<div data-tabs>
			<div class="active"><?php esc_html_e( 'FTP', 'shipper' ); ?></div>
			<div><?php esc_html_e( 'SSH', 'shipper' ); ?></div>
		</div>
		<div data-panes>
			<div class="sui-tab-boxed active">
				<p>
					<?php esc_html_e( 'Follow the instructions below to change permissions of your working directory.', 'shipper' ); ?>
				</p>
				<h5><?php esc_html_e( 'Instructions', 'shipper' ); ?></h5>
				<p>
					<?php echo wp_kses_post( sprintf( __( '1. Connect to your site via FTP, and go to your working directory at <code>%s</code>', 'shipper' ), $value ) ); ?><br />
					<?php esc_html_e( '2. Right click on your working directory, and click on the “File permissions” options.', 'shipper' ); ?><br />
					<?php esc_html_e( '3. Shipper needs 766 permissions to write to this directory, so enter 766 into the numeric value field.', 'shipper' ); ?><br />
					<?php esc_html_e( '4. Enable the “Recurse into subdirectories” option, and click OK to activate the permissions.', 'shipper' ); ?>
				</p>
			</div>
			<div class="sui-tab-boxed">
				<p>
					<?php esc_html_e( 'If you have SSH access to your hosting account, you can use chmod to change file permissions, which is the preferred method for experienced users only. Run the following command to change the permissions of your working directory:', 'shipper' ); ?>
				</p>
				<pre class="sui-code-snipper"><?php echo esc_html( sprintf( 'chmod -R 766 %s', $value ) ); ?></pre>
			</div>
		</div>
	</div><!-- .sui-tabs -->
</div>
<div class="sui-notice-top sui-notice-error sui-can-dismiss shipper-recheck-unsuccessful" style="display:none">
	<div class="sui-notice-content">
		<p>
			<?php echo wp_kses_post( sprintf(
				__( 'Working directory not writable on %1$s. Please fix this and check again.', 'shipper' ),
				$domain
			) ); ?>
		</p>
	</div>
	<span class="sui-notice-dismiss">
		<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
	</span>
</div>
