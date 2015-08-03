<?php
use tad\FunctionMocker\FunctionMocker as Test;

function a_callback() {
}

function falsy_callback() {
	return false;
}

function truthy_callback() {
	return true;
}


class Dummy {

	public static function static_method() {
	}

	public function instance_method() {
	}

	public static function falsy_method() {
		return false;
	}

	public function falsy_instance_method() {
		return false;
	}

	public static function truthy_method() {
		return true;
	}

	public function truthy_instance_method() {
		return true;
	}
}


class tad_RescheduleTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

	public function setUp() {

		// before
		parent::setUp();
		Test::setUp();
		Test::replace( 'wp_next_scheduled', false );

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
			[
				array(
					'Dummy',
					'static_method'
				),
				false
			],
			[
				array(
					'Dummy',
					'not_a_real_static_method'
				),
				true
			],
			[
				array(
					new Dummy(),
					'instance_method'
				),
				false
			],
			[
				array(
					new Dummy(),
					'not_a_real_instance_method'
				),
				true
			],
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
			[ [ 'Dummy', 'not_a_static_method' ], false ],
			// will not be able to spot this!
			[ [ new Dummy, 'instance_method' ], false ],
			[ [ new Dummy, 'not_an_instance_method' ], false ],
			// will not be able to spot this!
			[ [ ], false ],
			[ null, false ],
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

	public function falsyUntilConditions() {
		return [
			[ false ],
			[ 0 ],
			[ '0' ],
			[ null ],
			[ 'falsy_callback' ],
			[
				function () {
					return false;
				}
			],
			[ [ '\\Dummy', 'falsy_method' ] ],
			[ [ new Dummy(), 'falsy_instance_method' ] ]
		];
	}

	/**
	 * @test
	 * it should not schedule the event if the until condition is falsy
	 * @dataProvider falsyUntilConditions
	 */
	public function it_should_not_schedule_the_event_if_the_until_condition_is_false( $condition ) {
		$wp_schedule_single_event = Test::replace( 'wp_schedule_single_event' );

		tad_reschedule( 'some_hook' )->until( $condition )->each( 600 )->with_args( [ 23 ] );

		$wp_schedule_single_event->wasNotCalled();
	}

	public function truthyUntilConditions() {
		return [
			[ true ],
			[ 1 ],
			[ '1' ],
			[ 'foo' ],
			[ 'truthy_callback' ],
			[
				function () {
					return true;
				}
			],
			[ [ '\\Dummy', 'truthy_method' ] ],
			[ [ new Dummy(), 'truthy_instance_method' ] ]
		];
	}

	/**
	 * @test
	 * it should schedule single event when condition truthy
	 * @dataProvider truthyUntilConditions
	 */
	public function it_should_schedule_single_event_when_condition_truthy( $condition ) {
		$wp_schedule_single_event = Test::replace( 'wp_schedule_single_event' );

		tad_reschedule( 'some_hook' )->until( $condition )->each( 600 )->with_args( [ 23 ] );

		$wp_schedule_single_event->wasCalledOnce();
	}

	/**
	 * @test
	 * it should schedule the event after 10 minutes by default
	 */
	public function it_should_schedule_the_event_after_10_minutes_by_default() {
		$time                     = 0;
		$wp_schedule_single_event = Test::replace( 'wp_schedule_single_event', function ( $time_offset ) use ( &$time ) {
			$time = $time_offset;
		} );

		tad_reschedule( 'some_hook' )->until( true );

		// delta 5s to allow for some test lag
		Test::assertEquals( time() + 600, $time, 5 );
	}

	/**
	 * @test
	 * it should schedule the hook
	 */
	public function it_should_schedule_the_hook() {
		$wp_schedule_single_event = Test::replace( 'wp_schedule_single_event' );

		tad_reschedule( 'some_hook' )->until( true );

		// delta 5s to allow for some test lag
		$wp_schedule_single_event->wasCalledWithOnce( [ $this->isType( 'int' ), 'some_hook', [ ] ] );
	}

	public function args() {
		return [
			[ [ 'foo' ] ],
			[ [ 1, 2, 3 ] ],
			[ [ 23, 'foo' ] ],
			[ [ ] ],
			[ [ new stdClass() ] ],
		];
	}

	/**
	 * @test
	 * it should schedule the hook with the given args
	 * @dataProvider args
	 */
	public function it_should_schedule_the_hook_with_the_given_args( $args ) {
		$wp_schedule_single_event = Test::replace( 'wp_schedule_single_event' );

		tad_reschedule( 'some_hook' )->until( true )->with_args( $args );

		// delta 5s to allow for some test lag
		$wp_schedule_single_event->wasCalledWithOnce( [ $this->isType( 'int' ), $this->isType( 'string' ), $args ] );
	}

	/**
	 * @test
	 * it should call the args callback
	 */
	public function it_should_call_the_args_callback() {
		$wp_schedule_single_event = Test::replace( 'wp_schedule_single_event' );
		$callable                 = function () {
			return 23;
		};
		tad_reschedule( 'some_hook' )->until( true )->with_args( $callable );

		// delta 5s to allow for some test lag
		$wp_schedule_single_event->wasCalledWithOnce( [ $this->isType( 'int' ), $this->isType( 'string' ), [ 23 ] ] );
	}

	/**
	 * @test
	 * it should not call the until condition until destruction
	 */
	public function it_should_not_call_the_until_condition_until_destruction() {
		$method = Test::replace( 'Dummy::static_method', true );

		$keep = tad_reschedule( 'some_hook' )->until( [ 'Dummy', 'static_method' ] );

		$method->wasNotCalled();

		unset( $keep );

		$method->wasCalledOnce();
	}

	/**
	 * @test
	 * it should not call the each method until destruction
	 */
	public function it_should_not_call_the_each_method_until_destruction() {
		$method = Test::replace( 'Dummy::static_method', 1200 );

		$keep = tad_reschedule( 'some_hook' )->until( true )->each( [ 'Dummy', 'static_method' ] );

		$method->wasNotCalled();

		unset( $keep );

		$method->wasCalledOnce();
	}

	/**
	 * @test
	 * it should not call the args method until destruction
	 */
	public function it_should_not_call_the_args_method_until_destruction() {
		$method = Test::replace( 'Dummy::static_method', 1200 );

		$keep = tad_reschedule( 'some_hook' )->until( true )->each( 600 )->with_args( [ 'Dummy', 'static_method' ] );

		$method->wasNotCalled();

		unset( $keep );

		$method->wasCalledOnce();
	}

	/**
	 * @test
	 * it should call the until method only if rescheduling
	 */
	public function it_should_call_the_until_method_only_if_rescheduling() {
		$method = Test::replace( 'Dummy::static_method', 1200 );

		tad_reschedule( 'some_hook' )->until( false )->each( [ 'Dummy', 'static_method' ] );

		$method->wasNotCalled();
	}

	/**
	 * @test
	 * it should call the args method only if rescheduling
	 */
	public function it_should_call_the_args_method_only_if_rescheduling() {
		$method = Test::replace( 'Dummy::static_method', 1200 );

		tad_reschedule( 'some_hook' )->until( false )->with_args( [ 'Dummy', 'static_method' ] );

		$method->wasNotCalled();
	}
}
