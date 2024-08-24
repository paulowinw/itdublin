<?php
/**
 * EventController.
 * php version 5.6
 *
 * @category EventController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Controllers;

use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'EventController' ) ) :

	/**
	 * EventController
	 *
	 * @category EventController
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class EventController {

		use SingletonLoader;

		/**
		 * Registered triggers.
		 *
		 * @var array
		 */
		public $triggers = [];

		/**
		 * Registered actions.
		 *
		 * @var array
		 */
		public $actions = [];

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'prepare_events' ], 0 );
		}


		/**
		 * Prepare events and actions.
		 *
		 * @return void
		 */
		public function prepare_events() {
			$this->triggers = apply_filters( 'sure_trigger_register_trigger', $this->triggers );
			$this->actions  = apply_filters( 'sure_trigger_register_action', $this->actions );

			AutomationController::get_instance()->register_trigger_listener();
		}
	}

	EventController::get_instance();

endif;




