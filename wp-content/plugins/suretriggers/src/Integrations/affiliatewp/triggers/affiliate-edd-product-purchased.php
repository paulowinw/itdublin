<?php
/**
 * AffiliateEddProductPurchased.
 * php version 5.6
 *
 * @category AffiliateEddProductPurchased
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\AffiliateWP\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use EDD_Payment;
use EDD_Customer;

if ( ! class_exists( 'AffiliateEddProductPurchased' ) ) :

	/**
	 * AffiliateEddProductPurchased
	 *
	 * @category AffiliateEddProductPurchased
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AffiliateEddProductPurchased {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'AffiliateWP';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'affiliate_edd_product_purchased';

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
		 *
		 * @return array
		 */
		public function register( $triggers ) {
			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'Affiliate Refers a sale of Easy Digital Downloads Product', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'affwp_insert_referral',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $referral_id Referral ID.
		 *
		 * @return void
		 */
		public function trigger_listener( $referral_id ) {

			if ( ! function_exists( 'affwp_get_referral' ) || ! function_exists( 'affwp_get_affiliate' ) || ! function_exists( 'affwp_get_dynamic_affiliate_coupons' ) ) {
				return;
			}

			if ( ! class_exists( '\EDD_Payment' ) || ! class_exists( '\EDD_Customer' ) ) {
				return;
			}
			
			$referral = affwp_get_referral( $referral_id );

			if ( 'edd' !== (string) $referral->context ) {
				return;
			}

			$edd_payment_id = $referral->reference;
			$payment        = new EDD_Payment( $edd_payment_id );
			$customer       = new EDD_Customer( $payment->customer_id );
			$user_id        = $customer->user_id;

			$referral        = affwp_get_referral( $referral->referral_id );
			$affiliate       = affwp_get_affiliate( $referral->affiliate_id );
			$affiliate_data  = get_object_vars( $affiliate );
			$user_data       = WordPress::get_user_context( $user_id );
			$referral_data   = get_object_vars( $referral );
			$dynamic_coupons = affwp_get_dynamic_affiliate_coupons( $referral->affiliate_id, false );
			$cart_details    = $payment->cart_details;
			$payment         = get_object_vars( $payment );
			$context         = array_merge(
				$user_data,
				$affiliate_data,
				$referral_data,
				$dynamic_coupons,
				$payment
			);
			foreach ( $cart_details as $detail ) {
				$context['product'] = $detail['id'];
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => $affiliate->user_id,
					'context'    => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	AffiliateEddProductPurchased::get_instance();

endif;
