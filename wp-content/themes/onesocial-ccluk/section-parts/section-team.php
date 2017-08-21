<?php
$ccluk_team_id       = get_theme_mod( 'ccluk_team_id', esc_html__('team', 'onesocial') );
$ccluk_team_disable  = get_theme_mod( 'ccluk_team_disable' ) ==  1 ? true : false;
$ccluk_team_title    = get_theme_mod( 'ccluk_team_title', esc_html__('Our Team', 'onesocial' ));
$ccluk_team_subtitle = get_theme_mod( 'ccluk_team_subtitle', esc_html__('Section subtitle', 'onesocial' ));
$layout = intval( get_theme_mod( 'ccluk_team_layout', 3 ) );
if ( $layout <= 0 ){
    $layout = 3;
}
$user_ids = ccluk_get_section_team_data();
if ( ccluk_is_selective_refresh() ) {
    $ccluk_team_disable = false;
}
if ( ! empty( $user_ids ) ) {
    $desc = get_theme_mod( 'ccluk_team_desc' );
    ?>
    <?php if ( ! $ccluk_team_disable ) : ?>
        <?php if ( ! ccluk_is_selective_refresh() ){ ?>
        <section id="<?php if ($ccluk_team_id != '') echo $ccluk_team_id; ?>" <?php do_action('ccluk_section_atts', 'team'); ?>
                 class="<?php echo esc_attr(apply_filters('ccluk_section_class', 'section-team section-padding section-meta onepage-section', 'team')); ?>">
        <?php } ?>
            <?php do_action('ccluk_section_before_inner', 'team'); ?>
            <div class="container">
                <?php if ( $ccluk_team_title || $ccluk_team_subtitle || $desc ){ ?>
                <div class="section-title-area">
                    <?php if ($ccluk_team_subtitle != '') echo '<h5 class="section-subtitle">' . esc_html($ccluk_team_subtitle) . '</h5>'; ?>
                    <?php if ($ccluk_team_title != '') echo '<h2 class="section-title">' . esc_html($ccluk_team_title) . '</h2>'; ?>
                    <?php if ( $desc ) {
                        echo '<div class="section-desc">' . apply_filters( 'the_content', wp_kses_post( $desc ) ) . '</div>';
                    } ?>
                </div>
                <?php } ?>
                <div class="team-members row team-layout-<?php echo intval( 12 / $layout  ); ?>">
                    <?php
                    if ( ! empty( $user_ids ) ) {
                        $n = 0;

                        foreach ( $user_ids as $member ) {
                            $member = wp_parse_args( $member, array(
                                'user_id'  =>array(),
                            ));

                            $link = isset( $member['link'] ) ?  $member['link'] : '';
                            $user_id = wp_parse_args( $member['user_id'],array(
                                'id' => '',
                             ) );

                            $image_attributes = wp_get_attachment_image_src( $user_id['id'], 'ccluk-small' );
                            if ( $image_attributes ) {
                                $image = $image_attributes[0];
                                $data = get_post( $user_id['id'] );
                                $n ++ ;
                                ?>
                                <div class="team-member wow slideInUp">
                                    <div class="member-thumb">
                                        <?php if ( $link ) { ?>
                                            <a href="<?php echo esc_url( $link ); ?>">
                                        <?php } ?>
                                        <img src="<?php echo esc_url( $image ); ?>" alt="">
                                        <?php if ( $link ) { ?>
                                            </a>
                                        <?php } ?>
                                        <?php do_action( 'ccluk_section_team_member_media', $member ); ?>
                                    </div>
                                    <div class="member-info">
                                        <h5 class="member-name"><?php if ( $link ) { ?><a href="<?php echo esc_url( $link ); ?>"><?php } ?><?php echo esc_html( $data->post_title ); ?><?php if ( $link ) { ?></a><?php } ?></h5>
                                        <span class="member-position"><?php echo esc_html( $data->post_content ); ?></span>
                                    </div>
                                </div>
                                <?php
                            }

                        } // end foreach
                    }

                    ?>
                </div>
            </div>
            <?php do_action('ccluk_section_after_inner', 'team'); ?>
        <?php if ( ! ccluk_is_selective_refresh() ){ ?>
        </section>
        <?php } ?>
    <?php endif;
}
