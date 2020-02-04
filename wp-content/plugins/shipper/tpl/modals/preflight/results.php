<?php
/**
 * Shipper modals: preflight results hub template
 *
 * @since v1.0.3
 * @package shipper
 */

$ctrl = Shipper_Controller_Runner_Preflight::get();
$result = $ctrl->get_proxied_results();
$migration = new Shipper_Model_Stored_Migration;
?>

<div class="sui-box-header sui-block-content-center">
	<div class="sui-actions-left">
		<a href="#reload" class="shipper-reset-preflight">
			<i class="sui-icon-update" aria-hidden="true"></i>
			<span><?php esc_html_e( 'Reset preflight', 'shipper' ); ?></span>
		</a>
	</div>
	<h3 class="sui-box-title">
		<?php esc_html_e( 'Pre-flight Issues', 'shipper' ); ?>
	</h3>
	<div class="sui-actions-right">
		<button data-a11y-dialog-hide="<?php echo esc_attr( $modal_id ); ?>" class="sui-dialog-close" aria-label="<?php echo esc_attr( 'Close this dialog window', 'shipper' ); ?>"></button>
	</div>
</div>

<div class="sui-box-body sui-box-body-slim sui-block-content-center">
<?php if ( ! empty( $result['errors'] ) || ! empty( $result['warnings'] ) ) { ?>
	<p class="shipper-issues-intro">
		<?php echo esc_html( sprintf(
			__( '%s, we’ve uncovered a few potential issues that may affect this migration.', 'shipper' ),
			shipper_get_user_name()
		) ); ?>
		<?php esc_html_e( 'Take a look through them and action what you like.', 'shipper' ); ?>
		<?php esc_html_e( 'While you can ignore the warnings, you must fix the errors (if any) to continue your migration.', 'shipper' ); ?>
	</p>
<?php } ?>
</div>


<div class="sui-box shipper-check-result" id="shipper-preflight-check">

	<div data-migration_type="<?php echo esc_attr( $migration->get_type() ); ?>"
		class="sui-accordion sui-accordion-flushed shipper-result-sections">

		<div class="sui-accordion-item" data-section="local">
			<div class="sui-accordion-item-header">
				<div class="sui-accordion-item-title">
					<?php echo esc_html( $migration->get_source() ); ?>
					<?php
						$this->render(
							'tags/status-text-preflight-check',
							array( 'items' => $result['checks']['local']['checks'] )
						);
					?>
				</div>
				<div>
					<button class="sui-button-icon sui-accordion-open-indicator">
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
					</button>
				</div>
			</div>
			<div class="sui-accordion-item-body">
				<?php
					$this->render(
						'modals/preflight/checks-local',
						array( 'result' => $result, 'is_recheck' => ! empty( $is_recheck ) )
					);
				?>
			</div>
		</div>

		<div class="sui-accordion-item" data-section="remote">
			<div class="sui-accordion-item-header">
				<div class="sui-accordion-item-title">
					<?php echo esc_html( $migration->get_destination() ); ?>
					<?php
						$this->render(
							'tags/status-text-preflight-check',
							array( 'items' => $result['checks']['remote']['checks'] )
						);
					?>
				</div>
				<div>
					<button class="sui-button-icon sui-accordion-open-indicator">
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
					</button>
				</div>
			</div>
			<div class="sui-accordion-item-body">
				<?php
					$this->render(
						'modals/preflight/checks-remote',
						array( 'result' => $result, 'is_recheck' => ! empty( $is_recheck ) )
					);
				?>
			</div>
		</div>

		<div class="sui-accordion-item" data-section="sysdiff">
			<div class="sui-accordion-item-header">
				<div class="sui-accordion-item-title">
					<?php esc_html_e( 'Server Differences', 'shipper' ); ?>
					<?php
						$this->render(
							'tags/status-text-preflight-check',
							array( 'items' => $result['checks']['sysdiff']['checks'] )
						);
					?>
				</div>
				<div>
					<?php $this->render( 'tags/domains-tag' ); ?>
					<button class="sui-button-icon sui-accordion-open-indicator">
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
					</button>
				</div>
			</div>
			<div class="sui-accordion-item-body">
				<?php
					$this->render(
						'modals/preflight/checks-sysdiff',
						array( 'result' => $result, 'is_recheck' => ! empty( $is_recheck ) )
					);
				?>
			</div>
		</div>

	</div> <?php //.sui-accordion ?>

<div class="sui-box-body sui-box-body-slim sui-block-content-center">
	<p>
		<button class="sui-button shipper-reset-preflight">
			<i class="sui-icon-update" aria-hidden="true"></i>
			<?php esc_html_e( 'Run pre-flight', 'shipper' ); ?>
		</button>
		<a href="#start"
			data-start="<?php echo esc_url( add_query_arg( 'check', 'done' ) ); ?>"
			data-tooltip="<?php echo esc_attr_e( 'You must fix the pre-flight errors to continue your migration.', 'shipper' ); ?>"
			class="sui-button sui-button-ghost shipper-migration-start sui-tooltip">
			<i class="sui-icon-arrow-right" aria-hidden="true"></i>
			<?php esc_html_e( 'Continue anyway', 'shipper' ); ?>
		</a>
	</p>
</div>
</div>