<?php
/**
 * LpRemoveUserCourse.
 * php version 5.6
 *
 * @category LpRemoveUserCourse
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
 * LpRemoveUserCourse
 *
 * @category LpRemoveUserCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class LpRemoveUserCourse extends AutomateAction {

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
	public $action = 'lp_remove_user_course';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove User From Course', 'suretriggers' ),
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
		if ( ! function_exists( 'learn_press_delete_user_data' ) ) {
			return;
		}

		if ( is_email( $user_id ) ) {
			$user_data = get_user_by( 'email', $user_id );

			if ( $user_data ) {
				$user_id = $user_data->ID;
				// remove user course.
				learn_press_delete_user_data( $user_id, $course_id );
				return array_merge(
					WordPress::get_user_context( $user_id ),
					LearnPress::get_lpc_course_context( $course_id )
				);
			} else {
				throw new Exception( 'User not found' );
			}
		} else {
			throw new Exception( 'Please enter valid email address.' );
		}
	}
}

LpRemoveUserCourse::get_instance();
