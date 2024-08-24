<?php
/**
 * ResetUserCourseProgress.
 * php version 5.6
 *
 * @category ResetUserCourseProgress
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\TutorLMS\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * ResetUserCourseProgress
 *
 * @category ResetUserCourseProgress
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ResetUserCourseProgress extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'TutorLMS';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'tlms_reset_user_course_progress';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Reset User Course Progress', 'suretriggers' ),
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
	 * @return array|void
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$course_id  = isset( $selected_options['courses'] ) ? $selected_options['courses'] : '0';
		$user_email = ( isset( $selected_options['wp_user_email'] ) ) ? $selected_options['wp_user_email'] : '';

		if ( ! function_exists( 'tutor_utils' ) ) {
			return [];
		}
		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );
			if ( $user ) {
				$user_id = $user->ID;

				if ( ! $course_id || ! is_numeric( $course_id ) || ! tutor_utils()->is_enrolled( $course_id, $user_id ) ) {
					throw new Exception( 'Invalid Course ID or Access Denied.' );
				}
				tutor_utils()->delete_course_progress( $course_id, $user_id );
				$response = [
					'status'   => esc_attr__( 'Success', 'suretriggers' ),
					'response' => esc_attr__( 'User course progress has been successfully reset.', 'suretriggers' ),
				];
				return $response;
			} else {
				throw new Exception( 'User not found.' );
			}
		} else {
			throw new Exception( 'Enter valid email address.' );
		}
	}
}

ResetUserCourseProgress::get_instance();
