<?php

namespace WPForms;

/**
 * WPForms Class Loader.
 *
 * @since 1.5.8
 */
class Loader {

	/**
	 * Classes to register.
	 *
	 * @var array
	 */
	private $classes = array();

	/**
	 * Loader init.
	 *
	 * @since 1.5.8
	 */
	public function init() {

		$this->populate_classes();

		wpforms()->register_bulk( $this->classes );
	}

	/**
	 * Populate the classes to register.
	 *
	 * @since 1.5.8
	 */
	protected function populate_classes() {

		$this->populate_capabilities();
	}

	/**
	 * Populate access management (capabilities) classes.
	 *
	 * @since 1.5.8
	 */
	private function populate_capabilities() {

		array_push(
			$this->classes,
			[
				'name' => 'Access\Capabilities',
				'id'   => 'access',
				'hook' => 'plugins_loaded',
			],
			[
				'name' => 'Access\Integrations',
			],
			[
				'name'      => 'Admin\Settings\Access',
				'condition' => is_admin(),
			]
		);
	}
}
