<?php
/**
 * Shipper preflight templates: done section of the wizard
 *
 * @package shipper
 */
?>

<div class="shipper-wizard-tab">
<?php // Body copy area ?>
<?php if ( $has_issues ) { ?>
	<?php if ( $has_errors ) { ?>
		<div class="sui-notice sui-notice-error">
			<p><?php esc_html_e( 'You have a few errors, please check the sections above for more info.', 'shipper' ); ?></p>
		</div>
	<?php } else { // has errors ?>
		<div class="sui-notice sui-notice-warning">
			<p><?php esc_html_e( 'You have a few warnings, please check the sections above for more info.', 'shipper' ); ?></p>
		</div>
		<p>
			<?php esc_html_e( 'Don\'t worry!', 'shipper' ); ?>
			<?php esc_html_e( 'You can try to resolve these warnings or begin the migration right away ignoring these warnings.', 'shipper' ); ?>
			<?php esc_html_e( 'Note that Shipper overwrites any existing files or database tables on your destination website.', 'shipper' ); ?>
			<?php esc_html_e( 'Please make sure you have a backup.', 'shipper' ); ?>
		</p>
	<?php } // has errors ?>
<?php } else { // has issues ?>
		<p>
			<?php esc_html_e( 'You\'re ready to go!', 'shipper' ); ?>
			<?php esc_html_e( 'Note that Shipper overwrites any existing files or database tables on your destination website.', 'shipper' ); ?>
			<?php esc_html_e( 'Please make sure you have a backup.', 'shipper' ); ?>
		</p>
<?php } // has issues ?>

	<?php // Actions area ?>
	<div>
		<?php if ( $has_issues ) { ?>
			<a href="#reload" class="sui-button sui-button-ghost">
				<i class="sui-icon-update" aria-hidden="true"></i>
				<span><?php esc_html_e( 'Re-check', 'shipper' ); ?></span>
			</a>
		<?php } // has issues ?>

		<?php if ( ! $has_errors) { ?>
			<a href="<?php echo esc_url( add_query_arg( 'check', 'done' ) ); ?>" class="sui-button sui-button-primary">
				<?php esc_attr_e( 'Begin migration', 'shipper' ); ?>
			</a>
		<?php } // does not have errors ?>
	</div>
</div>
