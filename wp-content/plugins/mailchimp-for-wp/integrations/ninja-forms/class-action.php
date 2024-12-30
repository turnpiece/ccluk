<?php

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Class MC4WP_Ninja_Forms_Action
 */
class MC4WP_Ninja_Forms_Action extends NF_Abstracts_Action
{
	protected $_name           = 'mc4wp_subscribe';
	protected $_nicename       = 'Mailchimp';
	protected $_tags           = array( 'newsletter' );
	protected $_timing         = 'normal';
	protected $_priority       = '10';
	protected $_settings       = array();
    protected $_setting_labels = array(
        'list'   => 'List',
        'fields' => 'List Field Mapping',
    );

	public function __construct()
	{
		parent::__construct();

		$this->_settings['double_optin']    = array(
			'name'    => 'double_optin',
			'type'    => 'select',
			'label'   => 'Use double opt-in?',
			'width'   => 'full',
			'group'   => 'primary',
			'value'   => 1,
			'options' => array(
				array(
					'value' => 1,
					'label' => 'Yes',
				),
				array(
					'value' => 0,
					'label' => 'No',
				),
			),
		);
		$this->_settings['update_existing'] = array(
			'name'    => 'update_existing',
			'type'    => 'select',
			'label'   => 'Update existing subscribers?',
			'width'   => 'full',
			'group'   => 'primary',
			'value'   => 0,
			'options' => array(
				array(
					'value' => 1,
					'label' => 'Yes',
				),
				array(
					'value' => 0,
					'label' => 'No',
				),
			),
		);

		add_action('wp_ajax_nf_' . $this->_name . '_get_lists', array($this, '_get_lists'));
		add_action('init', array($this, 'translate_props'));

        $this->get_list_settings();
	}

	public function translate_props()
	{
		$this->_settings['double_optin']['label']    = __('Use double opt-in?', 'mailchimp-for-wp');
		$this->_settings['update_existing']['label'] = __('Update existing subscribers?', 'mailchimp-for-wp');

		$this->_settings[ $this->get_name() . 'newsletter_list_fields' ]['label'] = __('List Field Mapping', 'mailchimp-for-wp');
	}

	/*
	* PUBLIC METHODS
	*/

	public function save($action_settings)
	{
	}

	public function process($action_settings, $form_id, $data)
	{
		if (empty($action_settings['newsletter_list']) || empty($action_settings['EMAIL'])) {
			return;
		}

		// find "mc4wp_optin" type field, bail if not checked.
		foreach ($data['fields'] as $field_data) {
			if ($field_data['type'] === 'mc4wp_optin' && empty($field_data['value'])) {
				return;
			}
		}

		$list_id       = $action_settings['newsletter_list'];
		$email_address = $action_settings['EMAIL'];
		$mailchimp     = new MC4WP_MailChimp();

		$merge_fields = $mailchimp->get_list_merge_fields($list_id);
		foreach ($merge_fields as $merge_field) {
			if (! empty($action_settings[ $merge_field->tag ])) {
				$merge_fields[ $merge_field->tag ] = $action_settings[ $merge_field->tag ];
			}
		}

		$double_optin      = (int) $action_settings['double_optin'] !== 0;
		$update_existing   = (int) $action_settings['update_existing'] === 1;
		$replace_interests = isset($action_settings['replace_interests']) && (int) $action_settings['replace_interests'] === 1;

		do_action('mc4wp_integration_ninja_forms_subscribe', $email_address, $merge_fields, $list_id, $double_optin, $update_existing, $replace_interests, $form_id);
	}

	public function ajax_get_lists_handler()
	{
		check_ajax_referer('ninja_forms_builder_nonce', 'security');
		$lists = $this->get_lists();
		array_unshift($return, array( 'value' => 0, 'label' => '-', 'fields' => array(), 'groups' => array() ));
        echo wp_json_encode(array( 'lists' => $return ));
        wp_die();
	}

	private function get_lists()
	{
		$mailchimp = new MC4WP_MailChimp();

		/** @var array $lists */
		$lists  = $mailchimp->get_lists();
		$return = array();

		foreach ($lists as $list) {
			$list_fields = array();

			foreach ($mailchimp->get_list_merge_fields($list->id) as $merge_field) {
				$list_fields[] = array(
					'value' => $merge_field->tag,
					'label' => $merge_field->name,
				);
			}

			// TODO: Add support for groups once base class supports this.
			$return[] = array(
				'value'  => $list->id,
				'label'  => $list->name,
				'fields' => $list_fields,
			);
		}

		return $return;
	}

	private function get_list_settings()
    {
        $label_defaults = array(
            'list'   => 'List',
            'fields' => 'List Field Mapping',
        );
        $labels         = array_merge($label_defaults, $this->_setting_labels);
        $prefix         = $this->get_name();
     	$lists          = $this->get_lists();

        if (empty($lists)) {
return;
        }

        $this->_settings[ $prefix . 'newsletter_list' ] = array(
            'name' => 'newsletter_list',
            'type' => 'select',
            'label' => $labels[ 'list' ] . ' <a class="js-newsletter-list-update extra"><span class="dashicons dashicons-update"></span></a>',
            'width' => 'full',
            'group' => 'primary',
            'value' => '0',
            'options' => array(),
        );

        $fields = array();
        foreach ($lists as $list) {
            $this->_settings[ $prefix . 'newsletter_list' ][ 'options' ][] = $list;

            //Check to see if list has fields array set.
            if (isset($list[ 'fields' ])) {
                foreach ($list[ 'fields' ] as $field) {
                    $name     = $list[ 'value' ] . '_' . $field[ 'value' ];
                    $fields[] = array(
                        'name' => $name,
                        'type' => 'textbox',
                        'label' => $field[ 'label' ],
                        'width' => 'full',
                        'use_merge_tags' => array(
                            'exclude' => array(
                                'user', 'post', 'system', 'querystrings',
                            ),
                        ),
                    );
                }
            }
        }

        $this->_settings[ $prefix . 'newsletter_list_fields' ] = array(
            'name' => 'newsletter_list_fields',
            'label' => 'List Field Mapping',
            'type' => 'fieldset',
            'group' => 'primary',
            'settings' => array(),
        );
    }
}
