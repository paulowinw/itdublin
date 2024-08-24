<?php
/**
 * GiveWPUserCancelsRecurringDonation.
 * php version 5.6
 *
 * @category GiveWPUserCancelsRecurringDonation
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GiveWP\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use Give_Subscription;

if ( ! class_exists( 'GiveWPUserCancelsRecurringDonation' ) ) :

	/**
	 * GiveWPUserCancelsRecurringDonation
	 *
	 * @category GiveWPUserCancelsRecurringDonation
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class GiveWPUserCancelsRecurringDonation {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'GiveWP';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'givewp_user_cancels_recurring_donation';

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
				'label'         => __( 'User Cancels Recurring Donation', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'give_subscription_updated',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param string $status Status.
		 * @param int    $row_id Row ID.
		 * @param array  $data Data.
		 * @param string $where Where.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $status, $row_id, $data, $where ) {
			if ( ! class_exists( 'Give_Subscription' ) ) {
				return;
			}

			$subscription = new Give_Subscription( $row_id );
			
			if ( is_object( $subscription ) && property_exists( $subscription, 'form_id' ) ) {
				$give_form_id = $subscription->form_id;

				$context['form_id']      = $give_form_id;
				$context['subscription'] = $subscription;
				if ( 'cancelled' === (string) $data['status'] ) {
					AutomationController::sure_trigger_handle_trigger(
						[
							'trigger' => $this->trigger,
							'context' => $context,
						]
					);
				}
			}
		}

	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	GiveWPUserCancelsRecurringDonation::get_instance();

endif;
