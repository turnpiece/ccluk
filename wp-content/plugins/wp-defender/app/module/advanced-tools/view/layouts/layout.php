<div class="wrap">
    <div class="wpmud">
        <div id="wp-defender" class="wp-defender">
            <div class="advanced-tools">
                <h2 class="title">
				    <?php _e( "Advanced Tools", wp_defender()->domain ) ?>
                </h2>
                <div class="row">
                    <div class="col-third">
                        <ul class="inner-nav is-hidden-mobile">
                            <li class="issues-nav">
                                <a class="<?php echo \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', false ) == false ? 'active' : null ?>"
                                   href="<?php echo network_admin_url( 'admin.php?page=wdf-advanced-tools' ) ?>">
								    <?php _e( "Two-Factor Authentication", wp_defender()->domain ) ?>
                                </a>
                            </li>
                            <li class="issues-nav">
                                <a class="<?php echo \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', false ) == 'mask-login' ? 'active' : null ?>"
                                   href="<?php echo network_admin_url( 'admin.php?page=wdf-advanced-tools&view=mask-login' ) ?>">
								    <?php _e( "Mask Login Area", wp_defender()->domain ) ?>
                                </a>
                            </li>
                        </ul>
                        <div class="is-hidden-tablet mline">
                            <select class="mobile-nav">
                                <option <?php selected( '', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view' ) ) ?>
                                        value="<?php echo network_admin_url( 'admin.php?page=wdf-advanced-tools' ) ?>"><?php _e( "Two Factor Authentication", wp_defender()->domain ) ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-two-third">
					    <?php echo $contents ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>