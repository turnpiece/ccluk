<?php
/**
 * Shipper modals: preflight check *working* modal template
 *
 * @package shipper
 */

$ctrl   = Shipper_Controller_Runner_Preflight::get();
$result = $ctrl->get_proxied_results();

$has_issues          = $ctrl->has_issues();
$has_breaking_issues = ! empty( $result['errors'] );
$issues_count        = $has_issues
	? (int) $result['errors'] + (int) $result['warnings']
	: 0;
$shipper_url         = remove_query_arg( array( 'type', 'site' ) );
?>
<div class="sui-box shipper-check-result" id="shipper-preflight-check">
	<div class="sui-box-body">

		<?php
		$this->render(
			'pages/preflight/wizard',
			array(
				'result'       => $result,
				'has_issues'   => $has_issues,
				'has_errors'   => $has_breaking_issues,
				'issues_count' => $issues_count,
				'shipper_url'  => $shipper_url,
				'site'         => $site,
			)
		);
		?>

	</div>
</div>
<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>