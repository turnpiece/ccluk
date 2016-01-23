<?php

function cosmica_upgrade_control( $wp_customize ){


    class Cosmica_Pro_Customize_Control extends WP_Customize_Control
    {
      
          public function render_content() {
          ?>
            <div class="cosmica-pro">
              <a href="<?php echo esc_url('https://codeins.org/themes/cosmica-responsive-wordpress-theme/');?>" target="_blank" class="cdns-upgrade" id="cdns-upgrade-pro"><?php _e( 'UPGRADE  TO PRO ','cosmica' ); ?></a>
            </div>
          <?php
          }

    }

    class Cosmica_Review_Customize_Control extends WP_Customize_Control
    {
      
          public function render_content() {
          ?>
            <div class="cosmica-pro">
              <a href="<?php echo esc_url('https://wordpress.org/support/view/theme-reviews/cosmica#postform');?>" target="_blank" class="cdns-upgrade" id="cdns-reviwe"><?php _e( 'ADD REVIEW','cosmica' ); ?></a>
            </div>
          <?php
          }

    }

    class Cosmica_Docs_Customize_Control extends WP_Customize_Control
    {
      
          public function render_content() {
          ?>
            <div class="cosmica-pro">
              <a href="<?php echo esc_url('https://codeins.org/documentation/');?>" target="_blank" class="cdns-upgrade" id="cdns-docs"><?php _e( 'DOCUMENTATION','cosmica' ); ?></a>
            </div>
            <div class="pro-vesrion">
             <?php _e('The Pro Version gives you more opportunities to enhance your site and business. In order to create effective online presence one have to showcase their wide range of products, have to use contact us enquiry form, have to make effective about us page, have to introduce team members, etc etc . The pro version will give it all. Buy the pro version and give us a chance to serve you better. ','cosmica');?>
            </div>
          <?php
          }

    }

    class Cosmica_Sevice_Pro_Customize_Control extends WP_Customize_Control
    {
      
          public function render_content() {
          ?>
            <div class="cosmica-pro">
              <a href="<?php echo esc_url('https://codeins.org/themes/cosmica-responsive-wordpress-theme/');?>" target="_blank" class="cdns-upgrade" id="cdns-upgrade-sevice"><?php _e( 'Add More Services Get Pro','cosmica' ); ?></a>
            </div>
          <?php
          }

    }

    class Cosmica_Slider_Pro_Customize_Control extends WP_Customize_Control
    {
      
          public function render_content() {
          ?>
            <div class="cosmica-pro">
              <a href="<?php echo esc_url('https://codeins.org/themes/cosmica-responsive-wordpress-theme/');?>" target="_blank" class="cdns-upgrade" id="cdns-upgrade-sevice"><?php _e( 'Add More Slides Get Pro','cosmica' ); ?></a>
            </div>
          <?php
          }

    }


$wp_customize->add_section( 'cosmica_upgarde_pro_section', array(
        'title' => __( 'UPGRADE  TO PRO VERSION', 'cosmica' ), 
        'priority' => 1000, 
      ));


    $wp_customize->add_setting('upgrade_to_pro', array(
        'capability'     => 'edit_theme_options',
        'sanitize_callback' => 'cosmica_sanitize_html',
    ));
    $wp_customize->add_control( new Cosmica_Pro_Customize_Control( $wp_customize, 'upgrade_to_pro', array(
        'label' => __('Discover Cosmica Pro','cosmica'),
        'section' => 'cosmica_upgarde_pro_section',
        'setting' => 'upgrade_to_pro',
    )));


    $wp_customize->add_setting('cosmica_review', array(
        'capability'     => 'edit_theme_options',
        'sanitize_callback' => 'cosmica_sanitize_html',
    ));
    $wp_customize->add_control( new Cosmica_Review_Customize_Control( $wp_customize, 'cosmica_review', array(
        'label' => __('Discover Cosmica Pro','cosmica'),
        'section' => 'cosmica_upgarde_pro_section',
        'setting' => 'cosmica_review',
    )));


    $wp_customize->add_setting('cosmica_visit_docs', array(
        'capability'     => 'edit_theme_options',
        'sanitize_callback' => 'cosmica_sanitize_html',
    ));
    $wp_customize->add_control( new Cosmica_Docs_Customize_Control( $wp_customize, 'cosmica_visit_docs', array(
        'label' => __('Discover Cosmica Pro','cosmica'),
        'section' => 'cosmica_upgarde_pro_section',
        'setting' => 'cosmica_visit_docs',
    )));

   

    $wp_customize->add_section( 'service_section_pro', array(
        'title'         =>    __( 'Add More service', 'cosmica' ), 
        'priority'      =>    300, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_services_settings',
    ));

    $wp_customize->add_setting('cosmica_upgrade_service', array(
        'capability'     => 'edit_theme_options',
        'sanitize_callback' => 'cosmica_sanitize_html',
    ));
    $wp_customize->add_control( new Cosmica_Sevice_Pro_Customize_Control( $wp_customize, 'cosmica_upgrade_service', array(
        'label' => __('Add More Services Get Pro','cosmica'),
        'section' => 'service_section_pro',
        'setting' => 'cosmica_upgrade_service',
    )));


    $wp_customize->add_section( 'slider_section_pro', array(
        'title'         =>    __( 'Add More Slides', 'cosmica' ), 
        'priority'      =>    300, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_slider_settings',
    ));

    $wp_customize->add_setting('cosmica_upgrade_slider', array(
        'capability'     => 'edit_theme_options',
        'sanitize_callback' => 'cosmica_sanitize_html',
    ));
    $wp_customize->add_control( new Cosmica_Slider_Pro_Customize_Control( $wp_customize, 'cosmica_upgrade_slider', array(
        'label' => __('Add More Slides Get Pro','cosmica'),
        'section' => 'slider_section_pro',
        'setting' => 'cosmica_upgrade_slider',
    )));

   

}

class Cosmica_Customize {
   
   public static function register ( $wp_customize ) {

    
    $wp_customize->add_panel( 'cosmica_logo_settings', array(
        'priority'       =>   200,
        'capability'     =>   'edit_theme_options',
        'title'          =>   __( 'Logo Settings', 'cosmica' ), 
        'description'    =>   __('customize Logo', 'cosmica'),
    ));

    $wp_customize->add_section( 'cosmica_logo_section', array(
        'title'         =>    __( 'Logo Settings', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_logo_settings',
    ));

     $wp_customize->add_setting( 'cosmica_show_logo', 
       array(
          'default' => false, 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_checkbox',
       ) 
    );

    $wp_customize->add_control( 'cosmica_show_logo', array(
        'type'     => 'checkbox',
        'priority' => 1,
        'section'  => 'cosmica_logo_section',
        'label'    => __('Show Logo', 'cosmica'),
    ));





    $wp_customize->add_panel( 'cosmica_slider_settings', array(
        'priority'       =>   200,
        'capability'     =>   'edit_theme_options',
        'title'          =>   __( 'Slider Settings', 'cosmica' ), 
        'description'    =>   __('customize Slider', 'cosmica'),
    ));

    $wp_customize->add_section( 'cosmica_slider_section', array(
        'title'         =>    __( 'Cosmica Slider ON/OFF', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_slider_settings',
    ));

     $wp_customize->add_setting( 'cosmica_hide_demo_slider', 
       array(
          'default' => false, 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_checkbox',
       ) 
    );

    $wp_customize->add_control( 'cosmica_hide_demo_slider', array(
        'type'     => 'checkbox',
        'priority' => 1,
        'section'  => 'cosmica_slider_section',
        'label'    => __('Hide Home Slider ', 'cosmica'),
    ));

    
    $wp_customize->add_section( 'cosmica_slide_1_section', array(
        'title'         =>    __( 'Slide 1', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_slider_settings',
    ));

     $wp_customize->add_setting('cosmica_slide_1_heading', 
       array(
          'default' => __('Awesome Responsive Theme', 'cosmica'), 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_text',
       ) 
    );

    $wp_customize->add_control( 'cosmica_slide_1_heading', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_1_section',
        'label'    => __('Heading', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_1_description', 
       array(
          'default' => __('Cosmica is responsive WordPress theme for any business purpose. Cosmica have Theme Options where you can set social media links and other customization to theme.', 'cosmica'), 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_text',
    ));

    $wp_customize->add_control( 'cosmica_slide_1_description', array(
        'type'     => 'textarea',
        'priority' => 1,
        'section'  => 'cosmica_slide_1_section',
        'label'    => __('Description', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_1_bt_1_link', 
       array(
          'default' => '#', 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_url',
    ));

    $wp_customize->add_control( 'cosmica_slide_1_bt_1_link', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_1_section',
        'label'    => __('Button 1 Link ', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_1_bt_2_link', 
       array(
          'default' => '#', 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_url',
    ));

    $wp_customize->add_control( 'cosmica_slide_1_bt_2_link', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_1_section',
        'label'    => __('Button 2 Link ', 'cosmica'),
    ));




$wp_customize->add_section( 'cosmica_slide_2_section', array(
        'title'         =>    __( 'Slide 2', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_slider_settings',
    ));

     $wp_customize->add_setting('cosmica_slide_2_heading', 
       array(
          'default' => __('Awesome Responsive Theme', 'cosmica'), 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_text',
       ) 
    );

    $wp_customize->add_control( 'cosmica_slide_2_heading', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_2_section',
        'label'    => __('Heading', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_2_description', 
       array(
          'default' => __('Cosmica is responsive WordPress theme for any business purpose. Cosmica have Theme Options where you can set social media links and other customization to theme.', 'cosmica'), 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_text',
    ));

    $wp_customize->add_control( 'cosmica_slide_2_description', array(
        'type'     => 'textarea',
        'priority' => 1,
        'section'  => 'cosmica_slide_2_section',
        'label'    => __('Description', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_2_bt_1_link', 
       array(
          'default' => '#', 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_url',
    ));

    $wp_customize->add_control( 'cosmica_slide_2_bt_1_link', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_2_section',
        'label'    => __('Button 1 Link ', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_2_bt_2_link', 
       array(
          'default' => '#', 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_url',
    ));

    $wp_customize->add_control( 'cosmica_slide_2_bt_2_link', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_2_section',
        'label'    => __('Button 2 Link ', 'cosmica'),
    ));


    $wp_customize->add_section( 'cosmica_slide_3_section', array(
        'title'         =>    __( 'Slide 3', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_slider_settings',
    ));

     $wp_customize->add_setting('cosmica_slide_3_heading', 
       array(
          'default' => __('Awesome Responsive Theme', 'cosmica'), 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_text',
       ) 
    );

    $wp_customize->add_control( 'cosmica_slide_3_heading', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_3_section',
        'label'    => __('Heading', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_3_description', 
       array(
          'default' => __('Cosmica is responsive WordPress theme for any business purpose. Cosmica have Theme Options where you can set social media links and other customization to theme.', 'cosmica'), 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_text',
    ));

    $wp_customize->add_control( 'cosmica_slide_3_description', array(
        'type'     => 'textarea',
        'priority' => 1,
        'section'  => 'cosmica_slide_3_section',
        'label'    => __('Description', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_3_bt_1_link', 
       array(
          'default' => '#', 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_url',
    ));

    $wp_customize->add_control( 'cosmica_slide_3_bt_1_link', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_3_section',
        'label'    => __('Button 1 Link ', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_3_bt_2_link', 
       array(
          'default' => '#', 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_url',
    ));

    $wp_customize->add_control( 'cosmica_slide_3_bt_2_link', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_3_section',
        'label'    => __('Button 2 Link ', 'cosmica'),
    ));


    $wp_customize->add_section( 'cosmica_slide_4_section', array(
        'title'         =>    __( 'Slide 4', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_slider_settings',
    ));

     $wp_customize->add_setting('cosmica_slide_4_heading', 
       array(
          'default' => __('Awesome Responsive Theme', 'cosmica'), 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_text',
       ) 
    );

    $wp_customize->add_control( 'cosmica_slide_4_heading', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_4_section',
        'label'    => __('Heading', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_4_description', 
       array(
          'default' => __('Cosmica is responsive WordPress theme for any business purpose. Cosmica have Theme Options where you can set social media links and other customization to theme.', 'cosmica'), 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_text',
    ));

    $wp_customize->add_control( 'cosmica_slide_4_description', array(
        'type'     => 'textarea',
        'priority' => 1,
        'section'  => 'cosmica_slide_4_section',
        'label'    => __('Description', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_4_bt_1_link', 
       array(
          'default' => '#', 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_url',
    ));

    $wp_customize->add_control( 'cosmica_slide_4_bt_1_link', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_4_section',
        'label'    => __('Button 1 Link ', 'cosmica'),
    ));

    $wp_customize->add_setting('cosmica_slide_4_bt_2_link', 
       array(
          'default' => '#', 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'cosmica_sanitize_url',
    ));

    $wp_customize->add_control( 'cosmica_slide_4_bt_2_link', array(
        'type'     => 'text',
        'priority' => 1,
        'section'  => 'cosmica_slide_4_section',
        'label'    => __('Button 2 Link ', 'cosmica'),
    ));

    


    $wp_customize->add_panel( 'cosmica_social_settings', array(
        'priority'       =>   200,
        'capability'     =>   'edit_theme_options',
        'title'          =>   __( 'Social Settings', 'cosmica' ), 
        'description'    =>   __('customize Slider', 'cosmica'),
    ));

    $wp_customize->add_section( 'cosmica_social_section', array(
        'title'         =>    __( 'Cosmica Social', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_social_settings',
    ));
     $wp_customize->add_setting( 'social_link_open_in_new_tab', 
       array(
          'default' => true, 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'cosmica_sanitize_checkbox',
       ) 
    );

     $wp_customize->add_control( 'social_link_open_in_new_tab', array(
        'type'     => 'checkbox',
        'priority' => 200,
        'section'  => 'cosmica_social_section',
        'label'    => __('Open social links in new tab', 'cosmica'),
    ) );

      $wp_customize->add_setting( 'social_link_facebook', 
         array(
            'default' => esc_url('#'), 
            'type' => 'theme_mod', 
            'capability' => 'edit_theme_options', 
            'transport' => 'refresh', 
            'sanitize_callback' => 'cosmica_sanitize_url'
         ) 
      );      
            
      
      $wp_customize->add_control( 'social_link_facebook', array(
          'type'     => 'url',
          'priority' => 200,
          'section'  => 'cosmica_social_section',
          'label'    => __('Facebook Page URL', 'cosmica'),
      ) );


      $wp_customize->add_setting( 'social_link_google', 
         array(
            'default' => esc_url('#'), 
            'type' => 'theme_mod', 
            'capability' => 'edit_theme_options', 
            'transport' => 'refresh', 
            'sanitize_callback' => 'cosmica_sanitize_url'
         ) 
      );      
            
      
      $wp_customize->add_control( 'social_link_google', array(
          'type'     => 'url',
          'priority' => 200,
          'section'  => 'cosmica_social_section',
          'label'    => __('Google Page URL', 'cosmica'),
      ) );

      $wp_customize->add_setting( 'social_link_youtube', 
         array(
            'default' => esc_url('#'), 
            'type' => 'theme_mod', 
            'capability' => 'edit_theme_options', 
            'transport' => 'refresh', 
            'sanitize_callback' => 'cosmica_sanitize_url'
         ) 
      );      
            
      
      $wp_customize->add_control( 'social_link_youtube', array(
          'type'     => 'url',
          'priority' => 200,
          'section'  => 'cosmica_social_section',
          'label'    => __('Youtube Page URL', 'cosmica'),
      ) );

      $wp_customize->add_setting( 'social_link_twitter', 
         array(
            'default' => esc_url('#'), 
            'type' => 'theme_mod', 
            'capability' => 'edit_theme_options', 
            'transport' => 'refresh', 
            'sanitize_callback' => 'cosmica_sanitize_url'
         ) 
      );      
            
      
      $wp_customize->add_control( 'social_link_twitter', array(
          'type'     => 'url',
          'priority' => 200,
          'section'  => 'cosmica_social_section',
          'label'    => __('Twitter Page URL', 'cosmica'),
      ) );

      $wp_customize->add_setting( 'social_link_linkedin', 
         array(
            'default' => esc_url('#'), 
            'type' => 'theme_mod', 
            'capability' => 'edit_theme_options', 
            'transport' => 'refresh', 
            'sanitize_callback' => 'cosmica_sanitize_url'
         ) 
      );      
            
      
      $wp_customize->add_control( 'social_link_linkedin', array(
          'type'     => 'url',
          'priority' => 200,
          'section'  => 'cosmica_social_section',
          'label'    => __('Linkedin Page URL', 'cosmica'),
      ) );

      $wp_customize->add_section( 'cosmica_contact_section', 
         array(
            'title' => __( 'Contact Settings', 'cosmica' ), 
            'priority' => 200, 
            'capability' => 'edit_theme_options', 
            'description' => __('Allows you to add contact email and phone number', 'cosmica'), 
            'panel'   =>'cosmica_social_settings'
        ));

      $wp_customize->add_setting( 'contact_email', 
         array(
            'default' => 'mail@example.com', 
            'type' => 'theme_mod', 
            'capability' => 'edit_theme_options', 
            'transport' => 'refresh', 
            'sanitize_callback' => 'cosmica_sanitize_email'
         ) 
      );      
            
      
      $wp_customize->add_control( 'contact_email', array(
          'type'     => 'text',
          'priority' => 200,
          'section'  => 'cosmica_contact_section',
          'label'    => __('Enter Email', 'cosmica'),
      ));



    $wp_customize->add_setting( 'contact_phone', 
         array(
            'default'             => '000-000-0000', 
            'type'                => 'theme_mod', 
            'capability'          => 'edit_theme_options', 
            'transport'           => 'refresh',
            'sanitize_callback'   => 'cosmica_sanitize_text'
    ));


    $wp_customize->add_control( 'contact_phone', array(
          'type'     => 'text',
          'priority' => 200,
          'section'  => 'cosmica_contact_section',
          'label'    => __('Enter Phone Number', 'cosmica'),
    ));



    $wp_customize->add_panel( 'cosmica_footer_settings', array(
        'priority'       =>   200,
        'capability'     =>   'edit_theme_options',
        'title'          =>   __( 'Footer Settings', 'cosmica' ), 
    ));

    $wp_customize->add_section( 'cosmica_copyright_section', array(
        'title'         =>    __( 'Footer Copyright', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_footer_settings',
    ));

     $wp_customize->add_setting( 'cosmca_copyright_text', 
       array(
          'default' => '<div class="copyright"> ' .esc_html('&copy '.date("Y")). ' <a href="'. esc_url(get_site_url()).'" title="'. esc_attr(get_bloginfo('name')).'"><span>'. esc_html(get_bloginfo('name')).'</span></a> |  '.__('Theme by', 'cosmica').': <a href="'. esc_url('http://www.codeins.org').'" target="_blank" title="Codeins"><span>Codeins</span></a> | '. __('Proudly Powered by', 'cosmica').': <a href="'. esc_url('http://WordPress.org').'" target="_blank" title="WordPress"><span>WordPress</span></a> </div>', 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

     $wp_customize->add_control( 'cosmca_copyright_text', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_copyright_section',
        'label'    => __('Copyright Info', 'cosmica'),
    ) );


    $wp_customize->add_panel( 'cosmica_callout_settings', array(
        'priority'       =>   200,
        'capability'     =>   'edit_theme_options',
        'title'          =>   __( 'Call Out Box Settings', 'cosmica' ), 
    ));

    $wp_customize->add_section( 'cosmica_call_text_section', array(
        'title'         =>    __( 'Call Out Box Text', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_callout_settings',
    ));

     $wp_customize->add_setting( 'cosmca_call_header_text', 
       array(
          'default' => __('Work Speaks Thousand Words','cosmica'), 
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_call_header_text', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_call_text_section',
        'label'    => __('Call Out Header Text', 'cosmica'),
    ) );

    $wp_customize->add_setting( 'cosmca_call_desc_text', 
       array(
          'default' => __('We are a group of passionate designers and developers who really love to create awesome wordpress themes & support.','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_call_desc_text', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_call_text_section',
        'label'    => __('Call Out Header Text', 'cosmica'),
    ));


    $wp_customize->add_section( 'cosmica_call_button_section', array(
        'title'         =>    __( 'Call Out Box Buttons', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_callout_settings',
    ));

    $wp_customize->add_setting( 'cosmca_call_bt1_text', 
       array(
          'default' => __('Purchase Theme','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_call_bt1_text', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_call_button_section',
        'label'    => __('Button 1 Text', 'cosmica'),
    ));

    $wp_customize->add_setting( 'cosmca_call_bt1_link', 
       array(
          'default' =>esc_url('#'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_call_bt1_link', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_call_button_section',
        'label'    => __('Button 1 Link', 'cosmica'),
    ));




    $wp_customize->add_setting( 'cosmca_call_bt2_text', 
       array(
          'default' => __('See Details','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_call_bt2_text', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_call_button_section',
        'label'    => __('Button 2 Text', 'cosmica'),
    ));

    $wp_customize->add_setting( 'cosmca_call_bt2_link', 
       array(
          'default' =>esc_url('#'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_call_bt2_link', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_call_button_section',
        'label'    => __('Button 2 Link', 'cosmica'),
    ));

    $wp_customize->add_panel( 'cosmica_services_settings', array(
        'priority'       =>   200,
        'capability'     =>   'edit_theme_options',
        'title'          =>   __( 'Services Settings', 'cosmica' ), 
    ));

   $wp_customize->add_section( 'cosmica_service_text_section', array(
        'title'         =>    __( 'Services Text', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_services_settings',
   ));

   $wp_customize->add_setting( 'cosmca_services_header_text', 
       array(
          'default' => __('Awesome Services','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_services_header_text', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_text_section',
        'label'    => __('Title Text', 'cosmica'),
    ));

    $wp_customize->add_setting( 'cosmca_services_desc_text', 
       array(
          'default' => __('We are a group of passionate designers and developers who really love to create awesome WordPress themes & support','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control( 'cosmca_services_desc_text', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_text_section',
        'label'    => __('Description Text', 'cosmica'),
    ));


    $wp_customize->add_section( 'cosmica_service_1_section', array(
        'title'         =>    __( 'Services 1', 'cosmica' ), 
        'priority'      =>    1, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_services_settings',
   ));

   $wp_customize->add_setting( 'cosmca_services_1_title', 
       array(
          'default' => __('Lorem ipsum','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_services_1_title', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_1_section',
        'label'    => __('Title', 'cosmica'),
    ));

    $wp_customize->add_setting( 'cosmca_services_1_desc', 
       array(
          'default' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control( 'cosmca_services_1_desc', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_1_section',
        'label'    => __('Description', 'cosmica'),
    ));







    $wp_customize->add_section( 'cosmica_service_2_section', array(
        'title'         =>    __( 'Services 2', 'cosmica' ), 
        'priority'      =>    2, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_services_settings',
   ));

   $wp_customize->add_setting( 'cosmca_services_2_title', 
       array(
          'default' => __('Lorem ipsum','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_services_2_title', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_2_section',
        'label'    => __('Title', 'cosmica'),
    ));

    $wp_customize->add_setting( 'cosmca_services_2_desc', 
       array(
          'default' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control( 'cosmca_services_2_desc', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_2_section',
        'label'    => __('Description', 'cosmica'),
    ));





    $wp_customize->add_section( 'cosmica_service_3_section', array(
        'title'         =>    __( 'Services 3', 'cosmica' ), 
        'priority'      =>    3, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_services_settings',
   ));

   $wp_customize->add_setting( 'cosmca_services_3_title', 
       array(
          'default' => __('Lorem ipsum','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_services_3_title', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_3_section',
        'label'    => __('Title', 'cosmica'),
    ));

    $wp_customize->add_setting( 'cosmca_services_3_desc', 
       array(
          'default' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control( 'cosmca_services_3_desc', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_3_section',
        'label'    => __('Description', 'cosmica'),
    ));






    $wp_customize->add_section( 'cosmica_service_4_section', array(
        'title'         =>    __( 'Services 4', 'cosmica' ), 
        'priority'      =>    4, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_services_settings',
   ));

   $wp_customize->add_setting( 'cosmca_services_4_title', 
       array(
          'default' => __('Lorem ipsum','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_services_4_title', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_4_section',
        'label'    => __('Title', 'cosmica'),
    ));

    $wp_customize->add_setting( 'cosmca_services_4_desc', 
       array(
          'default' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control( 'cosmca_services_4_desc', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_4_section',
        'label'    => __('Description', 'cosmica'),
    ));








    $wp_customize->add_section( 'cosmica_service_5_section', array(
        'title'         =>    __( 'Services 5', 'cosmica' ), 
        'priority'      =>    5, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_services_settings',
   ));

   $wp_customize->add_setting( 'cosmca_services_5_title', 
       array(
          'default' => __('Lorem ipsum','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_services_5_title', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_5_section',
        'label'    => __('Title', 'cosmica'),
    ));

    $wp_customize->add_setting( 'cosmca_services_5_desc', 
       array(
          'default' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control( 'cosmca_services_5_desc', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_5_section',
        'label'    => __('Description', 'cosmica'),
    ));







    $wp_customize->add_section( 'cosmica_service_6_section', array(
        'title'         =>    __( 'Services 6', 'cosmica' ), 
        'priority'      =>    6, 
        'capability'    =>    'edit_theme_options', 
        'panel'         =>    'cosmica_services_settings',
   ));

   $wp_customize->add_setting( 'cosmca_services_6_title', 
       array(
          'default' => __('Lorem ipsum','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
           'sanitize_callback' => 'sanitize_text_field',
       )
    );

    $wp_customize->add_control( 'cosmca_services_6_title', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_6_section',
        'label'    => __('Title', 'cosmica'),
    ));

    $wp_customize->add_setting( 'cosmca_services_6_desc', 
       array(
          'default' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.','cosmica'),
          'type' => 'theme_mod', 
          'capability' => 'edit_theme_options', 
          'transport' => 'refresh', 
          'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control( 'cosmca_services_6_desc', array(
        'type'     => 'text',
        'priority' => 200,
        'section'  => 'cosmica_service_6_section',
        'label'    => __('Description', 'cosmica'),
    ));


    

   
   
      $wp_customize->get_section( 'title_tagline' )->priority = 10;    
      $wp_customize->get_section( 'static_front_page' )->priority = 30;
      $wp_customize->remove_section( 'colors' );
      $wp_customize->get_section( 'header_image' )->priority = 50;
      $wp_customize->remove_section( 'background_image' );
   
      

   }

   public static function header_output() {
      ?>
      <!--Customizer CSS--> 
      <style type="text/css">
           
      </style> 
      <!--/Customizer CSS-->
      <?php
   }
   
   public static function generate_css( $selector, $style, $mod_name, $prefix='', $postfix='', $echo=true ) {
      $return = '';
      $mod = get_theme_mod($mod_name);
      if ( ! empty( $mod ) ) {
         $return = sprintf('%s { %s:%s; }',
            $selector,
            $style,
            $prefix.$mod.$postfix
         );
         if ( $echo ) {
            echo $return;
         }
      }
      return $return;
    }
}


 add_action( 'customize_register' , array( 'Cosmica_Customize' , 'register' ) );
 add_action( 'wp_head' , array( 'Cosmica_Customize' , 'header_output' ) );
 add_action( 'customize_register' , 'cosmica_upgrade_control' );
?>
