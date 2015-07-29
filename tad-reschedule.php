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
	function tad_reschedule( $hook ) {
		if ( ! is_string( $hook ) ) {
			throw new InvalidArgumentException( 'Hook name must be a string' );
		}

		return new tad_Reschedule();
	}
}


class tad_Reschedule {

	/**
	 * @var string|callable A function name or a callable the result of which will be used to assert the until
	 *      condition.
	 */
	public $until_callback;

	public function until( $condition ) {
		if ( is_callable( $condition ) ) {
			$this->until_callback = $condition;
		}

		return $this;
	}

	public function each( $interval ) {
		if ( ! ( is_callable( $interval ) || is_numeric( $interval ) ) ) {
			throw new \InvalidArgumentException( 'Interval must be an int value or a callable.' );
		}

		return $this;
	}

	public function with_args( $args ) {
		if ( ! ( is_callable( $args ) || is_array( $args ) || is_null( $args ) ) ) {
			throw new \InvalidArgumentException( 'Arguments must be an array or a callable.' );
		}

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
		if ( $this->until_callback ) {
			call_user_func( $this->until_callback );
		}
	}
}
