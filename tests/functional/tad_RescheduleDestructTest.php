<?php

use tad\FunctionMocker\FunctionMocker as Test;

class Dummy1122 {

	public function callback() {

	}
}


class tad_RescheduleDestructTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();
		Test::setUp();
		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		Test::tearDown();
		parent::tearDown();
	}

	/**
	 * @test
	 * it should call the function when destructing
	 */
	public function it_should_call_the_function_when_destructing() {
		$object = Test::replace( 'Dummy1122' )->method( 'callback' )->get();

		tad_reschedule( 'my_hook' )->until( [
			$object,
			'callback'
		] )->each( 60 )->with_args( [ 'foo' ] );

		$object->wasCalledOnce( 'callback' );
	}
}