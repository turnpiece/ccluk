<?php
/**
 * @var Hustle_Email_Services $services
 */
?>
<div class="box-title">

    <h3><?php _e('Email Lists & Services', Opt_In::TEXT_DOMAIN); ?></h3>

</div>

<div class="box-content">

    <table class="wph-table wph-settings--email">

        <thead>

        <tr>

            <th colspan="2"><?php _e('Name', Opt_In::TEXT_DOMAIN); ?></th>

            <th>&nbsp;</th>

        </tr>

        </thead>

        <tbody>

        <tr class="wph-settings-message <?php echo $services->get_count() ? 'hidden' : '';  ?>" >

            <td colspan="3">

                <p><?php _e('You haven\'t added any Email Service yet, what you\'re waiting for? Remember we support Mailchimp, Aweber, Campaign Monitor and many other services.', Opt_In::TEXT_DOMAIN); ?></p>

            </td>

        </tr>
        <?php foreach( $services->get_all() as $id => $service ): $service = (object) $service; ?>
            <tr>

                <td class="wph-list--icon">

                    <div class="wph-list--<?php echo esc_attr( $service->name ) ?>"></div>

                </td>

                <td class="wph-list--info">

                    <span class="wph-table--title"><?php echo ucfirst( $service->name ); ?></span>

                    <span class="wph-table--subtitle"><?php echo $service->api_key; ?></span>
                    <?php if( !empty( $service->list_id ) ): ?>
                        <span class="wph-table--subtitle"><?php echo $service->list_id; ?></span>
                    <?php endif; ?>

                </td>

                <td class="wph-list--edit">

                    <button  data-source="<?php echo esc_attr( $service->source ); ?>" data-nonce="<?php echo $nonce; ?>" data-id="<?php echo esc_attr( $id ); ?>" class="wph-button wph-button--small wph-button--gray wph-providers-edit"><?php _e("Edit", Opt_In::TEXT_DOMAIN); ?></button>

                </td>

            </tr>
        <?php endforeach; ?>


        </tbody>

        <tfoot>

        <tr>

            <td colspan="3">

                <a href="" class="wph-button wph-button--blue wph-button--addList"><?php _e("Add List", Opt_In::TEXT_DOMAIN); ?></a>

            </td>

        </tr>

        </tfoot>

    </table>

</div>