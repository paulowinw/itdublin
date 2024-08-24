<?php
/**
 * EDDPurchaseProduct.
 * php version 5.6
 *
 * @category EDDPurchaseProduct
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\EDD\Triggers;

use EDD_Payment;
use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\EDD\EDD;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'EDDPurchaseProduct' ) ) :

	/**
	 * EDDPurchaseProduct
	 *
	 * @category EDDPurchaseProduct
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class EDDPurchaseProduct {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'EDD';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'edd_user_purchase_product';

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
				'label'         => __( 'Order Created', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'edd_complete_purchase',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int $payment_id The entry that was just created.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $payment_id ) {
			
			$payment = new EDD_Payment( $payment_id );

			if ( empty( $payment->cart_details ) ) {
				return;
			}
			$context = EDD::get_product_purchase_context( $payment );

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
	EDDPurchaseProduct::get_instance();

endif;
