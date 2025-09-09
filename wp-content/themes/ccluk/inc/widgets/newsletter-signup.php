<?php

class CCLUK_Newletter_Signup extends WP_Widget
{

    private $signup_form;
    private $privacy_page;

    function __construct()
    {

        parent::__construct(
            'ccluk-newsletter-signup',  // Base ID
            'Newsletter Signup'   // Name
        );

        add_action('widgets_init', function () {
            register_widget('CCLUK_Newletter_Signup');
        });

        $this->signup_form = get_theme_mod('ccluk_newsletter_signup_form');
        $this->privacy_page = get_theme_mod('ccluk_newsletter_privacy_page');
    }

    public $args = array(
        'before_title'  => '<h4 class="widgettitle">',
        'after_title'   => '</h4>',
        'before_widget' => '<div class="widget-wrap">',
        'after_widget'  => '</div></div>'
    );

    public function widget($args, $instance)
    {

        if ($this->signup_form == '')
            return;

        echo $args['before_widget'];

        if (! empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        echo '<div class="textwidget">';

        echo esc_html__($instance['text'], 'ccluk');

        echo $this->signup_form;

        if ($this->privacy_page && $instance['privacy']) : ?>
            <p class="privacy-policy">
                <a href="<?php echo get_permalink($this->privacy_page) ?>" title="<?php esc_attr_e('Our privacy policy', 'ccluk') ?>"><?php echo $instance['privacy'] ?></a>
            </p>
        <?php endif;

        echo '</div>';

        echo $args['after_widget'];
    }

    public function form($instance)
    {

        $title = ! empty($instance['title']) ? $instance['title'] : esc_html__('', 'ccluk');
        $text = ! empty($instance['text']) ? $instance['text'] : esc_html__('', 'ccluk');
        $privacy = ! empty($instance['privacy']) ? $instance['privacy'] : esc_html__('', 'ccluk');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title:', 'ccluk'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('text')); ?>"><?php esc_attr_e('Text:', 'ccluk'); ?></label>
            <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('text')); ?>" name="<?php echo esc_attr($this->get_field_name('text')); ?>" type="text" cols="30" rows="10"><?php echo esc_attr($text); ?></textarea>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('privacy')); ?>"><?php esc_attr_e('Privacy notice:', 'ccluk'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('privacy')); ?>" name="<?php echo esc_attr($this->get_field_name('privacy')); ?>" type="text" value="<?php echo esc_attr($privacy); ?>">
        </p>
        <?php if ($signup_form == '') : ?>
            <p class="warning">
                <?php _e('WARNING: This widget will not show until you set the signup form in Appearance > Customise > Newsletter.', 'ccluk') ?>
            </p>
<?php endif;
    }

    public function update($new_instance, $old_instance)
    {

        $instance = array();

        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['text'] = (!empty($new_instance['text'])) ? $new_instance['text'] : '';
        $instance['privacy'] = (!empty($new_instance['privacy'])) ? strip_tags($new_instance['privacy']) : '';

        return $instance;
    }
}
$my_widget = new CCLUK_Newletter_Signup();
