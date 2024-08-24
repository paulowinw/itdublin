<?php
/**
 * UserCancelsMembership.
 * php version 5.6
 *
 * @category UserCancelsMembership
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
 * UserCancelsMembership
 *
 * @category UserCancelsMembership
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UserCancelsMembership {


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
	public $trigger = 'lifterlms_user_cancels_membership';

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
			'label'         => __( 'User cancels a membership', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'llms_subscription_cancelled_by_student',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 20,
			'accepted_args' => 2,
		];

		return $triggers;

	}

	/**
	 * Trigger listener.
	 *
	 * @param object $order order.
	 * @param int    $user_id User id.
	 * @return void
	 */
	public function trigger_listener( $order, $user_id ) {
		if ( method_exists( $order, 'get' ) ) {
			$order_id                   = $order->get( 'id' );
			$context                    = array_merge( WordPress::get_post_context( $order_id ), WordPress::get_user_context( $user_id ) );
			$context['membership_id']   = get_post_meta( $order_id, '_llms_product_id', true );
			$context['membership_name'] = get_post_meta( $order_id, '_llms_product_title', true );

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

}

UserCancelsMembership::get_instance();
