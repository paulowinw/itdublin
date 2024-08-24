<?php
/**
 * SlUnenrollUserFromCourse.
 * php version 5.6
 *
 * @category SlUnenrollUserFromCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\SenseiLMS\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Sensei_Course_Manual_Enrolment_Provider;
use Sensei_Utils;

/**
 * SlUnenrollUserFromCourse
 *
 * @category SlUnenrollUserFromCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SlUnenrollUserFromCourse extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'SenseiLMS';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'sl_unenroll_user_from_course';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Unenroll User From Course', 'suretriggers' ),
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
	 * @throws Exception Throws exception.
	 *
	 * @return array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$course_id  = $selected_options['course'];
		$user_email = $selected_options['wp_user_email'];
		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );
			if ( $user ) {
				$user_id = $user->ID;
			} else {
				$error = [
					'status'   => esc_attr__( 'Error', 'suretriggers' ),
					'response' => esc_attr__( 'User not found with specified email address.', 'suretriggers' ),
				];
				return $error;
			}
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Please enter valid email address.', 'suretriggers' ),
			];

			return $error;
		}
		if ( ! class_exists( 'Sensei_Course_Manual_Enrolment_Provider' ) || ! class_exists( 'Sensei_Utils' ) ) {
			return [];
		}
		$manual_enrolment_provider = Sensei_Course_Manual_Enrolment_Provider::instance();
		if ( ! $manual_enrolment_provider ) {
			return [];
		}
		if ( '-1' === $course_id ) {
			$courses = Sensei_Utils::sensei_activity_ids(
				[
					'user_id' => $user_id,
					'type'    => 'sensei_course_status',
					'status'  => 'any',
				] 
			);
			if ( ! is_array( $courses ) ) {
				return [];
			}
			// Unroll user in courses.
			foreach ( $courses as $course_id ) {
				$manual_enrolment_provider->withdraw_learner( $user_id, $course_id );
			}
		} else {
			$manual_enrolment_provider->withdraw_learner( $user_id, $course_id );
		}
		$response = [
			'status'   => esc_attr__( 'Success', 'suretriggers' ),
			'response' => esc_attr__( 'User unenrolled successfully', 'suretriggers' ),
		];
		return $response;
	}
}

SlUnenrollUserFromCourse::get_instance();
