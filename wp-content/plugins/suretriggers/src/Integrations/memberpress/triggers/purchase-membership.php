<?php
/**
 * PurchaseMembership.
 * php version 5.6
 *
 * @category PurchaseMembership
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MemberPress\Triggers;

use MeprTransaction;
use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\MemberPress\MemberPress;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'PurchaseMembership' ) ) :

	/**
	 * PurchaseMembership
	 *
	 * @category PurchaseMembership
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class PurchaseMembership {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'MemberPress';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'mepr-event-transaction-completed';

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
				'label'         => __( 'Membership Purchased', 'suretriggers' ),
				'action'        => $this->trigger,
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;

		}


		/**
		 * Trigger listener
		 * This will trigger for both recurring and non-recurring transactions.
		 *
		 * @param object $event Event data.
		 *
		 * @return void
		 */
		public function trigger_listener( $event ) {

			$subscription = $event->get_data();
			$context      = array_merge(
				WordPress::get_user_context( $subscription->user_id ),
				MemberPress::get_membership_context( $subscription )
			);

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
	PurchaseMembership::get_instance();

endif;
