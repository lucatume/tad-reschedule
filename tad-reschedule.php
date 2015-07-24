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
	}
}


class tad_Reschedule {

}
