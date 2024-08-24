<?php
/**
 * PurchaseCourse.
 * php version 5.6
 *
 * @category PurchaseCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LearnDash\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\LearnDash\LearnDash;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'PurchaseCourse' ) ) :


	/**
	 * PurchaseCourse
	 *
	 * @category PurchaseCourse
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class PurchaseCourse {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'LearnDash';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ld_purchase_course';

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
				'label'         => __( 'Course Purchased', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'woocommerce_thankyou',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;

		}


		/**
		 *  Trigger listener
		 *
		 * @param int $order_id order ID.
		 *
		 * @return void
		 */
		public function trigger_listener( $order_id ) {

			if ( ! $order_id ) {
				return;
			}

			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				return;
			}

			$items = $order->get_items();

			if ( count( $items ) > 1 ) {
				return;
			}

			foreach ( $items as $item ) {
				if ( empty( get_post_meta( $item->get_product_id(), '_related_course', true ) ) ) {
					return;
				}
			}

			$context = LearnDash::get_purchase_course_context( $order );

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
	PurchaseCourse::get_instance();

endif;
