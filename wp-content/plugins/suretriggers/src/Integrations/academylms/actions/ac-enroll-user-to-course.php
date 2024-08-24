<?php
/**
 * AcEnrollUserToCourse.
 * php version 5.6
 *
 * @category AcEnrollUserToCourse
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
 * AcEnrollUserToCourse
 *
 * @category AcEnrollUserToCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AcEnrollUserToCourse extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'AcademyLMS';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'ac_enroll_user_to_course';

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

		global $wpdb;

		if ( ! class_exists( '\Academy\Helper' ) ) {
			return;
		}

		if ( is_email( $user_id ) ) {
			$user = get_user_by( 'email', $user_id );
			if ( $user ) {
				$user_id     = $user->ID;
				$is_enrolled = \Academy\Helper::is_enrolled( $course_id, $user_id, 'completed' );
				if ( ! $is_enrolled ) {
					$enrolled = \Academy\Helper::do_enroll( $course_id, $user_id );
					if ( $enrolled ) {
						$result = $wpdb->get_results(
							$wpdb->prepare(
								"SELECT * FROM {$wpdb->prefix}posts
						WHERE post_type = %s AND post_parent = %d 
						order by ID DESC LIMIT 1",
								'academy_enrolled',
								$course_id 
							) 
						);

						$context                    = WordPress::get_user_context( $result[0]->post_author );
						$context['course_data']     = WordPress::get_post_context( $result[0]->post_parent );
						$context['enrollment_data'] = $result[0];

						return $context;
					} else {
						$error = [
							'status'   => esc_attr__( 'Error', 'suretriggers' ),
							'response' => esc_attr__( 'Something went wrong.', 'suretriggers' ),
						];
						return $error;
					}
				} else {
					$error = [
						'status'   => esc_attr__( 'Error', 'suretriggers' ),
						'response' => esc_attr__( 'User is already enrolled to course.', 'suretriggers' ),
					];
					return $error;
				}
			} else {
				$error = [
					'status'   => esc_attr__( 'Error', 'suretriggers' ),
					'response' => esc_attr__( 'User not exists.', 'suretriggers' ),
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
	}
}

AcEnrollUserToCourse::get_instance();
