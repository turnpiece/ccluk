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
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: website name.*/
							__( 'Incorrect permissions for the working directory on <b>%s</b>.', 'shipper' ),
							$domain
						)
					);
					?>
				</p>
			</div>
		</div>
	</div>

	<h4><?php esc_html_e( 'How To Fix', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'Directories have permissions that specify who and what can read, write, modify, and access them. You need to provide proper permissions to the working directory for Shipper to operate correctly. ', 'shipper' );
		?>
	</p>
	<p></p>
	<p>
		<?php
		/* translators: %s: directory path.*/
		echo wp_kses_post( sprintf( __( 'Your working directory is located at <code>%s</code>. Please make sure that working directory has write permissions for PHP owner so Shipper scripts could write in this directory. If you are not sure how to check the permissions, contact your hosting support or your system admin to fix the permissions for you.', 'shipper' ), $value ) );
		?>
	</p>
</div>
<div class="sui-notice-top sui-notice-error sui-can-dismiss shipper-recheck-unsuccessful" style="display:none">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: website name.*/
						__( 'Working directory not writable on %1$s. Please fix this and check again.', 'shipper' ),
						$domain
					)
				);
				?>
			</p>
		</div>
	</div>
	<span class="sui-notice-dismiss">
		<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
	</span>
</div>