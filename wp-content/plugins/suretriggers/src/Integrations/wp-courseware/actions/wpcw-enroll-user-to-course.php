<?php
/**
 * WpcwEnrollUserToCourse.
 * php version 5.6
 *
 * @category WpcwEnrollUserToCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

/**
 * WpcwEnrollUserToCourse
 *
 * @category WpcwEnrollUserToCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WpcwEnrollUserToCourse extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WPCourseware';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wpcw_enroll_user_to_course';

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

		if ( ! function_exists( 'wpcw_get_courses' ) ) {
			throw new Exception( 'wpcw_get_courses does not exists.' );
		}

		if ( is_email( $user_id ) ) {
			$user = get_user_by( 'email', $user_id );

			if ( $user ) {
				$user_id            = $user->ID;
				$course_list        = wpcw_get_courses();
				$enroll_course_list = [];

				if ( ! empty( $course_list ) ) {
					foreach ( $course_list as $course ) {
						if ( intval( $course->course_post_id ) == intval( $course_id ) ) {
							$enroll_course_list[ $course->course_id ] = $course->course_id;
							continue;
						}
					}
				}

				if ( ! function_exists( 'WPCW_courses_syncUserAccess' ) ) {
					throw new Exception( 'WPCW_courses_syncUserAccess does not exists.' );
				}

				WPCW_courses_syncUserAccess( $user_id, $enroll_course_list, 'add' );

				$context = WordPress::get_user_context( $user_id );
				if ( function_exists( 'wpcw_get_course' ) ) {
					$context['course'] = wpcw_get_course( $course_id );
				}
				return $context;
			}
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Please enter valid email address.', 'suretriggers' ),
			];

			return $error;
		}
	}
}

WpcwEnrollUserToCourse::get_instance();
