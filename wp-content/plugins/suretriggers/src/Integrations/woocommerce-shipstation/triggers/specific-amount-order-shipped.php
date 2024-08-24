<?php
/**
 * SpecificAmountOrderShipped.
 * php version 5.6
 *
 * @category SpecificAmountOrderShipped
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WoocommerceShipstation\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Integrations\WooCommerce\WooCommerce;

if ( ! class_exists( 'SpecificAmountOrderShipped' ) ) :

	/**
	 * SpecificAmountOrderShipped
	 *
	 * @category SpecificAmountOrderShipped
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class SpecificAmountOrderShipped {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WoocommerceShipstation';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'wc_specific_amount_order_shipped';

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
				'label'         => __( 'Order For Specific Amount Shipped', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'woocommerce_shipstation_shipnotify',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param object $order Order.
		 * @param array  $argu Arg.
		 *
		 * @return void
		 */
		public function trigger_listener( $order, $argu ) {

			if ( ! $order ) {
				return;
			}
	
			if ( method_exists( $order, 'get_user_id' ) ) {
				$user_id = $order->get_user_id();
				if ( 0 === $user_id ) {
					return;
				}

				if ( method_exists( $order, 'get_id' ) ) {
					$order_id = $order->get_id();

					$order_detail = WooCommerce::get_order_context( $order_id );
					if ( is_array( $order_detail ) ) {
						$context = array_merge(
							$order_detail,
							WordPress::get_user_context( $user_id )
						);

						if ( method_exists( $order, 'get_total' ) ) {
							$order_total      = $order->get_total();
							$context['price'] = $order_total;
						}

						$context['shipping_tracking_number'] = $argu['tracking_number'];
						$context['shipping_carrier']         = $argu['carrier'];
						$timestamp                           = strtotime( (string) $argu['ship_date'] );
						/**
						 *
						 * Ignore line
						 *
						 * @phpstan-ignore-next-line
						 */
						$date                 = date_i18n( get_option( 'date_format' ), $timestamp );
						$context['ship_date'] = $date;

						AutomationController::sure_trigger_handle_trigger(
							[
								'trigger'    => $this->trigger,
								'wp_user_id' => $user_id,
								'context'    => $context,
							]
						);
					}
				}
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	SpecificAmountOrderShipped::get_instance();

endif;
