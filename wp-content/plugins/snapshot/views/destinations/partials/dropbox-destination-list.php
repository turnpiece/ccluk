
<?php if ( empty( $destinations ) ) : ?>

    <div class="wps-notice">
        <p><?php esc_html_e( "You haven't added a Dropbox destination yet.", SNAPSHOT_I18N_DOMAIN ); ?></p>
    </div>

<?php else : ?>

    <table cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th class="wps-destination-name"><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?></th>
            <th class="wps-destination-user"><?php esc_html_e( 'User', SNAPSHOT_I18N_DOMAIN ); ?></th>
            <th class="wps-destination-dir"><?php esc_html_e( 'Directory', SNAPSHOT_I18N_DOMAIN ); ?></th>
            <th class="wps-destination-shots"><?php esc_html_e( 'Snapshots', SNAPSHOT_I18N_DOMAIN ); ?></th>
            <th class="wps-destination-config"></th>
        </tr>
        </thead>
        <tbody>

        <?php foreach ( $destinations as $id => $destination ) : ?>

            <?php
            $required_fields = array(
                'name',
                'directory',
                'tokens' => array( 'access' => array( 'access_token' ) )
            );
            ?>

            <tr>
                <td class="wps-destination-name">
                    <?php
                    $destination_link = add_query_arg(
                            array(
                                'snapshot-action' => 'edit',
                                'type'            => $destination['type'],
                                'item'            => $id,
                                'destination-noonce-field' => wp_create_nonce( 'snapshot-destination' ),
                            ),
                            WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-destinations' )
                        );
                    ?>
                    <a href="<?php echo esc_url( $destination_link ); ?>"><?php echo esc_html( $destination['name'] ); ?></a>

                    <?php if ( ! Snapshot_Model_Destination::has_required_fields( $destination, $required_fields ) ) : ?>
                        <span class="incomplete-warning" title="<?php esc_html_e( 'This destination has not been fully configured.', SNAPSHOT_I18N_DOMAIN ); ?>"></span>
                    <?php endif; ?>

                </td>

                <td class="wps-destination-user"
                    data-text="User:"><?php echo isset( $destination['account_info']['email'] ) ? esc_html( $destination['account_info']['email'] ) : '-'; ?></td>
                <td class="wps-destination-dir" data-text="Dir:"><?php echo wp_kses_post( $destination['directory'] ); ?></td>
                <td class="wps-destination-shots"><?php Snapshot_Model_Destination::show_destination_item_count( $id ); ?></td>
                <td class="wps-destination-config">
                    <?php
                    $destination_config = add_query_arg(
                            array(
                                'snapshot-action' => 'edit',
                                'type'            => $destination['type'],
                                'item'            => $id,
                                'destination-noonce-field' => wp_create_nonce( 'snapshot-destination' ),
                            ),
                            WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-destinations' )
                        );
                    ?>
                    <a class="button button-small button-outline button-gray" href="<?php echo esc_url( $destination_config ); ?>">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <span class="wps-destination-config-text"><?php esc_html_e( 'Configure', SNAPSHOT_I18N_DOMAIN ); ?></span>
                    </a>
                </td>
            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

<?php endif; ?>