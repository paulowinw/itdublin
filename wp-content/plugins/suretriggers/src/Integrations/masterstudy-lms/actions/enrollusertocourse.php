<?php
/**
 * EnrollUserToCourse.
 * php version 5.6
 *
 * @category EnrollUserToCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use STM_LMS\STM_LMS_Mails;

/**
 * EnrollUserToCourse
 *
 * @category EnrollUserToCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class EnrollUserToCourse extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MasterStudyLms';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'enroll_user_to_course';

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

		if ( is_email( $user_id ) ) {
			$user = get_user_by( 'email', $user_id );

			if ( $user ) {
				$user_id = $user->ID;
			} else {
				$email    = $user_id;
				$username = sanitize_title( $email );
				$password = wp_generate_password();

				$user_id = wp_create_user( $username, $password, $email );

				$subject = esc_html__( 'Login credentials for your course', 'suretriggers' );

				$site_url = get_bloginfo( 'url' );
				$message  = sprintf(
					esc_html__( 'Login: %1$s Password: %2$s Site URL: %3$s', 'suretriggers' ),
					$username,
					$password,
					$site_url
				);

				if ( class_exists( '\STM_LMS_Mails' ) ) {
					// The STM_LMS_Mails class exists, so we can use it.
					\STM_LMS_Mails::wp_mail_text_html();
					\STM_LMS_Mails::send_email( $subject, $message, $email, [], 'stm_lms_new_user_creds', compact( 'username', 'password', 'site_url' ) );
					\STM_LMS_Mails::remove_wp_mail_text_html();
				}
			}
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Please enter valid email address.', 'suretriggers' ),
			];

			return $error;
		}

		// Enroll the user in the course if they are not already enrolled.
		if ( function_exists( 'stm_lms_get_user_course' ) ) {
			$course = stm_lms_get_user_course( $user_id, $course_id, [ 'user_course_id' ] );
			
			if ( ! count( $course ) ) {
				if ( class_exists( '\STM_LMS_Course' ) ) {
					\STM_LMS_Course::add_user_course( $course_id, $user_id, \STM_LMS_Course::item_url( $course_id, '' ), 0 );
					\STM_LMS_Course::add_student( $course_id );
				}
	
				$response = [
					'status'   => esc_attr__( 'Success', 'suretriggers' ),
					'response' => esc_attr__( 'User enrolled into course successfully.', 'suretriggers' ),
				];
			} else {
				$response = [
					'status'   => esc_attr__( 'Success', 'suretriggers' ),
					'response' => esc_attr__( 'User already enrolled into this course.', 'suretriggers' ),
				];
			}
			return $response;
		}
	}
}

EnrollUserToCourse::get_instance();
