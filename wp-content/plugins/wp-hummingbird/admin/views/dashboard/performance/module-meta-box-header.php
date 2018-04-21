<?php
/**
 * Performance meta box header on dashboard page.
 *
 * @package Hummingbird
 *
 * @var object        $last_report    Performance report object.
 * @var string        $title          Performance module title.
 * @var string        $scan_link      Link to run new performance scan.
 * @var bool|integer  $can_run_scan   True if a new test is available or the time in minutes remaining for next test.
 */

?>
<h3 class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<div class="sui-actions-right">
	<?php if ( true === $can_run_scan ) : ?>
		<a href="<?php echo esc_url( $scan_link ); ?>" class="sui-button sui-button-primary"><?php esc_html_e( 'Run Test', 'wphb' ); ?></a>
	<?php
	else :
		/* translators: %d: number of minutes. */
		$tooltip = sprintf( __( 'Hummingbird is just catching her breath - you can run another test in %d minutes', 'wphb' ), esc_attr( $can_run_scan ) );
		?>
		<a href="#" class="sui-button sui-button-primary sui-tooltip sui-tooltip-constrained" disabled="disabled" data-tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true"><?php esc_html_e( 'Run Test', 'wphb' ); ?></a>
	<?php endif; ?>
</div>