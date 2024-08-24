<?php
/**
 * SlEnrollUserToCourse.
 * php version 5.6
 *
 * @category SlEnrollUserToCourse
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
use WP_Query;

/**
 * SlEnrollUserToCourse
 *
 * @category SlEnrollUserToCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SlEnrollUserToCourse extends AutomateAction {


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
	public $action = 'sl_enroll_user_to_course';

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
		if ( ! class_exists( 'Sensei_Course_Manual_Enrolment_Provider' ) ) {
			return [];
		}
		$manual_enrolment_provider = Sensei_Course_Manual_Enrolment_Provider::instance();

		if ( ! $manual_enrolment_provider ) {
			return [];
		}
		if ( '-1' === $course_id ) {

			$query = new WP_Query(
				[
					'post_type'   => 'course',
					'post_status' => 'publish',
					'fields'      => 'ids',
				]
			);

			$courses = $query->get_posts();

			// Enroll user in courses.
			foreach ( $courses as $course_id ) {
				$manual_enrolment_provider->enrol_learner( $user_id, $course_id );
			}
		} else {
			$manual_enrolment_provider->enrol_learner( $user_id, $course_id );
		}
		$response = [
			'status'   => esc_attr__( 'Success', 'suretriggers' ),
			'response' => esc_attr__( 'User enrolled successfully', 'suretriggers' ),
		];
		return $response;
	}
}

SlEnrollUserToCourse::get_instance();
