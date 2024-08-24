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

namespace SureTriggers\Integrations\TutorLMS\Triggers;

use Exception;
use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
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
		public $integration = 'TutorLMS';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'tutor_lms_purchase_course';

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
				'label'         => __( 'User purchases a course [WooCommerce]', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'woocommerce_order_status_completed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 *
		 * Trigger listener
		 *
		 * @param int $order_id order ID.
		 * @return void
		 * @throws Exception Exception.
		 */
		public function trigger_listener( $order_id ) {
			if ( ! $order_id ) {
				throw new Exception( 'Invalid Order ID.' );
			}

			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				throw new Exception( 'Invalid Order.' );
			}

			$context             = WordPress::get_user_context( $order->get_customer_id() );
			$context['order_id'] = $order_id;
			$items               = $order->get_items();
			$context['currency'] = $order->get_currency();
			$context['amount']   = $order->get_total();
			$product_ids         = [];
			$product_name        = [];
			$course_id           = [];
			$course_name         = [];

			foreach ( $items as $item ) {
				$product_name[] = $item->get_name();
				$product_id     = $item->get_product_id();
				$product_ids[]  = $item->get_product_id();
				$course_args    = [
					'post_type'  => tutor()->course_post_type,
					'meta_query' => [
						[
							'key'     => '_tutor_course_product_id',
							'value'   => $product_id,
							'compare' => '==',
						],
					],
				];
				$courses        = get_posts( $course_args );
				$course_id[]    = $courses[0]->ID;
				$course_name[]  = $courses[0]->post_title;
			}

			$context['course']       = implode( ',', $course_id );
			$context['course_name']  = implode( ',', $course_name );
			$context['product_id']   = implode( ',', $product_ids );
			$context['product_name'] = implode( ',', $product_name );

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
