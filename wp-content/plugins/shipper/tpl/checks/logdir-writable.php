<?php
/**
 * Shipper checks body copy templates: log directory not writable
 *
 * @since v1.0.3
 * @package shipper
 */

?>
<div>
	<h4><?php esc_html_e( 'Overview', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'During migrations, Shipper writes logs, process locks, and other useful commands as it progresses. The log directory must be writable for Shipper to operate.', 'shipper' );
		?>
	</p>

	<h4><?php esc_html_e( 'Status', 'shipper' ); ?></h4>
	<div class="sui-notice sui-notice-error">
		<p>
			<?php
				echo wp_kses_post( sprintf(
					__( 'Incorrect permissions for the log directory on <b>%s</b>.', 'shipper' ),
					$domain
				) );
			?>
		</p>
	</div>

	<h4><?php esc_html_e( 'How To Fix', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'Directories have permissions that specify who and what can read, write, modify, and access them. You need to provide proper permissions to the log directory for Shipper to operate correctly. ', 'shipper' );
		?>
	</p>
    <p></p>
	<p>
		<?php
			echo wp_kses_post( sprintf( __( 'Your log directory is located at <code>%s</code>. Please make sure that log directory has write permissions for PHP owner so Shipper scripts could write in this directory. If you are not sure how to check the permissions, contact your hosting support or your system admin to fix the permissions for you.'  , 'shipper' ), $value ) );
		?>
	</p>
</div>
<div class="sui-notice-top sui-notice-error sui-can-dismiss shipper-recheck-unsuccessful" style="display:none">
	<div class="sui-notice-content">
		<p>
			<?php echo wp_kses_post( sprintf(
				__( 'Log directory is not writable on %1$s. Please fix this and check again.', 'shipper' ),
				$domain
			) ); ?>
		</p>
	</div>
	<span class="sui-notice-dismiss">
		<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
	</span>
</div>