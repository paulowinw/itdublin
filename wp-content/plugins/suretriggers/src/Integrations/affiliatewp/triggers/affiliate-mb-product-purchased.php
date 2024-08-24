<?php
/**
 * AffiliateMbProductPurchased.
 * php version 5.6
 *
 * @category AffiliateMbProductPurchased
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
use MeprTransaction;

if ( ! class_exists( 'AffiliateMbProductPurchased' ) ) :

	/**
	 * AffiliateMbProductPurchased
	 *
	 * @category AffiliateMbProductPurchased
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AffiliateMbProductPurchased {

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
		public $trigger = 'affiliate_mb_product_purchased';

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
				'label'         => __( 'MemberPress Product Purchased using Affiliate Referral', 'suretriggers' ),
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
			
			$referral = affwp_get_referral( $referral_id );
			global $wpdb;

			if ( 'memberpress' !== (string) $referral->context ) {
				return;
			}

			if ( ! class_exists( '\MeprTransaction' ) ) {
				return;
			}

			$reference_id = $referral->reference;
			$transaction  = new MeprTransaction( $reference_id );

			$user_id = $transaction->user_id;

			$referral        = affwp_get_referral( $referral->referral_id );
			$membership_id   = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT product_id FROM
            {$wpdb->prefix}mepr_transactions WHERE id = %d",
					$referral->reference
				)
			);
			$affiliate       = affwp_get_affiliate( $referral->affiliate_id );
			$affiliate_data  = get_object_vars( $affiliate );
			$user_data       = WordPress::get_user_context( $user_id );
			$referral_data   = get_object_vars( $referral );
			$dynamic_coupons = affwp_get_dynamic_affiliate_coupons( $referral->affiliate_id, false );

			$context = array_merge(
				$user_data,
				$affiliate_data,
				$referral_data,
				$dynamic_coupons
			);

			$context['product']      = $membership_id;
			$context['product_name'] = get_the_title( $membership_id );

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
	AffiliateMbProductPurchased::get_instance();

endif;
