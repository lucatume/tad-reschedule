<?php


class tad_RescheduleTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should return an instance of the tad_Reschedule class
	 */
	public function it_should_return_an_instance_of_the_tad_reschedule_class() {
		$this->assertInstanceOf( 'tad_Reschedule', tad_reschedule( 'my_hook' ) );
	}

}