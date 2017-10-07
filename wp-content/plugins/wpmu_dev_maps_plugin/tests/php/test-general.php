<?php

/**
 * Test general plugin aspects
 */
class MS_Test_General extends WP_UnitTestCase {

	/**
	 * Runs before the first test
	 * @beforeClass
	 */
	static function setup_once() {
		WP_UnitTestCase::setUpBeforeClass();
		require_once 'shared-setup.php';
	}

	/**
	 * Runs before the each test
	 * @before
	 */
	function setup() {
		parent::setUp();
		TData::reset();
	}

	/**
	 * General check that simply determines if the plugin was loaded at all.
	 * @test
	 */
	function plugin_is_loaded() {
		$this->assertTrue( defined( 'AGM_LANG' ) );
	}

	/**
	 * Checks if shared-setup was working.
	 * @test
	 */
	function staging_data_is_correct() {
		$this->assertFalse( empty( TData::id( 'user', 'admin' ) ) );
		wp_set_current_user( TData::id( 'user', 'admin' ) );
		$this->assertEquals( get_current_user_id(), TData::id( 'user', 'admin' ) );
		$this->assertFalse( empty( TData::id( 'user', 'editor' ) ) );

		$this->assertFalse( empty( TData::id( 'post', 'sample-page' ) ) );
		$this->assertEquals( 'page', get_post_type( TData::id( 'post', 'sample-page' ) ) );
	}
}