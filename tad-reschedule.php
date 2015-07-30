<?php
/**
 * Plugin Name: Reschedule Utility
 * Plugin URI: http://theAverageDev.com
 * Description: Easy cron event rescheduling.
 * Version: 1.0
 * Author: theAverageDev
 * Author URI: http://theAverageDev.com
 * License: GPL 2.0
 */

if ( ! function_exists( 'tad_reschedule' ) ) {
	/**
	 * @param string $hook The name of the action hook to reschedule.
	 *
	 * @return tad_Reschedule An instance of the underlying class to keep the chain going.
	 *
	 */
	function tad_reschedule( $hook ) {
		return tad_Reschedule::instance( $hook );
	}
}


class tad_Reschedule {


	/**
	 * @var string The name of the hook to reschedule
	 */
	protected $hook;

	/**
	 * @var bool|callable A function name or a callable the result of which will be used to assert the until
	 *      condition.
	 */
	protected $until_condition = true;

	/**
	 * @var int|callable A callable or an int value representing the reschedule time.
	 */
	protected $each = 600;

	/**
	 * @var array|callable An array of arguments that will be passed to the schedule action.
	 */
	protected $args = array();

	/**
	 * Instances and returns the object.
	 *
	 * @param string $hook The name of the action hook to reschedule.
	 *
	 * @return tad_Reschedule The instance of this class to keep the chain going.
	 */
	public static function instance( $hook ) {
		if ( ! is_string( $hook ) ) {
			throw new InvalidArgumentException( 'Hook name must be a string' );
		}

		$instance       = new self();
		$instance->hook = $hook;

		return $instance;
	}

	/**
	 * @param bool|callable $condition Either a boolean or a boolean cast-able value or a callable returning a boolean.
	 *                                 This will decide if the reschedule will happen or not.
	 *
	 * @return tad_Reschedule The instance of this class to keep the chain going.
	 */
	public function until( $condition ) {
		$this->until_condition = $condition;

		return $this;
	}

	/**
	 * @param int|callable $interval Either an int value representing the time offset in seconds or a callable returning
	 *                               and int.
	 *
	 * @return tad_Reschedule The instance of this class to keep the chain going.
	 */
	public function each( $interval ) {
		if ( ! ( is_callable( $interval ) || is_numeric( $interval ) ) ) {
			throw new \InvalidArgumentException( 'Interval must be an int value or a callable.' );
		}

		$this->each = $interval;

		return $this;
	}

	/**
	 * @param array|callable $args Either an array of args that will be passed to the scheduled action or a callable
	 *                             returning an array of args.
	 *
	 * @return tad_Reschedule The instance of this class to keep the chain going.
	 */
	public function with_args( $args ) {
		if ( ! ( is_callable( $args ) || is_array( $args ) || is_null( $args ) ) ) {
			throw new \InvalidArgumentException( 'Arguments must be an array or a callable.' );
		}
		$this->args = $args;

		return $this;
	}

	/**
	 * PHP 5 introduces a destructor concept similar to that of other object-oriented languages, such as C++.
	 * The destructor method will be called as soon as all references to a particular object are removed or
	 * when the object is explicitly destroyed or in any order in shutdown sequence.
	 *
	 * Like constructors, parent destructors will not be called implicitly by the engine.
	 * In order to run a parent destructor, one would have to explicitly call parent::__destruct() in the destructor
	 * body.
	 *
	 * Note: Destructors called during the script shutdown have HTTP headers already sent.
	 * The working directory in the script shutdown phase can be different with some SAPIs (e.g. Apache).
	 *
	 * Note: Attempting to throw an exception from a destructor (called in the time of script termination) causes a
	 * fatal error.
	 *
	 * @return void
	 * @link http://php.net/manual/en/language.oop5.decon.php
	 */
	function __destruct() {
		if ( ! $this->should_schedule() ) {
			return;
		}
		$this->schedule();
	}

	private function should_schedule() {
		$condition = is_callable( $this->until_condition ) ? (bool) call_user_func( $this->until_condition ) : (bool) $this->until_condition;
		if ( ! $condition ) {
			return false;
		}

		return true;
	}

	private function schedule() {
		$time_offset = is_callable( $this->each ) ? (int) call_user_func( $this->each ) : (int) $this->each;
		$args        = is_callable( $this->args ) ? call_user_func( $this->args ) : $this->args;
		$args        = is_array( $args ) ? $args : array( $args );
		wp_schedule_single_event( time() + $time_offset, $this->hook, $args );
	}
}
