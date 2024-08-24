<?php
/**
 * DeliveryDateChanged.
 * php version 5.6
 *
 * @category DeliveryDateChanged
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\ServicesForSureCart\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'DeliveryDateChanged' ) ) :

	/**
	 * DeliveryDateChanged
	 *
	 * @category DeliveryDateChanged
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class DeliveryDateChanged {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'ServicesForSureCart';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ss_delivery_date_changed';

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
				'label'         => __( 'Delivery Date Changed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'surelywp_services_delivery_date_change',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int $service_id Service ID.
		 * @param int $delivery_date Delivery Date.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $service_id, $delivery_date ) {
			global $wpdb;

			$result  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}surelywp_sv_services WHERE delivery_date IS NOT NULL AND service_id = %d", $service_id ), ARRAY_A );
			$context = $result;
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
	DeliveryDateChanged::get_instance();

endif;
