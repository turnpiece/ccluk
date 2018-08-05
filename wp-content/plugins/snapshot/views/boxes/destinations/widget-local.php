<?php $destinations = array();

foreach ( WPMUDEVSnapshot::instance()->config_data['destinations'] as $key => $item ){
	$type = $item['type'];

	if ( ! isset( $destinations[ $type ] ) ){
		$destinations[ $type ] = array();
	}

	$destinations[ $type ][ $key ] = $item;
} ?>

<section class="wpmud-box wpsd-widget-local">

	<div class="wpmud-box-title has-typecon has-button">

		<i class="wps-typecon local"></i>

		<h3><?php esc_html_e( 'Local', SNAPSHOT_I18N_DOMAIN ); ?></h3>

	</div>

	<div class="wpmud-box-content">

		<div class="row">

			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

				<?php
                $this->render(
					"destinations/partials/local-destination-list", false, array(
						'item' => $item,
						'destinations' => ( isset( $destinations[ 'local' ] ) ? $destinations[ 'local' ] : array() )
					), false, false
				);
				?>

			</div>

		</div>

	</div>

</section>