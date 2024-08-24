<?php
/**
 * UserCreditsExceedsSpecificAmount.
 * php version 5.6
 *
 * @category UserCreditsExceedsSpecificAmount
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\AdvancedCoupons\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'UserCreditsExceedsSpecificAmount' ) ) :

	/**
	 * UserCreditsExceedsSpecificAmount
	 *
	 * @category UserCreditsExceedsSpecificAmount
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserCreditsExceedsSpecificAmount {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'AdvancedCoupons';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_credit_exceeds_specific_amount';

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
				'label'         => __( 'User Store Credit Exceeds Specific Amount', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'acfw_create_store_credit_entry',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $data Data.
		 * @return void
		 */
		public function trigger_listener( $data ) {

			if ( 'decrease' === $data['type'] ) {
				return;
			}

			$user_id = ( isset( $data['user_id'] ) ) ? intval( $data['user_id'] ) : 0;

			if ( 0 === $user_id ) {
				return;
			}

			$added_amount = floatval( $data['amount'] );

			if ( function_exists( 'ACFWF' ) ) {
				$cur_balance                   = apply_filters( 'acfw_filter_amount', \ACFWF()->Store_Credits_Calculate->get_customer_balance( $user_id ) );
				$trigger_data['credit_amount'] = $cur_balance;
			}

			$trigger_data['added_amount'] = $added_amount;

			$context = array_merge( $trigger_data, WordPress::get_user_context( $user_id ) );

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
	UserCreditsExceedsSpecificAmount::get_instance();

endif;
