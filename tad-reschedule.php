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

	public function until( $condition ) {
		return $this;
	}

	public function each( $interval ) {
		if ( ! ( is_callable( $interval ) || is_numeric( $interval ) ) ) {
			throw new \InvalidArgumentException( 'Interval must be an int value or a callable.' );
		}

		return $this;
	}

	public function with_args( $args ) {
		if ( ! ( is_callable( $args ) || is_array( $args ) ) ) {
			throw new \InvalidArgumentException( 'Arguments must be an array or a callable.' );
		}

		return $this;
	}
}
