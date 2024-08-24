<?php
/**
 * UserSubscriptionUpdated.
 * php version 5.6
 *
 * @category UserSubscriptionUpdated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WoocommerceSubscriptions\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use WC_Subscription;
use WC_Customer;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'UserSubscriptionUpdated' ) ) :

	/**
	 * UserSubscriptionUpdated
	 *
	 * @category UserSubscriptionUpdated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserSubscriptionUpdated {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WoocommerceSubscriptions';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'wc_subscription_updated';

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
				'label'         => __( 'User Subscription Updated', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'woocommerce_subscription_status_updated',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 30,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param object $subscription Subscription.
		 * @param string $new_status New Status.
		 * @param string $old_status Old status.
		 *
		 * @return void
		 */
		public function trigger_listener( $subscription, $new_status, $old_status ) {

			if ( ! class_exists( '\WC_Subscription' ) ) {
				return;
			}
			if ( ! $subscription instanceof \WC_Subscription ) {
				return;
			}

			$items = $subscription->get_items();
			
			foreach ( $items as $item ) {
				$context['variation_id'] = $item->get_variation_id();
				$context['product_id']   = $item->get_product_id();
				if ( $item->get_variation_id() ) {
					$context['subscription'] = $item->get_variation_id();
				} else {
					$context['subscription'] = $item->get_product_id();
				}
				$context['variation_name'] = $item->get_name();
				$context['product_name']   = get_the_title( $item->get_product_id() );
			}

			$subscription_status            = $subscription->get_status();
			$subscription_start_date        = $subscription->get_date_created();
			$subscription_next_payment_date = $subscription->get_date( 'next_payment' );
			
			$context['subscription_data'] = [
				'start_date'        => $subscription_start_date,
				'next_payment_date' => $subscription_next_payment_date,
			];
			$context['subscription_id']   = $subscription->get_id();
			$context['user']              = WordPress::get_user_context( $subscription->get_user_id() );
			// Get WooCommerce checkout instance for the user details.
			WC()->customer = new WC_Customer( $subscription->get_user_id() );
			$checkout      = WC()->checkout();
			if ( $checkout ) {
				$billing_fields = $checkout->get_checkout_fields( 'billing' );
				$field_details  = [];
				foreach ( $billing_fields as $key => $field ) {
					$field_id                   = $key;
					$field_value                = get_user_meta( $subscription->get_user_id(), $field_id, true );
					$field_details[ $field_id ] = $field_value;
				}
				$context['user_billing_data'] = $field_details;
			}

			$context['new_status'] = $subscription_status;
			$context['old_status'] = $old_status;
			$context['status']     = 'wc-' . $subscription_status;

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
	UserSubscriptionUpdated::get_instance();

endif;
