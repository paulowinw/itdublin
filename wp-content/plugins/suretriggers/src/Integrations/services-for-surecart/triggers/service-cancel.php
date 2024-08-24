<?php
/**
 * ServiceCancel.
 * php version 5.6
 *
 * @category ServiceCancel
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\ServicesForSureCart\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'ServiceCancel' ) ) :

	/**
	 * ServiceCancel
	 *
	 * @category ServiceCancel
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class ServiceCancel {


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
		public $trigger = 'ss_service_cancel';

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
				'label'         => __( 'Service Cancel', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'surelywp_services_cancel',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int $service_id Service ID.
		 * @param int $order_id Order ID.
		 * @param int $product_id Product ID.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $service_id, $order_id, $product_id ) {
			global $wpdb;

			$result  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}surelywp_sv_services WHERE service_id = %d AND order_id = %d AND product_id = %d", $service_id, $order_id, $product_id ), ARRAY_A );
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
	ServiceCancel::get_instance();

endif;
