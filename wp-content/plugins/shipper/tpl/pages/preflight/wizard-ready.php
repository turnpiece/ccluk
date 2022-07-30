<?php
/**
 * Shipper preflight templates: done section of the wizard
 *
 * @package shipper
 */

?>

<div class="shipper-wizard-tab">
<?php // Body copy area. ?>
	<?php
	$this->render(
		'msgs/wizard-ready-main-notice',
		array(
			'has_issues' => $has_issues,
			'has_errors' => $has_errors,
			'result'     => $result,
		)
	);
	?>
	<?php // Actions area. ?>
	<div>
		<?php if ( $has_issues ) { ?>
			<a href="#reload" class="sui-button sui-button-ghost">
				<i class="sui-icon-update" aria-hidden="true"></i>
				<span><?php esc_html_e( 'Re-check', 'shipper' ); ?></span>
			</a>
		<?php } // has issues. ?>

		<?php if ( ! $has_errors ) { ?>
			<a href="<?php echo esc_url( add_query_arg( 'check', 'done' ) ); ?>" class="sui-button sui-button-primary">
				<?php esc_attr_e( 'Begin migration', 'shipper' ); ?>
			</a>
		<?php } // does not have errors. ?>
	</div>
</div>
