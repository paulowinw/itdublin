<?php
/**
 * AffiliatePayoutMade.
 * php version 5.6
 *
 * @category AffiliatePayoutMade
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\EasyAffiliate\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use EasyAffiliate\Lib\ModelFactory;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'AffiliatePayoutMade' ) ) :

	/**
	 * AffiliatePayoutMade
	 *
	 * @category AffiliatePayoutMade
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AffiliatePayoutMade {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'EasyAffiliate';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'affiliate_payout_made';

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
				'label'         => __( 'Payout made for Affiliate', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'esaf_event_payment-added',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $event Event.
		 * @return void|array
		 */
		public function trigger_listener( $event ) {

			if ( ! is_object( $event ) || ! property_exists( $event, 'rec' ) ) {
				return;
			}

			if ( ! is_object( $event->rec ) || ! property_exists( $event->rec, 'evt_id_type' ) || ! property_exists( $event->rec, 'evt_id' ) ) {
				return;
			}
			
			if ( empty( $event->rec->evt_id_type ) && empty( $event->rec->evt_id ) && 'payment' !== $event->rec->evt_id_type ) {
				return;
			}

			// Check if the properties exist before accessing them.
			if ( property_exists( $event->rec, 'evt_id_type' ) && property_exists( $event->rec, 'evt_id' ) ) {
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$data = ModelFactory::fetch( $event->rec->evt_id_type, $event->rec->evt_id );
				if ( is_object( $data ) && property_exists( $data, 'rec' ) ) {
					$affiliate = get_object_vars( $data->rec );
					if ( is_numeric( $affiliate['affiliate_id'] ) ) {
						$id      = (int) $affiliate['affiliate_id'];
						$context = array_merge( WordPress::get_user_context( $id ), $affiliate );
						
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
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	AffiliatePayoutMade::get_instance();

endif;
