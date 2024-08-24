<?php
/**
 * UserLogsIn.
 * php version 5.6
 *
 * @category UserLogsIn
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

if ( ! class_exists( 'UserLogsIn' ) ) :

	/**
	 * UserLogsIn
	 *
	 * @category UserLogsIn
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserLogsIn {

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
		public $trigger = 'user_logsin_form';

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
				'label'         => __( 'User LogsIn With A Form', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'um_user_login',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 9,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $um_args arguments.
		 * @return void
		 */
		public function trigger_listener( $um_args ) {
			
			if ( ! isset( $um_args['form_id'] ) ) {
				return;
			}
	
			if ( function_exists( 'um_user' ) ) {
				$user_id = um_user( 'ID' );
			} else {
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
	UserLogsIn::get_instance();

endif;
