<?php
/**
 * UserRegistersIn.
 * php version 5.6
 *
 * @category UserRegistersIn
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\UltimateMember\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'UserRegistersIn' ) ) :

	/**
	 * UserRegistersIn
	 *
	 * @category UserRegistersIn
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserRegistersIn {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'UltimateMember';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_registers_form';

		use SingletonLoader;

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 */
		public function __construct() {
			add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
		}

		/**
		 * Register action.
		 *
		 * @param array $triggers trigger data.
		 * @return array
		 */
		public function register( $triggers ) {

			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'User Registers With A Form', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'um_registration_complete',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int   $user_id User ID.
		 * @param array $um_args arguments.
		 * @return void
		 */
		public function trigger_listener( $user_id, $um_args ) {
			
			if ( ! isset( $um_args['form_id'] ) ) {
				return;
			}
			
			$data    = [
				'form_id' => absint( $um_args['form_id'] ),
				WordPress::get_user_context( $user_id ),
			];
			$context = $data;
			
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'user_id' => $user_id,
					'context' => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserRegistersIn::get_instance();

endif;
