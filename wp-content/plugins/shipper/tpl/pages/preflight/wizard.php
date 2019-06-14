<?php
/**
 * Shipper migrate page templates: migration preflight wizard
 *
 * @package shipper
 */

?>

<div class="shipper-migration-content shipper-migration-progress-content shipper-preflight-wizard">
	<div class="shipper-page-header">
		<h2>
		<?php esc_html_e( 'Pre-flight Check', 'shipper' ); ?>
		</h2>
	</div>

	<?php
		$destinations = new Shipper_Model_Stored_Destinations;
		$this->render('pages/migration/sourcedest-tag', array(
			'destinations' => $destinations,
			'site' => $site,
		));
	?>

	<div class="sui-tabs sui-tabs-flushed shipper-preflight-results">
		<div data-tabs="">
			<div class="shipper-preflight-done-tab" id="shipper-tab-source">
				<i class="sui-icon-storage-server-data" aria-hidden="true"></i>
				<span><?php esc_html_e( 'Source', 'shipper' ); ?></span>
				<span class="shipper-check-status">
				<?php
					$checks = $result['checks']['local'];
					$icon_type = empty( $checks['errors_count'] )
						? 'check-tick'
						: 'warning-alert'
					;
					$icon_kind = 'warning-alert' === $icon_type
						? ( empty( $checks['breaking_errors_count'] ) ? 'warning' : 'error' )
						: 'success'
					;
				?>
					<i aria-hidden="true"
						class="sui-icon-<?php
							echo $icon_type;
						?> sui-<?php echo $icon_kind; ?>"></i>
				</span>
			</div>
			<div class="shipper-preflight-done-tab" id="shipper-tab-destination">
				<i class="sui-icon-upload-cloud" aria-hidden="true"></i>
				<span><?php esc_html_e( 'Destination', 'shipper' ); ?></span>
				<span class="shipper-check-status">
				<?php
					$checks = $result['checks']['remote'];
					$icon_type = empty( $checks['errors_count'] )
						? 'check-tick'
						: 'warning-alert'
					;
					$icon_kind = 'warning-alert' === $icon_type
						? ( empty( $checks['breaking_errors_count'] ) ? 'warning' : 'error' )
						: 'success'
					;
				?>
					<i aria-hidden="true"
						class="sui-icon-<?php
							echo $icon_type;
						?> sui-<?php echo $icon_kind; ?>"></i>
				</span>
			</div>
			<div class="shipper-preflight-done-tab" id="shipper-tab-files">
				<i class="sui-icon-clipboard-notes" aria-hidden="true"></i>
				<span><?php esc_html_e( 'Files', 'shipper' ); ?></span>
				<span class="shipper-check-status">
				<?php
					$checks = $result['checks']['files'];
					$icon_type = empty( $checks['errors_count'] )
						? 'check-tick'
						: 'warning-alert'
					;
					$icon_kind = 'warning-alert' === $icon_type
						? ( empty( $checks['breaking_errors_count'] ) ? 'warning' : 'error' )
						: 'success'
					;
				?>
					<i aria-hidden="true"
						class="sui-icon-<?php
							echo $icon_type;
						?> sui-<?php echo $icon_kind; ?>"></i>
				</span>
			</div>
			<div  class="shipper-preflight-done-tab active" id="shipper-tab-overall">
				<i class="sui-icon-shipper-anchor" aria-hidden="true"></i>
				<span><?php esc_html_e( 'Ready to sail', 'shipper' ); ?></span>
				<span class="shipper-check-status">
				<?php
					$icon_type = empty( $has_issues )
						? 'check-tick'
						: 'warning-alert'
					;
					$icon_kind = 'warning-alert' === $icon_type
						? ( empty( $has_errors ) ? 'warning' : 'error' )
						: 'success'
					;
				?>
					<i aria-hidden="true"
						class="sui-icon-<?php
							echo $icon_type;
						?> sui-<?php echo $icon_kind; ?>"></i>
				</span>
			</div>
		</div>
		<div data-panes="">
			<div>
				<?php $this->render( 'pages/preflight/wizard-source', array(
					'result' => $result,
					'has_issues' => $has_issues,
					'has_errors' => $has_errors,
					'issues_count' => $issues_count,
					'shipper_url' => $shipper_url,
				) ); ?>
			</div>
			<div>
				<?php $this->render( 'pages/preflight/wizard-destination', array(
					'result' => $result,
					'has_issues' => $has_issues,
					'has_errors' => $has_errors,
					'issues_count' => $issues_count,
					'shipper_url' => $shipper_url,
				) ); ?>
			</div>
			<div>
				<?php $this->render( 'pages/preflight/wizard-files', array(
					'result' => $result,
					'has_issues' => $has_issues,
					'has_errors' => $has_errors,
					'issues_count' => $issues_count,
					'shipper_url' => $shipper_url,
				) ); ?>
			</div>
			<div class="active">
				<?php $this->render( 'pages/preflight/wizard-ready', array(
					'result' => $result,
					'has_issues' => $has_issues,
					'has_errors' => $has_errors,
					'issues_count' => $issues_count,
					'shipper_url' => $shipper_url,
				) ); ?>
			</div>
		</div>
	</div>
</div> <?php // .shipper-migration-progress-content ?>
