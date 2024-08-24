<?php
/**
 * UserAccountActivated.
 * php version 5.6
 *
 * @category UserAccountActivated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyPress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'UserAccountActivated' ) ) :

	/**
	 * UserAccountActivated
	 *
	 * @category UserAccountActivated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserAccountActivated {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'BuddyPress';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_account_activated';

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
				'label'         => __( "The user's account is activated", 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'bp_core_activated_user',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $user_id User ID.
		 * @param object $key Key.
		 * @param object $user User.
		 * @return void
		 */
		public function trigger_listener( $user_id, $key, $user ) {

			$context['user'] = WordPress::get_user_context( $user_id );

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
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
	UserAccountActivated::get_instance();

endif;
