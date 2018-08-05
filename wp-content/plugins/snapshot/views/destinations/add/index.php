<?php

$item = 0;

if ( ! isset( $_REQUEST['destination-noonce-field']  ) ) {
	return;
}

if ( ! wp_verify_nonce( $_REQUEST['destination-noonce-field'], 'snapshot-destination' ) ) {
	return;
}

if ( isset( $_REQUEST['snapshot-action'] ) ) {

	if ( sanitize_text_field( $_REQUEST['snapshot-action'] ) === 'edit' ) {
		if ( isset( $_REQUEST['item'] ) ) {
			$item_key = sanitize_text_field( $_REQUEST['item'] );
			if ( isset( WPMUDEVSnapshot::instance()->config_data['destinations'][ $item_key ] ) ) {
				$item = WPMUDEVSnapshot::instance()->config_data['destinations'][ $item_key ];
			}
		}
	} elseif ( sanitize_text_field( $_REQUEST['snapshot-action'] ) === 'add' ) {
		unset( $item );
		$item = array();

		if ( isset( $_POST['snapshot-destination'] ) ) {
			$item = $_POST['snapshot-destination'];
		}

		if ( isset( $_REQUEST['type'] ) ) {
			$item['type'] = sanitize_text_field( $_REQUEST['type'] );
		}
	} elseif ( sanitize_text_field( $_REQUEST['snapshot-action'] ) === 'update' ) {
		if ( isset( $_POST['snapshot-destination'] ) ) {
			$item = $_POST['snapshot-destination'];
		}
	}
}

/* Build the URL to submit the form to */
$form_url = add_query_arg(
	array(
		'snapshot-action' => sanitize_text_field( $_GET['snapshot-action'] ),
		'type'            => rawurlencode( $item['type'] )
	), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-destinations' )
);

if ( 'edit' === $_GET['snapshot-action'] || 'update' === $_GET['snapshot-action'] ) {
	$form_url = add_query_arg( 'item', sanitize_text_field( $_GET['item'] ), $form_url );
}

if ( 'dropbox' === $item['type'] && isset( $_GET['dropbox-authenticated'] ) ) {
	$form_url = add_query_arg( 'dropbox-authenticated', $_GET['dropbox-authenticated'], $form_url );
}

?>

<section id="header">
    <h1><?php esc_html_e( 'Destinations', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-destinations">

    <section id="wps-destinations-wizard" class="wpmud-box">

        <div class="wpmud-box-title has-button">

            <h3><?php esc_html_e( 'Destination Info', SNAPSHOT_I18N_DOMAIN ); ?></h3>

            <a class="button button-small button-outline button-gray"
               href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-destinations' ) ); ?>">
                <?php esc_html_e( 'Back', SNAPSHOT_I18N_DOMAIN ); ?></a>

        </div>

        <div class="wpmud-box-content">

            <div class="row">

                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

					<?php
					if ( $item ) :

						$type = 'local';

						if ( isset( $_REQUEST['snapshot-action'] ) && isset( $_REQUEST['type'] ) ) {
							$type = sanitize_text_field( $_REQUEST['type'] );
						}

						if ( ! in_array( $type, array( 'aws', 'dropbox', 'google-drive', 'ftp' ), true ) ) {
							$type = 'local';
						}
						$target = 'target="_self"';
						if ( 'dropbox' === $type ) {
							if ( ! isset( $item['tokens']['access']['authorization_token'], $item['tokens']['access']['access_token'] )
                                 || empty( $item['tokens']['access']['authorization_token'] ) ) {
								$target = 'target="_blank"';
							}
						}
						?>
                        <form action="<?php echo esc_url( $form_url ); ?>" method="post" <?php echo wp_kses_post( $target ); ?>>

							<?php
							$snapshot_action = sanitize_text_field( $_GET['snapshot-action'] );
							wp_nonce_field( 'snapshot-destination', 'destination-noonce-field' );

							if ( ( 'edit' === $snapshot_action ) || ( 'update' === $snapshot_action ) ) :
								?>

                                <input type="hidden" name="snapshot-action" value="update"/>

                                <input type="hidden" name="item"
                                       value="<?php echo esc_attr( sanitize_text_field( $_GET['item'] ) ); ?>"/>

								<?php wp_nonce_field( 'snapshot-update-destination', 'snapshot-noonce-field' ); ?>

							<?php elseif ( 'add' === $snapshot_action ) : ?>

                                <input type="hidden" name="snapshot-action" value="add"/>

								<?php wp_nonce_field( 'snapshot-add-destination', 'snapshot-noonce-field' ); ?>

							<?php endif; ?>

							<?php

							if ( 'local' === $type ) {
								$item_object   = new stdClass();
								$backup_folder = WPMUDEVSnapshot::instance()->config_data['config']['backupFolder'];
								$backup_folder = isset( $backup_folder ) ? $backup_folder : 'snapshots';

								$item_object->backup_folder = $backup_folder;

							} else {
								$item_object = Snapshot_Model_Destination::get_object_from_type( $type );
								if( null !== $item_object ){
									$item_object->init();
								}
							}

							if ( strpos( $type, 'dropbox' ) !== false && version_compare( phpversion(), '5.5.0', '<' ) ) {
								$this->render(
                                     "destinations/add/dropbox-error", false, array(
									'item'        => $item,
								), false, false, false
                                    );
							} else {
								$this->render(
                                     "destinations/add/$type", false, array(
									'item'        => $item,
									'item_object' => $item_object,
								), false, false, false
                                    );
							}

							?>
							<?php if ( strpos( $type, 'dropbox' ) !== false && version_compare( phpversion(), '5.5.0', '<' ) ) : ?>
								<div class="form-footer"></div>
							<?php else : ?>
                            <div class="form-footer">

								<?php
								if ( ( 'edit' === $snapshot_action || 'update' === $snapshot_action ) && 'local' !== $item['type'] ) :
									?>

                                    <div class="form-col"><a id="wps-destination-delete"
                                                             class="button button-outline button-gray"
                                                             href="#"><?php esc_html_e( 'Delete', SNAPSHOT_I18N_DOMAIN ); ?></a>
                                    </div>

									<?php
									$popup_warning_data = array(
										'popup_id'           => 'wps-destination-warning',
										'popup_title'        => __( 'Remove Destination', SNAPSHOT_I18N_DOMAIN ),
										'popup_content'      => sprintf( __( "You are deleting the destination <strong>%1\$s</strong>. This destination has %2\$d archives in it, are you sure you wish to remove it? You will still be able to access any archives at the destinations using the %3\$s interface.", SNAPSHOT_I18N_DOMAIN ), $item['name'], Snapshot_Model_Destination::get_destination_item_count( sanitize_text_field( $_GET['item'] ) ), Snapshot_Model_Destination::get_destination_nice_name( sanitize_text_field( $item['type'] ) ) ),
										'popup_action_title' => __( 'Remove', SNAPSHOT_I18N_DOMAIN ),
										'popup_action_url'   => add_query_arg(
                                             array(
											'snapshot-action' => 'delete',
											'item'                  => sanitize_text_field( $_GET['item'] ),
											'destination-noonce-field' => wp_create_nonce( 'snapshot-destination' ),
										), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-destinations' )
                                            ),
										'popup_cancel_title' => __( 'Cancel', SNAPSHOT_I18N_DOMAIN ),
										'popup_cancel_url'   => '#',
									);

									$this->render( 'boxes/modals/popup-warning', false, $popup_warning_data, false, false );
									?>

								<?php elseif ( "add" === $snapshot_action ) : ?>

                                    <div class="form-col">

                                        <a class="button button-outline button-gray"
                                           href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-destinations' ) ); ?>"><?php esc_html_e( 'Cancel', SNAPSHOT_I18N_DOMAIN ); ?></a>

                                    </div>

								<?php endif; ?>

                                <div class="form-col
                                <?php
                                if ( 'local' === $type ) {
									echo ' form-col-right';
								}
                                ?>
                                ">

									<?php
									if ( 'dropbox' === $type ) {
										?>
                                        <input class="button button-blue" type="submit"
                                               data-get-code-text="<?php esc_html_e( 'Get authorization code', SNAPSHOT_I18N_DOMAIN ); ?>"
                                               data-save-text="<?php esc_html_e( 'Save Destination', SNAPSHOT_I18N_DOMAIN ); ?>"
                                               data-authenticate-text="<?php esc_html_e( 'Authenticate', SNAPSHOT_I18N_DOMAIN ); ?>"
                                               value="<?php esc_html_e( 'Get authorization code', SNAPSHOT_I18N_DOMAIN ); ?>"/>
                                        <?php wp_nonce_field( 'add_dropbox_destination' ); ?>
									<?php } else { ?>
                                        <input class="button button-blue" type="submit" value="<?php esc_html_e( 'Save Destination', SNAPSHOT_I18N_DOMAIN ); ?>"/>
									<?php } ?>

                                </div>

                            </div>
							<?php endif; ?>

                        </form>

					<?php endif; ?>

                </div>

            </div>

        </div>

    </section>

</div>