<?php
/**
 * BMNewMessageReceived.
 * php version 5.6
 *
 * @category BMNewMessageReceived
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BetterMessages\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'BMNewMessageReceived' ) ) :

	/**
	 * BMNewMessageReceived
	 *
	 * @category BMNewMessageReceived
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class BMNewMessageReceived {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'BetterMessages';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'bm_new_message_received';

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
				'label'         => __( 'New Message Received', 'suretriggers' ),
				'action'        => 'bm_new_message_received',
				'common_action' => 'better_messages_message_sent',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $message Message object.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $message ) {
			if ( ! function_exists( 'Better_Messages' ) || ! property_exists( $message, 'id' ) ) {
				return;
			}

			$message = Better_Messages()->functions->get_message( $message->id );
			if ( is_object( $message ) ) {
				$message = get_object_vars( $message );
			}
			$context           = $message;
			$context['sender'] = WordPress::get_user_context( $message->sender_id );
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	BMNewMessageReceived::get_instance();

endif;
