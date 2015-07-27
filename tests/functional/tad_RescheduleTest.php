<?php

use tad\FunctionMocker\FunctionMocker as Test;

class Dummy {

	public static function static_method() {

	}

	public function instance_method() {

	}
}

class tad_RescheduleTest extends \WP_UnitTestCase {

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
	 * it should return an instance of the tad_Reschedule class
	 */
	public function it_should_return_an_instance_of_the_tad_reschedule_class() {
		Test::assertInstanceOf( 'tad_Reschedule', tad_reschedule( 'my_hook' ) );
	}

	public function primitives() {
		return array_map( function ( $val ) {
			return [ $val ];
		}, [ 1, '1', true, false, 'foo', 23, array(), new stdClass() ] );
	}

	/**
	 * @test
	 * it should accept primitives in the until method
	 * @dataProvider primitives
	 */
	public function it_should_accept_primitives_in_the_until_method( $primitive ) {
		$out = tad_reschedule( 'some_hook' )->until( $primitive );
		Test::assertInstanceOf( 'tad_Reschedule', $out );
	}

	public function callables() {
		return array_map( function ( $val ) {
			return [ $val ];
		}, [ 'my_condition', [ 'Dummy', 'static_method' ], [ new Dummy(), 'instance_method' ] ] );
	}

	/**
	 * @test
	 * it should accept callables in the until method
	 * @dataProvider callables
	 */
	public function it_should_accept_callables_in_the_until_method( $callable ) {
		$out = tad_reschedule( 'some_hook' )->until( $callable );
		Test::assertInstanceOf( 'tad_Reschedule', $out );
	}

}