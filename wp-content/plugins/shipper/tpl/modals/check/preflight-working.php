<?php
/**
 * Shipper modals: preflight check *working* modal template
 *
 * @package shipper
 */

$shipper_url = remove_query_arg( array( 'type', 'site' ) );
?>
<div class="sui-box shipper-working" id="shipper-preflight-check">
	<div class="sui-box-body">

		<?php
		$this->render(
			'pages/preflight/progress-bar',
			array(
				'progress'     => 0,
				'site'         => $site,
				'destinations' => $destinations,
			)
		);
		?>

	<?php $this->render( 'modals/migration-cancel' ); ?>

	</div>
</div>
<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>