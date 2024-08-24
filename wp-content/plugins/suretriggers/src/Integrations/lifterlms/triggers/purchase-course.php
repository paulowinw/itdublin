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

namespace SureTriggers\Integrations\LifterLMS\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;


/**
 * PurchaseCourse
 *
 * @category PurchaseCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class PurchaseCourse {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'LifterLMS';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'lifterlms_purchase_course';

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
			'label'         => __( 'User purchase course', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'lifterlms_order_complete',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 1,
		];

		return $triggers;

	}

	/**
	 * Trigger listener.
	 *
	 * @param int $order_id order id.
	 * @return void
	 */
	public function trigger_listener( $order_id ) {

		$user_id                      = get_post_meta( $order_id, '_llms_user_id', true );
		$context['course_id']         = get_post_meta( $order_id, '_llms_product_id', true );
		$context['course_name']       = get_post_meta( $order_id, '_llms_product_title', true );
		$context['course_amount']     = get_post_meta( $order_id, '_llms_original_total', true );
		$context['currency']          = get_post_meta( $order_id, '_llms_currency', true );
		$context ['order']            = WordPress::get_post_context( $order_id );
		$context['order_type']        = get_post_meta( $order_id, '_llms_order_type', true );
		$context['trial_offer']       = get_post_meta( $order_id, '_llms_trial_offer', true );
		$context['billing_frequency'] = get_post_meta( $order_id, '_llms_billing_frequency', true );
		$context                      = array_merge( $context, WordPress::get_user_context( $user_id ) );

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);

	}

}

PurchaseCourse::get_instance();
