<?php
/**
 * AffiliateSpecificTypeReferralPaid.
 * php version 5.6
 *
 * @category AffiliateSpecificTypeReferralPaid
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

if ( ! class_exists( 'AffiliateSpecificTypeReferralPaid' ) ) :

	/**
	 * AffiliateSpecificTypeReferralPaid
	 *
	 * @category AffiliateSpecificTypeReferralPaid
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AffiliateSpecificTypeReferralPaid {

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
		public $trigger = 'affiliate_specific_type_referral_paid';

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
				'label'         => __( 'Affiliate Referral of Specific Type Paid', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'affwp_set_referral_status',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $referral_id Referral ID.
		 * @param string $new_status New Status.
		 * @param string $old_status Old Status.
		 *
		 * @return void
		 */
		public function trigger_listener( $referral_id, $new_status, $old_status ) {

			if ( ! function_exists( 'affwp_get_referral' ) || ! function_exists( 'affwp_get_affiliate' ) ) {
				return;
			}
			
			if ( (string) $new_status === (string) $old_status || 'paid' !== (string) $new_status ) {
				return;
			}
			
			$referral       = affwp_get_referral( $referral_id );
			$affiliate      = affwp_get_affiliate( $referral->affiliate_id );
			$affiliate_data = get_object_vars( $affiliate );
			$user_data      = WordPress::get_user_context( $affiliate->user_id );
			$referral_data  = get_object_vars( $referral );
			$context        = array_merge(
				$user_data,
				$affiliate_data,
				$referral_data
			);

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
	AffiliateSpecificTypeReferralPaid::get_instance();

endif;
