<?php
/**
 * LpEnrollUserCourse.
 * php version 5.6
 *
 * @category LpEnrollUserCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\LearnPress\LearnPress;
use SureTriggers\Integrations\WordPress\WordPress;

/**
 * LpEnrollUserCourse
 *
 * @category LpEnrollUserCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class LpEnrollUserCourse extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'LearnPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'lp_enroll_user_course';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Enroll User To Course', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @psalm-suppress UndefinedMethod
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$course_id = $selected_options['course'];
		$user_id   = $selected_options['wp_user_email'];
		if ( ! function_exists( 'learn_press_get_user' ) || ! function_exists( 'learn_press_default_order_status' ) || 
		! function_exists( 'learn_press_get_course' ) || ! function_exists( 'learn_press_get_ip' ) ||
		! function_exists( 'learn_press_get_user_agent' ) || ! class_exists( '\LP_User_Item_Course' ) || 
		! class_exists( 'LP_Order' ) ) {
			return;
		}

		if ( is_email( $user_id ) ) {
			$user_data = get_user_by( 'email', $user_id );

			if ( $user_data ) {
				$user_id = $user_data->ID;
				$user    = learn_press_get_user( $user_id );
				$course  = learn_press_get_course( $course_id );
				if ( $user->has_enrolled_course( $course_id ) ) {
					throw new Exception( 'User already enrolled in course.' );
				}
				if ( $course && $course->exists() ) {
					$order = new LP_Order();
					$order->set_customer_note( __( 'Order created by SureTriggers', 'suretriggers' ) );
					$order->set_status( learn_press_default_order_status( 'lp-' ) );
					$order->set_total( 0 );
					$order->set_subtotal( 0 );
					$order->set_user_ip_address( learn_press_get_ip() );
					$order->set_user_agent( learn_press_get_user_agent() );
					$order->set_created_via( 'SureTriggers' );
					$order->set_user_id( $user_id );
					$order_id                      = $order->save();
					$order_item                    = [];
					$order_item['order_item_name'] = $course->get_title();
					$order_item['item_id']         = $course_id;
					$order_item['quantity']        = 1;
					$order_item['subtotal']        = 0;
					$order_item['total']           = 0;
					$item_id                       = $order->add_item( $order_item, 1 );
					$order->update_status( 'completed' );
					$user_item_data               = [
						'user_id' => $user->get_id(),
						'item_id' => $course->get_id(),
						'ref_id'  => $order_id,
					];
					$user_item_data['status']     = 'enrolled';
					$user_item_data['graduation'] = 'in-progress';
					$user_item_data['start_time'] = current_time( 'mysql', true );
					$user_item_new                = new \LP_User_Item_Course( $user_item_data );
					$result                       = $user_item_new->update();
		
					if ( ! $result ) {
						throw new Exception( 'Can not enroll user to course.' );
					}
					do_action( 'learnpress/user/course-enrolled', $order_id, $course->get_id(), $user->get_id() ); // @phpcs:ignore
					return array_merge(
						WordPress::get_user_context( $user->get_id() ),
						LearnPress::get_lpc_course_context( $course->get_id() )
					);
				} else {
					throw new Exception( 'Course not found.' );
				}
			} else {
				throw new Exception( 'User not found' );
			}
		} else {
			throw new Exception( 'Please enter valid email address.' );
		}
	}
}

LpEnrollUserCourse::get_instance();
