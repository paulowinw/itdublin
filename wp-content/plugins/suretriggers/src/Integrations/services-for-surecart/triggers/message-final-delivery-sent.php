<?php
/**
 * MessageFinalDeliverySent.
 * php version 5.6
 *
 * @category MessageFinalDeliverySent
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\ServicesForSureCart\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'MessageFinalDeliverySent' ) ) :

	/**
	 * MessageFinalDeliverySent
	 *
	 * @category MessageFinalDeliverySent
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class MessageFinalDeliverySent {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'ServicesForSureCart';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ss_message_final_delivery_sent';

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
				'label'         => __( 'Final Delivery Sent', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'surelywp_services_final_delivery_send',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array $message_data Message Data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $message_data ) {
			
			$context = $message_data;
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
	MessageFinalDeliverySent::get_instance();

endif;
