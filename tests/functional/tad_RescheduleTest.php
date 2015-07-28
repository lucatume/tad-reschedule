<?php

use tad\FunctionMocker\FunctionMocker as Test;

function a_callback() {

}


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

	public function intAndCallables() {
		return [
			[ 23, false ],
			[ '23', false ],
			[ 'foo', true ],
			[ array(), true ],
			[ new stdClass(), true ],
			[ 'a_callback', false ],
			[ array( 'Dummy', 'static_method' ), false ],
			[ array( 'Dummy', 'not_a_real_static_method' ), true ],
			[ array( new Dummy(), 'instance_method' ), false ],
			[ array( new Dummy(), 'not_a_real_instance_method' ), true ],
		];
	}

	/**
	 * @test
	 * it should only accept ints and callables in the each method
	 * @dataProvider intAndCallables
	 */
	public function it_should_only_accept_ints_and_callables_in_the_each_method( $in, $should_throw ) {
		if ( $should_throw ) {
			$this->setExpectedException( 'InvalidArgumentException' );
		}

		$out = tad_reschedule( 'some_hook' )->each( $in );
	}

	public function withArgsArgs() {
		return [
			[ [ 'one', 'two', 'three' ], false ],
			[ [ 'one' ], false ],
			[ [ 23, 12 ], false ],
			[ 'a_callback', false ],
			[ [ 'Dummy', 'static_method' ], false ],
			[ [ 'Dummy', 'not_a_static_method' ], false ], // will not be able to spot this!
			[ [ new Dummy, 'instance_method' ], false ],
			[ [ new Dummy, 'not_an_instance_method' ], false ] // will not be able to spot this!
		];
	}

	/**
	 * @test
	 * it should accept an array of args or a callable in the with args method
	 * @dataProvider withArgsArgs
	 */
	public function it_should_accept_an_array_of_args_or_a_callable_in_the_with_args_method( $in, $should_throw ) {
		if ( $should_throw ) {
			$this->setExpectedException( 'InvalidArgumentException' );
		}

		$out = tad_reschedule( 'some_hook' )->with_args( $in );
	}
}