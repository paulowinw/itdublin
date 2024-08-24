<?php
/**
 * UserReceivesStoreCredit.
 * php version 5.6
 *
 * @category UserReceivesStoreCredit
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
use SureTriggers\Integrations\WooCommerce\WooCommerce;

if ( ! class_exists( 'UserReceivesStoreCredit' ) ) :

	/**
	 * UserReceivesStoreCredit
	 *
	 * @category UserReceivesStoreCredit
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserReceivesStoreCredit {


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
		public $trigger = 'user_receives_store_credit';

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

			if ( ! isset( $data['type'] ) || 'decrease' === $data['type'] ) {
				return;
			}

			$user_id = ( isset( $data['user_id'] ) ) ? intval( $data['user_id'] ) : 0;

			if ( 0 === $user_id ) {
				return;
			}

			$balance = floatval( $data['amount'] );

			$trigger_data['credit_amount'] = $balance;

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
	UserReceivesStoreCredit::get_instance();

endif;
