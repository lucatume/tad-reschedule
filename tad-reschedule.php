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
	 * @param string|callable $hook_or_callable The name of the action hook to reschedule.
	 *
	 * @return tad_Reschedule An instance of the underlying class to keep the chain going.
	 *
	 */
	function tad_reschedule( $hook_or_callable ) {
		return tad_Reschedule::instance( $hook_or_callable );
	}
}

if ( ! class_exists( 'tad_Reschedule' ) ) {
	class tad_Reschedule {

		/**
		 * WordPress will not allow a same action to be rescheduled with the same
		 * args with a time offset (in seconds) smaller than this.
		 */
		const WP_MIN_SCHEDULE_SECOND_OFFSET = 600;

		/**
		 * @var string|callable The name of the hook to reschedule or a callable to hook to the action.
		 */
		protected $hook;

		/**
		 * @var bool|callable A function name or a callable the result of which
		 *      will be used to assert the until condition.
		 */
		protected $until_condition = true;

		/**
		 * @var int|callable|string A callable or an int value representing the reschedule or an action hook name.
		 *      time.
		 */
		protected $each = 600;

		/**
		 * @var array|callable|int An array of arguments that will be passed to the
		 *                         schedule action or, if scheduling a callable on an action, the number
		 *                         of arguments the callable will be passed from the action hook.
		 */
		protected $args = array();

		/**
		 * @var int If rescheduling on an hook this priority will be used to hook the action.
		 */
		protected $priority = 10;

		/**
		 * Instances and returns the object.
		 *
		 * @param string|callable $hook_or_callable The name of the action hook to reschedule or a callable to hook into the action.
		 *
		 * @return tad_Reschedule The instance of this class to keep the chain
		 *                        going.
		 */
		public static function instance( $hook_or_callable ) {
			if ( ! is_string( $hook_or_callable ) && ! is_callable( $hook_or_callable ) ) {
				throw new InvalidArgumentException( 'Reschedule either an action hook or a callable.' );
			}

			$instance       = new self();
			$instance->hook = $hook_or_callable;

			return $instance;
		}

		/**
		 * @param bool|callable $condition Either a boolean or a boolean cast-able
		 *                                 value or a callable returning a boolean.
		 *                                 This will decide if the reschedule will
		 *                                 happen or not.
		 *
		 * @return tad_Reschedule The instance of this class to keep the chain
		 *                        going.
		 */
		public function until( $condition ) {
			$this->until_condition = $condition;

			return $this;
		}

		/**
		 * @param int|callable|string $interval_or_hook Either an int value representing the time
		 *                                              offset in seconds or a callable returning
		 *                                              and int; if rescheduling on an action then the action hook name (e.g.
		 *                                              "shutdown").
		 *
		 * @return tad_Reschedule The instance of this class to keep the chain
		 *                        going.
		 */
		public function each( $interval_or_hook ) {
			if ( ! ( is_string( $interval_or_hook ) || is_callable( $interval_or_hook ) || is_numeric( $interval_or_hook ) ) ) {
				throw new InvalidArgumentException( 'Interval must be an action hook name, an int value or a callable.' );
			}

			$this->each = $interval_or_hook;

			return $this;
		}

		/**
		 * @param array|callable|int $args Either an array of args that will be passed
		 *                                 to the scheduled action or a callable
		 *                                 returning an array of args; if scheduling an action the number of args that
		 *                                 will be passed to the called function.
		 *
		 * @return tad_Reschedule The instance of this class to keep the chain
		 *                        going.
		 */
		public function with_args( $args ) {
			if ( ! ( is_int( $args ) || is_callable( $args ) || is_array( $args ) || is_null( $args ) ) ) {
				throw new InvalidArgumentException( 'Arguments must be an array, a callable or an int if scheduling on an hook.' );
			}
			$this->args = $args;

			return $this;
		}

		/**
		 * PHP 5 introduces a destructor concept similar to that of other
		 * object-oriented languages, such as C++. The destructor method will be
		 * called as soon as all references to a particular object are removed or
		 * when the object is explicitly destroyed or in any order in shutdown
		 * sequence.
		 *
		 * Like constructors, parent destructors will not be called implicitly by
		 * the engine. In order to run a parent destructor, one would have to
		 * explicitly call parent::__destruct() in the destructor body.
		 *
		 * Note: Destructors called during the script shutdown have HTTP headers
		 * already sent. The working directory in the script shutdown phase can be
		 * different with some SAPIs (e.g. Apache).
		 *
		 * Note: Attempting to throw an exception from a destructor (called in the
		 * time of script termination) causes a fatal error.
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
			if ( is_callable( $this->hook ) ) {
				// an action name
				$this->hook_action();
			} else {
				// a time offset in seconds
				$this->schedule_event();
			}
		}

		private function hook_action() {
			$args   = $this->get_action_args();
			$action = $this->get_action_hook_name();
			add_action( $action, $this->hook, $this->priority, $args );
		}

		private function schedule_event() {
			$args    = $this->get_schedule_args();
			$current = $this->get_schedule_timestamp();
			wp_schedule_single_event( $current, $this->hook, array( $args ) );
		}

		/**
		 * @return int
		 */
		private function get_action_args() {
			$args = is_numeric( $this->args ) ? (int) $this->args : 1;

			return $args;
		}

		/**
		 * @return callable|int|string
		 */
		private function get_action_hook_name() {
			$action = isset( $this->each ) && ! is_numeric( $this->each ) ? $this->each : 'shutdown';

			return $action;
		}

		/**
		 * @return array|callable|mixed
		 */
		private function get_schedule_args() {
			$args = is_callable( $this->args ) ? call_user_func( $this->args ) : $this->args;
			$args = is_array( $args ) ? $args : array( $args );

			return $args;
		}

		/**
		 * @return int
		 */
		private function get_schedule_timestamp() {
			$time_offset = is_callable( $this->each ) ? (int) call_user_func( $this->each ) : (int) $this->each;
			$current     = time() + $time_offset;

			return $current;
		}

		/**
		 * @param int $priority The priority that will be used to hook the callable function to the action hook.
		 */
		public function priority( $priority ) {
			if ( ! is_int( $priority ) ) {
				throw new InvalidArgumentException( 'Priority must be an int value' );
			}
			$this->priority = (int) $priority;
		}
	}
}
